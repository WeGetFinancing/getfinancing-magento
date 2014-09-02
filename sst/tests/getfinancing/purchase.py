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
common.click_button_by_title('Add to Cart', multiple=True)

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

# go through gf process
a.set_wait_timeout(60.0)

a.wait_for(a.switch_to_frame, 'popup')
a.wait_for(a.assert_displayed, 'id_ssn4')
a.write_textfield('id_ssn4', '6220')
a.click_element('id_agree')
a.click_button('submit-id-btn_find')

# we could either adopt the loan or start from scratch; check
el = a.wait_for(common.get_elements_multiple, [
    ([], { 'id': 'id_salary_current' }),
    ([], { 'id': 'comment' }),
    ])

if el[0].get_attribute('id') == 'id_salary_current':
    a.wait_for(a.assert_displayed, 'id_salary_current')
    a.write_textfield('id_salary_current', '120,000')
    a.click_button('submit-id-btn_find')

a.wait_for(a.assert_displayed, 'leave-comment')
common.click_button_by_name('offer_index', multiple=True)

a.wait_for(a.assert_displayed, 'uniform-id_agree')
a.click_element('id_agree')
a.click_button('submit-id-btn_complete')

a.wait_for(a.get_element_by_xpath, "//input[@value='Open']")

# complete lendingclub part
a.wait_for(a.switch_to_window, 1)
a.click_button('master_nextButton')

a.click_button('master_getYourRateButton')

a.click_button('br-button-defaultloan')

a.click_button('master_getLoanTermsButton')

a.click_button('master_getBankAccountButton')

a.click_button('master_doneButton')

a.close_window()

# close the popup
a.switch_to_window(0)

# this is done automatically
# a.wait_for(a.get_element_by_xpath, "//button[@class='btn-close']")

a.switch_to_frame()
a.wait_for(a.get_element, css_class='col-main', text_regex='Congratulations')

user.logout()
