# -*- Mode: Python -*-
# vi:si:et:sw=4:sts=4:ts=4

from sst import actions as a

import common
import data

common.go_to()
a.assert_title_contains('Home page')

user = common.User(data.user)

user.login()

# search for tv
a.write_textfield('search', 'tv')
common.click_button_by_title('Search')

# click it
common.click_button_by_title('Add to Cart')

common.click_button_by_title('Proceed to Checkout', multiple=True)

a.wait_for(a.assert_displayed, 'checkout-step-billing')

a.click_button(a.get_element_by_xpath(
    "//div[@id='checkout-step-billing']"
    "//button[@title='Continue']"))

a.wait_for(a.assert_displayed, 'checkout-step-shipping_method')

a.click_button(a.get_element_by_xpath(
    "//div[@id='checkout-step-shipping_method']"
    "//button"))

a.wait_for(a.assert_displayed, 'p_method_getfinancing')
a.click_element(a.get_element(id='p_method_getfinancing'))
a.click_button(a.get_element_by_xpath(
    "//div[@id='checkout-step-payment']"
    "//button"))


raise

user.logout()
