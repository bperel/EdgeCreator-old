#!/usr/bin/env bash

. common.sh

deploy() {
  docker exec ${container_name} /bin/bash ${webdir}/scripts/deploy/backup-app.sh && \
  docker exec ${container_name} rm -rf ${webdir}_new && \
  \
  for f in ${to_copy[@]}; \
  do \
    docker cp ${f} ${container_name}:${webdir}_new; \
  done \
  \
  && docker exec ${container_name} /bin/bash -c "rm -rf ${webdir} && mv ${webdir}_new ${webdir}" \
  && docker exec ${container_name} /bin/bash ${webdir}/scripts/deploy/apply-app.sh `git rev-parse HEAD`
}

container_name=$1

if [ -z "$container_name" ]; then
	echo "No container name provided. Usage : $0 <container_name>"
	exit 1
fi

deploy