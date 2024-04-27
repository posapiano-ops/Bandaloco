# Google Driver download 

## build
```bash
docker build \
		--build-arg USER=$(USER) \
        --build-arg UID=$(shell id -u) \
		--build-arg GID=$(shell id -g) \
		-t posapiano/gdown 

docker build --build-arg USER=gdown --build-arg UID=1000 --build-arg GID=1000 -t posapiano/gdown:1.0.0 .
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

docker run --rm -v ${PWD}/tmp:/home/gdown posapiano/gdown sh -c "gdown https://drive.google.com/uc?id=114Iq6TLocg7c8EKvwDL7Jj9EIAT8miG5"
```
```
version: "3.9"
services:
    gdown:
        volumes:
            - ${PWD}/tmp:/home/gdown
        image: posapiano/gdown
        command: sh -c "gdown https://drive.google.com/uc?id=114Iq6TLocg7c8EKvwDL7Jj9EIAT8miG5"

```