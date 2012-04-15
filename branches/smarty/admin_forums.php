<?php
// Tell header.php to use the admin template
define('PUN_ADMIN_CONSOLE', 1);

define('PUN_ROOT', './');
require PUN_ROOT . 'include/common.php';
require PUN_ROOT . 'include/common_admin.php';


if ($pun_user['g_id'] > PUN_ADMIN) {
    message($lang_common['No permission']);
}


// Add a "default" forum
if (isset($_POST['add_forum'])) {
    //confirm_referrer('admin_forums.php');
    
    $forum_name = (!trim($_POST['forum_name']))? 'New forum' : trim($_POST['forum_name']);
    $add_to_cat = intval($_POST['add_to_cat']);
    if ($add_to_cat < 1) {
        message($lang_common['Bad request']);
    }

    $db->query('INSERT INTO ' . $db->prefix . 'forums (cat_id, forum_name) VALUES(' . $add_to_cat . ', \'' . $db->escape($forum_name) . '\')') or error('Unable to create forum', __FILE__, __LINE__, $db->error());

    // Regenerate the quickjump cache
    include_once PUN_ROOT . 'include/cache.php';
    generate_quickjump_cache();

    redirect('admin_forums.php', 'Форум добавлен. Перенаправление &hellip;');
}


// Make new forum with the same permissions
else if(isset($_GET['clone_forum']))
{
$forum_id = intval($_GET['clone_forum']);
if ($forum_id < 1) {
    message($lang_common['Bad request']);
}

//confirm_referrer('admin_forums.php');

// Make copy of forum and its permissions
$db->query('INSERT INTO '.$db->prefix.'forums (cat_id, forum_name, redirect_url, moderators, sort_by) (SELECT cat_id, concat( \'Копия \', forum_name), redirect_url, moderators, sort_by FROM '.$db->prefix.'forums WHERE id='.$forum_id.')') or error('Unable to clone forum', __FILE__, __LINE__, $db->error());
$new_forum_id = $db->insert_id();
$db->query('INSERT INTO '.$db->prefix.'forum_perms (forum_id, group_id, read_forum, post_replies, post_topics, file_upload, file_download, file_limit) (SELECT '.$new_forum_id.', group_id, read_forum, post_replies, post_topics, file_upload, file_download, file_limit FROM '.$db->prefix.'forum_perms WHERE forum_id='.$forum_id.')') or error('Unable to clone forum_perms', __FILE__, __LINE__, $db->error());

// Regenerate the quickjump cache
include_once PUN_ROOT . 'include/cache.php';
generate_quickjump_cache();

// Immediatelly edit newborn forum
redirect('admin_forums.php?edit_forum='.$new_forum_id, 'Форум клонирован. Перенаправление &hellip;');
}

