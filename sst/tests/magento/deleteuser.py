# -*- Mode: Python -*-
# vi:si:et:sw=4:sts=4:ts=4

from sst import actions as a

import common
import data

common.go_to('admin/')
a.assert_title_contains('Admin')

common.fill_user_form(data.admin)
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

common.admin_nav('Customers', 'Manage Customers')
common.customer_edit_by_email(data.user['email_address'][1])

# for some reason there are two buttons? I can only find one in the source
buttons = a.get_elements(title='Delete Customer')
# wait=False lets us interact with the alert
a.click_button(buttons[0], wait=False)
# confirm the dialog when clicking to delete
a.accept_alert('Are you sure you want to do this?')

a.click_link(a.get_element(text='Log Out'))
