FROM php:7.4-apache
MAINTAINER Latheesan Kanesamoorthy <latheesan87@gmail.com>

# Update base system
ARG DEBIAN_FRONTEND=noninteractive
RUN apt-get update && \
    apt-get install --no-install-recommends -y wget curl nano sudo libzip-dev zip && \
    sudo apt-get -y purge && sudo apt-get -y clean && \
    sudo apt-get -y autoremove && sudo rm -rf /var/lib/apt/lists/* && \
    sudo rm -rf /usr/bin/apt*

# Install Cardano CLI
RUN mkdir cardano_cli_setup && cd cardano_cli_setup && \
    curl -sSL https://hydra.iohk.io/build/6263009/download/1/cardano-node-1.27.0-linux.tar.gz -o cardano_node.tar.gz && \
    tar -xf cardano_node.tar.gz && \
    mv ./cardano-cli /usr/local/bin/cardano-cli && \
    cd .. && rm -rf cardano_cli_setup

# Install & enable required php libraries
RUN pecl install -o -f redis \
    && rm -rf /tmp/pear \
    && docker-php-ext-enable redis \
    && docker-php-ext-install zip mysqli pdo_mysql

# Configure php & apache
RUN cp /usr/local/etc/php/php.ini-production php.ini && \
    rm -rf /etc/apache2/sites-available/* && \
    rm -rf /etc/apache2/sites-enabled/* && \
    echo 'ServerName heidrun.app' >> /etc/apache2/apache2.conf
COPY /docker/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY /docker/heidrun.app.conf /etc/apache2/sites-available/heidrun.app.conf
RUN a2enmod rewrite && \
    a2ensite heidrun.app

# Create heidrun user
RUN adduser --disabled-password --gecos '' heidrun && \
    echo '%sudo ALL=(ALL) NOPASSWD:ALL' >> /etc/sudoers && \
    adduser heidrun sudo && \
    chown -R heidrun:heidrun /home/heidrun/.*

# Install composer globally
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php \
    && rm -f composer-setup.php \
    && mv composer.phar /usr/local/bin/composer

# Set heidrun user
USER heidrun
WORKDIR /home/heidrun

# Expose apache port
EXPOSE 80
