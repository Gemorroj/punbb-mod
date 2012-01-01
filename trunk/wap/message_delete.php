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
    wap_redirect('message_list.php?box='.intval($_POST['box']).'&amp;p='.intval($_POST['p']));
} else {
    $page_title = pun_htmlspecialchars($pun_config['o_board_title']).' &#187; '.$lang_pms['Delete message'];

    require_once PUN_ROOT.'wap/header.php';
    include_once PUN_ROOT.'include/parser_wap.php';

    $cur_post['message'] = parse_message($cur_post['message'], intval(!$cur_post['smileys']));


    echo '<div class="red">'.$lang_pms['Delete message'].'</div>
<form method="post" action="message_delete.php?id='.$id.'">
<div class="msg">
<input type="hidden" name="box" value="'.intval($_GET['box']).'"/>
<input type="hidden" name="p" value="'.intval($_GET['p']).'"/>
'.$lang_pms['Sender'].': <strong>'.pun_htmlspecialchars($cur_post['sender']).'</strong><br/>
'.$cur_post['message'].'</div>
<div class="go_to"><input type="submit" name="delete" value="'.$lang_delete['Delete'].'" /></div></form>
<div class="navlinks">
<a href="message_list.php?box=0">'.$lang_pms['Inbox'].'</a> |
<a href="message_list.php?box=1">'.$lang_pms['Outbox'].'</a> |
<a href="message_list.php?box=2">'.$lang_pms['Options'].'</a> |
<a href="message_send.php">'.$lang_pms['New message'].'</a></div>';

    require_once PUN_ROOT.'wap/footer.php';
}

?>