# Google Driver download 

## build
```bash
docker build \
		--build-arg USER=$(USER) \
        --build-arg UID=$(shell id -u) \
		--build-arg GID=$(shell id -g) \
		-t posapiano/gdown 

```
## download
```bash
# example usage: `make download URL="https://drive.google.com/u/1/uc?id=17-FCstm8Fz3bDzFgTmOWHa_c39lTR_1P"`
.PHONY: download
download:
	mkdir -p tmp
	docker run \
    	-v $(PWD)/tmp:/home/${USER}/data \
    	-u $(shell id -u):$(shell id -g) \
		--rm \
		posapiano/gdown \
    	bash -c "cd /home/${USER}/data && gdown $(URL)
```