FROM php:8.3-apache

RUN a2enmod rewrite
RUN a2enmod ssl

RUN apt-get update \
  && apt-get install -y libzip-dev git wget --no-install-recommends \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN apt-get update && apt-get -y install libjpeg-dev libpng-dev libwebp-dev zlib1g-dev git zip librabbitmq-dev && pecl install amqp
RUN docker-php-ext-configure gd --with-jpeg --with-webp
RUN docker-php-ext-install gd
RUN docker-php-ext-enable gd
RUN docker-php-ext-enable amqp

RUN docker-php-ext-install pdo mysqli pdo_mysql zip;

#RUN docker-php-ext-install gd
RUN rm /etc/apache2/sites-enabled/000-default.conf
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
RUN ln -s /etc/apache2/sites-available/000-default.conf /etc/apache2/sites-enabled/
COPY . /var/www
RUN chown www-data:www-data -R /var/www/

WORKDIR /var/www

COPY docker/entrypoint.sh docker/entrypoint.sh
RUN chmod +x docker/entrypoint.sh

CMD ["apache2-foreground"]
ENTRYPOINT ["docker/entrypoint.sh"]
