# ベース: PHP 8.3 FPM
FROM php:8.3-fpm-alpine AS base

# 必要拡張の導入（intl, pdo_mysql, opcache, gd など）
RUN set -eux; \
    apk add --no-cache git unzip icu-dev libpng-dev oniguruma-dev $PHPIZE_DEPS; \
    docker-php-ext-install intl pdo_mysql opcache gd; \
    rm -rf /var/cache/apk/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# 開発用ターゲット（Xdebug入り）
FROM base AS dev
RUN pecl install xdebug && docker-php-ext-enable xdebug
# Xdebug の軽い既定設定
RUN { \
      echo "zend_extension=$(php -i | awk -F'=> ' '/xdebug.so/ {print $2; exit}')"; \
      echo "xdebug.client_port=9003"; \
      echo "xdebug.start_with_request=yes"; \
      echo "xdebug.discover_client_host=true"; \
    } > /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
