<?php

define('PUN_ROOT', '../');

require_once(PUN_ROOT . 'include/common.php');

if (! $pun_user['g_read_board']) {
    
    wap_message($lang_common['No view']);
}

$to = isset($_GET['to']) ? (int) $_GET['to'] : null;
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;

if (null !== $to) {
    
    vote($to, (int) @$_GET['vote']);
    
    $pid = isset($_GET['pid']) ? (int) $_GET['pid'] : null;
    
    if (null === $pid) {
        
        $id = $to;
    }
    else {
        
        wap_redirect('viewtopic.php?pid=' . $pid . '#p' . $pid);
        exit();
    }
}

// Наличие необходимых данных для работы скрипта
if (null === $id) {
    
    wap_message($lang_common['Bad request']);
}

$q = 'SELECT `group_id`, `username` '
   . 'FROM `' . $db->prefix . 'users` '
   . 'WHERE `id` = ' . $id;
$q = $db->query($q)
or error('Unable to fetch username',
         __FILE__,
         __LINE__,
         $db->error());

// Если пользователя с таким id нет, то чью карму то показывать?
// Гостей не учитываем.
if (! ($user = $db->fetch_assoc($q))
    || PUN_GUEST == $user['group_id']
) {
    
    wap_message($lang_common['Bad request']);
}

$subQ = '(SELECT COUNT(1)'
      . 'FROM `' . $db->prefix . 'karma` '
      . 'WHERE `vote` = \'-1\' '
      . 'AND `to` = ' . $id
      . ')';

$q = 'SELECT '
   . 'COUNT(1) AS `plus`, '
   . $subQ . ' AS `minus` '
   . 'FROM `' . $db->prefix . 'karma` '
   . 'WHERE `vote` = \'1\' '
   . 'AND `to` = ' . $id;

unset($subQ);

$q = $db->query($q)
or error('Unable to count votes',
         __FILE__,
         __LINE__,
         $db->error());

$karma = array();
if (! ($karma = $db->fetch_assoc($q))) {
    
    $q = $db->query($q)
    or error('Unable to fetch votes count',
             __FILE__,
             __LINE__,
             $db->error());
}

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
    
    $q = $db->query($q)
    or error('Unable to fetch votes',
             __FILE__,
             __LINE__,
             $db->error());
    
    $votes = array();
    while ($result = $db->fetch_assoc($q)) {
        
        $votes[] = $result;
    }
    
    $page_links = paginate($num_pages, $p, 'karma.php?id=' . $id);
}

require_once(PUN_ROOT . 'wap/header.php');

// Language: Общий языковой пакет с файлом common.

$page_title = $pun_config['o_board_title'] . ' / ' . $lang_common['Karma'] . ' - ' . $user['username'] . ' (' . $karma['total'] . ')';
$smarty->assign('page_title', $page_title);

$smarty->assign('karma', $karma);
$smarty->assign('votes', @$votes);
$smarty->assign('page_links', @$page_links);

//*/ + nanoMod / (un)comment in tpl too
$smarty->assign('id', $id);
$smarty->assign('username', $user['username']);
//*/ - nanoMod

$smarty->display('karma.tpl');
