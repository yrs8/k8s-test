FROM php:8.3.4-fpm
COPY ./index.php /var/www/html/index.php

# PHP-FPM の設定変更
RUN sed -i 's/listen = 127.0.0.1:9000/listen = 9000/g' /usr/local/etc/php-fpm.d/www.conf