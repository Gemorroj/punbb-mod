<?php
// После установки УДАЛИТЬ !!!

header('Expires: Thu, 21 Jul 1977 07:30:00 GMT');
header('Last-Modified: ' . gmdate('r') . ' GMT');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Content-Type: text/html; charset=utf-8');


$register_globals = @ini_get('register_globals');
if ($register_globals == 1 || strtolower($register_globals) == 'on') {
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Форум / Warning!</title>
<link rel="stylesheet" type="text/css" href="style/Oxygen_mod.css" />
</head>
<body>
<div id="punwrap">
<div id="punredirect" class="pun">
<div class="block">
<h2>Register Globals</h2>
<div class="box">
<div class="inbox">
<p>У Вас <strong>ВКЛЮЧЕНЫ</strong> глобальные переменные (register_globals on). Для правильной работы форума следует их отключить (register_globals off).<br />После этого повторите установку форума.</p>
</div>
</div>
</div>
</div>
</div>
</body>
</html>';
exit;
}

if (!function_exists('mb_internal_encoding')) {
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Форум / Warning!</title>
<link rel="stylesheet" type="text/css" href="style/Oxygen_mod.css" />
</head>
<body>
<div id="punwrap">
<div id="punredirect" class="pun">
<div class="block">
<h2>MBSTRING</h2>
<div class="box">
<div class="inbox">
<p>Вероятно, у Вас не установлена библиотека MBSTRING. Для правильной работы форума установите ее.<br />После этого повторите установку форума.</p>
</div>
</div>
</div>
</div>
</div>
</body>
</html>';
exit;
}

if (version_compare(PHP_VERSION, '5.2.3', '<')) {
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Форум / Warning!</title>
<link rel="stylesheet" type="text/css" href="style/Oxygen_mod.css" />
</head>
<body>
<div id="punwrap">
<div id="punredirect" class="pun">
<div class="block">
<h2>PHP 5.2</h2>
<div class="box">
<div class="inbox">
<p>Ваша версия PHP устарела.<br />Для правильной работы форума требуется версия PHP интерпретатора не менее <strong>5.2.3</strong><br />Обновите PHP интерпретатор и повторите установку форума.</p>
</div>
</div>
</div>
</div>
</div>
</body>
</html>';
exit;
}


define('PUN_ROOT', './');
require PUN_ROOT . 'config.php';
require PUN_ROOT . 'include/common_db.php';

