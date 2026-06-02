# PHP-FPM image — processes .php files for nginx over the internal network.
FROM php:8.3-fpm-alpine

# Common extensions you'll likely want. Add more here as needed.
RUN docker-php-ext-install opcache

# Match the host user so mounted files aren't owned by root (uid 1000 = typical first Linux user).
ARG UID=1000
ARG GID=1000
RUN deluser www-data 2>/dev/null; \
    addgroup -g ${GID} -S www-data && \
    adduser -u ${UID} -S -G www-data www-data

WORKDIR /var/www/html

# PHP-FPM listens on 9000 inside the Docker network (not exposed to the host).
EXPOSE 9000
CMD ["php-fpm"]
