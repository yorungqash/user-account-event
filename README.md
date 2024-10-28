# User account event

Шаблон микросервиса, обрабатывающего очередь из сообщений пользовательских аккаунтов

### Предварительные требования

Сборка протестирована под Ubuntu 22.04 LTS с самой актуальной версией docker

-----------------------------------------------------

1) Установка [docker](https://docs.docker.com/engine/install/ubuntu/#uninstall-old-versions)
2) Запуск приложения:
```bash
docker compose up app
```
3) Наблюдаем за логами в контейнере app-worker:
```bash
docker logs -f *container_id*
```
4) Пример запроса для нагрузки сервиса в user-event.http, тело запроса не должно превышать 2Мб
5) Для прямого доступа к redis указываем пароль по-умолчанию из redis.conf:
```bash
docker inspect *container_id* | grep IPAddress
docker exec -it *container_id* sh
redis-cli -h *IPAddress* -p 6379 -a *default_password*
```

-----------------------------------------------------
