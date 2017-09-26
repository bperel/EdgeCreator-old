#!/usr/bin/env bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
. ${DIR}/common.sh

mkdir -p ${webdir}_old && rm -rf ${webdir}_old/*

for f in ${to_copy[@]}
do
  if [ -d "${webdir}/$f" ] || [ -f "${webdir}/$f" ]; then
    cp -rp "${webdir}/$f" ${webdir}_old
  else
    echo "Warning: ${webdir}/$f does not exist"
  fi
done
