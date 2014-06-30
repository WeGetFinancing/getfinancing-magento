# -*- Mode: Python -*-
# vi:si:et:sw=4:sts=4:ts=4

from sst.actions import *

import common
import data

go_to('http://magento19.localhost/')
assert_title_contains('Home page')

user = common.User(data.user)

user.login()

user.add_billing_address()

user.logout()
