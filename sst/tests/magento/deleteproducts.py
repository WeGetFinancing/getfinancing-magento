# -*- Mode: Python -*-
# vi:si:et:sw=4:sts=4:ts=4

import common
import data

admin = common.Admin(data.admin)
admin.login()

admin.delete_products()

admin.logout()
