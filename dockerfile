FROM php:8.2-cli

# Install dependencies
RUN apt-get update && apt-get install -y \
    curl unzip git nodejs npm

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN composer install --no-dev --optimize-autoloader
RUN npm install && npm run build

EXPOSE 10000

CMD php artisan serve --host=0.0.0.0 --port=10000
