#!/bin/bash

docker run -d -p 80:80 --env-file db.env -e PG_HOST=docker.for.mac.host.internal corvimae/pokemon-manager-2:latest 