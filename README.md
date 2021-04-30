# Форум PunBB
Версия: 1.2.23
Версия модификации: 0.6.2
Разработка больше не ведется

Автор: Rickard Andersson ([punbb.org](http://punbb.org/))
Модификация: Gemorroj, tipsun, LookOfff

### Обсуждение мода, а так же новые модификации можно найти здесь:
* [WEB форум](http://forum.wapinet.ru/viewtopic.php?id=69)
* [WAP форум](http://forum.wapinet.ru/wap/viewtopic.php?id=69)
* [Github](https://github.com/Gemorroj/punbb-mod)



### Описание:
Форум имеет 2 версии - WAP и WEB
Возможность смены как WAP, так и WEB оформления
Развитую систему прав пользователей
Загрузка файлов как непосредственно в постах, так и в специальном разделе загрузок
Админ панель (WEB) с множеством настроек
И многое другое...

### Требования
* Apache 2 / Nginx
* MySQL >= 5.7
* PHP >= 7.1

### Установка:
Права на директории cache/, uploaded/, uploads/, img/avatars/, img/thumb/ - 777
Права на директории include/template/wap/new_line/cache/, include/template/wap/new_line/compiled/, include/template/wap/wap/cache/, include/template/wap/wap/compiled/, include/template/wap/xwab/cache/, include/template/wap/xwab/compiled/ - 777
Права на файлы /lang/Russian/stopwords.txt, /lang/English/stopwords.txt - 666

Создаем базу данных, вписываем в файл config.php данные от базы (название, логин/пароль с доступом а БД)
Заходим по адресу http://ваш_сайт/форум/.install.php
Если установка проходит без ошибок, авторизуемся на форуме админом и меняем настройки под себя
После установки не забудьте в профиле поменять пароль админа и удалить файлы .install.php и .update.php

### Авторизация:
* Логин: Admin
* Пароль: 1234

------------
### Обновление форума:

УДАЛЯЕМ ВСЕ ФАЙЛЫ, кроме тех, что в директориях
- uploaded/
- uploads/
- img/avatars/

Заливаем файлы из архива, заносим нужные данные в config.php
Заходим по адресу http://ваш_сайт/форум/update.php
Если обновление проходит без ошибок, авторизуемся на форуме админом и меняем настройки под себя
