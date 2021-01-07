<?php

// Tell header.php to use the admin template
\define('PUN_ADMIN_CONSOLE', 1);
// Tell common.php that we don't want output buffering
\define('PUN_DISABLE_BUFFERING', 1);

\define('PUN_ROOT', './');

require PUN_ROOT.'include/common.php';

require PUN_ROOT.'include/common_admin.php';
// Язык
//include PUN_ROOT.'lang/'.$pun_user['language'].'/admin.php';
include PUN_ROOT.'lang/Russian/admin.php';

if ($pun_user['g_id'] > PUN_ADMIN) {
    message($lang_common['No permission']);
}

if (isset($_GET['i_per_page'], $_GET['i_start_at'])) {
    $per_page = \intval($_GET['i_per_page']);
    $start_at = \intval($_GET['i_start_at']);
    if ($per_page < 1 || $start_at < 1) {
        message($lang_common['Bad request']);
    }

    @\set_time_limit(0);

    // If this is the first cycle of posts we empty the search index before we proceed
    if (isset($_GET['i_empty_index'])) {
        // This is the only potentially "dangerous" thing we can do here, so we check the referer
        //confirm_referrer('admin_maintenance.php');

        $db->query('TRUNCATE TABLE '.$db->prefix.'search_matches') or error('Unable to empty search index match table', __FILE__, __LINE__, $db->error());
        $db->query('TRUNCATE TABLE '.$db->prefix.'search_words') or error('Unable to empty search index words table', __FILE__, __LINE__, $db->error());

        // Reset the sequence for the search words (not needed for SQLite)
        $result = $db->query('ALTER TABLE '.$db->prefix.'search_words auto_increment=1') or error('Unable to update table auto_increment', __FILE__, __LINE__, $db->error());
    }

    $end_at = $start_at + $per_page;

    echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>'.pun_htmlspecialchars($pun_config['o_board_title']).' / '.$lang_admin['maintenance'].'&#8230;</title>
    <style type="text/css">body{font:10px Verdana, Arial, Helvetica, sans-serif;color:#333;background-color:#fff;}</style>
</head>
<body><div>'.$lang_admin['maintenance_go'].'<br /><br />';

    include PUN_ROOT.'include/search_idx.php';

    // Fetch posts to process
    $result = $db->query('SELECT DISTINCT t.id, p.id, p.message FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'posts AS p ON t.id=p.topic_id WHERE t.id >= '.$start_at.' AND t.id < '.$end_at.' ORDER BY t.id') or error('Unable to fetch topic/post info', __FILE__, __LINE__, $db->error());
    $cur_topic = 0;
    while ($cur_post = $db->fetch_row($result)) {
        if ($cur_post[0] != $cur_topic) {
            // Fetch subject and ID of first post in topic
            $result2 = $db->query('SELECT p.id, t.subject, MIN(p.posted) AS first FROM '.$db->prefix.'posts AS p INNER JOIN '.$db->prefix.'topics AS t ON t.id=p.topic_id WHERE t.id='.$cur_post[0].' GROUP BY p.id, t.subject ORDER BY first LIMIT 1') or error('Unable to fetch topic info', __FILE__, __LINE__, $db->error());
            [$first_post, $subject] = $db->fetch_row($result2);

            $cur_topic = $cur_post[0];
        }

        echo 'Processing post <strong>'.$cur_post[1].'</strong> in topic <strong>'.$cur_post[0].'</strong><br />';

        if ($cur_post[1] == $first_post) {
            // This is the "topic post" so we have to index the subject as well
            update_search_index('post', $cur_post[1], $cur_post[2], $subject);
        } else {
            update_search_index('post', $cur_post[1], $cur_post[2]);
        }
    }

    // Check if there is more work to do
    $result = $db->query('SELECT id FROM '.$db->prefix.'topics WHERE id > '.$cur_topic.' ORDER BY id ASC LIMIT 1') or error('Unable to fetch topic info', __FILE__, __LINE__, $db->error());

    $query_str = ($db->num_rows($result)) ? '?i_per_page='.$per_page.'&i_start_at='.$db->result($result) : '';

    $db->close();

    exit('<script>window.location.assign("admin_maintenance.php'.$query_str.'");</script><br />JavaScript redirect unsuccessful. Click <a href="admin_maintenance.php'.pun_htmlspecialchars($query_str).'">here</a> to continue.</div></body></html>');
}

// Get the first post ID from the db
$result = $db->query('SELECT id FROM '.$db->prefix.'topics ORDER BY id LIMIT 1') or error('Unable to fetch topic info', __FILE__, __LINE__, $db->error());
if ($db->num_rows($result)) {
    $first_id = $db->result($result);
} else {
    $first_id = 1;
}

$page_title = pun_htmlspecialchars($pun_config['o_board_title']).' / Admin / Maintenance';

require_once PUN_ROOT.'header.php';

generate_admin_menu('maintenance');

echo '<div class="blockform">
<h2><span>'.$lang_admin['maintenance_about'].'</span></h2>
<div class="box">
<form method="get" action="admin_maintenance.php?">
<div class="inform">
<fieldset>
<legend>'.$lang_admin['maintenance'].'</legend>
<div class="infldset">
<p>'.$lang_admin['maintenance_full_about'].'</p>
<table class="aligntop" cellspacing="0">
<tr>
<th scope="row">'.$lang_admin['maintenance_while'].'</th>
<td>
<input type="text" name="i_per_page" size="7" maxlength="7" value="100" />
<span>'.$lang_admin['maintenance_about_while'].'</span>
</td>
</tr>
<tr>
<th scope="row">'.$lang_admin['maintenance_from'].'</th>
<td>
<input type="text" name="i_start_at" size="7" maxlength="7" value="'.$first_id.'" />
<span>'.$lang_admin['maintenance_about_from'].'</span>
</td>
</tr>
<tr>
<th scope="row">'.$lang_admin['maintenance_clean'].'</th>
<td class="inputadmin">
<span><input type="checkbox" name="i_empty_index" value="1" checked="checked" />'.$lang_admin['maintenance_clean_check'].'</span>
</td>
</tr>
</table>
<p class="topspace">'.$lang_admin['maintenance_mess'].'</p>
<div class="fsetsubmit"><input type="submit" name="rebuild_index" value="'.$lang_admin['maintenance_submit'].'" /></div>
</div>
</fieldset>
</div>
</form>
</div>
</div>
<div class="clearer"></div>
</div>';

require_once PUN_ROOT.'footer.php';
