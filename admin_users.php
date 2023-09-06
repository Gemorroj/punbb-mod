<?php
// Tell header.php to use the admin template
\define('PUN_ADMIN_CONSOLE', 1);

\define('PUN_ROOT', './');

require PUN_ROOT.'include/common.php';

require PUN_ROOT.'include/common_admin.php';
// Язык
// include PUN_ROOT.'lang/'.$pun_user['language'].'/admin.php';
include PUN_ROOT.'lang/Russian/admin.php';

if ($pun_user['g_id'] > PUN_MOD) {
    \message($lang_common['No permission']);
}

// Show IP statistics for a certain user ID
if (isset($_GET['ip_stats'])) {
    $ip_stats = \intval($_GET['ip_stats']);
    if ($ip_stats < 1) {
        \message($lang_common['Bad request']);
    }

    $page_title = \pun_htmlspecialchars($pun_config['o_board_title']).' / Admin / Users';

    require_once PUN_ROOT.'header.php'; ?>
<div class="linkst">
    <div class="inbox">
        <div><a href="javascript:history.go(-1)"><?php echo $lang_admin['Back']; ?></a></div>
    </div>
</div>
<div id="users1" class="blocktable">
    <h2><span><?php echo $lang_admin['Users']; ?></span></h2>

    <div class="box">
        <div class="inbox">
            <table cellspacing="0">
                <thead>
                <tr>
                    <th class="tcl" scope="col"><?php echo $lang_admin['IP']; ?></th>
                    <th class="tc2" scope="col"><?php echo $lang_admin['Util']; ?></th>
                    <th class="tc3" scope="col"><?php echo $lang_admin['Time']; ?></th>
                    <th class="tcr" scope="col"><?php echo $lang_admin['Act']; ?></th>
                </tr>
                </thead>
                <tbody>
                    <?php

                    $result = $db->query('SELECT poster_ip, MAX(posted) AS last_used, COUNT(id) AS used_times FROM '.$db->prefix.'posts WHERE poster_id='.$ip_stats.' GROUP BY poster_ip ORDER BY last_used DESC') or \error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
    if ($db->num_rows($result)) {
        while ($cur_ip = $db->fetch_assoc($result)) {
            ?>
                        <tr>
                            <td class="tcl"><a
                                href="moderate.php?get_host=<?php echo $cur_ip['poster_ip']; ?>"><?php echo $cur_ip['poster_ip']; ?></a>
                            </td>
                            <td class="tc2"><?php echo \format_time($cur_ip['last_used']); ?></td>
                            <td class="tc3"><?php echo $cur_ip['used_times']; ?></td>
                            <td class="tcr"><a
                                href="admin_users.php?show_users=<?php echo $cur_ip['poster_ip']; ?>"><?php echo $lang_admin['All IP']; ?></a>
                            </td>
                        </tr>
                            <?php
        }
    } else {
        echo '<tr><td class="tcl" colspan="4">'.$lang_admin['No message'].'</tr>';
    } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="linksb">
    <div class="inbox">
        <div><a href="javascript:history.go(-1)"><?php echo $lang_admin['Back']; ?></a></div>
    </div>
</div>
<?php

    require_once PUN_ROOT.'footer.php';
}

