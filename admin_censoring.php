<?php
// Tell header.php to use the admin template
\define('PUN_ADMIN_CONSOLE', 1);

\define('PUN_ROOT', './');

require PUN_ROOT.'include/common.php';

require PUN_ROOT.'include/common_admin.php';
// Язык
//include PUN_ROOT.'lang/'.$pun_user['language'].'/admin.php';
include PUN_ROOT.'lang/Russian/admin.php';

if ($pun_user['g_id'] > PUN_MOD) {
    message($lang_common['No permission']);
}

// Add a censor word
if (isset($_POST['add_word'])) {
    //confirm_referrer('admin_censoring.php');

    $search_for = \trim($_POST['new_search_for']);
    $replace_with = \trim($_POST['new_replace_with']);

    if (!$search_for || !$replace_with) {
        message($lang_admin['Cens not found']);
    }

    $db->query('INSERT INTO '.$db->prefix.'censoring (search_for, replace_with) VALUES (\''.$db->escape($search_for).'\', \''.$db->escape($replace_with).'\')') or error('Unable to add censor word', __FILE__, __LINE__, $db->error());

    redirect('admin_censoring.php', $lang_admin['Added'].' '.$lang_admin['Redirect']);
} // Update a censor word
elseif (isset($_POST['update'])) {
    //confirm_referrer('admin_censoring.php');

    $id = \intval(\key($_POST['update']));

    $search_for = \trim($_POST['search_for'][$id]);
    $replace_with = \trim($_POST['replace_with'][$id]);

    if (!$search_for || !$replace_with) {
        message($lang_admin['Cens not found']);
    }

    $db->query('UPDATE '.$db->prefix.'censoring SET search_for=\''.$db->escape($search_for).'\', replace_with=\''.$db->escape($replace_with).'\' WHERE id='.$id) or error('Unable to update censor word', __FILE__, __LINE__, $db->error());

    redirect('admin_censoring.php', $lang_admin['Updated'].' '.$lang_admin['Redirect']);
} // Remove a censor word
elseif (isset($_POST['remove'])) {
    //confirm_referrer('admin_censoring.php');

    $id = \intval(\key($_POST['remove']));

    $db->query('DELETE FROM '.$db->prefix.'censoring WHERE id='.$id) or error('Unable to delete censor word', __FILE__, __LINE__, $db->error());

    redirect('admin_censoring.php', $lang_admin['Deleted'].' '.$lang_admin['Redirect']);
}

$page_title = pun_htmlspecialchars($pun_config['o_board_title']).' / Admin / Censoring';
$focus_element = ['censoring', 'new_search_for'];

require_once PUN_ROOT.'header.php';

generate_admin_menu('censoring');

?>
<div class="blockform">
    <h2><span><?php echo $lang_admin['Cens']; ?></span></h2>

    <div class="box">
        <form id="censoring" method="post" action="admin_censoring.php?action=foo">
            <div class="inform">
                <fieldset>
                    <legend><?php echo $lang_admin['Cens about']; ?></legend>
                    <div class="infldset">
                        <p><?php echo $lang_admin['Cens edit']; ?></p>
                        <table cellspacing="0">
                            <thead>
                            <tr>
                                <th class="tcl" scope="col"><?php echo $lang_admin['Cens 1']; ?></th>
                                <th class="tc2" scope="col"><?php echo $lang_admin['Cens 2']; ?></th>
                                <th class="hidehead" scope="col"><?php echo $lang_admin['Act']; ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td><input type="text" name="new_search_for" size="24" maxlength="60" />
                                </td>
                                <td><input type="text" name="new_replace_with" size="24" maxlength="60" />
                                </td>
                                <td><input type="submit" name="add_word" value=" Добавить " /></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </fieldset>
            </div>
            <div class="inform">
                <fieldset>
                    <legend><?php echo $lang_admin['Edit cens']; ?></legend>
                    <div class="infldset">
<?php

$result = $db->query('SELECT id, search_for, replace_with FROM '.$db->prefix.'censoring ORDER BY id') or error('Unable to fetch censor word list', __FILE__, __LINE__, $db->error());
if ($db->num_rows($result)) {
    echo '<table cellspacing="0">
<thead>
<tr>
<th class="tcl" scope="col">'.$lang_admin['Cens 1'].'</th>
<th class="tc2" scope="col">'.$lang_admin['Cens 2'].'</th>
<th class="hidehead" scope="col">'.$lang_admin['Act'].'</th>
</tr>
</thead>
<tbody>';

    while ($cur_word = $db->fetch_assoc($result)) {
        echo '<tr><td><input type="text" name="search_for['.$cur_word['id'].']" value="'.pun_htmlspecialchars($cur_word['search_for']).'" size="24" maxlength="60" /></td><td><input type="text" name="replace_with['.$cur_word['id'].']" value="'.pun_htmlspecialchars($cur_word['replace_with']).'" size="24" maxlength="60" /></td><td><input type="submit" name="update['.$cur_word['id'].']" value="'.$lang_admin['Upd'].'" /> <input type="submit" name="remove['.$cur_word['id'].']" value="'.$lang_admin['Del'].'" /></td></tr>';
    }

    echo '</tbody></table>';
} else {
    echo '<p>'.$lang_admin['Not cens'].'</p>';
}

echo '</div></fieldset></div></form></div></div><div class="clearer"></div></div>';

require_once PUN_ROOT.'footer.php';
