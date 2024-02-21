.DEFAULT_GOAL := help

.PHONY: confirm .env create-network wordpress backup show-containers

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

wordpress:
	@echo 'Install wordpress'
	@$(DOCKER_COMPOSE) run --rm wpcli wp core install --url=$(WP_URL) --title='$(WP_SITE_NAME)' --locale=$(WP_SITE_LANG) --admin_user=$(WP_SITE_USER) --admin_password=$(WP_SITE_PASSWORD) --admin_email=$(WP_SITE_MAIL)
	@echo 'Init config'
	@$(DOCKER_COMPOSE) run --rm wpcli language core install $(WP_SITE_LANG)
	@$(DOCKER_COMPOSE) run --rm wpcli site switch-language $(WP_SITE_LANG)
	@$(DOCKER_COMPOSE) run --rm wpcli option update timezone_string $(WP_SITE_TIMEZONE)
	@$(DOCKER_COMPOSE) run --rm wpcli option update date_format $(WP_SITE_DATE_FORMAT)
	@$(DOCKER_COMPOSE) run --rm wpcli option update time_format $(WP_SITE_TIME_FORMAT)
	@echo 'Init plugin'
	@$(DOCKER_COMPOSE) run --rm wpcli plugin install wp-mail-smtp --activate
	@$(DOCKER_COMPOSE) run --rm wpcli plugin uninstall hello
	@echo 'delete theme'
	@$(DOCKER_COMPOSE) run --rm wpcli theme uninstall twentytwentythree	
	@$(DOCKER_COMPOSE) run --rm wpcli theme uninstall twentytwentytwo

backup:
	@echo 'Backup Database'
	@./tools/export.sh

show-containers:
	@echo 'Those are the available containers in this project:'
	@echo '--> wp (Wordpress Server 6.x)'
	@echo '--> wpcli (Wordpress CLI 2.x)'
	@echo '--> db (DataBase Server MySQL 8.x)'
	@echo '--> dbeaver (Cloud Database Manager)'
	@echo '--> maildev (MAILdev fake SMTP)'
