
FROM php:8.2-apache

RUN apt-get update && apt-get install -y ca-certificates \
    && docker-php-ext-install mysqli pdo pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

COPY . /var/www/html/

# Remove invisible UTF-8 BOM from all PHP files
RUN find /var/www/html -type f -name '*.php' \
    -exec sed -i '1s/^\xEF\xBB\xBF//' {} +

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]
