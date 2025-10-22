# 共通ベース: PHP 8.3 FPM（本番/開発の共通拡張をここで入れる）
FROM php:8.3-fpm-alpine AS base

# Composer（全ステージで使えるよう base に配置）
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# 共通の PHP 拡張を導入（intl, pdo_mysql, opcache, gd など）
RUN set -eux; \
    apk add --no-cache \
      git unzip \
      icu-dev \
      libpng-dev libjpeg-turbo-dev freetype-dev \
      oniguruma-dev; \
    apk add --no-cache --virtual .build-deps $PHPIZE_DEPS; \
    docker-php-ext-configure gd --with-freetype --with-jpeg; \
    docker-php-ext-install -j"$(nproc)" intl pdo_mysql opcache gd; \
    apk del .build-deps; \
    rm -rf /var/cache/apk/*

# 本番用（xdebug なし）
FROM base AS app
# ここに本番用の追加設定があれば追記

# 開発用（xdebug をここだけで入れる）
FROM base AS dev
RUN set -eux; \
    apk add --no-cache --virtual .build-deps $PHPIZE_DEPS linux-headers; \
    pecl install xdebug; \
    docker-php-ext-enable xdebug; \
    apk del .build-deps; \
    rm -rf /tmp/pear

# Xdebug の既定設定
RUN { \
      # echo "zend_extension=$(php -i | awk -F'=> ' '/xdebug.so/ {print $2; exit}')"; \ shigeta: アプリの作成時のエラー解消のため消してみる
      echo "xdebug.mode=debug,develop"; \
      echo "xdebug.client_port=9003"; \
      echo "xdebug.start_with_request=yes"; \
      echo "xdebug.discover_client_host=true"; \
    } > /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