// Delete a forum
else if(isset($_GET['del_forum']))
{
//confirm_referrer('admin_forums.php');

$forum_id = intval($_GET['del_forum']);
if($forum_id < 1){
message($lang_common['Bad request']);
}

if(isset($_POST['del_forum_comply'])) // Delete a forum with all posts
{
@set_time_limit(0);

// Prune all posts and topics
prune($forum_id, 1, -1);

// Locate any "orphaned redirect topics" and delete them
$result = $db->query('SELECT t1.id FROM '.$db->prefix.'topics AS t1 LEFT JOIN '.$db->prefix.'topics AS t2 ON t1.moved_to=t2.id WHERE t2.id IS NULL AND t1.moved_to IS NOT NULL') or error('Unable to fetch redirect topics', __FILE__, __LINE__, $db->error());
$num_orphans = $db->num_rows($result);

if($num_orphans)
{
for($i=0; $i<$num_orphans; ++$i){
$orphans[] = $db->result($result, $i);
}

$db->query('DELETE FROM '.$db->prefix.'topics WHERE id IN('.implode(',', $orphans).')') or error('Unable to delete redirect topics', __FILE__, __LINE__, $db->error());
}

// Delete the forum and any forum specific group permissions
$db->query('DELETE FROM '.$db->prefix.'forums WHERE id='.$forum_id) or error('Unable to delete forum', __FILE__, __LINE__, $db->error());
$db->query('DELETE FROM '.$db->prefix.'forum_perms WHERE forum_id='.$forum_id) or error('Unable to delete group forum permissions', __FILE__, __LINE__, $db->error());

// Regenerate the quickjump cache
include_once PUN_ROOT.'include/cache.php';
generate_quickjump_cache();

redirect('admin_forums.php', 'Форум удален. Перенаправление &hellip;');
}
else	// If the user hasn't confirmed the delete
{
$result = $db->query('SELECT forum_name FROM '.$db->prefix.'forums WHERE id='.$forum_id) or error('Unable to fetch forum info', __FILE__, __LINE__, $db->error());
$forum_name = pun_htmlspecialchars($db->result($result));


$page_title = pun_htmlspecialchars($pun_config['o_board_title']).' / Admin / Forums';
require_once PUN_ROOT.'header.php';

generate_admin_menu('forums');


echo '<div class="blockform">
<h2><span>Подтверждение удаления форума</span></h2>
<div class="box">
<form method="post" action="admin_forums.php?del_forum='.$forum_id.'">
<div class="inform">
<fieldset>
<legend>Важно! Прочтите перед удалением</legend>
<div class="infldset">
<p>Вы уверены, что хотите удалить форум "'.$forum_name.'"?</p>
<p>ОСТОРОЖНО! Удаление форума приведет к удалению всех сообщений (если есть) в этом форуме!</p>
</div>
</fieldset>
</div>
<p><input type="submit" name="del_forum_comply" value="Удалить" /><a href="javascript:history.go(-1)">Назад</a></p>
</form>
</div>
</div>
<div class="clearer"></div>
</div>';

require_once PUN_ROOT.'footer.php';
}
}


// Update forum positions
else if(isset($_POST['update_positions']))
{
//confirm_referrer('admin_forums.php');

while(list($forum_id, $disp_position) = @each($_POST['position']))
{
if(!@preg_match('#^\d+$#', $disp_position)){
message('Position must be a positive integer value.');
}

$db->query('UPDATE '.$db->prefix.'forums SET disp_position='.$disp_position.' WHERE id='.intval($forum_id)) or error('Unable to update forum', __FILE__, __LINE__, $db->error());
}

// Regenerate the quickjump cache
include_once PUN_ROOT . 'include/cache.php';
generate_quickjump_cache();

redirect('admin_forums.php', 'Форумы обновлены. Перенаправление &hellip;');
}


