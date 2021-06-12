.DEFAULT_GOAL := up

.PHONY: up
up:
	$(MAKE) down
	docker-compose -f docker/docker-compose.yml up -d
	$(MAKE) composer-install
	$(MAKE) db-migrate

.PHONY: down
down:
	docker-compose -f docker/docker-compose.yml down --remove-orphans

.PHONY: build
build:
	docker-compose -f docker/docker-compose.yml build
	$(MAKE) up

#
# Helper functions
#

.PHONY: composer-install
composer-install:
	docker exec -it heidrun-web bash -c "cd application && composer install && composer dump-auto"

.PHONY: db-migrate
db-migrate:
	docker exec -it heidrun-web bash -c "cd application && php artisan migrate"

.PHONY: db-refresh
db-refresh:
	docker exec -it heidrun-web bash -c "cd application && php artisan migrate:fresh --seed"

.PHONY: admin-account
admin-account:
	docker exec -it heidrun-web bash -c "cd application && php artisan db:seed --class=AdminAccountSeeder"

.PHONY: status
status:
	docker-compose -f docker/docker-compose.yml ps

.PHONY: logs
logs:
	docker-compose -f docker/docker-compose.yml logs -f --tail=100

.PHONY: shell
shell:
	docker exec -it heidrun-web bash

.PHONY: stats
stats:
	docker stats heidrun-web heidrun-mysql heidrun-redis

.PHONY: artisan
artisan:
	docker exec -it heidrun-web bash -c "cd application && php artisan $(COMMAND)"
