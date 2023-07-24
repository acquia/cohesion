ARG PHP_VERSION
ARG DRUPAL_VERSION

FROM drupal:${DRUPAL_VERSION}-php${PHP_VERSION}-apache

ENV PHP_VERSION ${PHP_VERSION}
ENV DRUPAL_VERSION ${DRUPAL_VERSION}

RUN apt-get -y update && apt-get -y install git sudo vim mariadb-client time rsync

RUN echo "sendmail_path=$(which true)" > /usr/local/etc/php/conf.d/cohesion.ini

ARG GH_TOKEN

WORKDIR /opt/drupal/

RUN composer config -g github-oauth.github.com ${GH_TOKEN} && \
    composer config repositories.cohesion-install git git@github.com:acquia/cohesion-install.git && \
    composer config minimum-stability "dev" && \
    composer config prefer-stable "true" && \
    composer config --no-plugins allow-plugins.cweagans/composer-patches "true"
RUN composer require drush/drush:"^10 || ^11" && \
    composer require acquia/cohesion-install:dev-develop drupal/webform:^6 drupal/focal_point:^1 drupal/quick_node_clone:^1

# Add scripts
ADD ./scripts/run.sh .
ADD ./scripts/wait.sh .
ADD ./scripts/install.sh .
ADD ./scripts/rebuild.sh .

