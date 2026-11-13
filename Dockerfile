FROM composer:2 AS vendor
WORKDIR /build
COPY composer.* ./
RUN composer validate --no-check-publish --no-check-lock \
    && composer install --no-dev --no-interaction --no-progress --prefer-dist --optimize-autoloader --ignore-platform-req=ext-pdo_mysql

FROM php:8.4-apache-bookworm

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN apt-get update \
    && apt-get install -y --no-install-recommends curl \
    && docker-php-ext-install pdo_mysql \
    && a2enmod headers \
    && rm -rf /var/lib/apt/lists/*

COPY docker/apache-vhost.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php-production.ini /usr/local/etc/php/conf.d/zz-production.ini
COPY --from=vendor /build/vendor/ /var/www/html/vendor/
COPY composer.json /var/www/html/composer.json
COPY app/ /var/www/html/app/
COPY bin/ /var/www/html/bin/
COPY public/ /var/www/html/public/

RUN chown -R root:root /var/www/html \
    && find /var/www/html -type d -exec chmod 0555 {} \; \
    && find /var/www/html -type f -exec chmod 0444 {} \; \
    && php -r 'if (!extension_loaded("pdo_mysql")) { fwrite(STDERR, "PDO MySQL extension is required.\\n"); exit(1); }'

WORKDIR /var/www/html
EXPOSE 80
HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 \
    CMD curl --fail --silent http://127.0.0.1/health.php >/dev/null || exit 1
