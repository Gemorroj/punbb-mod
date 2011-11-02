<?php
// Настройки
$_SERVER['REMOTE_ADDR'] = preg_match('/^([1-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(\.([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3}$/', $_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['HTTP_X_REAL_IP'] : $_SERVER['REMOTE_ADDR']; //nginx

$db_host = 'localhost'; // Хост
$db_name = 'forum';     // Имя БД
$db_username = 'root';  // Имя пользователя БД
$db_password = '';      // Проль пользователя БД
$p_connect = false;     // Постоянное соединение (включать только если у форума большая посещаемость)
$db_prefix = null;



// Это можете не трогать
$cookie_name = 'punbb_cookie';
$cookie_domain = '';
$cookie_path = '/';
$cookie_secure = 0;
$cookie_seed = '02ere56958';

define('PUN', 1);

?>