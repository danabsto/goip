# Удобный интерфейс для вашего GoIP

## Установка и  настройка
Предварительно  необходимо установить и настроить необходимые php пакеты, а также nginx сервер и php-fpm.
### Загрузить и установить необходимые пакеты для проекта
```
composer global require "fxp/composer-asset-plugin"
composer install
``` 
### Сгенерировать конфигурационные файлы для промышленного окружения
```
php init --env=production --overwrite=All
```
После генерации конфигурационных файлов необходимо их отредактировать и внести ваши настройки в файлах находящихся в каталоге config

### Накатить миграции базы данных
```
php yii migrate/up --migrationPath=@vendor/dektrium/yii2-user/migrations
php yii migrate/up
```

### Сервисные скрипты
Для получения балансов по картам, получения СМС и других сервисных действий необходимо добавить в cron запуск скриптов обработки
```
GOIP_HOME=/home/goip/
*   *   *   *   *  /usr/bin/php $GOIP_HOME/yii sms/get
*   *   *   *   *  sleep 10; /usr/bin/php $GOIP_HOME/yii sms/get
*   *   *   *   *  sleep 20; /usr/bin/php $GOIP_HOME/yii sms/get
*   *   *   *   *  sleep 30; /usr/bin/php $GOIP_HOME/yii sms/get
*   *   *   *   *  sleep 40; /usr/bin/php $GOIP_HOME/yii sms/get
*   *   *   *   *  sleep 50; /usr/bin/php $GOIP_HOME/yii sms/get
*   *   *   *   *  /usr/bin/php $GOIP_HOME/yii status
10  */4   *   *   *  /usr/bin/php $GOIP_HOME/yii balances
0 12    *   *   1  /usr/bin/php $GOIP_HOME/yii calls
* * * * * flock -n ~/sms.lock -c "$GOIP_HOME/yii sms/send"
30 10 10 * * /usr/bin/php $GOIP_HOME/yii sms/block
```