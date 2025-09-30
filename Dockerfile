# Use official PHP image with FPM
FROM php:8.3-fpm

# Install Nginx, MySQL extension, and utilities
RUN apt-get update && apt-get install -y \
    nginx \
    curl \
    nano \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install mysqli pdo pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

# Remove default Nginx welcome page and default site configs
RUN rm -rf /etc/nginx/sites-enabled/* /etc/nginx/sites-available/* /var/www/html/*

# Copy PHP application files into container
COPY . /var/www/html

# Set working directory
WORKDIR /var/www/html

# Copy custom Nginx config
COPY default.conf /etc/nginx/conf.d/default.conf

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Update PHP-FPM to listen on TCP 127.0.0.1:9000
RUN sed -i 's|^listen = .*|listen = 127.0.0.1:9000|' /usr/local/etc/php-fpm.d/www.conf

# Expose port 80
EXPOSE 80

# Start PHP-FPM and Nginx together
CMD ["sh", "-c", "php-fpm -F & nginx -g 'daemon off;'"]