else if(isset($_GET['edit_forum']))
{
$forum_id = intval($_GET['edit_forum']);
if ($forum_id < 1) {
    message($lang_common['Bad request']);
}

// Update group permissions for $forum_id
if(isset($_POST['save']))
{
//confirm_referrer('admin_forums.php');

// Start with the forum details
$forum_name = trim($_POST['forum_name']);
$forum_desc = pun_linebreaks(trim($_POST['forum_desc']));
$cat_id = intval($_POST['cat_id']);
$sort_by = intval($_POST['sort_by']);
$redirect_url = isset($_POST['redirect_url']) ? trim($_POST['redirect_url']) : null;

if (!$forum_name) {
    message('You must enter a forum name.');
}

if ($cat_id < 1) {
    message($lang_common['Bad request']);
}

$forum_desc = ($forum_desc) ? '\''.$db->escape($forum_desc).'\'' : 'NULL';
$redirect_url = ($redirect_url) ? '\''.$db->escape($redirect_url).'\'' : 'NULL';

$db->query('UPDATE '.$db->prefix.'forums SET forum_name=\''.$db->escape($forum_name).'\', forum_desc='.$forum_desc.', redirect_url='.$redirect_url.', sort_by='.$sort_by.', cat_id='.$cat_id.' WHERE id='.$forum_id) or error('Unable to update forum', __FILE__, __LINE__, $db->error());

// Now let's deal with the permissions
if(isset($_POST['read_forum_old']))
{
$result = $db->query('SELECT g_id, g_read_board, g_post_replies, g_post_topics, g_file_upload, g_file_download, g_file_limit FROM '.$db->prefix.'groups WHERE g_id!='.PUN_ADMIN) or error('Unable to fetch user group list', __FILE__, __LINE__, $db->error());
while($cur_group = $db->fetch_assoc($result))
{
$read_forum_new = ($cur_group['g_read_board'] == 1) ? isset($_POST['read_forum_new'][$cur_group['g_id']]) ? 1 : '0' : intval($_POST['read_forum_old'][$cur_group['g_id']]);
$post_replies_new = isset($_POST['post_replies_new'][$cur_group['g_id']]) ? 1 : '0';
$post_topics_new = isset($_POST['post_topics_new'][$cur_group['g_id']]) ? 1 : '0';

$file_download_new = isset($_POST['file_download_new'][$cur_group['g_id']]) ? 1 : '0';
if($cur_group['g_id'] == PUN_GUEST)
{
// upload settings never changed for guests
$file_upload_new = $_POST['file_upload_old'][$cur_group['g_id']] = $cur_group['g_file_upload'];
$file_limit_new = $_POST['file_limit_old'][$cur_group['g_id']] = $cur_group['g_file_limit'];
}
else
{
$file_upload_new = isset($_POST['file_upload_new'] [$cur_group['g_id']]) ? 1 : '0';
$file_limit_new = isset($_POST['file_limit_new'][$cur_group['g_id']]) ? intval($_POST['file_limit_new'][$cur_group['g_id']]) : '0';
}

// Check if the new settings differ from the old
if($read_forum_new != $_POST['read_forum_old'][$cur_group['g_id']] || $post_replies_new != $_POST['post_replies_old'][$cur_group['g_id']] || $post_topics_new != $_POST['post_topics_old'][$cur_group['g_id']] || $file_download_new != $_POST['file_download_old'][$cur_group['g_id']] || $file_upload_new != $_POST['file_upload_old'][$cur_group['g_id']] || $file_limit_new != $_POST['file_limit_old'][$cur_group['g_id']] )
{
// If the new settings are identical to the default settings for this group, delete it's row in forum_perms
if($read_forum_new == 1 && $post_replies_new == $cur_group['g_post_replies'] && $post_topics_new == $cur_group['g_post_topics'] && $file_upload_new == $cur_group['g_file_upload'] && $file_download_new == $cur_group['g_file_download'] && $file_limit_new == $cur_group['g_file_limit']){
$db->query('DELETE FROM '.$db->prefix.'forum_perms WHERE group_id='.$cur_group['g_id'].' AND forum_id='.$forum_id) or error('Unable to delete group forum permissions', __FILE__, __LINE__, $db->error());
}
else{
// Run an UPDATE and see if it affected a row, if not, INSERT
$db->query('UPDATE '.$db->prefix.'forum_perms SET read_forum='.$read_forum_new.', post_replies='.$post_replies_new.', post_topics='.$post_topics_new.', file_upload='.$file_upload_new.', file_download='.$file_download_new.', file_limit='.$file_limit_new.' WHERE group_id='.$cur_group['g_id'].' AND forum_id='.$forum_id) or error('Unable to insert group forum permissions', __FILE__, __LINE__, $db->error());
if(!$db->affected_rows()){
$db->query('INSERT INTO '.$db->prefix.'forum_perms (group_id, forum_id, read_forum, post_replies, post_topics, file_upload, file_download, file_limit) VALUES('.$cur_group['g_id'].', '.$forum_id.', '.$read_forum_new.', '.$post_replies_new.', '.$post_topics_new.', '.$file_upload_new.', '.$file_download_new.', '.$file_limit_new.')') or error('Unable to insert group forum permissions', __FILE__, __LINE__, $db->error());
}
}
}
}
}

// Regenerate the quickjump cache
include_once PUN_ROOT.'include/cache.php';
generate_quickjump_cache();

redirect('admin_forums.php', 'Форум обновлен. Перенаправление &hellip;');
}
else if(isset($_POST['revert_perms']))
{
//confirm_referrer('admin_forums.php');

$db->query('DELETE FROM '.$db->prefix.'forum_perms WHERE forum_id='.$forum_id) or error('Unable to delete group forum permissions', __FILE__, __LINE__, $db->error());

// Regenerate the quickjump cache
include_once PUN_ROOT.'include/cache.php';
generate_quickjump_cache();

redirect('admin_forums.php?edit_forum='.$forum_id, 'Разрешения возвращены к начальным. Перенаправление &hellip;');
}


// Fetch forum info
$result = $db->query('SELECT id, forum_name, forum_desc, redirect_url, num_topics, sort_by, cat_id FROM '.$db->prefix.'forums WHERE id='.$forum_id) or error('Unable to fetch forum info', __FILE__, __LINE__, $db->error());
if(!$db->num_rows($result)){
message($lang_common['Bad request']);
}

$cur_forum = $db->fetch_assoc($result);


$page_title = pun_htmlspecialchars($pun_config['o_board_title']).' / Admin / Forums';
require_once PUN_ROOT.'header.php';

generate_admin_menu('forums');


echo '<div class="blockform">
<h2><span>Редактирование форума</span></h2>
<div class="box">
<form id="edit_forum" method="post" action="admin_forums.php?edit_forum='.$forum_id.'">
<p class="submittop"><input type="submit" name="save" value="Сохранить изменения" tabindex="6" /></p>
<div class="inform">
<fieldset>
<legend>Опции редактирования форума</legend>
<div class="infldset">
<table class="aligntop" cellspacing="0">
<tr>
<th scope="row">Имя форума</th>
<td><input type="text" name="forum_name" size="35" maxlength="80" value="'.pun_htmlspecialchars($cur_forum['forum_name']).'" tabindex="1" /></td>
</tr>
<tr>
<th scope="row">Описание (XHTML)</th>
<td><textarea name="forum_desc" rows="3" cols="50" tabindex="2">'.pun_htmlspecialchars($cur_forum['forum_desc']).'</textarea></td>
</tr>
<tr>
<th scope="row">Категория</th>
<td>
<select name="cat_id" tabindex="3">';

$result = $db->query('SELECT id, cat_name FROM '.$db->prefix.'categories ORDER BY disp_position') or error('Unable to fetch category list', __FILE__, __LINE__, $db->error());
while($cur_cat = $db->fetch_assoc($result))
{
$selected = ($cur_cat['id'] == $cur_forum['cat_id']) ? ' selected="selected"' : '';
echo '<option value="'.$cur_cat['id'].'"'.$selected.'>'.pun_htmlspecialchars($cur_cat['cat_name']).'</option>';
}


echo '</select>
</td>
</tr>
<tr>
<th scope="row">Сортировать темы по</th>
<td>
<select name="sort_by" tabindex="4">
<option value="0"';
if(!$cur_forum['sort_by']){
echo ' selected="selected"';
}
echo '>Последнему посту</option><option value="1"';
if($cur_forum['sort_by']==1){
echo ' selected="selected"';
}
echo '>Последней теме</option>
</select>
</td>
</tr>
<tr>
<th scope="row">URL переадресации</th>
<td>';
if($cur_forum['num_topics']){
echo 'Доступно только для пустых форумов';
}
else{
echo '<input type="text" name="redirect_url" size="45" maxlength="100" value="'.pun_htmlspecialchars($cur_forum['redirect_url']).'" tabindex="5" />';
}
echo '</td>
</tr>
</table>
</div>
</fieldset>
</div>
<div class="inform">
<fieldset>
<legend>Редактирование групповых разрешений для этого форума</legend>
<div class="infldset">
<p>В этой форме вы можете задать специфические разрешения для различных групп пользователей. Если вы не изменили групповых разрешений для этих форумов, тогда вы видите ниже настройки по умолчанию, взятые из <a href="admin_groups.php">Группы</a>. Администраторы всегда имеют полные разрешения, поэтому они исключены. Настройки разрешений отличные от настроек по умолчанию для группы пользователей помечены красным. Галочка "Читать форум" недоступна если в настройках группы пользователей нет разрешения "Читать форумы". Для переадресации форумов, изменяется только разрешение "Читать форум".</p>
<table id="forumperms" cellspacing="0">
<thead>
<tr>
<th class="atcl"> </th>
<th>Читать форум</th>
<th>Отвечать в темах</th>
<th>Создавать темы</th>
<th>Скачивать файлы</th>
<th>Выкладывать файлы</th>
<th>Лимит файлов</th>
</tr>
</thead>
<tbody>';

$result = $db->query('SELECT g.g_id, g.g_title, g.g_read_board, g.g_post_replies, g.g_post_topics, g.g_file_upload, g.g_file_download, g.g_file_limit, fp.read_forum, fp.post_replies, fp.post_topics, fp.file_upload, fp.file_download, fp.file_limit FROM '.$db->prefix.'groups AS g LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (g.g_id=fp.group_id AND fp.forum_id='.$forum_id.') WHERE g.g_id!='.PUN_ADMIN.' ORDER BY g.g_id') or error('Unable to fetch group forum permission list', __FILE__, __LINE__, $db->error());

while($cur_perm = $db->fetch_assoc($result))
{
$read_forum = ($cur_perm['read_forum']) ? true : false;
$post_replies = ((!$cur_perm['g_post_replies'] && $cur_perm['post_replies'] == 1) || ($cur_perm['g_post_replies'] == 1 && $cur_perm['post_replies'])) ? true : false;
$post_topics = ((!$cur_perm['g_post_topics'] && $cur_perm['post_topics'] == 1) || ($cur_perm['g_post_topics'] == 1 && $cur_perm['post_topics'])) ? true : false;
$file_upload = ((!$cur_perm['g_file_upload'] && $cur_perm['file_upload'] == 1) || ($cur_perm['g_file_upload'] == 1 && $cur_perm['file_upload'])) ? true : false;
$file_download = ((!$cur_perm['g_file_download'] && $cur_perm['file_download'] == 1) || ($cur_perm['g_file_download'] == 1 && $cur_perm['file_download'])) ? true : false;
$file_limit = ($cur_perm['file_limit']) ? $cur_perm['file_limit'] : $cur_perm['g_file_limit'];

// Determine if the current sittings differ from the default or not
$read_forum_def = (!$cur_perm['read_forum']) ? false : true;
$post_replies_def = (($post_replies && !$cur_perm['g_post_replies']) || (!$post_replies && (!$cur_perm['g_post_replies'] || $cur_perm['g_post_replies'] == 1))) ? false : true;
$post_topics_def = (($post_topics && !$cur_perm['g_post_topics']) || (!$post_topics && (!$cur_perm['g_post_topics'] || $cur_perm['g_post_topics'] == 1))) ? false : true;
$file_upload_def = (($file_upload && !$cur_perm['g_file_upload']) || (!$file_upload && (!$cur_perm['g_file_upload'] || $cur_perm['g_file_upload'] == 1))) ? false : true;
$file_download_def = (($file_download && !$cur_perm['g_file_download']) || (!$file_download && (!$cur_perm['g_file_download'] || $cur_perm['g_file_download'] == 1))) ? false : true;
$file_limit_def = ($file_limit == $cur_perm['g_file_limit']);

?>
<tr>
<th class="atcl"><?php echo pun_htmlspecialchars($cur_perm['g_title']) ?></th>
<td<?php if (!$read_forum_def) echo ' class="nodefault"'; ?>>
<input type="hidden" name="read_forum_old[<?php echo $cur_perm['g_id'] ?>]" value="<?php echo ($read_forum) ? '1' : '0'; ?>" />
<input type="checkbox" name="read_forum_new[<?php echo $cur_perm['g_id'] ?>]" value="1"<?php echo ($read_forum) ? ' checked="checked"' : ''; ?><?php echo (!$cur_perm['g_read_board']) ? ' disabled="disabled"' : ''; ?> />
</td>
<td<?php if (!$post_replies_def && !$cur_forum['redirect_url']) echo ' class="nodefault"'; ?>>
<input type="hidden" name="post_replies_old[<?php echo $cur_perm['g_id'] ?>]" value="<?php echo ($post_replies) ? '1' : '0'; ?>" />
<input type="checkbox" name="post_replies_new[<?php echo $cur_perm['g_id'] ?>]" value="1"<?php echo ($post_replies) ? ' checked="checked"' : ''; ?><?php echo ($cur_forum['redirect_url']) ? ' disabled="disabled"' : ''; ?> />
</td>
<td<?php if (!$post_topics_def && !$cur_forum['redirect_url']) echo ' class="nodefault"'; ?>>
<input type="hidden" name="post_topics_old[<?php echo $cur_perm['g_id'] ?>]" value="<?php echo ($post_topics) ? '1' : '0'; ?>" />
<input type="checkbox" name="post_topics_new[<?php echo $cur_perm['g_id'] ?>]" value="1"<?php echo ($post_topics) ? ' checked="checked"' : ''; ?><?php echo ($cur_forum['redirect_url']) ? ' disabled="disabled"' : ''; ?> />
</td>

<td<?php if (!$file_download_def && !$cur_forum['redirect_url']) echo ' class="nodefault"'; ?>>
<input type="hidden" name="file_download_old[<?php echo $cur_perm['g_id'] ?>]" value="<?php echo ($file_download) ? '1' : '0'; ?>" />
<input type="checkbox" name="file_download_new[<?php echo $cur_perm['g_id'] ?>]" value="1"<?php echo ($file_download) ? ' checked="checked"' : ''; ?><?php echo ($cur_forum['redirect_url']) ? ' disabled="disabled"' : ''; ?> />
</td>
<td<?php if (!$file_upload_def && !$cur_forum['redirect_url']) echo ' class="nodefault"'; ?>>
<input type="hidden" name="file_upload_old[<?php echo $cur_perm['g_id'] ?>]" value="<?php echo ($file_upload) ? '1' : '0'; ?>" />
<input type="checkbox" name="file_upload_new[<?php echo $cur_perm['g_id'] ?>]" value="1"<?php echo ($file_upload) ? ' checked="checked"' : ''; ?><?php echo ($cur_forum['redirect_url'] || $cur_perm['g_id'] == PUN_GUEST) ? ' disabled="disabled"' : ''; ?> />
</td>
<td<?php if (!$file_limit_def && !$cur_forum['redirect_url']) echo ' class="nodefault"'; ?>>
<input type="hidden" name="file_limit_old[<?php echo $cur_perm['g_id'] ?>]" value="<?php echo $file_limit ?>" />
<input type="text" name="file_limit_new[<?php echo $cur_perm['g_id'] ?>]" size="5" maxlength="4" value=<?php echo '"'.$file_limit.'"' ?><?php echo ($cur_forum['redirect_url'] || $cur_perm['g_id'] == PUN_GUEST) ? ' disabled="disabled"' : ''; ?> />
</td>
</tr>
<?php

}


echo '</tbody>
</table>
<div class="fsetsubmit"><input type="submit" name="revert_perms" value="Вернуть по умолчанию" /></div>
</div>
</fieldset>
</div>
<p class="submitend"><input type="submit" name="save" value="Сохранить изменения" /></p>
</form>
</div>
</div>
<div class="clearer"></div>
</div>';

require_once PUN_ROOT.'footer.php';
}


