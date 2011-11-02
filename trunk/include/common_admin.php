<?php
// Make sure no one attempts to run this script "directly"
if(!defined('PUN')){
exit;
}

//
// Display the admin navigation menu
//
function generate_admin_menu($page = '')
{
global $pun_config, $pun_user;

$is_admin = $pun_user['g_id'] == PUN_ADMIN ? true : false;

?>
<div id="adminconsole" class="block2col">
<div id="adminmenu" class="blockmenu">
<h2><span><?php echo ($is_admin) ? 'Admin' : 'Moderator' ?> menu</span></h2>
<div class="box">
<div class="inbox">
<ul>
<li<?php if ($page == 'index') echo ' class="isactive"'; ?>><a href="admin_index.php">Index</a></li>
<?php if ($is_admin): ?>
<li<?php if ($page == 'categories') echo ' class="isactive"'; ?>><a href="admin_categories.php">Categories</a></li>
<?php endif; ?><?php if ($is_admin): ?>
<li<?php if ($page == 'forums') echo ' class="isactive"'; ?>><a href="admin_forums.php">Forums</a></li>
<?php endif; ?>
<?php if ($is_admin): ?>
<li<?php if ($page == 'files') echo ' class="isactive"'; ?>><a href="admin_files.php">Files</a></li>
<?php endif; ?>
<li<?php if ($page == 'users') echo ' class="isactive"'; ?>><a href="admin_users.php">Users</a></li>
<?php if ($is_admin): ?>
<li<?php if ($page == 'groups') echo ' class="isactive"'; ?>><a href="admin_groups.php">User groups</a></li>
<?php endif; ?><?php if ($is_admin): ?>
<li<?php if ($page == 'options') echo ' class="isactive"'; ?>><a href="admin_options.php">Options</a></li>
<?php endif; ?><?php if ($is_admin): ?>
<li<?php if ($page == 'permissions') echo ' class="isactive"'; ?>><a href="admin_permissions.php">Permissions</a></li>
<?php endif; ?>
<li<?php if ($page == 'censoring') echo ' class="isactive"'; ?>><a href="admin_censoring.php">Censoring</a></li>
<?php if ($is_admin): ?>
<li<?php if ($page == 'ranks') echo ' class="isactive"'; ?>><a href="admin_ranks.php">Ranks</a></li>
<?php endif; ?><?php if ($is_admin || $pun_config['p_mod_ban_users'] == 1): ?>
<li<?php if ($page == 'bans') echo ' class="isactive"'; ?>><a href="admin_bans.php">Bans</a></li>
<?php endif; ?><?php if ($is_admin): ?>
<li<?php if ($page == 'prune') echo ' class="isactive"'; ?>><a href="admin_prune.php">Prune</a></li>
<?php endif; ?><?php if ($is_admin): ?>
<li<?php if ($page == 'maintenance') echo ' class="isactive"'; ?>><a href="admin_maintenance.php">Maintenance</a></li>
<?php endif; ?>
<li<?php if ($page == 'reports') echo ' class="isactive"'; ?>><a href="admin_reports.php">Reports</a></li>
</ul>
</div>
</div>
<?php

// See if there are any plugins
$plugins = array();
$d = dir(PUN_ROOT.'plugins');
while(($entry = $d->read()) !== false)
{
$prefix = substr($entry, 0, strpos($entry, '_'));
$suffix = substr($entry, strlen($entry) - 4);

if($suffix == '.php' && ((!$is_admin && $prefix == 'AMP') || ($is_admin && ($prefix == 'AP' || $prefix == 'AMP')))){
$plugins[] = array(substr(substr($entry, strpos($entry, '_') + 1), 0, -4), $entry);
}
}
$d->close();

// Did we find any plugins?
if(sizeof($plugins)>1)
{
print '<h2 class="block2"><span>Plugins</span></h2>
<div class="box">
<div class="inbox">
<ul>';

while(list(, $cur_plugin) = @each($plugins)){
echo '<li'.(($page == $cur_plugin[1]) ? ' class="isactive"' : '').'><a href="admin_loader.php?plugin='.$cur_plugin[1].'">'.str_replace('_', ' ', $cur_plugin[0]).'</a></li>';
}

print'</ul></div></div>';

}

print '</div>';

}

//
// Delete topics from $forum_id that are "older than" $prune_date (if $prune_sticky is 1, sticky topics will also be deleted)
//
function prune($forum_id, $prune_sticky, $prune_date)
{
global $db;

$extra_sql = ($prune_date != -1) ? ' AND last_post<'.$prune_date : '';

if(!$prune_sticky){
$extra_sql .= ' AND sticky=\'0\'';
}

// Fetch topics to prune
$result = $db->query('SELECT `id` FROM `'.$db->prefix.'topics` WHERE `forum_id`='.$forum_id.$extra_sql, true) or error('Unable to fetch topics', __FILE__, __LINE__, $db->error());

$topic_ids = null;
while($row = $db->fetch_row($result)){
$topic_ids .= (($topic_ids) ? ',' : '').$row[0];
}

if($topic_ids){
// Fetch posts to prune
$result = $db->query('SELECT `id` FROM `'.$db->prefix.'posts` WHERE `topic_id` IN('.$topic_ids.')', true) or error('Unable to fetch posts', __FILE__, __LINE__, $db->error());

$post_ids = null;
while($row = $db->fetch_row($result)){
$post_ids .= (($post_ids) ? ',' : '').$row[0];
}

if($post_ids)
{
// hcs AJAX POLL MOD BEGIN
include_once PUN_ROOT.'include/poll/poll.inc.php';
$Poll->deleteTopic($topic_ids);
// hcs AJAX POLL MOD END

// Delete topics
$db->query('DELETE FROM '.$db->prefix.'topics WHERE id IN('.$topic_ids.')') or error('Unable to prune topics', __FILE__, __LINE__, $db->error());
// Delete subscriptions
$db->query('DELETE FROM '.$db->prefix.'subscriptions WHERE topic_id IN('.$topic_ids.')') or error('Unable to prune subscriptions', __FILE__, __LINE__, $db->error());
// Delete posts
$db->query('DELETE FROM '.$db->prefix.'posts WHERE id IN('.$post_ids.')') or error('Unable to prune posts', __FILE__, __LINE__, $db->error());

// We removed a bunch of posts, so now we have to update the search index
include_once PUN_ROOT.'include/search_idx.php';
strip_search_index($post_ids);

// Delete attachments
include_once PUN_ROOT.'include/file_upload.php';
delete_post_attachments($post_ids);
}
}
}
?>