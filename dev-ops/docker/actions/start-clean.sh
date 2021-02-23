#!/usr/bin/env bash

docker-compose rm --stop --force

git add --force .idea
git clean -dfX
git reset HEAD .idea

docker-compose build --pull
docker-compose up --build -d
docker exec -t opencaching-webserver ./psh.phar docker:init
