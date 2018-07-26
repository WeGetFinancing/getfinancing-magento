FROM debian

WORKDIR /var/www/html

ENV PHP_VERSION=5.6
ARG YOURAPP_ENV=production

RUN apt-get update && apt-get install -y apache2 mysql-client \
    && apt-get -y install apt-transport-https lsb-release ca-certificates \
    && echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list \
    && apt-get update \
    && apt-get install -y php$PHP_VERSION php$PHP_VERSION-common php$PHP_VERSION-mysql php$PHP_VERSION-xml \
    php$PHP_VERSION-mcrypt php$PHP_VERSION-mbstring curl vim php$PHP_VERSION-curl php5.6-gd php5.6-gd-dbgsym \
    zip php5.6-soap --allow-unauthenticated \
    && a2enmod rewrite 

ADD ./run.sh /bin/run.sh 
ADD ./.env /var/www/
ADD magento1.conf /
ADD magento-install.sh /bin/
ADD rebuild-db.sh /bin/
RUN chmod u+x /bin/run.sh /bin/magento-install.sh  /bin/rebuild-db.sh \
    && curl -O https://files.magerun.net/n98-magerun.phar; mv n98-magerun.phar /bin/n98-magerun \
    && chmod u+x /bin/n98-magerun  && mkdir /var/www/html/magento 

ENTRYPOINT ["/bin/run.sh"]
