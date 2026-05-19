FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    curl unzip git nodejs npm

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Install Node dependencies and build assets
RUN npm install && npm run build

# Cache Laravel config
RUN php artisan config:cache && php artisan route:cache

EXPOSE 10000

CMD php artisan serve --host=0.0.0.0 --port=10000
