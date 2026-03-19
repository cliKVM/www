# MoleKVM Apache/PHP Docker Image (Plesk-Compatible)
FROM php:8.2-apache

# Install required PHP extensions for Plesk-like environment
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    curl \
    vim \
    nano \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd mysqli pdo_mysql zip opcache

# Enable Apache modules (common in Plesk)
RUN a2enmod rewrite headers ssl deflate expires

# Copy Apache configuration (Plesk-like setup)
COPY apache-vhost.conf /etc/apache2/sites-available/000-default.conf

# Set PHP configuration (Plesk-like defaults)
RUN echo "upload_max_filesize = 64M" >> /usr/local/etc/php/conf.d/plesk.ini \
    && echo "post_max_size = 64M" >> /usr/local/etc/php/conf.d/plesk.ini \
    && echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/plesk.ini \
    && echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/plesk.ini \
    && echo "max_input_vars = 3000" >> /usr/local/etc/php/conf.d/plesk.ini \
    && echo "error_log = /var/log/apache2/php-error.log" >> /usr/local/etc/php/conf.d/plesk.ini

# Create log directory
RUN mkdir -p /var/log/apache2 && touch /var/log/apache2/php-error.log \
    && chown -R www-data:www-data /var/log/apache2

# Set working directory (Plesk httpdocs equivalent)
WORKDIR /var/www/html

# Copy application files
COPY index.php /var/www/html/
COPY .env.example /var/www/html/.env

# Set proper permissions (Plesk-style)
RUN chown -R www-data:www-data /var/www/html \
    && chmod 644 /var/www/html/index.php \
    && chmod 644 /var/www/html/.env

# Expose port 80
EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

# Start Apache
CMD ["apache2-foreground"]
