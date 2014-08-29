# -*- Mode: Python -*-
# vi:si:et:sw=4:sts=4:ts=4

# configure the getfinancing magento module

import getfinancing
import data

admin = getfinancing.Admin(data.admin)
admin.login()

admin.configure_getfinancing()

admin.logout()
