ARG PHP_VERSION
ARG DRUPAL_VERSION

FROM drupal:${DRUPAL_VERSION}-php${PHP_VERSION}-apache

ENV PHP_VERSION ${PHP_VERSION}
ENV DRUPAL_VERSION ${DRUPAL_VERSION}

RUN apt-get -y update && apt-get -y install git sudo vim mariadb-client time rsync unzip libicu-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl

RUN echo "sendmail_path=$(which true)" > /usr/local/etc/php/conf.d/cohesion.ini

ARG GH_TOKEN

WORKDIR /opt/drupal/

RUN composer config -g github-oauth.github.com ${GH_TOKEN} && \
    composer config repositories.cohesion-install git git@github.com:acquia/cohesion-install.git && \
    composer config minimum-stability "dev" && \
    composer config prefer-stable "true" && \
    composer config --no-plugins allow-plugins.cweagans/composer-patches "true" && \
    composer require drush/drush:"^11.6 || ^12"

# Base dependencies for cypress
RUN composer require acquia/cohesion-install:7.x-dev drupal/webform:^6 drupal/focal_point:^2 drupal/quick_node_clone:^1 drupal/ckeditor:^1.0 drupal/tmgmt:^1.14

# Add required packages for phpunit
RUN composer require drupal/jsonapi_extras:^3.24 e0ipso/shaper:^1 drupal/acquia_contenthub:^3.3 drupal/context:^5.0@RC

# Needed for PHPCS
RUN composer require dealerdirect/phpcodesniffer-composer-installer && \
    composer require acquia/coding-standards:^1.1 && \
    composer require drupal/core-dev:"^9 || ^10"

RUN composer install

ADD ./testing.services.yml ./web/sites/default/testing.services.yml

# Add scripts
ADD ./scripts/run.sh .
ADD ./scripts/wait.sh .
ADD ./scripts/install.sh .
ADD ./scripts/rebuild.sh .

