FROM php:7.1-apache

LABEL vendor="Mautic"
LABEL maintainer="MotaWor`d <it@motaword.com>"

# Install PHP extensions
RUN apt-get update && apt-get install --no-install-recommends -y \
    cron \
    git \
    wget \
    libc-client-dev \
    libicu-dev \
    libkrb5-dev \
    libmcrypt-dev \
    libssl-dev \
    libz-dev \
    unzip \
    zip \
    && apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false \
    && rm -rf /var/lib/apt/lists/* \
    && rm /etc/cron.daily/*

RUN apt-get install $PHPIZE_DEPS
RUN docker-php-ext-configure imap --with-imap --with-imap-ssl --with-kerberos
RUN pecl install xdebug
RUN docker-php-ext-install imap intl bcmath mbstring mcrypt mysqli pdo_mysql sockets zip opcache
RUN docker-php-ext-enable imap intl bcmath mbstring mcrypt mysqli pdo_mysql sockets zip opcache


# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

# By default enable cron jobs
ENV MAUTIC_RUN_CRON_JOBS true

# Setting an root user for test
ENV MAUTIC_DB_HOST database
ENV MAUTIC_DB_USER mautic
ENV MAUTIC_DB_NAME mautic

RUN mkdir /var/log/mautic && chmod 777 /var/log/mautic

# Copy init scripts and custom .htaccess
COPY docker/docker-entrypoint.sh /entrypoint.sh
COPY docker/makedb.php /makedb.php
COPY docker/mautic.crontab /etc/cron.d/mautic
COPY docker/mautic-php.ini /usr/local/etc/php/conf.d/mautic-php.ini
COPY docker/init.sql /init.sql
ADD . /var/www/html
RUN cd /var/www/html && composer install

# Enable Apache Rewrite Module
RUN a2enmod rewrite

# Apply necessary permissions
RUN ["chmod", "+x", "/entrypoint.sh"]
ENTRYPOINT ["/entrypoint.sh"]

CMD ["apache2-foreground"]
