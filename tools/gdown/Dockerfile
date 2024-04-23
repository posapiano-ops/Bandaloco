FROM python:3.12.3-alpine

RUN pip install gdown

ARG USER
ARG UID
ARG GID

RUN useradd -m ${USER} --uid=${UID}

USER ${UID}:${GID}
WORKDIR /home/${USER}