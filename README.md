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