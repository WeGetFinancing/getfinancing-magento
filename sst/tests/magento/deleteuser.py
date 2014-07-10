# -*- Mode: Python -*-
# vi:si:et:sw=4:sts=4:ts=4

from sst import actions as a

import common
import data

admin = common.Admin(data.admin)
admin.login()

admin.navigate('Customers', 'Manage Customers')
admin.customer_edit_by_email(data.user['email_address'][1])

# for some reason there are two buttons? I can only find one in the source
buttons = a.get_elements(title='Delete Customer')
# wait=False lets us interact with the alert
a.click_button(buttons[0], wait=False)
# confirm the dialog when clicking to delete
a.accept_alert('Are you sure you want to do this?')

admin.logout()
