<?php
// Make sure no one attempts to run this script "directly"
if (!\defined('PUN')) {
    exit;
}

// Tell admin_loader.php that this is indeed a plugin and that it is loaded
\define('PUN_PLUGIN_LOADED', 1);
\define('PLUGIN_VERSION', 1.2);

$result = $db->query('SELECT * FROM '.$db->prefix.'uploads_conf WHERE g_id='.$pun_user['g_id']);
$upl_conf = $db->fetch_assoc($result);
if (!$upl_conf) {
    $result = $db->query('SELECT * FROM '.$db->prefix.'uploads_conf WHERE g_id=0');
    $upl_conf = $db->fetch_assoc($result);
}

// If the "Show text" button was clicked
if (isset($_POST['save_options'])) {
    $k = 1;
    while ($k <= $_POST['k']) {
        $p_view[$k] = isset($_POST['p_view_'.$k]) ? \intval($_POST['p_view_'.$k]) : 0;
        $p_upload[$k] = isset($_POST['p_upload_'.$k]) ? \intval($_POST['p_upload_'.$k]) : 0;
        $p_globalview[$k] = isset($_POST['p_globalview_'.$k]) ? \intval($_POST['p_globalview_'.$k]) : 0;
        $p_delete[$k] = isset($_POST['p_delete_'.$k]) ? \intval($_POST['p_delete_'.$k]) : 0;
        $p_globaldelete[$k] = isset($_POST['p_globaldelete_'.$k]) ? \intval($_POST['p_globaldelete_'.$k]) : 0;
        $p_setop[$k] = isset($_POST['p_setop_'.$k]) ? \intval($_POST['p_setop_'.$k]) : 0;
        $u_fsize[$k] = isset($_POST['u_fsize_'.$k]) ? \intval($_POST['u_fsize_'.$k]) : 0;

        $result2 = $db->query('SELECT g_id FROM '.$db->prefix.'uploads_conf WHERE g_id='.$k);
        if ($db->fetch_assoc($result2)) {
            $query = '
                UPDATE '.$db->prefix.'uploads_conf
                SET p_view = '.$p_view[$k].',
                p_upload ='.$p_upload[$k].',
                p_globalview = '.$p_globalview[$k].',
                p_delete = '.$p_delete[$k].',
                p_globaldelete = '.$p_globaldelete[$k].',
                p_setop = '.$p_setop[$k].',
                u_fsize = '.$u_fsize[$k].'
                WHERE g_id='.$k.';
            ';
        } else {
            $query = '
                INSERT INTO '.$db->prefix.'uploads_conf VALUES (
                    '.$k.',
                    '.$u_fsize[$k].',
                    '.$p_view[$k].',
                    '.$p_globalview[$k].',
                    '.$p_upload[$k].',
                    '.$p_delete[$k].',
                    '.$p_globaldelete[$k].',
                    '.$p_setop[$k].'
                )
            ';
        }
        $result = $db->query($query);
        ++$k;
    }
    \redirect($_SERVER['REQUEST_URI'], 'Permissions updated, redirecting &#x2026;');
} elseif (isset($_POST['save_types'])) {
    $k = 1;
    while ($k <= $_POST['num_types']) {
        $db->query('UPDATE '.$db->prefix.'uploads_types SET type="'.$db->escape($_POST['cat'.$k]).'", exts="'.$db->escape($_POST['ext'.$k]).'" WHERE id='.$k) or \error('Unable to update info about types', __FILE__, __LINE__, $db->error());
        ++$k;
    }
    \redirect($_SERVER['REQUEST_URI'], 'Types updated, redirecting &#x2026;');
} elseif (isset($_POST['add_type'])) {
    $db->query('INSERT INTO '.$db->prefix.'uploads_types (type,exts) VALUES ("'.$db->escape($_POST['cat0']).'","'.$db->escape($_POST['ext0']).'")') or \error('Unable to add new type', __FILE__, __LINE__, $db->error());
    \redirect($_SERVER['REQUEST_URI'], 'New type added, redirecting &#x2026;');
} elseif (isset($_GET['action'], $_GET['id'])) {
    if ('delete' == $_GET['action']) {
        $db->query('DELETE FROM '.$db->prefix.'uploads_types WHERE id='.\intval($_GET['id'])) or \error('Unable to delete a type', __FILE__, __LINE__, $db->error());
        \redirect('admin_loader.php?plugin='.$plugin, 'Type deleted, redirecting &#x2026;');
    } else {
        \redirect('admin_loader.php?plugin='.$plugin, 'Action unknown, redirecting &#x2026;');
    }
}

// Display the admin navigation menu
\generate_admin_menu($plugin);

?>
<div id="exampleplugin" class="blockform">
<h2><span>PunUploadExtra <?php echo PLUGIN_VERSION; ?> module options</span></h2>
<div class="box">
<div class="inbox">
<?php

