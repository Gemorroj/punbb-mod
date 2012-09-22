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


if (isset($_GET['action']) || isset($_POST['prune']) || isset($_POST['prune_comply'])) {
    if (isset($_POST['prune_comply'])) {
//confirm_referrer('admin_prune.php');

        $prune_from = $_POST['prune_from'];
        $prune_sticky = isset($_POST['prune_sticky']) ? 1 : 0;
        $prune_days = intval($_POST['prune_days']);
        $prune_date = ($prune_days) ? time() - ($prune_days * 86400) : -1;

        @set_time_limit(0);

        if ($prune_from == 'all') {
            $result = $db->query('SELECT id FROM ' . $db->prefix . 'forums') or error('Unable to fetch forum list', __FILE__, __LINE__, $db->error());
            $num_forums = $db->num_rows($result);

            for ($i = 0; $i < $num_forums; ++$i) {
                $fid = $db->result($result, $i);

                prune($fid, $prune_sticky, $prune_date);
                update_forum($fid);
            }
        } else {
            $prune_from = intval($prune_from);
            prune($prune_from, $prune_sticky, $prune_date);
            update_forum($prune_from);
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

        redirect('admin_prune.php', $lang_admin['Cleared'] . ' ' . $lang_admin['Redirect']);
    }


    $prune_days = $_POST['req_prune_days'];
    if (!@preg_match('#^\d+$#', $prune_days)) {
        message($lang_admin['Prune days not numeric']);
    }

    $prune_date = time() - ($prune_days * 86400);
    $prune_from = $_POST['prune_from'];

// Concatenate together the query for counting number or topics to prune
    $sql = 'SELECT COUNT(id) FROM ' . $db->prefix . 'topics WHERE last_post<' . $prune_date . ' AND moved_to IS NULL';

    if (!$prune_sticky) {
        $sql .= ' AND sticky=0';
    }

    if ($prune_from != 'all') {
        $prune_from = intval($prune_from);
        $sql .= ' AND forum_id=' . $prune_from;

// Fetch the forum name (just for cosmetic reasons)
        $result = $db->query('SELECT forum_name FROM ' . $db->prefix . 'forums WHERE id=' . $prune_from) or error('Unable to fetch forum name', __FILE__, __LINE__, $db->error());
        $forum = '"' . pun_htmlspecialchars($db->result($result)) . '"';
    } else {
        $forum = 'all forums';
    }

    $result = $db->query($sql) or error('Unable to fetch topic prune count', __FILE__, __LINE__, $db->error());
    $num_topics = $db->result($result);

    if (!$num_topics) {
        message($lang_admin['Prune not found']);
    }

    $page_title = pun_htmlspecialchars($pun_config['o_board_title']) . ' / Admin / Prune';
    require_once PUN_ROOT . 'header.php';

    generate_admin_menu('prune');
    ?>
<div class="blockform">
    <h2><span><?php print $lang_admin['Prune']; ?></span></h2>

    <div class="box">
        <form method="post" action="admin_prune.php?action=foo">
            <div class="inform">
                <input type="hidden" name="prune_days" value="<?php echo $prune_days ?>"/>
                <input type="hidden" name="prune_sticky" value="<?php echo $_POST['prune_sticky'] ?>"/>
                <input type="hidden" name="prune_from" value="<?php echo $prune_from ?>"/>
                <fieldset>
                    <legend><?php print $lang_admin['Prune true'] ?></legend>
                    <div class="infldset">
                        <p>
                            <?php print $lang_admin['Prune true2'] . ' <strong>' . $num_topics . '</strong>'; ?>
                        </p>
                    </div>
                </fieldset>
            </div>
            <p><input type="submit" name="prune_comply" value="<?php print $lang_admin['Clear']; ?>"/><a
                href="javascript:history.go(-1)"><?php print $lang_admin['Back']; ?></a></p>
        </form>
    </div>
</div>
<div class="clearer"></div>
</div>
<?php
    require_once PUN_ROOT . 'footer.php';
} else {
    $page_title = pun_htmlspecialchars($pun_config['o_board_title']) . ' / Admin / Prune';
    $required_fields = array('req_prune_days' => 'Days old');
    $focus_element = array('prune', 'req_prune_days');
    require_once PUN_ROOT . 'header.php';

    generate_admin_menu('prune');
    ?>
<div class="blockform">
    <h2><span><?php print $lang_admin['Prune']; ?></span></h2>

    <div class="box">
        <form id="prune" method="post" action="admin_prune.php?action=foo" onsubmit="return process_form(this)">
            <div class="inform">
                <input type="hidden" name="form_sent" value="1"/>
                <fieldset>
                    <legend><?php print $lang_admin['About prune']; ?></legend>
                    <div class="infldset">
                        <table class="aligntop" cellspacing="0">
                            <tr>
                                <th scope="row"><?php print $lang_admin['Prune days']; ?></th>
                                <td>
                                    <input type="text" name="req_prune_days" size="3" maxlength="3" />
<span>
<?php print $lang_admin['Enter prune']; ?>
</span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php print $lang_admin['Prune themes']; ?></th>
                                <td>
                                    <input type="radio" name="prune_sticky" value="1" checked="checked"/>
                                    <strong><?php print $lang_admin['Yes']; ?></strong>
                                    <input type="radio" name="prune_sticky" value="0"/>
                                    <strong><?php print $lang_admin['No']; ?></strong>
<span>
<?php print $lang_admin['About prune themes']; ?>
</span>
                                </td>
                            </tr>
                            <tr>
                            <th scope="row"><?php echo $lang_admin['Prune forums']; ?></th>
                            <td>
                                <select name="prune_from">
                                    <option value="all"><?php echo $lang_admin['All forums']; ?></option>
<?php
$result = $db->query('SELECT c.id AS cid, c.cat_name, f.id AS fid, f.forum_name FROM ' . $db->prefix . 'categories AS c INNER JOIN ' . $db->prefix . 'forums AS f ON c.id=f.cat_id WHERE f.redirect_url IS NULL ORDER BY c.disp_position, c.id, f.disp_position') or error('Unable to fetch category/forum list', __FILE__, __LINE__, $db->error());

$cur_category = 0;
while ($forum = $db->fetch_assoc($result)) {
    if ($forum['cid'] != $cur_category) // Are we still in the same category?
    {
        if ($cur_category) {
            echo '</optgroup>';
        }
        echo '<optgroup label="' . pun_htmlspecialchars($forum['cat_name']) . '">';
        $cur_category = $forum['cid'];
    }

    echo '<option value="' . $forum['fid'] . '">' . pun_htmlspecialchars($forum['forum_name']) . '</option>';
}

echo '</optgroup>
</select>
<span>' . $lang_admin['About prune forums'] . '</span>
</td>
</tr>
</table>
<p class="topspace">' . $lang_admin['Enter prune forums'] . '</p>
<div class="fsetsubmit"><input type="submit" name="prune" value="' . $lang_admin['Clear'] . '" /></div>
</div>
</fieldset>
</div>
</form>
</div>
</div>
<div class="clearer"></div>
</div>';

require_once PUN_ROOT . 'footer.php';
}
?>