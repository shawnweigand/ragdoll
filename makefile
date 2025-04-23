# Define variables
DOCKER_COMPOSE = docker-compose
APP_CONTAINER = app  # Change this to the name of your app container
SHELL_COMMAND = /bin/bash  # Or /bin/sh, depending on your container

.PHONY: up exec run install secrets setup

# Bring up the Docker Compose services
up:
	$(DOCKER_COMPOSE) up -d

# Execute into the app container
exec:
	$(DOCKER_COMPOSE) exec $(APP_CONTAINER) $(SHELL_COMMAND)

# Combined target to run both up and exec
run: up exec

install:
	composer install
	npm install

migrate:
	php artisan migrate --force

schedule:
	php artisan schedule:work

seed:
	php artisan db:seed

setup:
	make install
	make migrate
	# make schedule

# Sync HashiCorp Vault secrets with cluster
secrets:
	pwsh secrets-sync.ps1