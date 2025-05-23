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

# Copy composer files first for better caching
COPY composer.json composer.lock ./

# Install composer dependencies
RUN composer install --no-interaction --no-dev --optimize-autoloader --no-scripts

# Copy application files
COPY . /var/www

# Copy PHP-FPM configuration
RUN echo '[www]' > /usr/local/etc/php-fpm.d/www.conf && \
    echo 'user = www-data' >> /usr/local/etc/php-fpm.d/www.conf && \
    echo 'group = www-data' >> /usr/local/etc/php-fpm.d/www.conf && \
    echo 'listen = 127.0.0.1:9000' >> /usr/local/etc/php-fpm.d/www.conf && \
    echo 'pm = dynamic' >> /usr/local/etc/php-fpm.d/www.conf && \
    echo 'pm.max_children = 5' >> /usr/local/etc/php-fpm.d/www.conf && \
    echo 'pm.start_servers = 2' >> /usr/local/etc/php-fpm.d/www.conf && \
    echo 'pm.min_spare_servers = 1' >> /usr/local/etc/php-fpm.d/www.conf && \
    echo 'pm.max_spare_servers = 3' >> /usr/local/etc/php-fpm.d/www.conf

# Copy nginx config
COPY docker/nginx/default.conf /etc/nginx/sites-available/default
RUN ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

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