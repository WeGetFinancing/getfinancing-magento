# -*- Mode: Python -*-
# vi:si:et:sw=4:sts=4:ts=4

import os

from sst import actions as a

def get_version():
    """
    Return the desired magento version four-tuple.
    """
    version = os.environ.get('MAGENTO', '19')

    return {
        '17': (1, 7, 0, 0),
        '18': (1, 8, 0, 0),
        '19': (1, 9, 0, 0),
    }.get(version, (1, 9, 0, 0))

def go_to(url=''):
    version = os.environ.get('MAGENTO', '19')

    a.go_to('http://magento%s.localhost/%s' % (version, url))

def click_element_by_xpath(xpath, multiple=False):
    buttons = a.get_elements_by_xpath(xpath)

    if len(buttons) == 0:
        raise AssertionError, "Could not identify element: 0 elements found"

    if len(buttons) > 1 and not multiple:
        raise AssertionError, \
            "Could not identify element: %d elements found" % len(buttons)

    button = buttons[0]

    a.click_button(button)


def click_button_by_name(name, multiple=False):
    click_element_by_xpath("//button[@name='%s']" % name, multiple)


def click_button_by_title(title, multiple=False):
    click_element_by_xpath("//button[@title='%s']" % title, multiple)


def monkey_patch_sst():
    if 'tel' not in a._textfields:
        a._textfields = tuple(list(a._textfields) + ['tel', ])


def fill_user_form(user):
    monkey_patch_sst()
    for key, (vtype, value) in user.items():
        # print 'fill', key, vtype, value
        try:
            el = a.get_element(id=key)
        except AssertionError:
            continue

        if vtype == 'text':
            a.write_textfield(el, value)
        elif vtype == 'password':
            a.write_textfield(el, value, check=False)
        elif vtype == 'option':
            a.set_dropdown_value(el, value)

def admin_nav(first, second):
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

def customer_edit_by_email(email):
    edit_link = a.get_element_by_xpath(
        "//table[@id='customerGrid_table']"
        "//td[%s]"
        "//.."
        "//a" % (xpath_contains_text(email))
    )
    a.click_link(edit_link)


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
        print 'THOMAS:', args, kwargs
        l = []
        try:
            l = a.get_elements(*args, **kwargs)
        except AssertionError, e:
            print 'THOMAS: assertion', e
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
        fill_user_form(self._data)

        if self._version >= (1, 9, 0, 0):
            title = 'Register'
        else:
            title = 'Submit'
        button = a.get_element_by_xpath("//button[@title='%s']" % title)
        a.click_button(button)

    def login(self):
        self._account_menu_1_9('Log In')

        # fill in data on login screen
        data = {
            'email': self._data['email_address'],
            'pass': self._data['password'],
        }
        fill_user_form(data)

        button = a.get_element_by_xpath("//button[@title='Login']")
        a.click_button(button)

        e = a.get_element_by_xpath("//p[@class='welcome-msg']")
        assert e.text == u'WELCOME, MARY BERNARD!', "Login failed: %r" % e.text

    def logout(self):
        self._account_menu_1_9('Log Out')


    def add_billing_address(self):
        # from the account page
        manage_link = a.get_element_by_xpath("//a[text()='Manage Addresses']")
        a.click_link(manage_link)

        fill_user_form(self._data)
        button = a.get_element_by_xpath("//button[@title='Save Address']")
        a.click_button(button)


