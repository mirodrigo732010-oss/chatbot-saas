FROM richarvey/nginx-php-fpm:3.1.9
ENV WEBROOT /app/public
ENV PHP_ERRORS_STDERR 1
ENV RUN_SCRIPTS 1
ENV REAL_IP_HEADER 1
ENV SKIP_COMPOSER 1
COPY . .
RUN composer install --no-dev --optimize-autoloader --no-scripts
RUN chown -R www-www-data /app && chmod -R 777 /app/storage && chmod -R 777 /app/bootstrap/cache
EXPOSE 80
