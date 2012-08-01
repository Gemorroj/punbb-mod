<?php
define('PUN_ROOT', '../');

require PUN_ROOT.'include/common.php';

if ($pun_user['is_guest'] || !$pun_user['g_pm']) {
    wap_message($lang_common['No permission']);
}

$id = intval($_GET['id']);

if (!$id) {
    wap_message($lang_common['Bad request']);
}


// Load the delete.php language file
require PUN_ROOT.'lang/'.$pun_user['language'].'/pms.php';
require PUN_ROOT.'lang/'.$pun_user['language'].'/delete.php';

// Fetch some info from the message we are deleting
$result = $db->query('SELECT * FROM '.$db->prefix.'messages WHERE id='.$id) or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
if (!$db->num_rows($result)) {
    wap_message($lang_common['Bad request']);
}

$cur_post = $db->fetch_assoc($result);

// Check permissions
if ($cur_post['owner'] != $pun_user['id']) {
    wap_message($lang_common['No permission']);
}

if (isset($_POST['delete'])) {
    //confirm_referrer('message_delete.php');

    // Delete message
    $db->query('DELETE FROM '.$db->prefix.'messages WHERE id='.$id) or error('Unable to fetch online list', __FILE__, __LINE__, $db->error());

    // Redirect
    wap_redirect('message_list.php?box='.intval($_POST['box']).'&p='.intval($_POST['p']));
} else {
    
    $page_title = $pun_config['o_board_title'].' / '.$lang_pms['Delete message'];

    require_once PUN_ROOT.'wap/header.php';
    include_once PUN_ROOT.'include/parser.php';

    $cur_post['message'] = parse_message($cur_post['message'], intval(!$cur_post['smileys']));

    $smarty->assign('page_title', $page_title);    
    $smarty->assign('lang_pms', $lang_pms);
    $smarty->assign('id', $id);
    $smarty->assign('cur_post', $cur_post);
    $smarty->assign('lang_delete', $lang_delete);
    //$smarty->assign('', $);
    
    $smarty->display('message_delete.tpl');
}

?>