$page_title = pun_htmlspecialchars($pun_config['o_board_title']).' / Admin / Forums';
require_once PUN_ROOT.'header.php';

generate_admin_menu('forums');


echo '<div class="blockform">
<h2><span>Добавить форум</span></h2>
<div class="box">
<form method="post" action="admin_forums.php?action=adddel">
<div class="inform">
<fieldset>
<legend>Создать новый форум</legend>
<div class="infldset">
<table class="aligntop" cellspacing="0">
<tr>
<th scope="row">Имя форума</th>
<td><input type="text" name="forum_name" size="35" maxlength="80" value="New forum" tabindex="1" /></td>
</tr>
<tr>
<th scope="row">Добавить в категорию</th>
<td>
<select name="add_to_cat" tabindex="2">';

$result = $db->query('SELECT id, cat_name FROM '.$db->prefix.'categories ORDER BY disp_position') or error('Unable to fetch category list', __FILE__, __LINE__, $db->error());
if($db->num_rows($result) > 0)
{
    while($cur_cat = $db->fetch_assoc($result)) {
        echo '<option value="'.$cur_cat['id'].'">'.pun_htmlspecialchars($cur_cat['cat_name']).'</option>';
    }
}
else{
    echo '<option value="0" disabled="disabled">No categories exist</option>';
}


