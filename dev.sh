#!/bin/bash

docker run -d -p 80:80 --env-file db.env corvimae/pokemon-manager-2:latest