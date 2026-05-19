FROM node:20 AS build-stage
WORKDIR /var/www/html

COPY package.json package-lock.json ./
RUN npm ci

COPY vite.config.js postcss.config.js tailwind.config.js .
COPY resources resources
COPY public public
RUN npm run build

FROM php:8.2-cli AS runtime
WORKDIR /var/www/html

RUN apt-get update \
  && apt-get install -y --no-install-recommends zip unzip git sqlite3 libsqlite3-dev libzip-dev \
  && docker-php-ext-install pdo pdo_sqlite zip \
  && rm -rf /var/lib/apt/lists/*

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
  && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
  && rm composer-setup.php

COPY . .
RUN rm -f public/hot

COPY --from=build-stage /var/www/html/public/build public/build

RUN mkdir -p storage/app/public storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs
RUN composer install --no-dev --optimize-autoloader
RUN mkdir -p database && touch database/database.sqlite && php artisan storage:link
RUN chown -R www-data:www-data storage bootstrap/cache || true

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 8000
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["sh", "-lc", "php -S 0.0.0.0:${PORT:-8000} -t public"]
