#!/bin/bash

docker build -t corvimae/pokemon-manager-2:latest .
docker push corvimae/pokemon-manager-2:latest
kubectl rollout restart deployment pokemon-manager