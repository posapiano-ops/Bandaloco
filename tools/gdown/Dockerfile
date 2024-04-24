FROM python:3.12.3-alpine

RUN pip install gdown

ARG USER
ARG UID
ARG GID
ENV GROUPNAME=$USER

RUN addgroup \
    --gid "$GID" \
    "$GROUPNAME" \
&&  adduser \
    --disabled-password \
    --gecos "" \
    --ingroup "$GROUPNAME" \
    --uid "$UID" \
    $USER

USER ${UID}:${GID}
WORKDIR /home/${USER}

CMD ["gdown"]