FROM nginx:1.25.4
ARG ENV_SHORT
COPY ./default-${ENV_SHORT}.conf /etc/nginx/conf.d/default.conf
RUN mkdir -p /var/www/html
RUN touch /var/www/html/index.php