FROM php:8.4-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    nodejs \
    npm \
    && docker-php-ext-install pdo pdo_pgsql zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy application
COPY . .

# Install PHP dependencies
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction

# Install JS dependencies and build Vite
RUN npm install
RUN npm run build

# Laravel needs writable storage/cache directories
RUN chmod -R 775 storage bootstrap/cache

EXPOSE 10000

CMD php artisan serve --host=0.0.0.0 --port=10000