FROM nginx:1.25.1-alpine3.17

COPY docker/nginx/conf.d /etc/nginx/conf.d
COPY docker/nginx/include /etc/nginx/include
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf

WORKDIR /app
COPY app /app