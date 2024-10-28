FROM redis:7.2.6-alpine3.20

COPY docker/redis/redis.conf /usr/local/etc/redis/redis.conf

WORKDIR /app
COPY app /app

CMD ["redis-server", "/usr/local/etc/redis/redis.conf"]