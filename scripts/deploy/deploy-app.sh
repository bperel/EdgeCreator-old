#!/usr/bin/env bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
. ${DIR}/common.sh

backup() {
  docker exec ${container_name} mkdir -p ${webdir}/scripts/deploy && \
  docker cp scripts/deploy ${container_name}:${webdir}/scripts && \
  docker exec ${container_name} /bin/bash ${webdir}/scripts/deploy/backup-app.sh
}

deploy() {
  docker exec ${container_name} /bin/bash -c "rm -rf ${webdir}_new && mkdir -p ${webdir}_new" && \
  \
  for f in ${to_copy[@]}; \
  do \
    docker cp ${f} ${container_name}:${webdir}_new/${f}; \
  done && \
  docker exec ${container_name} /bin/bash -c "cd ${webdir}_new && bower --allow-root install" && \
  docker exec ${container_name} /bin/bash -c "rm -rf ${webdir} && mv ${webdir}_new ${webdir}" && \
  docker exec ${container_name} /bin/bash ${webdir}/scripts/deploy/apply-app.sh `git rev-parse HEAD` && \
  docker exec ${container_name} mkdir -m 777 -p ${webdir}/_sessions
}

container_name=$1

if [ -z "$container_name" ]; then
	echo "No container name provided. Usage : $0 <container_name>"
	exit 1
fi

backup
deploy