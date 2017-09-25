#!/usr/bin/env bash

webdir=/var/www/html
deployment_commit_id=$1

cd ${webdir}

echo ${deployment_commit_id} > deployment_commit_id.txt && \
echo "Deployed:" && cat deployment_commit_id.txt