echo '</select>
<span>Выберите категорию в которую хотите добавить новый форум.</span>
</td>
</tr>
</table>
</div>
</fieldset>
</div>
<p class="submittop"><input type="submit" name="add_forum" value=" Добавить " tabindex="3" /></p>
</form>
</div>';

// Display all the categories and forums
$result = $db->query('SELECT c.id AS cid, c.cat_name, f.id AS fid, f.forum_name, f.disp_position FROM '.$db->prefix.'categories AS c INNER JOIN '.$db->prefix.'forums AS f ON c.id=f.cat_id ORDER BY c.disp_position, c.id, f.disp_position') or error('Unable to fetch category/forum list', __FILE__, __LINE__, $db->error());

if($db->num_rows($result))
{

echo '<h2 class="block2"><span>Редактирование форумов</span></h2>
<div class="box">
<form id="edforum" method="post" action="admin_forums.php?action=edit">
<p class="submittop"><input type="submit" name="update_positions" value="Обновить позиции" tabindex="3" /></p>';

$tabindex_count = 4;

// Display all the categories and forums
//$result = $db->query('SELECT c.id AS cid, c.cat_name, f.id AS fid, f.forum_name, f.disp_position FROM '.$db->prefix.'categories AS c INNER JOIN '.$db->prefix.'forums AS f ON c.id=f.cat_id ORDER BY c.disp_position, c.id, f.disp_position') or error('Unable to fetch category/forum list', __FILE__, __LINE__, $db->error());

$cur_category = 0;
while($cur_forum = $db->fetch_assoc($result))
{
if($cur_forum['cid'] != $cur_category) // A new category since last iteration?
{
if($cur_category != 0)
{echo '</table></div></fieldset></div>';}


echo '<div class="inform">
<fieldset>
<legend>Категория: '.pun_htmlspecialchars($cur_forum['cat_name']).'</legend>
<div class="infldset">
<table cellspacing="0">';

$cur_category = $cur_forum['cid'];
}


echo '<tr><th style="white-space:nowrap"><a href="admin_forums.php?clone_forum='.$cur_forum['fid'].'">Клон</a> - <a href="admin_forums.php?edit_forum='.$cur_forum['fid'].'">Править</a> - <a href="admin_forums.php?del_forum='.$cur_forum['fid'].'">Удалить</a></th><td>Позиция <input type="text" name="position['.$cur_forum['fid'].']" size="3" maxlength="3" value="'.$cur_forum['disp_position'].'" tabindex="'.$tabindex_count.'" /><strong>'.pun_htmlspecialchars($cur_forum['forum_name']).'</strong></td></tr>';

$tabindex_count += 2;
}


echo '</table>
</div>
</fieldset>
</div>
<p class="submitend"><input type="submit" name="update_positions" value="Обновить позиции" tabindex="'.$tabindex_count.'" /></p>
</form>
</div>';

}


echo '</div><div class="clearer"></div></div>';

require_once PUN_ROOT . 'footer.php';

?>