# -*- Mode: Python -*-
# vi:si:et:sw=4:sts=4:ts=4

user = {
    # user registration data
    'firstname':     ('text', 'Mary'),
    'lastname':      ('text', 'Bernard'),
    'email_address': ('text', 'mary@email.com'),
    'password':      ('password', 'marybernard'),

    # address data
    'telephone': ('text', '1234567890'),
    'street_1':  ('text', 'PO BOX 190101'),
    'region_id': ('option', 'Alaska'),
    'zip':       ('text', 'PO BOX 190101'),
    'country':   ('option', 'United States'),

}

user['confirmation'] = user['password']

admin = {
    'username':      ('text', 'admin'),
    'login':         ('password', 'adminpassword'),
}
