<?php
// Tell header.php to use the admin template
define('PUN_ADMIN_CONSOLE', 1);

define('PUN_ROOT', './');
require PUN_ROOT . 'include/common.php';
require PUN_ROOT . 'include/common_admin.php';
// Язык
//include PUN_ROOT.'lang/'.$pun_user['language'].'/admin.php';
include PUN_ROOT . 'lang/Russian/admin.php';

if ($pun_user['g_id'] > PUN_ADMIN) {
    message($lang_common['No permission']);
}

// Add a new category
if (isset($_POST['add_cat'])) {
//confirm_referrer('admin_categories.php');

    $new_cat_name = trim($_POST['new_cat_name']);
    if (!$new_cat_name) {
        message($lang_admin['categories_no']);
    }

    $db->query('INSERT INTO ' . $db->prefix . 'categories (cat_name) VALUES(\'' . $db->escape($new_cat_name) . '\')') or error('Unable to create category', __FILE__, __LINE__, $db->error());

    redirect('admin_categories.php', $lang_admin['categories_yes']);
} // Delete a category
else if (isset($_POST['del_cat']) || isset($_POST['del_cat_comply'])) {
//confirm_referrer('admin_categories.php');

    $cat_to_delete = intval($_POST['cat_to_delete']);
    if ($cat_to_delete < 1) {
        message($lang_common['Bad request']);
    }

    if (isset($_POST['del_cat_comply'])) // Delete a category with all forums and posts
    {
        @set_time_limit(0);

        $result = $db->query('SELECT id FROM ' . $db->prefix . 'forums WHERE cat_id=' . $cat_to_delete) or error('Unable to fetch forum list', __FILE__, __LINE__, $db->error());
        $num_forums = $db->num_rows($result);

        for ($i = 0; $i < $num_forums; ++$i) {
            $cur_forum = $db->result($result, $i);

// Prune all posts and topics
            prune($cur_forum, 1, -1);

// Delete the forum
            $db->query('DELETE FROM ' . $db->prefix . 'forums WHERE id=' . $cur_forum) or error('Unable to delete forum', __FILE__, __LINE__, $db->error());
        }

// Locate any "orphaned redirect topics" and delete them
        $result = $db->query('SELECT t1.id FROM ' . $db->prefix . 'topics AS t1 LEFT JOIN ' . $db->prefix . 'topics AS t2 ON t1.moved_to=t2.id WHERE t2.id IS NULL AND t1.moved_to IS NOT NULL') or error('Unable to fetch redirect topics', __FILE__, __LINE__, $db->error());
        $num_orphans = $db->num_rows($result);

        if ($num_orphans) {
            for ($i = 0; $i < $num_orphans; ++$i) {
                $orphans[] = $db->result($result, $i);
            }

            $db->query('DELETE FROM ' . $db->prefix . 'topics WHERE id IN(' . implode(',', $orphans) . ')') or error('Unable to delete redirect topics', __FILE__, __LINE__, $db->error());
        }

// Delete the category
        $db->query('DELETE FROM ' . $db->prefix . 'categories WHERE id=' . $cat_to_delete) or error('Unable to delete category', __FILE__, __LINE__, $db->error());

// Regenerate the quickjump cache
        include_once PUN_ROOT . 'include/cache.php';
        generate_quickjump_cache();

        redirect('admin_categories.php', $lang_admin['categories_del_true']);
    } else // If the user hasn't comfirmed the delete
    {
        $result = $db->query('SELECT cat_name FROM ' . $db->prefix . 'categories WHERE id=' . $cat_to_delete) or error('Unable to fetch category info', __FILE__, __LINE__, $db->error());
        $cat_name = $db->result($result);

        $page_title = pun_htmlspecialchars($pun_config['o_board_title']) . ' / Admin / Categories';
        require_once PUN_ROOT . 'header.php';

        generate_admin_menu('categories');

        print '<div class="blockform">
<h2><span>' . $lang_admin['categories_del'] . '</span></h2>
<div class="box">
<form method="post" action="admin_categories.php?">
<div class="inform">
<input type="hidden" name="cat_to_delete" value="' . $cat_to_delete . '" />
<fieldset>
<legend>' . $lang_admin['categories_del_a'] . '</legend>
<div class="infldset">
<p>' . $lang_admin['categories_del_b'] . ' (<strong>' . $cat_name . '</strong>)</p>
<p>' . $lang_admin['categories_del_c'] . '</p>
</div>
</fieldset>
</div>
<p><input type="submit" name="del_cat_comply" value="' . $lang_admin['Del'] . '" /><a href="javascript:history.go(-1)">' . $lang_admin['Back'] . '</a></p>
</form>
</div>
</div>
<div class="clearer"></div>
</div>';


        require_once PUN_ROOT . 'footer.php';
    }
} else if (isset($_POST['update'])) // Change position and name of the categories
{
//confirm_referrer('admin_categories.php');

    $cat_order = $_POST['cat_order'];
    $cat_name = $_POST['cat_name'];

    $result = $db->query('SELECT id, disp_position FROM ' . $db->prefix . 'categories ORDER BY disp_position') or error('Unable to fetch category list', __FILE__, __LINE__, $db->error());
    $num_cats = $db->num_rows($result);

    for ($i = 0; $i < $num_cats; ++$i) {
        if (!$cat_name[$i]) {
            message($lang_admin['categories_no']);
        }

        if (!@preg_match('#^\d+$#', $cat_order[$i])) {
            message($_lang_admin['categories_fail_position']);
        }

        list($cat_id, $position) = $db->fetch_row($result);

        $db->query('UPDATE ' . $db->prefix . 'categories SET cat_name=\'' . $db->escape($cat_name[$i]) . '\', disp_position=' . $cat_order[$i] . ' WHERE id=' . $cat_id) or error('Unable to update category', __FILE__, __LINE__, $db->error());
    }

// Regenerate the quickjump cache
    include_once PUN_ROOT . 'include/cache.php';
    generate_quickjump_cache();

    redirect('admin_categories.php', $lang_admin['categories_update_true']);
}


