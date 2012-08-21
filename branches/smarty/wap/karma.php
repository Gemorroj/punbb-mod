<?php

define('PUN_ROOT', '../');

require_once(PUN_ROOT . 'include/common.php');

if (! $pun_user['g_read_board']) {
    
    wap_message($lang_common['No view']);
}

$to = isset($_GET['to']) ? (int) $_GET['to'] : null;

if ($to) {
    
    vote($to, (int) @$_GET['vote']);
    
    $pid = (int) @$_GET['pid'];
    wap_redirect('viewtopic.php?pid=' . $pid . '#p' . $pid);
    exit();
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;

// Гость записанный в таблице пользователей (`users`) имеет id = 1. Зачем тогда его учитывать?
if (1 > $id) {
    
    wap_message($lang_common['Bad request']);
}

$q = 'SELECT `username` '
   . 'FROM `' . $db->prefix . 'users` '
   . 'WHERE `id` = ' . $id;
$q = $db->query($q);

// Если пользователя с таким id нет, то чью карму то показывать?
if (! ($q && $username = $db->result($q, 0))) {
    
    wap_message($lang_common['Bad request']);
}

$subQ = '(SELECT COUNT(1) '
      . 'FROM `' . $db->prefix . 'karma` '
      . 'WHERE `vote` = \'-1\' '
      . 'AND `to` = ' . $id
      . ')';

$q = 'SELECT '
   . 'COUNT(1), '
   . $subQ . ' '
   . 'FROM `' . $db->prefix . 'karma` '
   . 'WHERE `vote` = \'1\' '
   . 'AND `to` = ' . $id;

$karma = array();
list (
$karma['plus'],
$karma['minus']
) = $db->fetch_row($db->query($q));
unset($subQ);

$karma['total'] = $karma['plus'] - $karma['minus'];

// Count items per page
$num_hits = $karma['plus'] + $karma['minus'];

if ($num_hits) {
    
    //+ Pagination
    $num_pages = ceil($num_hits / $pun_user['disp_posts']);
    $p = (isset($_GET['p']) && 1 < $_GET['p'] && $num_pages >= $_GET['p']) ? (int) $_GET['p'] : 1;
    $start = ($p - 1) * $pun_user['disp_posts'];
    if (@$_GET['action'] == 'all') {
        
        $p = $num_pages + 1;
        $pun_user['disp_posts'] = $num_hits;
        $start = 0;
    }
    //- Pagination
    
    $q = 'SELECT `karma`.*, '
       . '`users`.`username` AS `from`'
       . 'FROM `' . $db->prefix . 'karma` AS `karma` '
       . 'LEFT JOIN `' . $db->prefix . 'users` AS `users` '
       . 'ON `users`.`id` = `karma`.`id` '
       . 'WHERE `karma`.`to` = ' . $id . ' '
       . 'ORDER BY `karma`.`time` DESC '
       . 'LIMIT ' . $start . ',' . $pun_user['disp_posts'];
    
    $q = $db->query($q);
    
    while ($result = $db->fetch_assoc($q)) {
        
        $array[] = $result;
    }
    
    $page_links = paginate($num_pages, $p, 'karma.php?id=' . $id);
}

require_once(PUN_ROOT . 'wap/header.php');

// Language: Общий языковой пакет с файлом common.

$page_title = $pun_config['o_board_title'] . ' / ' . $lang_common['Karma'] . ' - ' . @$username . ' (' . $karma['total'] . ')';
$smarty->assign('page_title', $page_title);

$smarty->assign('karma', @$karma);
$smarty->assign('array', @$array);
$smarty->assign('page_links', @$page_links);

/*
// + nanoMod / uncomment in tpl too
$smarty->assign('id', $id);
$smarty->assign('pun_user', $pun_user);
$smarty->assign('username', $username);
// - nanoMod
*/

$smarty->display('karma.tpl');