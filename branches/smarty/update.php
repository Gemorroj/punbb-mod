<?php
// После установки УДАЛИТЬ !!!
define('PUN_ROOT', './');
require PUN_ROOT . 'config.php';
require PUN_ROOT . 'include/common_db.php';


$q = mysql_query('SELECT * FROM `config`');
while ($arr = mysql_fetch_assoc($q)) {
    if ($arr['conf_name'] == 'o_show_version') {
        break;
    }
}
$version = @$arr['conf_value'];

if (!$version || $version == 1 || ($version < '0.5.2')) {

    $query = mysql_query('INSERT INTO `config` (`conf_name`, `conf_value`) VALUES ("o_antiflood", "1"), ("o_antiflood_a", "5"), ("o_antiflood_b", "3600")');

    if (!$query) {
        $error[] = mysql_error();
    }

    $query = mysql_query('
        CREATE TABLE `spam_regexp` (
        `id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
        `matches` INT( 11 ) unsigned NOT NULL default "0",
        `regexpr` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
        PRIMARY KEY ( `id` )
        ) ENGINE = MYISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
    ');

    if (!$query) {
        $error[] = mysql_error();
    }

    $query = mysql_query('INSERT INTO `spam_regexp` (`id`, `matches`, `regexpr`) VALUES ("0", "0", "/.*все бесплатно.*/isuU");') or die (mysql_error());

    if (!$query) {
        $error[] = mysql_error();
    }


    $query = mysql_query('UPDATE `config` SET `conf_value` = "0.5.1" WHERE CONVERT( `config`.`conf_name` USING utf8 ) = "o_show_version" LIMIT 1 ;');

    if (!$query) {
        $error[] = mysql_error();
    }

    $version = '0.5.1';
}


if ($version == '0.5.1') {

    $query = mysql_query('ALTER TABLE `search_words` CHANGE `word` `word` VARBINARY( 128 ) NOT NULL');
    if (!$query) {
        $error[] = mysql_error();
    }

    $query = mysql_query('
        ALTER TABLE `users`
        ADD `sex` ENUM( "0", "1" ) NOT NULL AFTER `url` ,
        ADD `birthday` VARCHAR( 10 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `sex` ;
    ');
    if (!$query) {
        $error[] = mysql_error();
    }


    $query = mysql_query('UPDATE `config` SET `conf_value` = "0.5.2" WHERE CONVERT( `config`.`conf_name` USING utf8 ) = "o_show_version" LIMIT 1 ;');

    if (!$query) {
        $error[] = mysql_error();
    }

    $version = '0.5.2';
}


if ($version == '0.5.2') {

    $query = mysql_query('
        INSERT INTO `config` (
            `conf_name`, `conf_value`
        ) VALUES (
            "o_show_post_karma", "1"
        );
    ');
    if (!$query) {
        $error[] = mysql_error();
    }

    $query = mysql_query('
        CREATE TABLE IF NOT EXISTS `karma` (
        `id` int(10) unsigned NOT NULL default "0",
        `to` int(10) unsigned NOT NULL default "0",
        `vote` tinyint(1) NOT NULL default "0",
        `time` int(10) NOT NULL default "0",
        UNIQUE KEY `id` (`id`,`to`),
        KEY `to` (`to`),
        KEY `to_2` (`to`,`vote`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
    ');
    if (!$query) {
        $error[] = mysql_error();
    }

    $query = mysql_query('UPDATE `config` SET `conf_value` = "0.5.3" WHERE CONVERT( `config`.`conf_name` USING utf8 ) = "o_show_version" LIMIT 1 ;');
    if (!$query) {
        $error[] = mysql_error();
    }

    $version = '0.5.3';
}


if ($version = '0.5.3') {

    $query = mysql_query('UPDATE `config` SET `conf_value` = "0.5.4" WHERE CONVERT( `config`.`conf_name` USING utf8 ) = "o_show_version" LIMIT 1 ;');
    if (!$query) {
        $error[] = mysql_error();
    }

    $version = '0.5.4';
}

if ($version = '0.5.4') {

    $query = mysql_query('UPDATE `config` SET `conf_value` = "0.5.5" WHERE CONVERT( `config`.`conf_name` USING utf8 ) = "o_show_version" LIMIT 1 ;');
    if (!$query) {
        $error[] = mysql_error();
    }

    $version = '0.5.5';
}

if ($version = '0.5.5') {

    $query = mysql_query('UPDATE `config` SET `conf_value` = "1.2.22" WHERE CONVERT( `config`.`conf_name` USING utf8 ) = "o_cur_version" LIMIT 1 ;');
    if (!$query) {
        $error[] = mysql_error();
    }

    $query = mysql_query('UPDATE `config` SET `conf_value` = "0.5.6" WHERE CONVERT( `config`.`conf_name` USING utf8 ) = "o_show_version" LIMIT 1 ;');
    if (!$query) {
        $error[] = mysql_error();
    }

    $version = '0.5.6';
}

if ($version = '0.5.6') {

    $query = mysql_query('UPDATE `config` SET `conf_value` = "1.2.23" WHERE CONVERT( `config`.`conf_name` USING utf8 ) = "o_cur_version" LIMIT 1 ;');
    if (!$query) {
        $error[] = mysql_error();
    }

    $query = mysql_query('UPDATE `config` SET `conf_value` = "0.5.7" WHERE CONVERT( `config`.`conf_name` USING utf8 ) = "o_show_version" LIMIT 1 ;');
    if (!$query) {
        $error[] = mysql_error();
    }

    $version = '0.5.7';
}

if ($version = '0.5.7') {

    $query = mysql_query('ALTER TABLE `karma` CHANGE `vote` `vote` ENUM( "1", "-1" ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT "1";');
    if (!$query) {
        $error[] = mysql_error();
    }

    $query = mysql_query('UPDATE `karma` SET `vote` = "1" WHERE `vote` = "";');
    if (!$query) {
        $error[] = mysql_error();
    }


    $query = mysql_query('UPDATE `config` SET `conf_value` = "0.5.8" WHERE CONVERT( `config`.`conf_name` USING utf8 ) = "o_show_version" LIMIT 1 ;');
    if (!$query) {
        $error[] = mysql_error();
    }

    $version = '0.5.8';
}

if ($version = '0.5.8') {
    $query = mysql_query('ALTER TABLE `topics` ADD INDEX `last_post_id_idx` ( `last_post_id` );');
    if (!$query) {
        $error[] = mysql_error();
    }

    $query = mysql_query('UPDATE `users` SET `style` = "" WHERE `id` = 1;');
    if (!$query) {
        $error[] = mysql_error();
    }

    $query = mysql_query('UPDATE `users` SET `style_wap` = "";');
    if (!$query) {
        $error[] = mysql_error();
    }

    $query = mysql_query('UPDATE `config` SET `conf_value` = "0.5.9" WHERE CONVERT( `config`.`conf_name` USING utf8 ) = "o_show_version" LIMIT 1 ;');
    if (!$query) {
        $error[] = mysql_error();
    }


    $query = mysql_query('UPDATE `config` SET `conf_value` = "5" WHERE `config`.`conf_name` = "o_spam_gid";');
    if (!$query) {
        $error[] = mysql_error();
    }


    $version = '0.5.9';
}

header('Expires: Thu, 21 Jul 1977 07:30:00 GMT');
header('Last-Modified: ' . gmdate('r') . ' GMT');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Content-Type: text/html; charset=utf-8');

if (@$error) {
    echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="refresh" content="15;URL=index.php" />
<title>Форум / Переадресация</title>
<link rel="stylesheet" type="text/css" href="style/Oxygen_mod.css" />
</head>
<body>
<div id="punwrap">
<div id="punredirect" class="pun">
<div class="block">
<h2>Ошибка При Обновлении Базы</h2>
<div class="box">
<div class="inbox">
<p>';
    foreach ($error as $v) {
        echo $v . '<br/>';
    }
    echo '</p>
</div>
</div>
</div>
</div>
</div>
</body>
</html>';
    exit;
}

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="refresh" content="5; url=index.php" />
<title>Форум / Переадресация</title>
<link rel="stylesheet" type="text/css" href="style/Oxygen_mod.css" />
</head>
<body>
<div id="punwrap">
<div id="punredirect" class="pun">
<div class="block">
<h2>Переадресация</h2>
<div class="box">
<div class="inbox">
<p>Таблицы Обновлены<br />Не забудтье удалить файл <strong>update.php</strong><br />Перенаправление &#x2026;<br />
 &#187; <a href="index.php">WEB Версия</a><br />
 &#187; <a href="wap/index.php">WAP Версия</a>
</p>
</div>
</div>
</div>
</div>
</div>
</body>
</html>';
?>