if (!$upl_conf['p_setop']) {
    echo '<p>You do not have permissions to set configuration of this module. Please contact Administration.</p>';
} else {
    ?>
    <p>This plugin edits settings for PunUploadExtra module.</p>
    <?php
    $result = $db->query('SELECT g_id, g_title FROM `'.$db->prefix.'groups`') or \error('Unable to get useergroups', __FILE__, __LINE__, $db->error());
    $i = 0;
    while ($i < $db->num_rows($result)) {
        $groups[$i] = $db->fetch_assoc($result);
        $result2 = $db->query('SELECT * FROM '.$db->prefix.'uploads_conf WHERE g_id='.$groups[$i]['g_id']) or \error('Unable to read upload persmissions', __FILE__, __LINE__, $db->error());
        $perms[$i] = $db->fetch_assoc($result2);
        if (!$perms[$i]) {
            $result2 = $db->query('SELECT * FROM '.$db->prefix.'uploads_conf WHERE g_id=0');
            $perms[$i] = $db->fetch_assoc($result2);
        }
        ++$i;
    }

    echo '<div class="inform">
<form id="example" method="post" action="'.$_SERVER['REQUEST_URI'].'">
<fieldset>
<legend>Edit group permissions for uploads module</legend>
<div class="infldset">
<table id="forumperms" cellspacing="0">
<thead>
<tr>
<th class="atcl"> </th>
<th>Access to uploader</th>
<th>Upload files</th>
<th>View *all* uploaded files</th>
<th>Delete files</th>
<th>Global Moderation</th>
<th>Set uploader options</th>
<th>Max upload file size (Kb)</th>
</tr>
</thead>
<tbody>';

    $k = 0;
    foreach ($groups as $group) {
        echo '<tr><th class="atcl">'.\pun_htmlspecialchars($group['g_title']).'<input type="hidden" name="g_title_'.$group['g_id'].'" value="'.\pun_htmlspecialchars($group['g_title']).'" /></th>'; ?>

        <td>
        <input type="checkbox" name="p_view_<?php echo $group['g_id']; ?>"
               value="1" <?php if (1 == $perms[$k]['p_view']) {
            echo 'checked="checked"';
        } ?> />
        </td>
        <td>
            <input type="checkbox" name="p_upload_<?php echo $group['g_id']; ?>"
                   value="1" <?php if (1 == $perms[$k]['p_upload']) {
            echo 'checked="checked"';
        } ?> />
        </td>
        <td>
            <input type="checkbox" name="p_globalview_<?php echo $group['g_id']; ?>"
                   value="1" <?php if (1 == $perms[$k]['p_globalview']) {
            echo 'checked="checked"';
        } ?> />
        </td>
        <td>
            <input type="checkbox" name="p_delete_<?php echo $group['g_id']; ?>"
                   value="1" <?php if (1 == $perms[$k]['p_delete']) {
            echo 'checked="checked"';
        } ?> />
        </td>
        <td>
            <?php if (3 != $group['g_id']) {
            ?>
            <input type="checkbox" name="p_globaldelete_<?php echo $group['g_id']; ?>"
                   value="1" <?php if (1 == $perms[$k]['p_globaldelete']) {
                echo 'checked="checked"';
            } ?> />
            <?php
        } else {
            echo '<strong>N/A</strong>'; ?><input type="hidden" name="p_setop_<?php echo $group['g_id']; ?>"
                                                  value="0"/><?php
        } ?>
        </td>
        <td>
            <?php if (1 == $group['g_id'] || 2 == $group['g_id']) {
            ?>
            <input type="checkbox" name="p_setop_<?php echo $group['g_id']; ?>"
                   value="1" <?php if (1 == $perms[$k]['p_setop']) {
                echo 'checked="checked"';
            } ?> />
            <?php
        } else {
            echo '<strong>N/A</strong>'; ?><input type="hidden" name="p_setop_<?php echo $group['g_id']; ?>"
                                                  value="0"/><?php
        } ?>
        </td>
        <td>
            <input type="text" size="7" name="u_fsize_<?php echo $group['g_id']; ?>"
                   value="<?php echo $perms[$k]['u_fsize']; ?>"/>
        </td>
        </tr>

        <?php
        ++$k;
    } ?>

    </tbody>
    </table>
    <div class="fsetsubmit"><input type="submit" name="save_options" value="Save options"/></div>
    <input type="hidden" name="k" value="<?php echo $k; ?>"/>
</div>
</fieldset>
    </form>
</div>

<div class="inform">
    <form id="types" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
        <fieldset>
            <legend>Edit file categories and their types, or add a new one</legend>
            <div class="infldset">

                <div class="fsetsubmit"><input type="submit" name="save_types" value="Save types"/></div>
                <br/>
<?php
$result = $db->query('SELECT * FROM '.$db->prefix.'uploads_types') or \error('Unable to read upload typess', __FILE__, __LINE__, $db->error());
    $num_types = 0;
    while ($ar = $db->fetch_assoc($result)) {
        echo '<input type="text" size="30" maxlength="1000" value="'.\pun_htmlspecialchars($ar['type']).'" name="cat'.$ar['id'].'" /> <input type="text" size="50" maxlength="5000" value="'.\pun_htmlspecialchars($ar['exts']).'" name="ext'.$ar['id'].'" /> <a href="'.$_SERVER['REQUEST_URI'].'&amp;action=delete&amp;id='.$ar['id'].'">Delete</a><br />';
        ++$num_types;
    }

    echo '<input type="hidden" name="num_types" value="'.$num_types.'" />
<div class="fsetsubmit"><input type="submit" name="save_types" value="Save types" /></div><br />
<div class="inform">
<fieldset><legend>Add a new type</legend>
<div class="infldset">
<input type="text" size="30" maxlength="1000" value="" name="cat0" />&#160; <input type="text" size="50" maxlength="5000" value="" name="ext0" /><br />
<input type="submit" name="add_type" value="Add new type" />
</div>
</fieldset>
</div>
</div>
</fieldset>
</form>
</div>
</div>
</div>
</div>';
}
//$upl_conf['p_setop']
