SHELL := /bin/bash

ROOT_DIR:=$(shell dirname $(realpath $(lastword $(MAKEFILE_LIST))))

include $(ROOT_DIR)/.env
include $(ROOT_DIR)/libs/variables.mk
include $(ROOT_DIR)/libs/common.mk


.PHONY: help up start stop ps clean setup destroy 

help: ## Show this help. 
	@awk 'BEGIN {FS = ":.*##"; printf "\nUsage: make [target]\n \033[36m\033[0m\n"} /^[$$()% 0-9a-zA-Z_-]+:.*?##/ { printf "  \033[1;33m%-15s\033[0;37m %s\n", $$1, $$2 } /^##@/ { printf "\n\033[1m%s\033[0m\n", substr($$0, 5) } ' $(MAKEFILE_LIST)

variables: ## Print variables
	$(info root directory = $(ROOT_DIR))
	$(info docker compose type = $(DOCKER_COMPOSE))
	$(info docker compose file = $(COMPOSE_YML))
	$(info docker network name = $(NETWORK_NAME))
	$(info wordpress url = $(WP_URL))
	$(info wordpress site name = $(WP_SITE_NAME))
	$(info wordpress admin username = $(WP_SITE_USER))
	
setup: .env create-network start ## Setup core WP system
	@sleep 15

up: $(COMPOSE_YML) ## Start core containers in foreground (or only one c=<container-name>)
	@$(DOCKER_COMPOSE) -f $(COMPOSE_YML) up $(c)

start: $(COMPOSE_YML) ## Like up but containers are started in background
	@$(DOCKER_COMPOSE) -f $(COMPOSE_YML) up -d $(c)

stop: ## Stop services (or only c=<container-name>)
	@$(DOCKER_COMPOSE) -f $(COMPOSE_YML) stop $(c)

list: ## Show status of running containers
	@docker container ls

services: show-containers ## Show all the available containers you can use

ps: ## Like list but for container in the docker-compose file
	@$(DOCKER_COMPOSE) -f $(COMPOSE_YML) ps

clean: confirm ## Stop and remove all containers, networks..
	$(DOCKER_COMPOSE) -f $(COMPOSE_YML) down

destroy: confirm ## Remove all containers and their volumes (or only one c=<container-name>)
	@${DOCKER_COMPOSE} down -v $(c) ;
	@sudo rm -rf $(filter-out  wp-app/readme.md,$(wildcard  wp-app/*)) wp-app/.htaccess .installed plugins/fusion-builder plugins/fusion-core theme/Avada ;
