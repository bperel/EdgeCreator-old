#!/usr/bin/env bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
. ${DIR}/common.sh

deployment_commit_id=$1

cd ${webdir}

echo ${deployment_commit_id} > deployment_commit_id.txt && \
echo "Deployed:" && cat deployment_commit_id.txt