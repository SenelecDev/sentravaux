.PHONY: build up down logs shell migrate ports

build:
	docker compose build

up:
	docker compose up -d

down:
	docker compose down

logs:
	docker compose logs -f

shell:
	docker compose exec app bash

migrate:
	docker compose exec app php artisan migrate --force

ports:
	@bash deploy/check-ports.sh 8093 3313

prod-deploy:
	@bash deploy/deploy-centos7.sh $(CURDIR)
