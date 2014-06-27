# -*- Mode: Python -*-
# vi:si:et:sw=4:sts=4:ts=4

from sst import actions as a

def fill_user_form(user):
    for key, (vtype, value) in user.items():
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
        "//li[%s]/*/*[text()[contains(.,'%s')]]/.." % (
            xpath_contains_class('level0'), first))
    a.click_link(hover_link)

    final_link = a.get_element_by_xpath(
        "//li[%s]/*/*[text()[contains(.,'%s')]]/.." % (
            xpath_contains_class('level1'), second))
    a.click_link(final_link)

# See
# http://stackoverflow.com/questions/8808921/selecting-a-css-class-with-xpath
def xpath_contains_class(name):
    return 'contains(concat(" ", normalize-space(@class), " "), " %s ")' % name
