#!/bin/bash

docker compose -f docker-compose-local.yaml stop
docker system prune -f
docker volume rm -f docker_files-oc-1
docker volume rm -f docker_mysql-oc-1
docker volume rm -f docker_redis-oc-1
docker volume rm -f docker_files-oc-2
docker volume rm -f docker_mysql-oc-2
docker volume rm -f docker_redis-oc-2
docker volume rm -f docker_mysql-nc-1
docker image rm -f docker-oc-1
docker image rm -f docker-oc-2
docker image rm -f docker-nc-1
docker volume prune -f