FROM php:8.4-cli

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    default-mysql-client \
    libzip-dev \
    nodejs \
    npm \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy application
COPY . .

# Install PHP dependencies
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction

# Install JavaScript dependencies
RUN npm ci

# Build Vite assets
RUN npm run build

# Laravel needs writable directories
RUN chmod -R 775 storage bootstrap/cache

# Railway web server port
EXPOSE 10000

# Start Laravel
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=10000"]