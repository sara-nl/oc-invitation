#!/bin/bash

docker compose -f docker-compose-local.yaml stop
docker system prune -f
docker volume rm docker_files-oc-1
docker volume rm docker_mysql-oc-1
docker volume rm docker_redis-oc-1
docker volume rm docker_files-oc-2
docker volume rm docker_mysql-oc-2
docker volume rm docker_redis-oc-2
docker image rm docker-oc-1
docker image rm docker-oc-2