mysql_query("CREATE TABLE IF NOT EXISTS `bans` (
`id` int(10) unsigned NOT NULL auto_increment,
`username` varchar(200) default NULL,
`ip` varchar(255) default NULL,
`email` varchar(50) default NULL,
`message` varchar(255) default NULL,
`expire` int(10) unsigned default NULL,
PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;") or die (mysql_error());


mysql_query("CREATE TABLE IF NOT EXISTS `categories` (
`id` int(10) unsigned NOT NULL auto_increment,
`cat_name` varchar(80) NOT NULL default 'New Category',
`disp_position` int(10) NOT NULL default '0',
PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;") or die (mysql_error());


mysql_query("CREATE TABLE IF NOT EXISTS `censoring` (
`id` int(10) unsigned NOT NULL auto_increment,
`search_for` varchar(60) NOT NULL default '',
`replace_with` varchar(60) NOT NULL default '',
PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;") or die (mysql_error());


mysql_query("CREATE TABLE IF NOT EXISTS `config` (
`conf_name` varchar(255) NOT NULL default '',
`conf_value` text,
PRIMARY KEY  (`conf_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;") or die (mysql_error());


mysql_query("INSERT INTO `config` (`conf_name`, `conf_value`) VALUES
('o_cur_version', '1.2.23'),
('o_board_title', 'Форум'),
('o_board_desc', NULL),
('o_server_timezone', '3'),
('o_time_format', 'H:i:s'),
('o_date_format', 'Y-m-d'),
('o_timeout_visit', '600'),
('o_timeout_online', '500'),
('o_timeout_merge', '600'),
('o_timeout_reg', '600'),
('o_antiflood', '0'),
('o_antiflood_a', '5'),
('o_antiflood_b', '3600'),
('o_redirect_delay', '1'),
('o_show_post_karma', '1'),
('o_show_user_info', '1'),
('o_show_post_count', '1'),
('o_show_moderators', '0'),
('o_smilies', '1'),
('o_smilies_sig', '1'),
('o_make_links', '0'),
('o_default_lang', 'Russian'),
('o_default_style', 'VbStyle-Black'),
('o_default_style_wap', 'wap'),
('o_default_user_group', '4'),
('o_topic_review', '15'),
('o_disp_topics_default', '30'),
('o_disp_posts_default', '25'),
('o_indent_num_spaces', '4'),
('o_quickpost', '1'),
('o_users_online', '1'),
('o_censoring', '0'),
('o_ranks', '1'),
('o_show_dot', '0'),
('o_quickjump', '1'),
('o_gzip', '1'),
('o_additional_navlinks', ''),
('o_report_method', '0'),
('o_regs_report', '0'),
('o_mailing_list', 'admin@".$_SERVER['HTTP_HOST']."'),
('o_avatars', '1'),
('o_avatars_dir', 'img/avatars'),
('o_avatars_width', '60'),
('o_avatars_height', '60'),
('o_avatars_size', '10240'),
('o_search_all_forums', '1'),
('o_base_url', 'http://".$_SERVER['HTTP_HOST'].str_replace('\\','/',dirname($_SERVER['PHP_SELF']))."'),
('o_admin_email', 'admin@".$_SERVER['HTTP_HOST']."'),
('o_webmaster_email', 'admin@".$_SERVER['HTTP_HOST']."'),
('o_subscriptions', '0'),
('o_smtp_host', NULL),
('o_smtp_user', NULL),
('o_smtp_pass', NULL),
('o_regs_allow', '1'),
('o_regs_verify', '0'),
('o_announcement', '0'),
('o_announcement_message', 'Превед =)'),
('o_rules', '0'),
('o_rules_message', 'Правил нет =)'),
('o_maintenance', '0'),
('o_maintenance_message', 'Форум на ремонте, зайдите позднее<br />'),
('p_mod_edit_users', '1'),
('p_mod_rename_users', '0'),
('p_mod_change_passwords', '0'),
('p_mod_ban_users', '0'),
('p_message_bbcode', '1'),
('p_message_img_tag', '1'),
('p_message_all_caps', '1'),
('p_subject_all_caps', '1'),
('p_sig_all_caps', '1'),
('p_sig_bbcode', '1'),
('p_sig_img_tag', '0'),
('p_sig_length', '255'),
('p_sig_lines', '4'),
('p_allow_banned_email', '1'),
('p_allow_dupe_email', '0'),
('p_force_guest_email', '0'),
('o_pms_enabled', '1'),
('o_pms_mess_per_page', '10'),
('o_regs_verify_image', '1'),
('o_spam_gid', '5'),
('file_allowed_ext', 'gif,png,jpg,zip,rar,7z,tgz,gz,bz2,mp3,wav,wma,3gp,avi,mpg,wmv,exe'),
('file_image_ext', 'gif,png,jpg'),
('file_max_width', '1600'),
('file_max_height', '1200'),
('file_max_post_files', '5'),
('file_max_size', '500000'),
('file_first_only', '0'),
('file_popup_info', '1'),
('file_preview_height', '240'),
('file_preview_width', '240'),
('file_thumb_width', '80'),
('file_thumb_height', '80'),
('file_thumb_path', 'img/thumb/'),
('file_upload_path', 'uploads/'),
('poll_enabled', '1'),
('antispam_enabled', '0'),
('o_show_version', '0.5.9');") or die (mysql_error());


mysql_query("CREATE TABLE IF NOT EXISTS `forums` (
`id` int(10) unsigned NOT NULL auto_increment,
`forum_name` varchar(80) NOT NULL default 'New forum',
`forum_desc` text,
`redirect_url` varchar(100) default NULL,
`moderators` text,
`num_topics` mediumint(8) unsigned NOT NULL default '0',
`num_posts` mediumint(8) unsigned NOT NULL default '0',
`last_post` int(10) unsigned default NULL,
`last_post_id` int(10) unsigned default NULL,
`last_poster` varchar(200) default NULL,
`sort_by` tinyint(1) NOT NULL default '0',
`disp_position` int(10) NOT NULL default '0',
`cat_id` int(10) unsigned NOT NULL default '0',
PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;") or die (mysql_error());


mysql_query("CREATE TABLE IF NOT EXISTS `forum_perms` (
`group_id` int(10) NOT NULL default '0',
`forum_id` int(10) NOT NULL default '0',
`read_forum` tinyint(1) NOT NULL default '1',
`post_replies` tinyint(1) NOT NULL default '1',
`post_topics` tinyint(1) NOT NULL default '1',
`file_upload` tinyint(1) NOT NULL default '0',
`file_download` tinyint(1) NOT NULL default '0',
`file_limit` int(10) NOT NULL default '0',
PRIMARY KEY  (`group_id`,`forum_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;") or die (mysql_error());


mysql_query("CREATE TABLE IF NOT EXISTS `groups` (
`g_id` int(10) unsigned NOT NULL auto_increment,
`g_title` varchar(50) NOT NULL default '',
`g_user_title` varchar(50) default NULL,
`g_read_board` tinyint(1) NOT NULL default '1',
`g_post_replies` tinyint(1) NOT NULL default '1',
`g_post_topics` tinyint(1) NOT NULL default '1',
`g_post_polls` tinyint(1) NOT NULL default '1',
`g_edit_posts` tinyint(1) NOT NULL default '1',
`g_delete_posts` tinyint(1) NOT NULL default '1',
`g_delete_topics` tinyint(1) NOT NULL default '1',
`g_set_title` tinyint(1) NOT NULL default '1',
`g_search` tinyint(1) NOT NULL default '1',
`g_search_users` tinyint(1) NOT NULL default '1',
`g_edit_subjects_interval` smallint(6) NOT NULL default '300',
`g_post_flood` smallint(6) NOT NULL default '30',
`g_search_flood` smallint(6) NOT NULL default '30',
`g_file_download` tinyint(1) NOT NULL default '0',
`g_file_upload` tinyint(1) NOT NULL default '0',
`g_file_limit` int(10) NOT NULL default '0',
`g_pm` int(11) NOT NULL default '1',
`g_pm_limit` int(11) NOT NULL default '20',
PRIMARY KEY  (`g_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;") or die (mysql_error());


mysql_query("INSERT INTO `groups` (`g_id`, `g_title`, `g_user_title`, `g_read_board`, `g_post_replies`, `g_post_topics`, `g_post_polls`, `g_edit_posts`, `g_delete_posts`, `g_delete_topics`, `g_set_title`, `g_search`, `g_search_users`, `g_edit_subjects_interval`, `g_post_flood`, `g_search_flood`, `g_file_download`, `g_file_upload`, `g_file_limit`, `g_pm`, `g_pm_limit`) VALUES
(1, 'Administrators', 'Administrator', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, 1, 1, 999, 1, 20),
(2, 'Moderators', 'Moderator', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, 1, 1, 100, 1, 20),
(3, 'Guest', NULL, 1, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 0, 1, 20),
(4, 'Members', NULL, 1, 1, 1, 1, 1, 1, 1, 0, 1, 1, 300, 60, 30, 1, 1, 50, 1, 20),
(5, 'Spammer', 'spammer', 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 20);") or die (mysql_error());


mysql_query("CREATE TABLE IF NOT EXISTS `log_forums` (
`user_id` int(10) unsigned NOT NULL default '0',
`forum_id` int(10) unsigned NOT NULL default '0',
`log_time` int(10) unsigned NOT NULL default '0',
`mark_read` int(10) unsigned NOT NULL default '0',
PRIMARY KEY  (`user_id`,`forum_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;") or die (mysql_error());


mysql_query("CREATE TABLE IF NOT EXISTS `log_topics` (
`user_id` int(10) unsigned NOT NULL default '0',
`topic_id` int(10) unsigned NOT NULL default '0',
`forum_id` int(10) unsigned NOT NULL default '0',
`log_time` int(10) unsigned NOT NULL default '0',
PRIMARY KEY  (`user_id`,`topic_id`,`forum_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;") or die (mysql_error());


mysql_query("CREATE TABLE IF NOT EXISTS `messages` (
`id` int(10) unsigned NOT NULL auto_increment,
`owner` int(10) NOT NULL default '0',
`subject` varchar(120) NOT NULL default '',
`message` text,
`sender` varchar(120) NOT NULL default '',
`sender_id` int(10) NOT NULL default '0',
`posted` int(10) NOT NULL default '0',
`sender_ip` varchar(120) default NULL,
`smileys` tinyint(4) default '1',
`status` tinyint(4) default '0',
`showed` tinyint(4) default '0',
`popup` tinyint(4) default '0',
PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;") or die (mysql_error());


mysql_query("CREATE TABLE IF NOT EXISTS `online` (
`user_id` int(10) unsigned NOT NULL default '1',
`ident` varchar(200) NOT NULL default '',
`logged` int(10) unsigned NOT NULL default '0',
`idle` tinyint(1) NOT NULL default '0',
UNIQUE KEY `online_user_id_ident_idx` (`user_id`,`ident`),
KEY `online_user_id_idx` (`user_id`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8;") or die (mysql_error());


mysql_query("CREATE TABLE IF NOT EXISTS `posts` (
`id` int(10) unsigned NOT NULL auto_increment,
`poster` varchar(200) NOT NULL default '',
`poster_id` int(10) unsigned NOT NULL default '1',
`poster_ip` varchar(15) default NULL,
`poster_email` varchar(50) default NULL,
`message` text,
`hide_smilies` tinyint(1) NOT NULL default '0',
`posted` int(10) unsigned NOT NULL default '0',
`edited` int(10) unsigned default NULL,
`edited_by` varchar(200) default NULL,
`topic_id` int(10) unsigned NOT NULL default '0',
PRIMARY KEY  (`id`),
KEY `posts_topic_id_idx` (`topic_id`),
KEY `posts_multi_idx` (`poster_id`,`topic_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;") or die (mysql_error());


mysql_query("CREATE TABLE IF NOT EXISTS `ranks` (
`id` int(10) unsigned NOT NULL auto_increment,
`rank` varchar(50) NOT NULL default '',
`min_posts` mediumint(8) unsigned NOT NULL default '0',
PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;") or die (mysql_error());


mysql_query("INSERT INTO `ranks` (`id`, `rank`, `min_posts`) VALUES
(1, 'New member', 0),
(2, 'Member', 10);") or die (mysql_error());


mysql_query("CREATE TABLE IF NOT EXISTS `reports` (
`id` int(10) unsigned NOT NULL auto_increment,
`post_id` int(10) unsigned NOT NULL default '0',
`topic_id` int(10) unsigned NOT NULL default '0',
`forum_id` int(10) unsigned NOT NULL default '0',
`reported_by` int(10) unsigned NOT NULL default '0',
`created` int(10) unsigned NOT NULL default '0',
`message` text,
`zapped` int(10) unsigned default NULL,
`zapped_by` int(10) unsigned default NULL,
PRIMARY KEY  (`id`),
KEY `reports_zapped_idx` (`zapped`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;") or die (mysql_error());


mysql_query("CREATE TABLE IF NOT EXISTS `search_cache` (
`id` int(10) unsigned NOT NULL default '0',
`ident` varchar(200) NOT NULL default '',
`search_data` text,
PRIMARY KEY  (`id`),
KEY `search_cache_ident_idx` (`ident`(8))
) ENGINE=MyISAM DEFAULT CHARSET=utf8;") or die (mysql_error());


mysql_query("CREATE TABLE IF NOT EXISTS `search_matches` (
`post_id` int(10) unsigned NOT NULL default '0',
`word_id` mediumint(8) unsigned NOT NULL default '0',
`subject_match` tinyint(1) NOT NULL default '0',
KEY `search_matches_word_id_idx` (`word_id`),
KEY `search_matches_post_id_idx` (`post_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;") or die (mysql_error());


mysql_query("CREATE TABLE IF NOT EXISTS `search_words` (
`id` mediumint(8) unsigned NOT NULL auto_increment,
`word` varbinary(128) NOT NULL default '',
PRIMARY KEY  (`word`),
KEY `search_words_id_idx` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;") or die (mysql_error());


mysql_query("INSERT INTO `search_words` (`id`, `word`) VALUES
(1, 'print');") or die (mysql_error());


mysql_query("CREATE TABLE IF NOT EXISTS `spam_repository` (
`id` int(10) unsigned NOT NULL auto_increment,
`post_id` int(10) unsigned NOT NULL default '0',
`last_gid` int(10) unsigned NOT NULL default '0',
`message` text NOT NULL,
`pattern` text NOT NULL,
PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;") or die (mysql_error());


mysql_query('CREATE TABLE IF NOT EXISTS `spam_regexp` (
`id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
`matches` INT( 11 ) unsigned NOT NULL default "0",
`regexpr` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
PRIMARY KEY ( `id` ) 
) ENGINE = MYISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;') or die (mysql_error());

mysql_query('INSERT INTO `spam_regexp` (`id`, `matches`, `regexpr`) VALUES
("0", "0", "/(.*)все бесплатно(.*)/isuU");') or die (mysql_error());

mysql_query("CREATE TABLE IF NOT EXISTS `subscriptions` (
`user_id` int(10) unsigned NOT NULL default '0',
`topic_id` int(10) unsigned NOT NULL default '0',
PRIMARY KEY  (`user_id`,`topic_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;") or die (mysql_error());


mysql_query("CREATE TABLE IF NOT EXISTS `topics` (
`id` int(10) unsigned NOT NULL auto_increment,
`poster` varchar(200) NOT NULL default '',
`subject` varchar(255) NOT NULL default '',
`posted` int(10) unsigned NOT NULL default '0',
`last_post` int(10) unsigned NOT NULL default '0',
`last_post_id` int(10) unsigned NOT NULL default '0',
`last_poster` varchar(200) default NULL,
`num_views` mediumint(8) unsigned NOT NULL default '0',
`num_replies` mediumint(8) unsigned NOT NULL default '0',
`closed` tinyint(1) NOT NULL default '0',
`sticky` tinyint(1) NOT NULL default '0',
`moved_to` int(10) unsigned default NULL,
`forum_id` int(10) unsigned NOT NULL default '0',
`has_poll` int(10) NOT NULL default '0',
PRIMARY KEY  (`id`),
KEY `topics_forum_id_idx` (`forum_id`),
KEY `topics_moved_to_idx` (`moved_to`),
KEY `last_post_id_idx` (`last_post_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;") or die (mysql_error());


mysql_query("CREATE TABLE IF NOT EXISTS `uploaded` (
`id` int(11) NOT NULL auto_increment,
`file` text NOT NULL,
`user` text NOT NULL,
`user_stat` text NOT NULL,
`data` int(11) NOT NULL default '0',
`uid` int(11) NOT NULL default '0',
`size` int(11) NOT NULL default '0',
`downs` int(11) NOT NULL default '0',
`descr` text NOT NULL,
PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;") or die (mysql_error());


mysql_query("CREATE TABLE IF NOT EXISTS `uploads_conf` (
`g_id` smallint(6) NOT NULL default '0',
`u_fsize` int(10) unsigned NOT NULL default '0',
`p_view` tinyint(4) NOT NULL default '0',
`p_globalview` tinyint(4) NOT NULL default '0',
`p_upload` tinyint(4) NOT NULL default '0',
`p_delete` tinyint(4) NOT NULL default '0',
`p_globaldelete` tinyint(4) NOT NULL default '0',
`p_setop` tinyint(4) NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;") or die (mysql_error());


mysql_query("INSERT INTO `uploads_conf` (`g_id`, `u_fsize`, `p_view`, `p_globalview`, `p_upload`, `p_delete`, `p_globaldelete`, `p_setop`) VALUES
(0, 0, 0, 0, 0, 0, 0, 0),
(1, 99999, 1, 1, 1, 1, 1, 1),
(2, 10000, 1, 1, 1, 0, 0, 0),
(3, 0, 1, 0, 0, 0, 0, 0),
(4, 5000, 1, 1, 1, 0, 0, 0),
(5, 0, 0, 0, 0, 0, 0, 0);") or die (mysql_error());


mysql_query("CREATE TABLE IF NOT EXISTS `uploads_types` (
`id` int(11) NOT NULL auto_increment,
`type` text NOT NULL,
`exts` text NOT NULL,
PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;") or die (mysql_error());


mysql_query("INSERT INTO `uploads_types` (`id`, `type`, `exts`) VALUES
(1, 'Pictures', '.gif .png .jpg .jpeg .jpe'),
(2, 'Documents', '.txt .rtf .pdf .doc .exe .msi'),
(3, 'Archives', '.zip .rar .gz .tgz .bz .bz2 .7z'),
(4, 'Media', '.3gp .avi .mpg .wmv .sxw .mp3 .wav .wma');") or die (mysql_error());


mysql_query("CREATE TABLE IF NOT EXISTS `users` (
`id` int(10) unsigned NOT NULL auto_increment,
`group_id` int(10) unsigned NOT NULL default '4',
`username` varchar(200) NOT NULL default '',
`password` varchar(40) NOT NULL default '',
`email` varchar(50) NOT NULL default '',
`title` varchar(50) default NULL,
`realname` varchar(40) default NULL,
`url` varchar(100) default NULL,
`sex` enum('0','1') NOT NULL default '1',
`birthday` varchar(10) NOT NULL,
`jabber` varchar(75) default NULL,
`icq` varchar(12) default NULL,
`msn` varchar(50) default NULL,
`aim` varchar(30) default NULL,
`yahoo` varchar(30) default NULL,
`location` varchar(30) default NULL,
`use_avatar` tinyint(1) NOT NULL default '0',
`signature` text,
`disp_topics` tinyint(3) unsigned default NULL,
`disp_posts` tinyint(3) unsigned default NULL,
`email_setting` tinyint(1) NOT NULL default '1',
`save_pass` tinyint(1) NOT NULL default '1',
`notify_with_post` tinyint(1) NOT NULL default '0',
`show_smilies` tinyint(1) NOT NULL default '1',
`show_img` tinyint(1) NOT NULL default '1',
`show_img_sig` tinyint(1) NOT NULL default '1',
`show_avatars` tinyint(1) NOT NULL default '1',
`show_sig` tinyint(1) NOT NULL default '1',
`timezone` float NOT NULL default '0',
`language` varchar(25) NOT NULL default 'Russian',
`style` varchar(25) NOT NULL default 'VbStyle-Black',
`style_wap` varchar(25) NOT NULL default 'wap',
`num_posts` int(10) unsigned NOT NULL default '0',
`last_post` int(10) unsigned default NULL,
`registered` int(10) unsigned NOT NULL default '0',
`registration_ip` varchar(15) NOT NULL default '0.0.0.0',
`last_visit` int(10) unsigned NOT NULL default '0',
`admin_note` varchar(30) default NULL,
`activate_string` varchar(50) default NULL,
`activate_key` varchar(8) default NULL,
`num_files` int(10) NOT NULL default '0',
`file_bonus` int(10) NOT NULL default '0',
`show_bbpanel_qpost` tinyint(1) NOT NULL default '0',
`popup_enable` tinyint(4) default '1',
`messages_enable` tinyint(4) default '1',
`mark_after` int(10) NOT NULL default '1296000',
PRIMARY KEY  (`id`),
KEY `users_registered_idx` (`registered`),
KEY `users_username_idx` (`username`(3))
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;") or die (mysql_error());


mysql_query("INSERT INTO `users` (`id`, `group_id`, `username`, `password`, `email`, `title`, `realname`, `url`, `jabber`, `icq`, `msn`, `aim`, `yahoo`, `location`, `use_avatar`, `signature`, `disp_topics`, `disp_posts`, `email_setting`, `save_pass`, `notify_with_post`, `show_smilies`, `show_img`, `show_img_sig`, `show_avatars`, `show_sig`, `timezone`, `language`, `style`, `style_wap`, `num_posts`, `last_post`, `registered`, `registration_ip`, `last_visit`, `admin_note`, `activate_string`, `activate_key`, `popup_enable`, `messages_enable`, `mark_after`) VALUES
(1, 3, 'Guest', 'Guest', 'Guest', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, 1, 1, 0, 1, 1, 1, 1, 1, 0, 'Russian', 'VbStyle-Black', 'wap', 0, NULL, 0, '0.0.0.0', 0, NULL, NULL, NULL, 1, 1, 1296000),
(2, 1, 'Admin', '7110eda4d09e062aa5e4a390b0a572ac0d2c0220', 'admin@".$_SERVER['HTTP_HOST']."', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, 1, 1, 0, 1, 1, 1, 1, 1, 0, 'Russian', '', '', 0, NULL, 0, '127.0.0.1', 0, NULL, NULL, NULL, 1, 1, 1296000);") or die (mysql_error());


mysql_query("CREATE TABLE IF NOT EXISTS `attachments` (
`id` int(10) unsigned NOT NULL auto_increment,
`poster_id` int(10) unsigned NOT NULL default '0',
`topic_id` int(10) unsigned NOT NULL default '0',
`post_id` int(10) unsigned NOT NULL default '0',
`uploaded` int(10) unsigned NOT NULL default '0',
`filename` varchar(255) NOT NULL default 'error.file',
`mime` varchar(64) NOT NULL default '',
`location` text,
`size` int(10) unsigned NOT NULL default '0',
`image_dim` varchar(64) NOT NULL default '',
`downloads` int(10) unsigned NOT NULL default '0',
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;") or die(mysql_error());


mysql_query("CREATE TABLE IF NOT EXISTS `log_polls` (
`pid` int(10) unsigned NOT NULL,
`uid` int(10) unsigned NOT NULL,
PRIMARY KEY (`pid`,`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;") or die(mysql_error());


mysql_query("CREATE TABLE IF NOT EXISTS `polls` (
`id` int(10) unsigned NOT NULL auto_increment,
`description` text,
`data` text,
`expire` int(10) default '0',
`owner` int(10) NOT NULL default '0',
`time` int(10) NOT NULL default '0',
`multiselect` tinyint(1) default '0',
`vcount` int(10) default '0',
`last_edit` int(10) default '0',
`edit_uid` int(10) default '0',
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;") or die(mysql_error());



mysql_query("CREATE TABLE IF NOT EXISTS `karma` (
  `id` int(10) unsigned NOT NULL default '0',
  `to` int(10) unsigned NOT NULL default '0',
  `vote` enum('1', '-1') NOT NULL default '1',
  `time` int(10) NOT NULL default '0',
  UNIQUE KEY `id` (`id`,`to`),
  KEY `to` (`to`),
  KEY `to_2` (`to`,`vote`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;") or die(mysql_error());



@chmod(PUN_ROOT.'uploads/',0777);
@chmod(PUN_ROOT.'uploaded/',0777);
@chmod(PUN_ROOT.'cache/',0777);
@chmod(PUN_ROOT.'tmp/',0777);
@chmod(PUN_ROOT.'img/avatars/',0777);
@chmod(PUN_ROOT.'img/thumb/',0777);
@chmod(PUN_ROOT.'rss.xml',0666);
@chmod(PUN_ROOT.'lang/Russian/stopwords.txt',0666);
@chmod(PUN_ROOT.'lang/English/stopwords.txt',0666);


echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="refresh" content="15; url=index.php" />
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
<p>Таблицы Залиты<br />Не забудтье удалить файл <strong>install.php</strong><br />Перенаправление &hellip;<br />
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