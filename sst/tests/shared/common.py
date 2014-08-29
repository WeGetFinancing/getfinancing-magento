# -*- Mode: Python -*-
# vi:si:et:sw=4:sts=4:ts=4

import os

from sst import actions as a

def get_version():
    """
    Return the desired magento version four-tuple.
    """
    version = os.environ.get('MAGENTO_VERSION', '19')

    return {
        '14': (1, 4, 0, 0),
        '15': (1, 5, 0, 0),
        '16': (1, 6, 0, 0),
        '17': (1, 7, 0, 1),
        '18': (1, 8, 0, 0),
        '19': (1, 9, 0, 0),
    }.get(version, (1, 9, 0, 0))

def go_to(url=''):
    protocol = os.environ.get('MAGENTO_PROTOCOL', 'http')
    domain = os.environ.get('MAGENTO_DOMAIN_SUFFIX', '.localhost')

    vtuple = get_version()
    hostname = os.environ.get('MAGENTO_HOSTNAME',
        'magento-' + '-'.join(str(v) for v in vtuple))

    a.go_to('%s://%s%s/%s' % (protocol, hostname, domain, url))

def click_element_by_xpath(xpath, multiple=False, wait=True):
    """
    Click the element given by the xpath.

    @param multiple: if True, allow multiple elements and click the first one.
    @param wait:     if True, wait for a page with body element available.
    """
    elements = a.get_elements_by_xpath(xpath)

    if len(elements) == 0:
        raise AssertionError, "Could not identify element: 0 elements found"

    if len(elements) > 1 and not multiple:
        raise AssertionError, \
            "Could not identify element: %d elements found" % len(elements)

    element = elements[0]

    a.click_element(element, wait=wait)

def click_link_by_text(text, multiple=False, wait=True):
    click_element_by_xpath("//a[%s]" % (
        xpath_contains_text(text)),
        multiple=multiple, wait=wait)

def click_button_by_name(name, multiple=False, wait=True):
    click_element_by_xpath("//button[@name='%s']" % name,
        multiple=multiple, wait=wait)


def click_link_by_title(title, multiple=False, wait=True):
    click_element_by_xpath("//a[@title='%s']" % title,
        multiple=multiple, wait=wait)


def click_button_by_title(title, multiple=False, wait=True):
    click_element_by_xpath("//button[@title='%s']" % title,
        multiple=multiple, wait=wait)


def monkey_patch_sst():
    if 'tel' not in a._textfields:
        a._textfields = tuple(list(a._textfields) + ['tel', ])


def fill_user_form(user, version=None):
    monkey_patch_sst()

    keys = user.keys()
    # for 1.8 and possibly lower, country needs to be filled before region_id
    # is a dropdown
    keys.sort()

    for key in keys:
        (vtype, value) = user[key]
        print 'fill', key, vtype, value
        try:
            el = a.get_element(id=key)
        except AssertionError:
            continue

        # before 1.9 region was a text field
        #if version < (1, 9, 0, 0) and key == 'region_id':
        #    el = a.get_element(id='region')
        #    a.write_textfield(el, value)
        if vtype == 'text':
            a.write_textfield(el, value)
        elif vtype == 'password':
            a.write_textfield(el, value, check=False)
        elif vtype == 'option':
            a.wait_for(a.assert_displayed, el)
            a.set_dropdown_value(el, value)


# See
# http://stackoverflow.com/questions/8808921/selecting-a-css-class-with-xpath
def xpath_contains_class(name):
    return 'contains(concat(" ", normalize-space(@class), " "), " %s ")' % name


def xpath_contains_text(text):
    return "text()[contains(.,'%s')]" % text


def get_elements_multiple(args_kwargs):
    """
    Call multiple get_elements calls, returning a list of all results.

    Useful when waiting for different possible elements to appear.
    """
    ret = []

    for args, kwargs in args_kwargs:
        l = []
        try:
            l = a.get_elements(*args, **kwargs)
        except AssertionError:
            pass
        ret.extend(l)

    if not ret:
        raise AssertionError('Could not identify elements: 0 elements found')

    return ret


class User(object):
    def __init__(self, data, version=None):
        if not version:
            version = get_version()
        self._data = data
        self._version = version

    def _account_menu_1_9(self, item):
        account = a.get_element_by_xpath(
            "//a[contains(@class, 'skip-account')]")
        a.click_link(account)

        link = a.get_element_by_xpath("//a[@title='%s']" % item)
        a.click_link(link)


    def register(self):
        if self._version >= (1, 9, 0, 0):
            self._account_menu_1_9('Register')
        else:
            a.click_link(a.get_element(text='My Account'))
            button = a.get_element_by_xpath(
                "//button[@title='Create an Account']")
            a.click_button(button)


        # fill in data for mary and register
        fill_user_form(self._data, version=self._version)

        if self._version >= (1, 9, 0, 0):
            title = 'Register'
        else:
            title = 'Submit'
        button = a.get_element_by_xpath("//button[@title='%s']" % title)
        a.click_button(button)

    def login(self):
        if self._version >= (1, 9, 0, 0):
            self._account_menu_1_9('Log In')
        else:
            a.click_link(a.get_element(text='Log In'))

        # fill in data on login screen
        data = {
            'email': self._data['email_address'],
            'pass': self._data['password'],
        }
        fill_user_form(data, version=self._version)

        button = a.get_element_by_xpath("//button[@title='Login']")
        a.click_button(button)

        e = a.get_element_by_xpath("//p[@class='welcome-msg']")
        assert e.text.upper() == u'WELCOME, MARY BERNARD!', \
            "Login failed: %r" % e.text

    def logout(self):
        if self._version >= (1, 9, 0, 0):
            self._account_menu_1_9('Log Out')
        else:
            a.click_link(a.get_element(text='Log Out'))


    def add_billing_address(self):
        # from the account page
        manage_link = a.get_element_by_xpath("//a[text()='Manage Addresses']")
        a.click_link(manage_link)

        fill_user_form(self._data, version=self._version)
        button = a.get_element_by_xpath("//button[@title='Save Address']")
        a.click_button(button)


