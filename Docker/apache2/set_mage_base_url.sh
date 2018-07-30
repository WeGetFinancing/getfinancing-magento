#!/bin/bash

source /var/www/.env

read -p "New base_url value (include protocol (http|https) and final '/' i.e. http://new-url.com/): " URL

URL_QRY="update core_config_data set value='$URL' where path like '%base_url%';"
mysql -u root -p$MYSQL_ROOT_PASSWORD -e "use $MYSQL_DATABASE; $URL_QRY;" 

NEW_VALUES=$(mysql -u root -p$MYSQL_ROOT_PASSWORD -e "use magento1; select * from core_config_data where path like '%base_url%'")

echo "New URL values"
for i in "$NEW_VALUES"; do
    echo "$i"
done

