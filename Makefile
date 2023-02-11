docker-up:
	docker compose up -d
docker-down:
	docker compose down
docker-build:
	docker compose up --build -d

test:
	docker compose exec php-fpm vendor/bin/phpunit --colors=always

perm:
	sudo chown ${USER}:${USER} bootstrap/cache -R
	sudo chown ${USER}:${USER} storage -R
	sudo chown ${USER}:${USER} dump/ -R
	sudo chmod 777 storage/docker/dump -R