if (isset($_GET['show_users'])) {
    $ip = $_GET['show_users'];

    if (!@\preg_match('/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/', $ip)) {
        \message($lang_admin['Bad IP']);
    }

    $page_title = \pun_htmlspecialchars($pun_config['o_board_title']).' / Admin / Users';

    require_once PUN_ROOT.'header.php'; ?>
<div class="linkst">
    <div class="inbox">
        <div><a href="javascript:history.go(-1)"><?php echo $lang_admin['Back']; ?></a></div>
    </div>
</div>
<div id="users2" class="blocktable">
<h2><span><?php echo $lang_admin['Users']; ?></span></h2>
<div class="box">
<div class="inbox">
<table cellspacing="0">
<thead>
<tr>
    <th class="tcl" scope="col"><?php echo $lang_admin['Username']; ?></th>
    <th class="tc2" scope="col"><?php echo $lang_admin['Email']; ?></th>
    <th class="tc3" scope="col"><?php echo $lang_admin['Title']; ?></th>
    <th class="tc4" scope="col"><?php echo $lang_admin['Num posts']; ?></th>
    <th class="tc5" scope="col"><?php echo $lang_admin['Comment']; ?></th>
    <th class="tcr" scope="col"><?php echo $lang_admin['Acts']; ?></th>
</tr>
</thead>
<tbody>
<?php

    $result = $db->query('SELECT DISTINCT poster_id, poster FROM '.$db->prefix.'posts WHERE poster_ip=\''.$db->escape($ip).'\' ORDER BY poster DESC') or \error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
    $num_posts = $db->num_rows($result);

    if ($num_posts) {
        // Loop through users and print out some info
        for ($i = 0; $i < $num_posts; ++$i) {
            [$poster_id, $poster] = $db->fetch_row($result);

            $result2 = $db->query('SELECT u.id, u.username, u.email, u.title, u.num_posts, u.admin_note, g.g_id, g.g_user_title FROM '.$db->prefix.'users AS u INNER JOIN `'.$db->prefix.'groups` AS g ON g.g_id=u.group_id WHERE u.id>1 AND u.id='.$poster_id) or \error('Unable to fetch user info', __FILE__, __LINE__, $db->error());

            if ($user_data = $db->fetch_assoc($result2)) {
                $user_title = \get_title($user_data);
                $actions = '<a href="admin_users.php?ip_stats='.$user_data['id'].'">'.$lang_admin['IP stats'].'</a> - <a href="search.php?action=show_user&amp;user_id='.$user_data['id'].'">'.$lang_admin['Num posts'].'</a>'; ?>
            <tr>
                <td class="tcl"><?php echo '<a href="profile.php?id='.$user_data['id'].'">'.\pun_htmlspecialchars($user_data['username']).'</a>'; ?></td>
                <td class="tc2"><a
                    href="mailto:<?php echo $user_data['email']; ?>"><?php echo $user_data['email']; ?></a></td>
                <td class="tc3"><?php echo $user_title; ?></td>
                <td class="tc4"><?php echo $user_data['num_posts']; ?></td>
                <td class="tc5"><?php echo ($user_data['admin_note']) ? $user_data['admin_note'] : ' '; ?></td>
                <td class="tcr"><?php echo $actions; ?></td>
            </tr>
                <?php
            } else {
                echo '<tr>
<td class="tcl">'.\pun_htmlspecialchars($poster).'</td>
<td class="tc2"> </td>
<td class="tc3">Guest</td>
<td class="tc4"> </td>
<td class="tc5"> </td>
<td class="tcr"> </td>
</tr>';
            }
        }
    } else {
        echo '<tr><td class="tcl" colspan="6">'.$lang_admin['IP not found'].'</td></tr>';
    }

    echo '</tbody>
</table>
</div>
</div>
</div>
<div class="linksb">
<div class="inbox">
<div><a href="javascript:history.go(-1)">'.$lang_admin['Back'].'</a></div>
</div>
</div>';

    require_once PUN_ROOT.'footer.php';
} elseif (isset($_POST['find_user'])) {
    $form = $_POST['form'];
    $form['username'] = $_POST['username'];

    // trim() all elements in $form
    $form = \array_map('trim', $form);
    $conditions = [];

    $posts_greater = \trim($_POST['posts_greater']);
    $posts_less = \trim($_POST['posts_less']);
    $last_post_after = \trim($_POST['last_post_after']);
    $last_post_before = \trim($_POST['last_post_before']);
    $registered_after = \trim($_POST['registered_after']);
    $registered_before = \trim($_POST['registered_before']);
    $order_by = $_POST['order_by'];
    $direction = $_POST['direction'];
    $user_group = $_POST['user_group'];

    if (\preg_match('/[^0-9]/', $posts_greater.$posts_less)) {
        \message($lang_admin['Not numeric']);
    }

    // Try to convert date/time to timestamps
    if ($last_post_after) {
        $last_post_after = \strtotime($last_post_after);
    }
    if ($last_post_before) {
        $last_post_before = \strtotime($last_post_before);
    }
    if ($registered_after) {
        $registered_after = \strtotime($registered_after);
    }
    if ($registered_before) {
        $registered_before = \strtotime($registered_before);
    }

    if (-1 == $last_post_after || -1 == $last_post_before || -1 == $registered_after || -1 == $registered_before) {
        \message($lang_admin['Bad time']);
    }

    if ($last_post_after) {
        $conditions[] = 'u.last_post>'.$last_post_after;
    }
    if ($last_post_before) {
        $conditions[] = 'u.last_post<'.$last_post_before;
    }
    if ($registered_after) {
        $conditions[] = 'u.registered>'.$registered_after;
    }
    if ($registered_before) {
        $conditions[] = 'u.registered<'.$registered_before;
    }

    $like_command = 'LIKE';
    foreach ($form as $key => $input) {
        if ($input && \in_array($key, ['username', 'email', 'title', 'realname', 'url', 'jabber', 'icq', 'msn', 'aim', 'yahoo', 'location', 'signature', 'admin_note'])) {
            $conditions[] = 'u.'.$db->escape($key).' '.$like_command.' \''.$db->escape(\str_replace('*', '%', $input)).'\'';
        }
    }

    if ($posts_greater) {
        $conditions[] = 'u.num_posts>'.$posts_greater;
    }
    if ($posts_less) {
        $conditions[] = 'u.num_posts<'.$posts_less;
    }
    if ('all' != $user_group) {
        $conditions[] = 'u.group_id='.\intval($user_group);
    }

    if (empty($conditions)) {
        \message($lang_admin['Bad search']);
    }

    $page_title = \pun_htmlspecialchars($pun_config['o_board_title']).' / Admin / Users';

    require_once PUN_ROOT.'header.php'; ?>
<div class="linkst">
    <div class="inbox">
        <div><a href="javascript:history.go(-1)"><?php echo $lang_admin['Back']; ?></a></div>
    </div>
</div>
<div id="users2" class="blocktable">
    <h2><span><?php echo $lang_admin['Users']; ?></span></h2>

    <div class="box">
        <div class="inbox">
            <table cellspacing="0">
                <thead>
                <tr>
                    <th class="tcl" scope="col"><?php echo $lang_admin['Username']; ?></th>
                    <th class="tc2" scope="col"><?php echo $lang_admin['Email']; ?></th>
                    <th class="tc3" scope="col"><?php echo $lang_admin['Title']; ?></th>
                    <th class="tc4" scope="col"><?php echo $lang_admin['Num posts']; ?></th>
                    <th class="tc5" scope="col"><?php echo $lang_admin['Comment']; ?></th>
                    <th class="tcr" scope="col"><?php echo $lang_admin['Acts']; ?></th>
                </tr>
                </thead>
                <tbody>
                    <?php
                    $result = $db->query('SELECT u.id, u.username, u.email, u.title, u.num_posts, u.admin_note, g.g_id, g.g_user_title FROM `'.$db->prefix.'users` AS u LEFT JOIN `'.$db->prefix.'groups` AS g ON g.g_id=u.group_id WHERE u.id>1 AND '.\implode(' AND ', $conditions).' ORDER BY '.$db->escape($order_by).' '.$db->escape($direction)) or \error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
    if ($db->num_rows($result)) {
        while ($user_data = $db->fetch_assoc($result)) {
            $user_title = \get_title($user_data);

            // This script is a special case in that we want to display "Not verified" for non-verified users
            if ((!$user_data['g_id'] || PUN_UNVERIFIED == $user_data['g_id']) && $user_title != $lang_common['Banned']) {
                $user_title = '<span class="warntext">Не проверен</span>';
            }

            $actions = '<a href="admin_users.php?ip_stats='.$user_data['id'].'">'.$lang_admin['IP stats'].'</a> - <a href="search.php?action=show_user&amp;user_id='.$user_data['id'].'">'.$lang_admin['Num posts'].'</a>'; ?>
                        <tr>
                            <td class="tcl"><?php echo '<a href="profile.php?id='.$user_data['id'].'">'.\pun_htmlspecialchars($user_data['username']).'</a>'; ?></td>
                            <td class="tc2"><a
                                href="mailto:<?php echo $user_data['email']; ?>"><?php echo $user_data['email']; ?></a>
                            </td>
                            <td class="tc3"><?php echo $user_title; ?></td>
                            <td class="tc4"><?php echo $user_data['num_posts']; ?></td>
                            <td class="tc5"><?php echo ($user_data['admin_note']) ? $user_data['admin_note'] : ' '; ?></td>
                            <td class="tcr"><?php echo $actions; ?></td>
                        </tr>
                            <?php
        }
    } else {
        echo '<tr><td class="tcl" colspan="6">'.$lang_admin['Not found'].'</td></tr>';
    } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="linksb">
    <div class="inbox">
        <div><a href="javascript:history.go(-1)"><?php echo $lang_admin['Back']; ?></a></div>
    </div>
</div>
    <?php

    require_once PUN_ROOT.'footer.php';
} else {
    $page_title = \pun_htmlspecialchars($pun_config['o_board_title']).' / Admin / Users';
    $focus_element = ['find_user', 'username'];

    require_once PUN_ROOT.'header.php';

    \generate_admin_menu('users'); ?>
<div class="blockform">
    <h2><span><?php echo $lang_admin['Search users']; ?></span></h2>

    <div class="box">
        <form id="find_user" method="post" action="admin_users.php?action=find_user">
            <p class="submittop"><input type="submit" name="find_user" value="<?php echo $lang_admin['Search']; ?>"/></p>

            <div class="inform">
                <fieldset>
                    <legend><?php echo $lang_admin['Enter search users']; ?></legend>
                    <div class="infldset">
                        <p><?php echo $lang_admin['About search users']; ?></p>
                        <table class="aligntop" cellspacing="0">
                            <tr>
                                <th scope="row"><?php echo $lang_admin['Username']; ?></th>
                                <td><input type="text" name="username" size="25" maxlength="25" /></td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo $lang_admin['Email']; ?></th>
                                <td><input type="text" name="form[email]" size="30" maxlength="50" /></td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo $lang_admin['Title']; ?></th>
                                <td><input type="text" name="form[title]" size="30" maxlength="50" /></td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo $lang_admin['Real name']; ?></th>
                                <td><input type="text" name="form[realname]" size="30" maxlength="40" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">URL</th>
                                <td><input type="text" name="form[url]" size="35" maxlength="100" /></td>
                            </tr>
                            <tr>
                                <th scope="row">ICQ</th>
                                <td><input type="text" name="form[icq]" size="12" maxlength="12" /></td>
                            </tr>
                            <tr>
                                <th scope="row">MSN Messenger</th>
                                <td><input type="text" name="form[msn]" size="30" maxlength="50" /></td>
                            </tr>
                            <tr>
                                <th scope="row">AOL IM</th>
                                <td><input type="text" name="form[aim]" size="20" maxlength="20" /></td>
                            </tr>
                            <tr>
                                <th scope="row">Yahoo! Messenger</th>
                                <td><input type="text" name="form[yahoo]" size="20" maxlength="20" /></td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo $lang_admin['Location']; ?></th>
                                <td><input type="text" name="form[location]" size="30" maxlength="30" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo $lang_admin['Signature']; ?></th>
                                <td><input type="text" name="form[signature]" size="35" maxlength="512" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo $lang_admin['Admin note']; ?></th>
                                <td><input type="text" name="form[admin_note]" size="30" maxlength="30" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo $lang_admin['Posts greater']; ?></th>
                                <td><input type="text" name="posts_greater" size="5" maxlength="8" /></td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo $lang_admin['Posts less']; ?></th>
                                <td><input type="text" name="posts_less" size="5" maxlength="8" /></td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo $lang_admin['Last post after']; ?></th>
                                <td><input type="text" name="last_post_after" size="24" maxlength="19" />
                                    <span>(<?php echo $lang_admin['Datetime']; ?>)</span></td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo $lang_admin['Last post before']; ?></th>
                                <td><input type="text" name="last_post_before" size="24" maxlength="19" />
                                    <span>(<?php echo $lang_admin['Datetime']; ?>)</span></td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo $lang_admin['Registered after']; ?></th>
                                <td><input type="text" name="registered_after" size="24" maxlength="19" />
                                    <span>(<?php echo $lang_admin['Datetime']; ?>)</span></td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo $lang_admin['Registered before']; ?></th>
                                <td><input type="text" name="registered_before" size="24" maxlength="19" />
                                    <span>(<?php echo $lang_admin['Datetime']; ?>)</span></td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo $lang_admin['Order']; ?></th>
                                <td>
                                    <select name="order_by">
                                        <option value="username"
                                                selected="selected"><?php echo $lang_admin['Username']; ?></option>
                                        <option value="email"><?php echo $lang_admin['Email']; ?></option>
                                        <option value="num_posts"><?php echo $lang_admin['Num posts']; ?></option>
                                        <option value="last_post"><?php echo $lang_admin['Last post']; ?></option>
                                        <option value="registered"><?php echo $lang_admin['Registered']; ?></option>
                                    </select>&#160; &#160;<select name="direction">
                                    <option value="ASC" selected="selected"><?php echo $lang_admin['ASC']; ?></option>
                                    <option value="DESC"><?php echo $lang_admin['DESC']; ?></option>
                                </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo $lang_admin['Group']; ?></th>
                                <td>
                                    <select name="user_group">
                                        <option value="all"
                                                selected="selected"><?php echo $lang_admin['All groups']; ?></option>
                                        <?php
                                        $result = $db->query('SELECT g_id, g_title FROM `'.$db->prefix.'groups` WHERE g_id!='.PUN_GUEST.' ORDER BY g_title') or \error('Unable to fetch user group list', __FILE__, __LINE__, $db->error());

    while ($cur_group = $db->fetch_assoc($result)) {
        echo '<option value="'.$cur_group['g_id'].'">'.\pun_htmlspecialchars($cur_group['g_title']).'</option>';
    } ?>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    </div>
                </fieldset>
            </div>
            <p class="submitend"><input type="submit" name="find_user" value="<?php echo $lang_admin['Search']; ?>"/></p>
        </form>
    </div>
    <h2 class="block2"><span><?php echo $lang_admin['Search IP']; ?></span></h2>

    <div class="box">
        <form method="get" action="admin_users.php">
            <div class="inform">
                <fieldset>
                    <legend><?php echo $lang_admin['Enter search IP']; ?></legend>
                    <div class="infldset">
                        <table class="aligntop" cellspacing="0">
                            <tr>
                                <th scope="row"><?php echo $lang_admin['IP']; ?>
                                <div><input type="submit" value="<?php echo $lang_admin['Search']; ?>" />
                                </div>
                                </th>
                                <td><input type="text" name="show_users" size="18" maxlength="15" />
                                    <span><?php echo $lang_admin['About search IP']; ?></span></td>
                            </tr>
                        </table>
                    </div>
                </fieldset>
            </div>
        </form>
    </div>
</div>
<div class="clearer"></div>
</div>
<?php
    require_once PUN_ROOT.'footer.php';
}
