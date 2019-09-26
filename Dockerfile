FROM joy2fun/php:laravel

RUN curl -s https://raw.githubusercontent.com/composer/getcomposer.org/314aa57fdcfc942065996f59fb73a8b3f74f3fa5/web/installer | php -- --install-dir=/bin --filename=composer --quiet

COPY --chown=www-data:www-data /. /app
COPY docker-entrypoint /usr/local/bin/

ENTRYPOINT ["docker-entrypoint"]
