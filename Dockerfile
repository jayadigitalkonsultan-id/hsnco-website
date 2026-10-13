# syntax=docker/dockerfile:1
# Override with a verified digest in EasyPanel for fully immutable base images.
ARG FRANKENPHP_IMAGE=dunglas/frankenphp:1-php8.3-bookworm
FROM ${FRANKENPHP_IMAGE} AS php-base
WORKDIR /app
RUN install-php-extensions ctype dom fileinfo iconv mbstring session tokenizer pdo_mysql pdo_sqlite zip opcache \
    && cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY docker/php-production.ini /usr/local/etc/php/conf.d/zz-hsnco-production.ini

FROM composer:2 AS composer-bin

FROM php-base AS dependencies
COPY --from=composer-bin /usr/bin/composer /usr/local/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress \
    --no-scripts --no-autoloader
COPY app ./app
COPY bootstrap ./bootstrap
COPY config ./config
COPY database ./database
COPY public ./public
COPY resources ./resources
COPY routes ./routes
COPY artisan ./artisan
RUN mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && composer dump-autoload --no-dev --optimize --no-scripts \
    && php artisan package:discover --no-interaction \
    && composer check-platform-reqs --no-dev

FROM php-base AS production
ENV APP_ENV=production APP_DEBUG=false \
    LOG_CHANNEL=stderr LOG_LEVEL=warning \
    SESSION_DRIVER=file CACHE_STORE=file QUEUE_CONNECTION=sync \
    SESSION_SECURE_COOKIE=true
COPY --from=dependencies /app /app
COPY docker/Caddyfile /etc/caddy/Caddyfile
COPY docker/entrypoint.sh /usr/local/bin/hsnco-entrypoint
COPY docker/healthcheck.php /usr/local/bin/hsnco-healthcheck.php
RUN groupadd --gid 1000 hsnco \
    && useradd --uid 1000 --gid hsnco --home-dir /app --no-create-home hsnco \
    && mkdir -p /app/storage/app/public /app/storage/app/private \
        /app/storage/framework/cache/data /app/storage/framework/sessions \
        /app/storage/framework/views /app/storage/logs /app/bootstrap/cache \
        /data/caddy /config/caddy \
    && chown -R hsnco:hsnco /app/storage /app/bootstrap/cache /data/caddy /config/caddy \
    && chmod -R ug+rwX /app/storage /app/bootstrap/cache \
    && chmod 755 /usr/local/bin/hsnco-entrypoint \
    && ln -s /app/storage/app/public /app/public/storage \
    && if command -v setcap >/dev/null 2>&1; then setcap -r /usr/local/bin/frankenphp 2>/dev/null || true; fi
USER 1000:1000
EXPOSE 8080
STOPSIGNAL SIGTERM
HEALTHCHECK --interval=30s --timeout=5s --start-period=60s --retries=3 \
    CMD ["php", "/usr/local/bin/hsnco-healthcheck.php"]
ENTRYPOINT ["/usr/local/bin/hsnco-entrypoint"]
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile", "--adapter", "caddyfile"]
