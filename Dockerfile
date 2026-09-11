FROM php:8.2-apache

# Enable mod_rewrite and mod_headers
RUN a2enmod rewrite headers

# Install mysqli and pdo_mysql
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Access rules and security headers (dotfiles, internals, uploads)
COPY docker/apache/portfolio.conf /etc/apache2/conf-available/portfolio.conf
RUN a2enconf portfolio

# Production-safe PHP settings (errors hidden, upload size, sessions)
COPY docker/php/portfolio.ini /usr/local/etc/php/conf.d/portfolio.ini

# Copy project files
COPY . /var/www/html/

# Admin image uploads land here
RUN mkdir -p /var/www/html/uploads \
    && chown -R www-data:www-data /var/www/html/uploads

# Expose port 80
EXPOSE 80
