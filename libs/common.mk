.DEFAULT_GOAL := help

.PHONY: confirm .env create-network show-containers

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

show-containers:
	@echo 'Those are the available containers in this project:'
	@echo '--> wp (Wordpress Server 6.x)'
	@echo '--> wpcli (Wordpress CLI 2.x)'
	@echo '--> db (DataBase Server MySQL 8.x)'
	@echo '--> dbeaver (Cloud Database Manager)'
