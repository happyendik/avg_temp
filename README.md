Чтобы развернуть проект, выполните следующие шаги:

 - git clone git@github.com:happyendik/avg_temp.git
 - composer install
 - Перейти в папку проекта.
 - ./init
 - Настроить подключение к БД в common/config/main-local.php
 - ./yii migrate
 - Заполнить БД данными, выполнив в консоли команду:
 ./yii weather-api/generate-data
