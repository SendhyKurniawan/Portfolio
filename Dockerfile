FROM php:8.2-apache

# Enable mod_rewrite
RUN a2enmod rewrite

# Copy project files
COPY . /var/www/html/

# Create necessary directories and set permissions
RUN mkdir -p /var/www/html/data \
    && mkdir -p /var/www/html/img/uploads \
    && chown -R www-data:www-data /var/www/html/data \
    && chown -R www-data:www-data /var/www/html/img/uploads

# Expose port 80
EXPOSE 80
