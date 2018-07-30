#!/bin/bash

echo " ---------------- Rebuild db ---------------- "
/bin/rebuild-db.sh
echo " ---------------- End of rebuild db ---------------- "

echo " ---------------- Deleting current installation directory (please wait) ---------------- "
rm -rv /var/www/html/magento > /dev/null
mkdir /var/www/html/magento
APACHE_USER=$(grep www-data /etc/passwd | cut -d ":" -f 3)
APACHE_GRP=$(grep www-data /etc/passwd | cut -d ":" -f 4)

echo " ---------------- Executing (Magento 1 install) php install.php (please wait) ---------------- "
cd /var/www/html/magento/
n98-magerun install --dbHost="$MYSQL_HOST" --dbUser="$MYSQL_USER" --dbPass="$MYSQL_PASSWORD" \
--dbName="$MYSQL_DATABASE" --installSampleData=yes --useDefaultConfigParams=yes \
--magentoVersionByName="magento-mirror-1.9.3.8" --installationFolder="/var/www/html/magento/" \
--baseUrl="$APACHE_HOST"
cp /var/www/html/magento/errors/local.xml.sample /var/www/html/magento/errors/local.xml
n98-magerun cache:disable
n98-magerun admin:user:change-password admin $MAGE_PASSWORD
echo " ---------------- finished php install.php ---------------- "

echo " ---------------- install GetFinancing plugin (please wait) ---------------- "
cd /var/www/html/magento
GF_PLUGIN=$(curl https://api.github.com/repos/GetFinancing/getfinancing-magento/releases/latest | grep -i browser_download_url | awk '{print $2}' | sed s/\"//g)
GF_FILE=${GF_PLUGIN##*/}
curl -L $GF_PLUGIN > $GF_FILE
chmod ug+x /var/www/html/magento/mage
/var/www/html/magento/mage mage-setup
/var/www/html/magento/mage install-file /var/www/html/magento/$GF_FILE
rm /var/www/html/magento/$GF_FILE
chown $APACHE_USER:$APACHE_GRP /var/www/html/magento -R

echo " ---------------- Installation FINISHED, you can browse http://magento.local:8080/ ---------------- "
echo " ---------------- Use 8280 port with: "
echo " docker run -d -p 8280:80 --net=gf_mage1_net --name gf_mage1_http getfinancingdockerhub/magento1 ---------------- "
