FROM joy2fun/php:laravel

RUN curl -s https://raw.githubusercontent.com/composer/getcomposer.org/ba13e3fc70f1c66250d1ea7ea4911d593aa1dba5/web/installer | php -- --install-dir=/bin --filename=composer --quiet

COPY --chown=www-data:www-data /. /app

RUN cd /app && touch .env && composer install -n
