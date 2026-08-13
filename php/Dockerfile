FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
        icu-dev \
        libzip-dev \
        oniguruma-dev \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        mbstring \
        intl \
        opcache \
    && rm -rf /tmp/* /var/cache/apk/*

RUN addgroup -S appgroup && \
    adduser -S appuser -G appgroup

WORKDIR /var/www/html

COPY www.conf /usr/local/etc/php-fpm.d/www.conf
COPY src/ /var/www/html/

RUN chown -R appuser:appgroup /var/www/html

USER appuser

EXPOSE 9000

CMD ["php-fpm", "-F"]