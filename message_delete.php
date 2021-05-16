<?php

\define('PUN_ROOT', './');

require PUN_ROOT.'include/common.php';

if ($pun_user['is_guest'] || !$pun_user['g_pm']) {
    \message($lang_common['No permission']);
}

$id = \intval($_GET['id']);
if (!$id) {
    \message($lang_common['Bad request']);
}

// Load the delete.php language file
require PUN_ROOT.'lang/'.$pun_user['language'].'/pms.php';

require PUN_ROOT.'lang/'.$pun_user['language'].'/delete.php';

// Fetch some info from the message we are deleting
$result = $db->query('SELECT * FROM '.$db->prefix.'messages WHERE id='.$id) or \error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
if (!$db->num_rows($result)) {
    \message($lang_common['Bad request']);
}

$cur_post = $db->fetch_assoc($result);

// Check permissions
if ($cur_post['owner'] != $pun_user['id']) {
    \message($lang_common['No permission']);
}

if (isset($_POST['delete'])) {
    // confirm_referrer('message_delete.php');

    // Delete message
    $db->query('DELETE FROM '.$db->prefix.'messages WHERE id='.$id) or \error('Unable to fetch online list', __FILE__, __LINE__, $db->error());

    // Redirect
    \redirect('message_list.php?box='.$_POST['box'].'&p='.$_POST['p'], $lang_pms['Del redirect']);
} else {
    $page_title = \pun_htmlspecialchars($pun_config['o_board_title']).' / '.$lang_pms['Delete message'];

    require_once PUN_ROOT.'header.php';

    include_once PUN_ROOT.'include/parser.php';

    $cur_post['message'] = \parse_message($cur_post['message'], \intval(!$cur_post['smileys']));

    echo '<div class="blockform">
<h2><span>'.$lang_pms['Delete message'].'</span></h2>
<div class="box">
<form method="post" action="message_delete.php?id='.$id.'">
<input type="hidden" name="box" value="'.\intval($_GET['box']).'">
<input type="hidden" name="p" value="'.\intval($_GET['p']).'">
<div class="inform">
<fieldset>
<div class="infldset">
<div class="postmsg">
<p>'.$lang_pms['Sender'].': <strong>'.\pun_htmlspecialchars($cur_post['sender']).'</strong></p>
'.$cur_post['message'].'
</div>
</div>
</fieldset>
</div>
<p><input type="submit" name="delete" value="<'.$lang_delete['Delete'].'" /><a href="javascript:history.go(-1)">'.$lang_common['Go back'].'</a></p>
</form>
</div>
</div>';

    require_once PUN_ROOT.'footer.php';
}
