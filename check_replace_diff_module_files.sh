#!/bin/bash

BASE_DOCKER=$1

if [ ! -d "$1" ]; then
    echo "Please set your docker app folder as the first parameter"
    echo "BASE_DOCKER is Docker app folder, i.e. /var/lib/docker/volumes/docker_mage1-http/_data/magento/"
    exit 1
fi

read -p "Replace differente files in Docker folder (y/n)?" RESP

find_diffs () {
    for f in $(find $1 -type f); do
        FILE_DIR=$(echo $f | sed -e 's/[^\/]*$//')
        FILE_NAME=$(echo $f | rev | cut -d '/' -f 1 | rev);
        HAS_DIFF=$(diff $BASE_DOCKER"$f" $f --color)
        if [ "$HAS_DIFF" != "" ]; then
            echo $BASE_DOCKER$f
            echo "Has changes from "
            echo $FILE_DIR$FILE_NAME
            if [ "$RESP" == 'y' ]; then
                echo " ------------------- REPLACING  "
                cp -f $FILE_DIR$FILE_NAME $BASE_DOCKER$f
            fi
        fi
    done
}

for folder in "app" "lib"; do 
    find_diffs "$folder"
done