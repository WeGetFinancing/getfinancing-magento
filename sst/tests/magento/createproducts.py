# -*- Mode: Python -*-
# vi:si:et:sw=4:sts=4:ts=4

import common
import data

admin = common.Admin(data.admin)
admin.login()

admin.add_product('80-inch OLED TV', '1295.99', '1')
admin.add_product('iPhone 6S', '950.55', '2')
admin.add_product('iPhone Kryptonite Cover', '99.95', '3')

admin.logout()
