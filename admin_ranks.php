<?php

// Tell header.php to use the admin template
define('PUN_ADMIN_CONSOLE', 1);

define('PUN_ROOT', './');
require PUN_ROOT.'include/common.php';
require PUN_ROOT.'include/common_admin.php';
// Язык
//include PUN_ROOT.'lang/'.$pun_user['language'].'/admin.php';
include PUN_ROOT.'lang/Russian/admin.php';

if ($pun_user['g_id'] > PUN_ADMIN) {
    message($lang_common['No permission']);
}

// Add a rank
if (isset($_POST['add_rank'])) {
    //confirm_referrer('admin_ranks.php');

    $rank = trim($_POST['new_rank']);
    $min_posts = $_POST['new_min_posts'];

    if (!$rank) {
        message($lang_admin['Rank']);
    }

    if (!@preg_match('#^\d+$#', $min_posts)) {
        message($lang_admin['Rank posts not numeric']);
    }

    // Make sure there isn't already a rank with the same min_posts value
    $result = $db->query('SELECT 1 FROM '.$db->prefix.'ranks WHERE min_posts='.$min_posts) or error('Unable to fetch rank info', __FILE__, __LINE__, $db->error());
    if ($db->num_rows($result)) {
        message($lang_admin['Double rank']);
    }

    $db->query('INSERT INTO '.$db->prefix.'ranks (rank, min_posts) VALUES(\''.$db->escape($rank).'\', '.$min_posts.')') or error('Unable to add rank', __FILE__, __LINE__, $db->error());

    // Regenerate the ranks cache
    include_once PUN_ROOT.'include/cache.php';
    generate_ranks_cache();

    redirect('admin_ranks.php', $lang_admin['Added'].' '.$lang_admin['Redirect']);
} // Update a rank
elseif (isset($_POST['update'])) {
    //confirm_referrer('admin_ranks.php');

    $id = intval(key($_POST['update']));

    $rank = trim($_POST['rank'][$id]);
    $min_posts = trim($_POST['min_posts'][$id]);

    if (!$rank) {
        message($lang_admin['Rank']);
    }

    if (!@preg_match('#^\d+$#', $min_posts)) {
        message($lang_admin['Rank posts not numeric']);
    }

    // Make sure there isn't already a rank with the same min_posts value
    $result = $db->query('SELECT 1 FROM '.$db->prefix.'ranks WHERE id!='.$id.' AND min_posts='.$min_posts) or error('Unable to fetch rank info', __FILE__, __LINE__, $db->error());
    if ($db->num_rows($result)) {
        message($lang_admin['Double rank']);
    }

    $db->query('UPDATE '.$db->prefix.'ranks SET rank=\''.$db->escape($rank).'\', min_posts='.$min_posts.' WHERE id='.$id) or error('Unable to update rank', __FILE__, __LINE__, $db->error());

    // Regenerate the ranks cache
    include_once PUN_ROOT.'include/cache.php';
    generate_ranks_cache();

    redirect('admin_ranks.php', $lang_admin['Updated'].' '.$lang_admin['Redirect']);
} // Remove a rank
elseif (isset($_POST['remove'])) {
    //confirm_referrer('admin_ranks.php');

    $id = intval(key($_POST['remove']));

    $db->query('DELETE FROM '.$db->prefix.'ranks WHERE id='.$id) or error('Unable to delete rank', __FILE__, __LINE__, $db->error());

    // Regenerate the ranks cache
    include_once PUN_ROOT.'include/cache.php';
    generate_ranks_cache();

    redirect('admin_ranks.php', $lang_admin['Deleted'].' '.$lang_admin['Redirect']);
}

$page_title = pun_htmlspecialchars($pun_config['o_board_title']).' / Admin / Ranks';
$focus_element = array('ranks', 'new_rank');
require_once PUN_ROOT.'header.php';

generate_admin_menu('ranks');

echo '<div class="blockform">
<h2><span>'.$lang_admin['Ranks'].'</span></h2>
<div class="box">
<form id="ranks" method="post" action="admin_ranks.php?action=foo">
<div class="inform">
<fieldset>
<legend>'.$lang_admin['Rank'].'</legend>
<div class="infldset">
<p>'.$lang_admin['About ranks'].'</strong></p>
<table cellspacing="0">
<thead>
<tr>
<th class="tcl" scope="col">'.$lang_admin['Rank name'].'</th>
<th class="tc2" scope="col">'.$lang_admin['Rank posts'].'</th>
<th class="hidehead" scope="col">'.$lang_admin['Act'].'</th>
</tr>
</thead>
<tbody>
<tr>
<td><input type="text" name="new_rank" size="24" maxlength="50" /></td>
<td><input type="text" name="new_min_posts" size="7" maxlength="7" /></td>
<td><input type="submit" name="add_rank" value="'.$lang_admin['Add'].'" /></td>
</tr>
</tbody>
</table>
</div>
</fieldset>
</div>
<div class="inform">
<fieldset>
<legend>'.$lang_admin['Edit ranks'].'</legend>
<div class="infldset">';

$result = $db->query('SELECT id, rank, min_posts FROM '.$db->prefix.'ranks ORDER BY min_posts') or error('Unable to fetch rank list', __FILE__, __LINE__, $db->error());
if ($db->num_rows($result)) {
    echo '<table cellspacing="0">
<thead>
<tr>
<th class="tcl" scope="col">'.$lang_admin['Rank name'].'</th>
<th class="tc2" scope="col">'.$lang_admin['Rank posts'].'</th>
<th class="hidehead" scope="col">'.$lang_admin['Act'].'</th>
</tr>
</thead>
<tbody>';

    while ($cur_rank = $db->fetch_assoc($result)) {
        echo '<tr><td><input type="text" name="rank['.$cur_rank['id'].']" value="'.pun_htmlspecialchars($cur_rank['rank']).'" size="24" maxlength="50" /></td><td><input type="text" name="min_posts['.$cur_rank['id'].']" value="'.$cur_rank['min_posts'].'" size="7" maxlength="7" /></td><td><input type="submit" name="update['.$cur_rank['id'].']" value="'.$lang_admin['Upd'].'" /> <input type="submit" name="remove['.$cur_rank['id'].']" value="'.$lang_admin['Del'].'" /></td></tr>';
    }

    echo '</tbody></table>';
} else {
    echo '<p>'.$lang_admin['Not ranks'].'</p>';
}

echo '</div></fieldset></div></form></div></div><div class="clearer"></div></div>';

require_once PUN_ROOT.'footer.php';
