FROM nginx:1.25.4
COPY ./default-local.conf /etc/nginx/conf.d/default.conf

# ドキュメントルート作成
RUN mkdir -p /var/www/html

# ドキュメントルートに空ファイル作成
RUN touch /var/www/html/index.php