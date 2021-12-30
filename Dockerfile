FROM php:8.1-apache

RUN docker-php-ext-install pdo pdo_mysql

# Enable mod_rewrite for images with apache
RUN if command -v a2enmod >/dev/null 2>&1; then \
        a2enmod rewrite headers \
    ;fi