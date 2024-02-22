.DEFAULT_GOAL := help

.PHONY: confirm .env create-network fix-permissions wordpress backup show-containers

confirm:
	@( read -p "$(RED)Are you sure? [y/N]$(RESET): " sure && case "$$sure" in [yY]) true;; *) false;; esac )

.env: sample.env
	@cp --update sample.env .env

create-network:
	@$(eval NETWORK=$(shell docker network ls | grep -o '$(NETWORK_NAME)' | awk '{print $$1}')) \
	if [ -z $(NETWORK) ]; then \
		echo "Creating $(NETWORK_NAME) network"; \
		docker network create $(NETWORK_NAME); \
	else \
		echo "$(NETWORK_NAME) network exits"; \
	fi

fix-permissions:
	@sudo chmod 777 -R ./wp-app

wordpress: fix-permissions ## Install and confing wordpress
	@echo 'Install wordpress'
	@$(DOCKER_COMPOSE) run --rm wpcli wp core install --url=$(WP_URL) --title='$(WP_SITE_NAME)' --locale=$(WP_SITE_LANG) --admin_user=$(WP_SITE_USER) --admin_password=$(WP_SITE_PASSWORD) --admin_email=$(WP_SITE_MAIL)
	@echo 'Init config'
	@$(DOCKER_COMPOSE) run --rm wpcli language core install $(WP_SITE_LANG)
	@$(DOCKER_COMPOSE) run --rm wpcli site switch-language $(WP_SITE_LANG)
	@$(DOCKER_COMPOSE) run --rm wpcli option update timezone_string $(WP_SITE_TIMEZONE)
	@$(DOCKER_COMPOSE) run --rm wpcli option update date_format $(WP_SITE_DATE_FORMAT)
	@$(DOCKER_COMPOSE) run --rm wpcli option update time_format $(WP_SITE_TIME_FORMAT)
	@if [ ! -f .installed ]; then \
		echo 'Init plugin'; \
		$(DOCKER_COMPOSE) run --rm wpcli plugin install wp-mail-smtp --activate; \
		$(DOCKER_COMPOSE) run --rm wpcli plugin install sg-security --activate ; \
		$(DOCKER_COMPOSE) run --rm wpcli plugin uninstall akismet ; \
		$(DOCKER_COMPOSE) run --rm wpcli plugin uninstall hello ; \
	fi
	@echo 'Avada check'
	@if [ ! -z $(WP_AVADA) ]; then \
			$(DOCKER_COMPOSE) run --rm wpcli plugin install gutenberg --activate ; \
	fi 
	@touch .installed 
	@echo 'delete theme inactive'
	@$(DOCKER_COMPOSE) run --rm wpcli theme uninstall twentytwentythree	
	@$(DOCKER_COMPOSE) run --rm wpcli theme uninstall twentytwentytwo

backup: ## Backup Database wordpress
	@echo 'Backup Database'
	@$(DOCKER_COMPOSE) run --rm wpcli db export
	@mv ./wp-app/*.sql ./wp-data/

show-containers:
	@echo 'Those are the available containers in this project:'
	@echo '--> wp (Wordpress Server 6.x)'
	@echo '--> wpcli (Wordpress CLI 2.x)'
	@echo '--> db (DataBase Server MariaDB 11.x)'
	@echo '--> dbeaver (Cloud Database Manager)'
	@echo '--> maildev (MAILdev fake SMTP)'
