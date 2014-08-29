# -*- Mode: Python -*-
# vi:si:et:sw=4:sts=4:ts=4

import os

import common

from sst import actions as a

class Admin(common.Admin):

    def configure_getfinancing(self, merch_id=None, username=None,
        password=None):

        if not merch_id:
            merch_id = os.getenv('GF_MERCH_ID')
        if not username:
            username = os.getenv('GF_USERNAME')
        if not password:
            password = os.getenv('GF_PASSWORD')

        self.navigate('System', 'Configuration')
        self.navigate_configuration('Payment Methods')

        # GetFinancing could be open already
        try:
            a.assert_displayed('payment_getfinancing_active')
        except AssertionError:
            common.click_link_by_text('GetFinancing')

        a.set_dropdown_value('payment_getfinancing_active', 'Yes')
        a.write_textfield('payment_getfinancing_merch_id', merch_id)
        a.write_textfield('payment_getfinancing_username', username)
        a.write_textfield('payment_getfinancing_password', password)
        common.click_button_by_title('Save Config', multiple=True)

        self.allow_symlinks()
        self.enable_log()


