# syntax=docker/dockerfile:1

FROM php:8.3.4-cli as base

LABEL maintainer="Ahmedul Haque Abid <a_h_abid@hotmail.com>"

USER root

# Set Timezone & Trust CA Certs
ARG TIMEZONE="Asia/Dhaka"

ENV TZ="${TIMEZONE}"

RUN echo "-- Configure Timezone --" \
        && echo "${TIMEZONE}" > /etc/timezone \
        && rm /etc/localtime \
        && dpkg-reconfigure -f noninteractive tzdata \
    && echo "-- Trust CA Certs --" \
        && update-ca-certificates

ARG HTTP_PROXY=""
ARG HTTPS_PROXY=""
ARG NO_PROXY="127.0.0.1,localhost"

ENV http_proxy="${HTTP_PROXY}" \
    https_proxy="${HTTPS_PROXY}" \
    no_proxy="${NO_PROXY}"

ARG EXTRA_INSTALL_APT_PACKAGES=""
ARG EXTRA_INSTALL_PHP_EXTENSIONS=""
ARG DOCKER_PHP_EXTENSION_VERSION="2.2.5"

# Install Dependencies
RUN echo "-- Install/Upgrade APT Dependencies --" \
        && apt update \
        && apt upgrade -y \
        && apt install -V -y --no-install-recommends --no-install-suggests \
            bc \
            curl \
            openssl \
            unzip \
            zip \
        && if [ ! -z "${EXTRA_INSTALL_APT_PACKAGES}" ]; then \
            apt install -y ${EXTRA_INSTALL_APT_PACKAGES} \
        ;fi \
    && echo "-- Install PHP Extensions --" \
        && if [ ! -z "${HTTP_PROXY}" ]; then \
            pear config-set http_proxy ${HTTP_PROXY} \
        ;fi \
        && curl -L -o /usr/local/bin/install-php-extensions https://github.com/mlocati/docker-php-extension-installer/releases/download/${DOCKER_PHP_EXTENSION_VERSION}/install-php-extensions \
        && chmod a+x /usr/local/bin/install-php-extensions \
        && install-php-extensions \
            bcmath \
            exif \
            fileinfo \
            gettext \
            intl \
            opcache \
            pcntl \
            sockets \
            zip \
        && if [ ! -z "${EXTRA_INSTALL_PHP_EXTENSIONS}" ]; then \
            install-php-extensions ${EXTRA_INSTALL_PHP_EXTENSIONS} \
        ;fi \
        && if [ ! -z "${HTTP_PROXY}" ]; then \
            pear config-set http_proxy "" \
        ;fi \
    && echo "--- Create Blank Dirs ---" \
        && mkdir -p -m 777 /tmp/xdebug \
        && mkdir /run/php \
    && echo "--- Clean Up ---" \
        && apt clean -y \
        && apt autoremove -y \
        && rm -rf /tmp/* /var/tmp/* \
        && if [ -f "/usr/local/etc/php-fpm.d/zz-docker.conf" ]; then \
            rm /usr/local/etc/php-fpm.d/zz-docker.conf \
        ;fi

# PHP Composer Installation & Directory Permissions
ARG COMPOSER_VERSION="2.7.2"

RUN curl -L -o /usr/local/bin/composer https://github.com/composer/composer/releases/download/${COMPOSER_VERSION}/composer.phar \
    && chmod ugo+x /usr/local/bin/composer \
    && composer --version

ARG UID="1000"
ARG GID="1000"

RUN groupadd --gid ${GID} appuser \
    && useradd --uid ${UID} --create-home --system --comment "AppUser" --shell /bin/bash --gid appuser appuser \
    && install -d -o appuser -g appuser /home/appuser/appsrc

USER appuser

WORKDIR /home/appuser/appsrc

# PHP INI envs
ENV PHP_INI_OUTPUT_BUFFERING=4096 \
    PHP_INI_MAX_EXECUTION_TIME=60 \
    PHP_INI_MAX_INPUT_TIME=60 \
    PHP_INI_MEMORY_LIMIT="256M" \
    PHP_INI_DISPLAY_ERRORS="Off" \
    PHP_INI_DISPLAY_STARTUP_ERRORS="Off" \
    PHP_INI_POST_MAX_SIZE="2M" \
    PHP_INI_FILE_UPLOADS="On" \
    PHP_INI_UPLOAD_MAX_FILESIZE="2M" \
    PHP_INI_MAX_FILE_UPLOADS="2" \
    PHP_INI_ALLOW_URL_FOPEN="On" \
    PHP_INI_ERROR_LOG="" \
    PHP_INI_DATE_TIMEZONE="${TIMEZONE}" \
    PHP_INI_SESSION_SAVE_HANDLER="files" \
    PHP_INI_SESSION_SAVE_PATH="/tmp" \
    PHP_INI_SESSION_USE_STRICT_MODE=0 \
    PHP_INI_SESSION_USE_COOKIES=1 \
    PHP_INI_SESSION_USE_ONLY_COOKIES=1 \
    PHP_INI_SESSION_NAME="APP_SSID" \
    PHP_INI_SESSION_COOKIE_SECURE="On" \
    PHP_INI_SESSION_COOKIE_LIFETIME=0 \
    PHP_INI_SESSION_COOKIE_PATH="/" \
    PHP_INI_SESSION_COOKIE_DOMAIN="" \
    PHP_INI_SESSION_COOKIE_HTTPONLY="On" \
    PHP_INI_SESSION_COOKIE_SAMESITE="" \
    PHP_INI_SESSION_UPLOAD_PROGRESS_NAME="APP_UPLOAD_PROGRESS" \
    PHP_INI_OPCACHE_ENABLE=1 \
    PHP_INI_OPCACHE_ENABLE_CLI=0 \
    PHP_INI_OPCACHE_MEMORY_CONSUMPTION=256 \
    PHP_INI_OPCACHE_INTERNED_STRINGS_BUFFER=16 \
    PHP_INI_OPCACHE_MAX_ACCELERATED_FILES=100000 \
    PHP_INI_OPCACHE_MAX_WASTED_PERCENTAGE=25 \
    PHP_INI_OPCACHE_USE_CWD=0 \
    PHP_INI_OPCACHE_VALIDATE_TIMESTAMPS=0 \
    PHP_INI_OPCACHE_REVALIDATE_FREQ=0 \
    PHP_INI_OPCACHE_SAVE_COMMENTS=0 \
    PHP_INI_OPCACHE_ENABLE_FILE_OVERRIDE=1 \
    PHP_INI_OPCACHE_MAX_FILE_SIZE=0 \
    PHP_INI_OPCACHE_FAST_SHUTDOWN=1 \
    PHP_INI_XDEBUG_MODE="off" \
    PHP_INI_XDEBUG_OUTPUT_DIR="/home/appuser/xdebug/" \
    PHP_INI_XDEBUG_CLIENT_HOST="host.docker.internal" \
    PHP_INI_XDEBUG_CLIENT_PORT=10000 \
    PHP_INI_XDEBUG_START_WITH_REQUEST="yes"

COPY ./docker/app/php.ini /usr/local/etc/php/php.ini

# Copy & Install Composer Packages
COPY --chown=appuser:appuser ./codes/composer*.json ./codes/composer*.lock* ./

ARG COMPOSER_INSTALL_OPTS="--profile"

# Install Composer Dependencies
RUN composer install ${COMPOSER_INSTALL_OPTS} --no-interaction --no-scripts --no-autoloader

# Copy Source Files
COPY --chown=appuser:appuser ./codes ./

# Make Necessary Blank Directories
RUN mkdir -p \
        ./bootstrap/cache \
        ./public/build \
        ./storage/app/public \
        ./storage/framework/cache/data \
        ./storage/framework/sessions \
        ./storage/framework/testing \
        ./storage/framework/views \
        ./storage/logs \
    && chown -R appuser:appuser \
        ./bootstrap/cache \
        ./public/build \
        ./storage

# Create Autoload Files
RUN composer dump-autoload -o --classmap-authoritative

# Remove proxy envs
RUN unset http_proxy https_proxy no_proxy

ENV http_proxy="" \
    https_proxy="" \
    no_proxy=""

CMD [ "php", "artisan", "serve", "--host=0.0.0.0", "--port=8080" ]
