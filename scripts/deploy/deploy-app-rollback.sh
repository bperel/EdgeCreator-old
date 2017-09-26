#!/usr/bin/env bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
. ${DIR}/common.sh

container_name=$1

if [ -z "$container_name" ]; then
	echo "No container name provided. Usage : $0 <container_name>"
	exit 1
fi

docker exec ${container_name} /bin/bash ${webdir}/scripts/deploy/restore-app.sh
