<?php
// После установки УДАЛИТЬ !!!
\define('PUN_ROOT', './');
require PUN_ROOT . 'config.php';
require PUN_ROOT . 'include/DBLayer.php';


$q = $db->query('SELECT * FROM `config`');
while ($arr = $q->fetch_assoc()) {
    if ($arr['conf_name'] === 'o_show_version') {
        break;
    }
}
$version = @$arr['conf_value'];

if (!$version || $version == 1 || ($version < '0.5.2')) {

    $query = $db->query('INSERT INTO `config` (`conf_name`, `conf_value`) VALUES ("o_antiflood", "1"), ("o_antiflood_a", "5"), ("o_antiflood_b", "3600")');

    if (!$query) {
        $error[] = var_export($db->error(), true);
    }

    $query = $db->query('
        CREATE TABLE `spam_regexp` (
        `id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
        `matches` INT( 11 ) unsigned NOT NULL default "0",
        `regexpr` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
        PRIMARY KEY ( `id` )
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
    ');

    if (!$query) {
        $error[] = var_export($db->error(), true);
    }

    $query = $db->query('INSERT INTO `spam_regexp` (`id`, `matches`, `regexpr`) VALUES ("0", "0", "/.*все бесплатно.*/isuU");');

    if (!$query) {
        $error[] = var_export($db->error(), true);
    }


    $query = $db->query('UPDATE `config` SET `conf_value` = "0.5.1" WHERE CONVERT( `config`.`conf_name` USING utf8 ) = "o_show_version" LIMIT 1 ;');

    if (!$query) {
        $error[] = var_export($db->error(), true);
    }

    $version = '0.5.1';
}

if ($version == '0.5.1') {

    $query = $db->query('ALTER TABLE `search_words` CHANGE `word` `word` VARBINARY( 128 ) NOT NULL');
    if (!$query) {
        $error[] = var_export($db->error(), true);
    }

    $query = $db->query('
        ALTER TABLE `users`
        ADD `sex` ENUM( "0", "1" ) NOT NULL AFTER `url` ,
        ADD `birthday` VARCHAR( 10 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL  default \'\' AFTER `sex` ;
    ');
    if (!$query) {
        $error[] = var_export($db->error(), true);
    }


    $query = $db->query('UPDATE `config` SET `conf_value` = "0.5.2" WHERE CONVERT( `config`.`conf_name` USING utf8 ) = "o_show_version" LIMIT 1 ;');

    if (!$query) {
        $error[] = var_export($db->error(), true);
    }

    $version = '0.5.2';
}

if ($version == '0.5.2') {

    $query = $db->query('
        INSERT INTO `config` (
            `conf_name`, `conf_value`
        ) VALUES (
            "o_show_post_karma", "1"
        );
    ');
    if (!$query) {
        $error[] = var_export($db->error(), true);
    }

    $query = $db->query('
        CREATE TABLE IF NOT EXISTS `karma` (
        `id` int(10) unsigned NOT NULL default "0",
        `to` int(10) unsigned NOT NULL default "0",
        `vote` tinyint(1) NOT NULL default "0",
        `time` int(10) NOT NULL default "0",
        UNIQUE KEY `id` (`id`,`to`),
        KEY `to` (`to`),
        KEY `to_2` (`to`,`vote`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ');
    if (!$query) {
        $error[] = var_export($db->error(), true);
    }

    $query = $db->query('UPDATE `config` SET `conf_value` = "0.5.3" WHERE CONVERT( `config`.`conf_name` USING utf8 ) = "o_show_version" LIMIT 1 ;');
    if (!$query) {
        $error[] = var_export($db->error(), true);
    }

    $version = '0.5.3';
}

if ($version == '0.5.3') {

    $query = $db->query('UPDATE `config` SET `conf_value` = "0.5.4" WHERE CONVERT( `config`.`conf_name` USING utf8 ) = "o_show_version" LIMIT 1 ;');
    if (!$query) {
        $error[] = var_export($db->error(), true);
    }

    $version = '0.5.4';
}

if ($version == '0.5.4') {

    $query = $db->query('UPDATE `config` SET `conf_value` = "0.5.5" WHERE CONVERT( `config`.`conf_name` USING utf8 ) = "o_show_version" LIMIT 1 ;');
    if (!$query) {
        $error[] = var_export($db->error(), true);
    }

    $version = '0.5.5';
}

if ($version == '0.5.5') {

    $query = $db->query('UPDATE `config` SET `conf_value` = "1.2.22" WHERE CONVERT( `config`.`conf_name` USING utf8 ) = "o_cur_version" LIMIT 1 ;');
    if (!$query) {
        $error[] = var_export($db->error(), true);
    }

    $query = $db->query('UPDATE `config` SET `conf_value` = "0.5.6" WHERE CONVERT( `config`.`conf_name` USING utf8 ) = "o_show_version" LIMIT 1 ;');
    if (!$query) {
        $error[] = var_export($db->error(), true);
    }

    $version = '0.5.6';
}

if ($version == '0.5.6') {

    $query = $db->query('UPDATE `config` SET `conf_value` = "1.2.23" WHERE CONVERT( `config`.`conf_name` USING utf8 ) = "o_cur_version" LIMIT 1 ;');
    if (!$query) {
        $error[] = var_export($db->error(), true);
    }

    $query = $db->query('UPDATE `config` SET `conf_value` = "0.5.7" WHERE CONVERT( `config`.`conf_name` USING utf8 ) = "o_show_version" LIMIT 1 ;');
    if (!$query) {
        $error[] = var_export($db->error(), true);
    }

    $version = '0.5.7';
}

if ($version == '0.5.7') {

    $query = $db->query('ALTER TABLE `karma` CHANGE `vote` `vote` ENUM( "1", "-1" ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT "1";');
    if (!$query) {
        $error[] = var_export($db->error(), true);
    }

    $query = $db->query('UPDATE `karma` SET `vote` = "1" WHERE `vote` = "";');
    if (!$query) {
        $error[] = var_export($db->error(), true);
    }


    $query = $db->query('UPDATE `config` SET `conf_value` = "0.5.8" WHERE CONVERT( `config`.`conf_name` USING utf8 ) = "o_show_version" LIMIT 1 ;');
    if (!$query) {
        $error[] = var_export($db->error(), true);
    }

    $version = '0.5.8';
}

if ($version == '0.5.8') {
    $query = $db->query('ALTER TABLE `topics` ADD INDEX `last_post_id_idx` ( `last_post_id` );');
    if (!$query) {
        $error[] = var_export($db->error(), true);
    }

    $query = $db->query('UPDATE `users` SET `style` = "" WHERE `id` = 1;');
    if (!$query) {
        $error[] = var_export($db->error(), true);
    }

    $query = $db->query('UPDATE `users` SET `style_wap` = "";');
    if (!$query) {
        $error[] = var_export($db->error(), true);
    }

    $query = $db->query('UPDATE `config` SET `conf_value` = "0.5.9" WHERE CONVERT( `config`.`conf_name` USING utf8 ) = "o_show_version" LIMIT 1 ;');
    if (!$query) {
        $error[] = var_export($db->error(), true);
    }


    $query = $db->query('UPDATE `config` SET `conf_value` = "5" WHERE `config`.`conf_name` = "o_spam_gid";');
    if (!$query) {
        $error[] = var_export($db->error(), true);
    }

    $version = '0.5.9';
}

if ($version == '0.5.9') {
    $query = $db->query('UPDATE `config` SET `conf_value` = "0.6.0" WHERE CONVERT( `config`.`conf_name` USING utf8 ) = "o_show_version" LIMIT 1 ;');
    if (!$query) {
        $error[] = var_export($db->error(), true);
    }

    $version = '0.6.0';
}

if ($version == '0.6.0') {
    $query = $db->query('UPDATE `config` SET `conf_value` = "0.6.1" WHERE CONVERT( `config`.`conf_name` USING utf8 ) = "o_show_version" LIMIT 1 ;');
    if (!$query) {
        $error[] = var_export($db->error(), true);
    }

    $query = $db->query("DELETE FROM `config` WHERE `config`.`conf_name` = 'antispam_enabled'");
    if (!$query) {
        $error[] = var_export($db->error(), true);
    }

    $query = $db->query('DROP TABLE IF EXISTS spam_repository');
    if (!$query) {
        $error[] = var_export($db->error(), true);
    }

    $query = $db->query('DROP TABLE IF EXISTS spam_regexp');
    if (!$query) {
        $error[] = var_export($db->error(), true);
    }


    $query = $db->query("ALTER TABLE `karma` CHANGE `id` `id` INT(10) UNSIGNED NOT NULL COMMENT 'id user';");
    if (!$query) {
        $error[] = var_export($db->error(), true);
    }
    $query = $db->query("ALTER TABLE `karma` CHANGE `to` `to` INT(10) UNSIGNED NOT NULL COMMENT 'id user to';");
    if (!$query) {
        $error[] = var_export($db->error(), true);
    }
    $query = $db->query("ALTER TABLE `karma` DROP INDEX `to`, DROP INDEX `to_2`, ADD INDEX `to_vote` (`to`, `vote`) USING BTREE;");
    if (!$query) {
        $error[] = var_export($db->error(), true);
    }

    $query = $db->query("ALTER TABLE `online` DROP INDEX `online_user_id_idx`;");
    if (!$query) {
        $error[] = var_export($db->error(), true);
    }

    $version = '0.6.1';
}

if ($version == '0.6.1') {
    $query = $db->query('UPDATE `config` SET `conf_value` = "0.6.2" WHERE `config`.`conf_name` = "o_show_version" LIMIT 1 ;');
    if (!$query) {
        $error[] = var_export($db->error(), true);
    }

    $result = $db->query('SHOW TABLE STATUS');
    while ($row = $db->fetch_assoc($result)) {
        if (!$db->query('ALTER TABLE `'.\str_replace('`', '``', $row['Name']).'` CONVERT TO CHARACTER SET utf8mb4;')) {
            $error[] = \var_export($db->error(), true);
        }
    }
}

\header('Expires: Thu, 21 Jul 1977 07:30:00 GMT');
\header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
\header('Cache-Control: post-check=0, pre-check=0', false);
\header('Content-Type: text/html; charset=utf-8');

if (@$error) {
    echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
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

echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
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
<p>Таблицы обновлены<br />Не забудьте удалить файлы <strong>install.php</strong> и <strong>update.php</strong><br />Перенаправление &#x2026;<br />
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
