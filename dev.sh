#!/bin/bash

docker build -t corvimae/pokemon-manager-2:latest .
docker run \
  -p 80:80 \
  --env-file db.env \
  -e PG_HOST=docker.for.mac.host.internal \
  -e ALLOWED_ORIGIN=http://localhost:3000 \
  corvimae/pokemon-manager-2:latest 