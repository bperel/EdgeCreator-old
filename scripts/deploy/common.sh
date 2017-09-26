#!/usr/bin/env bash

webdir=/var/www/html
to_copy=(application css helpers images js scripts system .bowerrc .htaccess bower.json index.php)
to_restore=("${to_copy[@]}" vendor deployment_commit_id.txt)