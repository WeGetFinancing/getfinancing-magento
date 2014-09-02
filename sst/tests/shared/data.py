# -*- Mode: Python -*-
# vi:si:et:sw=4:sts=4:ts=4

import os

user = {
    # user registration data
    'firstname':     ('text', 'Mary'),
    'lastname':      ('text', 'Bernard'),
    'email_address': ('text', 'mary@example.com'),
    'password':      ('password', 'marybernard'),

    # address data
    'telephone': ('text', '1234567890'),
    'street_1':  ('text', 'PO BOX 190648'),
    'region_id': ('option', 'Alaska'),
    'zip':       ('text', '99519'),
    'country':   ('option', 'United States'),
    'city':      ('text', 'Anchorage'),

}

user['confirmation'] = user['password']

admin = {
    'username':      ('text', 'admin'),
    'login':         ('password', os.environ.get(
                        'MAGENTO_ADMIN_PASSWORD', 'adminpassword')),
}
