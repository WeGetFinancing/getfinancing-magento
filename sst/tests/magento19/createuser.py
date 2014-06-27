# -*- Mode: Python -*-
# vi:si:et:sw=4:sts=4:ts=4

from sst.actions import *

import data

def fill_user_form(user):
    for key, (vtype, value) in user.items():
        try:
            el = get_element(id=key)
        except AssertionError:
            continue

        if vtype == 'text':
            write_textfield(el, value)
        elif vtype == 'password':
            write_textfield(el, value, check=False)
        elif vtype == 'option':
            set_dropdown_value(el, value)


go_to('http://magento19.localhost/')
assert_title_contains('Home page')

# go to registration
account = get_element_by_xpath("//a[contains(@class, 'skip-account')]")
click_link(account)

link = get_element_by_xpath("//a[@title='Register']")
click_link(link)

# fill in data for mary and register
fill_user_form(data.user)

button = get_element_by_xpath("//button[@title='Register']")
click_button(button)

# add a billing address
manage_link = get_element_by_xpath("//a[text()='Manage Addresses']")
click_link(manage_link)

fill_user_form(data.user)
button = get_element_by_xpath("//button[@title='Save Address']")
click_button(button)

raise
