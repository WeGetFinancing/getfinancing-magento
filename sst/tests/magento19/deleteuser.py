# -*- Mode: Python -*-
# vi:si:et:sw=4:sts=4:ts=4

from sst.actions import *

import common
import data

go_to('http://magento19.localhost/admin/')
assert_title_contains('Admin')

common.fill_user_form(data.admin)
button = get_element_by_xpath("//input[@title='Login']")
click_button(button)

# do away with messages if any
try:
    close = get_element_by_xpath(
        "//div[@id='message-popup-window']"
        "//a[@title='close']")
except AssertionError:
    pass
if close:
    click_link(close)

common.admin_nav('Customers', 'Manage Customers')

raise
