`make` command has helper functions to manage this images:
=

Use make command to see available options:\
`make`\

With Make can View Magento container ID, See info of current variables, Build the image\
Build the image not using cache, Start the images, Start the images (daemon mode)\
Stop, Delete images, show logs (tail), show logs in real time, Show docker processes\
Show ALL docker processes for this Target (include inactive), Log into the machine\
Delete/Rebuild Magento database, Install Magento base, Magento sample data and GetFinancing plugin\
Clear magento cache

Delete Magento cache:\
`docker exec mage1-apache2 n98-magerun cache:clean --root-dir=/var/www/html/magento`
