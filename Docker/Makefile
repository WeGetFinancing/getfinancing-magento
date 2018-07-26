include apache2/.env
TARGET="mage1-"
MAGENTO_ID=$(shell docker ps -a | grep $(TARGET) | cut -d ' ' -f 1)
MG_WEB_ID=$(shell docker ps -a | grep $(TARGET)apache2 | cut -d ' ' -f 1)

show_img_id: ##@ View Magento Image ID
	@echo $(MAGENTO_ID)

env_info: ##@ See info of current variables
	@echo TARGET=$(TARGET) MAGENTO_ID=$(MAGENTO_ID) ADMIN_USR=$(ADMIN_USR) ADMIN_PASSWORD=$(ADMIN_PASSWORD) 
	@echo ADMIN_FIRSTNAME=$(ADMIN_FIRSTNAME) ADMIN_LASTNAME=$(ADMIN_LASTNAME) ADMIN_EMAIL=$(ADMIN_EMAIL) MG_BASE_URL=$(MG_BASE_URL) MG_SECURE_URL=$(MG_SECURE_URL)
	@echo MG_MODE=$(MG_MODE) $(APACHE_LOG_DIR)

build: ##@ Build the image
	docker-compose build

build-nc: ##@ Build the image not using cache MG_SECURE_URL= MG_SECURE_URL= MG_MODE= 
	docker-compose build --no-cache

up: ##@ Start the images
	docker-compose up 

upd: ##@ Start the images (daemon mode)
	docker-compose up -d

stop: ##@ Stop image
	docker-compose stop

delete: ##@ Delete image
	docker rm $(MAGENTO_ID)

logs: ##@ show logs (tail)
	docker logs -t $(MG_WEB_ID)

logsf: ##@ show logs in real time
	docker logs -f -t $(MG_WEB_ID)

ps: ##@ Show docker processes
	docker ps

psa: ##@ Show ALL docker processes for this Target (include inactive)
	docker ps -a | grep $(TARGET)

login_bash: ##@ Log into the machine
	docker exec -it $(MG_WEB_ID) bash

mg_rebuild_db: ##@ Delete/Rebuild Magento database
	@echo Delete/Rebuild database in $(MG_WEB_ID)
	docker exec $(MG_WEB_ID) /bin/rebuild-db.sh

mg_install_gf_plugin: ##@ Install Magento base, Magento sample data and GetFinancing plugin
	@echo install Magento in $(MG_WEB_ID)
	docker exec $(MG_WEB_ID) /bin/magento-install.sh

mg_cache_clear: ##@ Clear magento cache
	@echo Clear cache in $(MG_WEB_ID)
	docker exec $(MG_WEB_ID) n98-magerun cache:clean --root-dir=/var/www/html/magento
	docker exec $(MG_WEB_ID) n98-magerun cache:flush --root-dir=/var/www/html/magento

mg_set_base_url: ##@ Set a new base url for Magento1
	@echo Set new base url for $(MG_WEB_ID)
	docker exec -it $(MG_WEB_ID) /bin/set_mage_base_url.sh 

.DEFAULT_GOAL := help

help:
	@grep -E '(^[a-zA-Z_-]+:.*?##@.*$$)' $(MAKEFILE_LIST) \
		| sed s/makefile://i \
		| awk 'BEGIN { FS = ":" }; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' \
		| sed s/##@/==\>/ \
		| sed -e 's/\[32m##/[33m/'
