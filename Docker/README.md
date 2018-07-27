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
**mg_set_base_url**: (Set a new base url for Magento1)\

### Delete Magento cache:
`docker exec mage1-apache2 n98-magerun cache:clean --root-dir=/var/www/html/magento`

## Tools

In order to receive postbacks **ngrok** (https://ngrok.com/) and **localtunnel** (https://localtunnel.github.io/www/) tools will help you to expose your container to internet for free\

SSL Tunnel, if you have a server with ssh access you can use it to redirect:\
`ssh -R 8280:localhost:8480  serveruser@yourserverhost.com`\
8280 is any port you can use to access your server, then **change your magento url**\
`make mg_set_base_url`\
Change it to **http://serverhost.com:8280/**


With **ngrock**:

`ngrok http 80` 

Set the URL with:

`make mg_set_base_url`\
 or\
`docker exec -it gf_mage1_http /bin/set_mage_base_url.sh `

## Notes:

**US Country** is in German, you will find it as **"Vereinigte Staaten"**
