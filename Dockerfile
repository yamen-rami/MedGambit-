FROM php:8.4-cli

# Install system dependencies, build tools, and PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    default-mysql-client \
    libzip-dev \
    nodejs \
    npm \
    $PHPIZE_DEPS \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Application directory
WORKDIR /var/www

# Copy application
COPY . .

# Install production PHP dependencies
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction

# Install frontend dependencies
RUN npm ci

# Build Vite assets
RUN npm run build

# Laravel writable directories
RUN chmod -R 775 storage bootstrap/cache

# Railway fallback port
EXPOSE 10000

# Start Laravel
# Railway provides PORT automatically.
CMD ["sh", "-c", "php artisan serve --host=0.0.0.0 --port=${PORT:-10000}"]