// Generate an array with all categories
$result = $db->query('SELECT id, cat_name, disp_position FROM ' . $db->prefix . 'categories ORDER BY disp_position') or error('Unable to fetch category list', __FILE__, __LINE__, $db->error());
$num_cats = $db->num_rows($result);

for ($i = 0; $i < $num_cats; ++$i) {
    $cat_list[] = $db->fetch_row($result);
}

$page_title = pun_htmlspecialchars($pun_config['o_board_title']) . ' / Admin / Categories';
require_once PUN_ROOT . 'header.php';

generate_admin_menu('categories');

print '<div class="blockform">
<h2><span>' . $lang_admin['categories'] . '</span></h2>
<div class="box">
<form method="post" action="admin_categories.php?action=foo">
<div class="inform">
<fieldset>
<legend>' . $lang_admin['categories_del_ins'] . '</legend>
<div class="infldset">
<table class="aligntop" cellspacing="0">
<tr>
<th scope="row">
' . $lang_admin['categories_ins'] . '
<div><input type="submit" name="add_cat" value="' . $lang_admin['Add'] . '" /></div>
</th>
<td>
<input type="text" name="new_cat_name" size="35" maxlength="80" />
<span>' . $lang_admin['categories_ins_about'] . '</span>
</td>
</tr>';

if ($num_cats) {
    print '<tr>
<th scope="row">' . $lang_admin['categories_delete'] . '<div><input type="submit" name="del_cat" value="' . $lang_admin['Del'] . '" /></div></th>
<td><select name="cat_to_delete">';


    while (list(, list($cat_id, $cat_name, ,)) = @each($cat_list)) {
        echo '<option value="' . $cat_id . '">' . pun_htmlspecialchars($cat_name) . '</option>';
    }

    echo '</select><span>' . $lang_admin['categories_delete_about'] . '</span></td></tr>';
}


echo '</table></div></fieldset></div>';

if ($num_cats) {
    echo '<div class="inform">
<fieldset>
<legend>' . $lang_admin['categories_set'] . '</legend>
<div class="infldset">
<table id="categoryedit" cellspacing="0" >
<thead>
<tr>
<th class="tcl" scope="col">' . $lang_admin['categories_name'] . '</th>
<th scope="col">' . $lang_admin['categories_position'] . '</th>
<th> </th>
</tr>
</thead>
<tbody>';


    @reset($cat_list);
    for ($i = 0; $i < $num_cats; ++$i) {
        list(, list($cat_id, $cat_name, $position)) = @each($cat_list);

        echo '<tr><td>
<input type="text" name="cat_name[' . $i . ']" value="' . pun_htmlspecialchars($cat_name) . '" size="35" maxlength="80" />
</td>
<td>
<input type="text" name="cat_order[' . $i . ']" value="' . $position . '" size="3" maxlength="3" />
</td>
<td> </td>
</tr>';
    }


    echo '</tbody>
</table>
<div class="fsetsubmit"><input type="submit" name="update" value="' . $lang_admin['Upd'] . '" /></div>
</div>
</fieldset>
</div>';
}

echo '</form></div></div><div class="clearer"></div></div>';

require_once PUN_ROOT . 'footer.php';
