#!/bin/bash

launcherJar=( server/plugins/org.eclipse.equinox.launcher*.jar )

echo "Cleaning Cloudbeaver Server"

[ -d "workspace/.metadata" ] && rm -rf "workspace/.metadata"

echo "Starting Cloudbeaver Server"

[ ! -d "workspace/.metadata" ] && mkdir -p workspace/GlobalConfiguration/.dbeaver && cp conf/initial-data-sources.conf workspace/GlobalConfiguration/.dbeaver/data-sources.json \
    && cp conf/project-metadata.conf workspace/GlobalConfiguration/.dbeaver/project-metadata.json \
    #&& cp workspace/script/*.sql workspace/GlobalConfiguration/

VMARGS_OPTS="${JAVA_OPTS:--Xmx2048M}"

java -jar ${launcherJar} -product io.cloudbeaver.product.ce.product -web-config conf/cloudbeaver.conf -nl en -registryMultiLanguage -vmargs ${VMARGS_OPTS}
