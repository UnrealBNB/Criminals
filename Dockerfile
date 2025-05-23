FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nginx \
    supervisor \
    cron

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy application files
COPY . /var/www

# Copy nginx config
COPY docker/nginx/default.conf /etc/nginx/sites-available/default

# Copy supervisor config
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy cron file
COPY docker/cron/crontab /etc/cron.d/criminals-cron

# Give execution rights on the cron job
RUN chmod 0644 /etc/cron.d/criminals-cron

# Apply cron job
RUN crontab /etc/cron.d/criminals-cron

# Create storage directories
RUN mkdir -p storage/logs storage/cache storage/framework storage/framework/sessions storage/framework/cache

# Set permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage \
    && chmod +x /var/www/bin/console

# Install composer dependencies
RUN composer install --no-interaction --no-dev --optimize-autoloader

# Expose port 80
EXPOSE 80

# Start supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]