<?php
define('PUN_ROOT', './');
require PUN_ROOT.'include/common.php';


if(!$pun_user['g_read_board'])
{message($lang_common['No view']);}


// Load the index.php language file
require PUN_ROOT.'lang/'.$pun_user['language'].'/index.php';

$page_title = pun_htmlspecialchars($pun_config['o_board_title']);
define('PUN_ALLOW_INDEX', 1);
require_once PUN_ROOT.'header.php';

$fid_list = $categories = $forums = array();

// get available forum list
$result = $db->query('SELECT f.id AS fid, f.forum_name FROM '.$db->prefix.'forums AS f LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$pun_user['g_id'].') WHERE fp.read_forum IS NULL OR fp.read_forum=1 ORDER BY f.id') or error('Unable to fetch forum list', __FILE__, __LINE__, $db->error());
while($cur_forum = $db->fetch_assoc($result))
{
$fid_list[] = $cur_forum['fid'];
$forums[$cur_forum['fid']] = $cur_forum['forum_name'];
}
$fid_list = implode(',', $fid_list);

// get category list for cache
$result = $db->query('SELECT id, cat_name FROM '.$db->prefix.'categories') or error('Unable to fetch category list', __FILE__, __LINE__, $db->error());
while ($cur_category = $db->fetch_assoc($result))
{
$categories[$cur_category['id']] = $cur_category['cat_name'];
}

// loop through topics
$result = $db->query('SELECT f.cat_id, t.forum_id, t.id, t.subject, t.last_post, t.poster FROM
'.$db->prefix.'topics AS t INNER JOIN
'.$db->prefix.'forums AS f ON f.id = t.forum_id INNER JOIN
'.$db->prefix.'categories AS c ON f.cat_id = c.id
WHERE f.id in ('.$fid_list.')
ORDER BY c.disp_position, f.disp_position, f.cat_id, t.forum_id, t.last_post desc') or
error('Unable to fetch topic list', __FILE__, __LINE__, $db->error());

$cur_category = $cur_forum = 0;

echo '<div class="box"><div class="inbox">';

while($cur_topic = $db->fetch_assoc($result))
{
if($cur_topic['cat_id'] != $cur_category) // A new category since last iteration?
{
if($cur_category)
{echo '<br />';}
print '<h2><span>'.pun_htmlspecialchars($categories[$cur_topic['cat_id']]).'</span></h2>';
$cur_category = $cur_topic['cat_id'];
}

if ($cur_topic['forum_id'] != $cur_forum) // A new forum since last iteration?
{
echo '<p> <strong>'.pun_htmlspecialchars($forums[$cur_topic['forum_id']]).'</strong></p>';
$cur_forum = $cur_topic['forum_id'];
}
echo '<p> &raquo; '.pun_htmlspecialchars($cur_topic['poster']).':<br /><a href="viewtopic.php?id='.$cur_topic['id'].'">"'.pun_htmlspecialchars($cur_topic['subject']).'</a></p>';
}

echo '</div></div><br /><div class="clearer"></div>';

$footer_style = 'index';
require_once PUN_ROOT.'footer.php';
?>