ARG APP_IMAGE=sentravaux-app:latest
FROM ${APP_IMAGE} AS laravel

FROM nginx:1.25-alpine
COPY --from=laravel /var/www/html/public /var/www/html/public
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
WORKDIR /var/www/html/public
