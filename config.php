<?php

// Настройки
// $_SERVER['REMOTE_ADDR'] = (isset($_SERVER['HTTP_X_REAL_IP']) && preg_match('/^([1-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(\.([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3}$/', $_SERVER['HTTP_X_REAL_IP'])) ? $_SERVER['HTTP_X_REAL_IP'] : $_SERVER['REMOTE_ADDR']; // nginx/angie

$db_host = 'localhost'; // Хост
$db_name = 'forum';     // Имя БД
$db_username = 'root';  // Имя пользователя БД
$db_password = '';      // Пароль пользователя БД
$db_prefix = null;

// Это можете не трогать
$cookie_name = 'punbb_cookie';
$cookie_domain = '';
$cookie_path = '/';
$cookie_secure = 0;
$cookie_seed = 'change me';

\define('PUN', 1);
