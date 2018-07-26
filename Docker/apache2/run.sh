#!/bin/bash

echo "------------  Making env variables available for every run, i.e. Mysql DB is $MYSQL_DATABASE ------------ "
source /var/www/.env

# Check database connection or wait until it's up
while [ "$(mysql --connect-timeout=1 -h $MYSQL_HOST -u root -p$MYSQL_ROOT_PASSWORD -e "show databases")" == "" ]; do 
    sleep 1; # Wait to be sure the mysql connection is possible
done

mkdir -p /var/run/apache2 # Create the APACHE_RUN_DIR (just to avoid warnings)

rm /etc/apache2/sites-enabled/000-default.conf
cp /magento1.conf /etc/apache2/sites-enabled/000-default.conf

sed -i "s#ServerName www.example.com#ServerName $APACHE_HOST#g" /etc/apache2/sites-enabled/000-default.conf
cd /var/www/html/ 

HAS_DB=$(mysql -u root -p$MYSQL_ROOT_PASSWORD -e "show databases" | grep $MYSQL_DATABASE | tr -d " \t\n\r")
HAS_TABLES=$(mysql -u root -p$MYSQL_ROOT_PASSWORD -e "use $MYSQL_DATABASE; show tables;" 2>/dev/null | wc -l)
HAS_MAGE=$(ls /var/www/html/magento)
if [ -n "$HAS_MAGE" ] && [ -n "HAS_DB" ] && [ "$HAS_TABLES" > 0 ]; then
    echo " --------- MAGENTO is already installed ---------"
    echo " --------- MAGENTO has the Database and tables installed too ---------"
else
    echo " --------- DB, Magento files or tables ar missing, Installing MAGENTO (please wait) ---------"
    /bin/magento-install.sh 
fi

APACHE_SIGNATURE=$(grep ServerSignature /etc/apache2/apache2.conf | tr -d " \t\n\r")
if [ "$APACHE_SIGNATURE" == "" ]; then # Avoid showing Apache data
    echo ServerSignature Off >> /etc/apache2/apache2.conf
fi

# Prevents "httpd (pid 1) already running" error when restarting the container
rm -f $APACHE_PID_FILE
exec /usr/sbin/apachectl -D FOREGROUND