class Admin(object):
    def __init__(self, data, version=None):
        if not version:
            version = get_version()
        self._data = data
        self._version = version


    def login(self):
        go_to('admin/')
        a.assert_title_contains('Admin')

        fill_user_form(self._data)
        button = a.get_element_by_xpath("//input[@title='Login']")
        a.click_button(button)

        # do away with messages if any
        try:
            close = a.get_element_by_xpath(
                "//div[@id='message-popup-window']"
                "//a[@title='close']")
        except AssertionError:
            pass
        if close:
            a.click_link(close)

    def logout(self):
        a.click_link(a.get_element(text='Log Out'))

    def navigate(self, first, second):
        # no idea how to hover, but there's a false click handler so that works
        hover_link = a.get_element_by_xpath(
            "//li[%s]/*/*[%s]/.." % (
                xpath_contains_class('level0'),
                xpath_contains_text(first))
        )
        a.click_link(hover_link)

        final_link = a.get_element_by_xpath(
            "//li[%s]/*/*[%s]/.." % (
                xpath_contains_class('level1'),
                xpath_contains_text(second))
        )
        a.click_link(final_link)

    def customer_edit_by_email(self, email):
        edit_link = a.get_element_by_xpath(
            "//table[@id='customerGrid_table']"
            "//td[%s]"
            "//.."
            "//a" % (xpath_contains_text(email))
        )
        a.click_link(edit_link)

    def add_product(self, name, price, sku):
        self.navigate('Catalog', 'Manage Products')
        click_button_by_title('Add Product', multiple=True)
        click_button_by_title('Continue', multiple=True)


        a.write_textfield('name', name)
        a.write_textfield('description', name)
        a.write_textfield('short_description', name)
        a.write_textfield('sku', sku)
        a.write_textfield('weight', '1')
        a.set_dropdown_value('status', 'Enabled')
        click_button_by_title('Save and Continue Edit', multiple=True)

        a.set_dropdown_value('tax_class_id', 'None')
        a.write_textfield('price', price)
        click_button_by_title('Save and Continue Edit', multiple=True)

        a.wait_for(a.get_element_by_xpath, "//div[@id='messages']//span[%s]" %
            xpath_contains_text('product has been saved')
        )
        # Clicking reset clears the message; allowing us to assert again later
        # to make sure the change is made
        click_button_by_title('Reset', multiple=True)

        click_link_by_title('Inventory')
        a.write_textfield('inventory_qty', '9999999')
        a.set_dropdown_value('inventory_stock_availability', 'In Stock')
        click_button_by_title('Save', multiple=True)
        a.wait_for(a.get_element_by_xpath, "//div[@id='messages']//span[%s]" %
            xpath_contains_text('product has been saved')
        )

    def delete_products(self):
        self.navigate('Catalog', 'Manage Products')

        select_all_link = a.get_element_by_xpath(
            "//a[%s]" % (
                xpath_contains_text('Select All'))
        )
        a.click_link(select_all_link)
        a.set_dropdown_value('productGrid_massaction-select', 'Delete')
        click_button_by_title('Submit', wait=False)
        a.accept_alert('Are you sure?')

    def rebuild_indexes(self):
        self.navigate('System', 'Index Management')

        select_all_link = a.get_element_by_xpath(
            "//a[%s]" % (
                xpath_contains_text('Select All'))
        )
        a.click_link(select_all_link)
        a.set_dropdown_value('indexer_processes_grid_massaction-select',
            'Reindex Data')
        click_button_by_title('Submit', wait=False)

    def navigate_configuration(self, option):
        """
        From System > Configuration, navigate to an option in the left menu.
        """
        click_element_by_xpath("//a/span[%s]" % (
            xpath_contains_text(option)),
            multiple=False, wait=True)

    def allow_symlinks(self):
        # configure magento to allow symlinks in templates
        # useful for allowing modman to work
        self.navigate('System', 'Configuration')
        self.navigate_configuration('Developer')
        # TemplateSettings could be open already
        try:
            a.assert_displayed('dev_template_allow_symlink')
        except AssertionError:
            click_link_by_text('Template Settings')
        a.set_dropdown_value('dev_template_allow_symlink', 'Yes')
        click_button_by_title('Save Config', multiple=True)
