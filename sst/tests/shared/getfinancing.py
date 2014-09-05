# -*- Mode: Python -*-
# vi:si:et:sw=4:sts=4:ts=4

import os

import common

from sst import actions as a

class Admin(common.Admin):

    def configure_getfinancing(self, merch_id=None, username=None,
        password=None, postback_username=None, postback_password=None):

        if not merch_id:
            merch_id = os.getenv('GF_MERCH_ID')
        if not username:
            username = os.getenv('GF_USERNAME')
        if not password:
            password = os.getenv('GF_PASSWORD')
        if not postback_username:
            postback_username = os.getenv('GF_POSTBACK_USERNAME')
        if not postback_password:
            postback_password = os.getenv('GF_POSTBACK_PASSWORD')

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
        a.write_textfield('payment_getfinancing_postback_username',
            postback_username)
        a.write_textfield('payment_getfinancing_postback_password',
            postback_password)
        self.click_save_config()

        self.allow_symlinks()
        self.enable_log()


