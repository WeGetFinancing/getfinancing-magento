# Usage:
In order to correctly use docker for this project, there are some `Make` commands prepared.


## List of most usefull commands:
**show_img_id**: (View Magento Image ID)\
**env_info**: (See info of current variables)\
**build**: (Build the image)\
**build-nc**: (Build the image without cache)\
**up**: (Start the images)\
**upd**: (Start the images / Daemon mode)\
**stop**: (Stop image)\
**delete**: (Delete image)\
**logs**: (show logs)\
**logsf**: (show logs in real time)\
**ps**: (Show docker processes)\
**psa**: (Show docker processes, inactives too)\
**login_bash**: (Log into the container)\ 
**mg_rebuild_db**: (Delete/Rebuild Magento database)\
**mg_install_gf_plugin**: (Reinstall Magento and GetFinancing Plugin)\
**mg_cache_clear**: (Clear magento cache)\

### Delete Magento cache:
`docker exec mage1-apache2 n98-magerun cache:clean --root-dir=/var/www/html/magento`
