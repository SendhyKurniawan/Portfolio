FROM php:8.2-apache

# Enable mod_rewrite
RUN a2enmod rewrite

# Install mysqli and pdo_mysql
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Never serve dotfiles/dirs (.env, .git) from the mounted web root
RUN printf '%s\n' \
      '<DirectoryMatch "/\.">' '    Require all denied' '</DirectoryMatch>' \
      '<FilesMatch "^\.">' '    Require all denied' '</FilesMatch>' \
      > /etc/apache2/conf-available/deny-dotfiles.conf \
    && a2enconf deny-dotfiles

# Copy project files
COPY . /var/www/html/

# Create necessary directories and set permissions
RUN mkdir -p /var/www/html/data \
    && mkdir -p /var/www/html/img/uploads \
    && chown -R www-data:www-data /var/www/html/data \
    && chown -R www-data:www-data /var/www/html/img/uploads

# Expose port 80
EXPOSE 80
