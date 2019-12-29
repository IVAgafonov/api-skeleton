FROM php:7.3.11-fpm
# copy project
COPY ./ /usr/share/nginx/html/
# add configs
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
# run web server for api docs
CMD ["/bin/bash", "-c", "php-fpm --daemonize && php -S 0.0.0.0:80 -t /usr/share/nginx/html/www/"]