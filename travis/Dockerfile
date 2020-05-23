# see https://dockerfile.readthedocs.io/en/latest/content/DockerImages/dockerfiles/php-apache.html
FROM webdevops/php-apache:7.3

RUN apt-get update -y && apt-get install -y sudo jq vim mariadb-server && \
    /etc/init.d/mysql restart && mysqladmin password "password" && \
    find /var/lib/mysql -type f -exec touch {} \; && /etc/init.d/mysql restart && sleep 5 && \
    mysql -uroot -ppassword -e 'CREATE USER "webadmin"@"%" IDENTIFIED BY "webadmin"; create database drupal; GRANT ALL ON drupal.* TO "webadmin"@"%";'

RUN echo "sendmail_path=$(which true)" > /usr/local/etc/php/conf.d/cohesion.ini
RUN echo "memory_limit=-1" >> /usr/local/etc/php/conf.d/cohesion.ini
RUN echo "max_execution_time=0" >> /usr/local/etc/php/conf.d/cohesion.ini

WORKDIR /app

ADD ./container-stable.sh /app
ADD ./install-cohesion-dependencies.sh /app
ADD ./apache-vhost.conf /opt/docker/etc/httpd/vhost.common.d/drupal.conf

# Install SSH keys and composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php composer-setup.php && \
    php -r "unlink('composer-setup.php');" && \
    mv composer.phar /usr/local/bin/composer

# Install drupal-check
RUN mkdir -p /home/application/tools/drupal-check
RUN COMPOSER_MEMORY_LIMIT=-1 composer global require -d /home/application/tools/drupal-check mglaman/drupal-check:1.0.14
RUN chown -R application: /home/application/tools

# Install drupal-lightning using composer create-project
RUN COMPOSER_MEMORY_LIMIT=-1 composer create-project acquia/lightning-project:8.7.1 drupal --no-install && \
    composer config -g github-oauth.github.com "d7e4768dba9f24dc4cb82afde6e2503b23416486" && \
    cd /app/drupal && composer require acquia/lightning:4.1.0 && \
    composer require drush/drush && \
    mkdir -p /app/drupal/docroot/themes && \
    mkdir -p /app/drupal/docroot/profiles/contrib && \
    chown -R www-data: /app/drupal && \
    chmod -R 0777 /app/drupal/docroot

RUN adduser application www-data