FROM php:8.3.4-fpm
ARG ENV
COPY ./*.php /var/www/html/

# php-fpm の設定変更
RUN sed -i 's/listen = 127.0.0.1:9000/listen = 9000/g' /usr/local/etc/php-fpm.d/www.conf

# php.ini の設定変更
RUN cp /usr/local/etc/php/php.ini-${ENV} /usr/local/etc/php/php.ini \
    && sed -i -e 's/expose_php = On/expose_php = Off/' /usr/local/etc/php/php.ini