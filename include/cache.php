<?php
// Make sure no one attempts to run this script "directly"
if (!defined('PUN')) {
    exit;
}

//
// Generate the config cache PHP script
//
function generate_config_cache()
{
    global $db;

    $output = array();

    // Get the forum config from the DB
    $result = $db->query('SELECT * FROM ' . $db->prefix . 'config', true) or error('Unable to fetch forum config', __FILE__, __LINE__, $db->error());
    while ($item = $db->fetch_row($result)) {
        $output[$item[0]] = $item[1];
    }

    // Права на загрузчик
    $result = $db->query('SELECT `g_id`, `p_view` FROM `' . $db->prefix . 'uploads_conf`') or error('Unable to select opions', __FILE__, __LINE__, $db->error());
    while ($q = $db->fetch_assoc($result)) {
        $output['uploads_conf'][$q['g_id']] = $q['p_view'];
    }

    // Output config as PHP code
    $fh = @fopen(PUN_ROOT . 'cache/cache_config.php', 'wb');
    if (!$fh) {
        error('Unable to write configuration cache file to cache directory. Please make sure PHP has write access to the directory "cache"', __FILE__, __LINE__);
    }

    fputs($fh, '<?php' . "\n\n" . 'define(\'PUN_CONFIG_LOADED\', 1);' . "\n\n" . '$pun_config = ' . var_export($output, true) . ';' . "\n\n" . '?>');
    fclose($fh);
}


//
// Generate the bans cache PHP script
//
function generate_bans_cache()
{
    global $db;

    // Get the ban list from the DB
    $result = $db->query('SELECT * FROM ' . $db->prefix . 'bans', true) or error('Unable to fetch ban list', __FILE__, __LINE__, $db->error());

    $output = array();
    while ($cur_ban = $db->fetch_assoc($result)) {
        $output[] = $cur_ban;
    }

    // Output ban list as PHP code
    $fh = @fopen(PUN_ROOT . 'cache/cache_bans.php', 'wb');
    if (!$fh) {
        error('Unable to write bans cache file to cache directory. Please make sure PHP has write access to the directory "cache"', __FILE__, __LINE__);
    }

    fputs($fh, '<?php' . "\n\n" . 'define(\'PUN_BANS_LOADED\', 1);' . "\n\n" . '$pun_bans = ' . var_export($output, true) . ';' . "\n\n" . '?>');
    fclose($fh);
}


//
// Generate the ranks cache PHP script
//
function generate_ranks_cache()
{
    global $db;

    // Get the rank list from the DB
    $result = $db->query('SELECT * FROM ' . $db->prefix . 'ranks ORDER BY min_posts', true) or error('Unable to fetch rank list', __FILE__, __LINE__, $db->error());

    $output = array();
    while ($cur_rank = $db->fetch_assoc($result)) {
        $output[] = $cur_rank;
    }

    // Output ranks list as PHP code
    $fh = @fopen(PUN_ROOT . 'cache/cache_ranks.php', 'wb');
    if (!$fh) {
        error('Unable to write ranks cache file to cache directory. Please make sure PHP has write access to the directory "cache"', __FILE__, __LINE__);
    }

    fputs($fh, '<?php' . "\n\n" . 'define(\'PUN_RANKS_LOADED\', 1);' . "\n\n" . '$pun_ranks = ' . var_export($output, true) . ';' . "\n\n" . '?>');
    fclose($fh);
}


//
// Generate quickjump cache PHP scripts
//
function generate_quickjump_cache($group_id = false)
{
    global $db, $lang_common, $pun_config;

    $groups = array();
    // If a group_id was supplied, we generate the quickjump cache for that group only
    if ($group_id !== false) {
        $groups[0] = $group_id;
    } else {
        // A group_id was now supplied, so we generate the quickjump cache for all groups
        $result = $db->query('SELECT g_id FROM ' . $db->prefix . 'groups') or error('Unable to fetch user group list', __FILE__, __LINE__, $db->error());
        $num_groups = $db->num_rows($result);

        for ($i = 0; $i < $num_groups; ++$i) {
            $groups[] = $db->result($result, $i);
        }
    }

    // Loop through the groups in $groups and output the cache for each of them
    foreach ($groups as $group_id) {
        // Output quickjump as PHP code
        $fh = @fopen(PUN_ROOT . 'cache/cache_quickjump_' . $group_id . '.php', 'wb');
        if (!$fh) {
            error('Unable to write quickjump cache file to cache directory. Please make sure PHP has write access to the directory "cache"', __FILE__, __LINE__);
        }

        $output = '<?php return \'<form id="qjump" method="get" action="viewforum.php">
<div><label>\' . $lang_common[\'Jump to\'] . \'<br />
<select name="id" onchange="window.location.assign(\\\'\' . $pun_config[\'o_base_url\'] . \'/viewforum.php?id=\\\'+this.options[this.selectedIndex].value);">';


        $result = $db->query('SELECT c.id AS cid, c.cat_name, f.id AS fid, f.forum_name, f.redirect_url FROM ' . $db->prefix . 'categories AS c INNER JOIN ' . $db->prefix . 'forums AS f ON c.id=f.cat_id LEFT JOIN ' . $db->prefix . 'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id=' . $group_id . ') WHERE fp.read_forum IS NULL OR fp.read_forum=1 ORDER BY c.disp_position, c.id, f.disp_position', true) or error('Unable to fetch category/forum list', __FILE__, __LINE__, $db->error());

        $cur_category = 0;
        while ($cur_forum = $db->fetch_assoc($result)) {
            // A new category since last iteration?
            if ($cur_forum['cid'] != $cur_category) {
                if ($cur_category) {
                    $output .= '</optgroup>';
                }

                $output .= '<optgroup label="' . pun_htmlspecialchars($cur_forum['cat_name']) . '">';
                $cur_category = $cur_forum['cid'];
            }

            $redirect_tag = ($cur_forum['redirect_url']) ? ' &gt;&gt;&gt;' : '';
            $output .= '<option value="' . $cur_forum['fid'] . '"';

            if ($group_id == $cur_forum['fid']) {
                $output .= ' selected="selected"';
            }
            $output .= '>' . pun_htmlspecialchars($cur_forum['forum_name']) . $redirect_tag . '</option>';
        }

        $output .= '</optgroup></select><input type="submit" value="\' . $lang_common[\'Go\'] . \'" accesskey="g" /></label></div></form>\';';

        fputs($fh, $output);
        fclose($fh);
    }
}

//
// Generate WAP quickjump cache PHP scripts
//
function generate_wap_quickjump_cache($group_id = false)
{
    global $db, $lang_common, $pun_config;

    // If a group_id was supplied, we generate the quickjump cache for that group only
    if ($group_id !== false) {
        $groups[0] = $group_id;
    } else {
        // A group_id was now supplied, so we generate the quickjump cache for all groups
        $result = $db->query('SELECT g_id FROM ' . $db->prefix . 'groups') or error('Unable to fetch user group list', __FILE__, __LINE__, $db->error());
        $num_groups = $db->num_rows($result);

        for ($i = 0; $i < $num_groups; ++$i) {
            $groups[] = $db->result($result, $i);
        }
    }

    // Loop through the groups in $groups and output the cache for each of them
    while (list(, $group_id) = @each($groups)) {
        // Output wap quickjump as PHP code
        $fh = fopen(PUN_ROOT . 'cache/cache_wap_quickjump_' . $group_id . '.php', 'wb');
        if (!$fh) {
            error('Unable to write quickjump cache file to cache directory. Please make sure PHP has write access to the directory "cache"', __FILE__, __LINE__);
        }

        $output = '<?php return \'<form id="qjump" method="get" action="viewforum.php">
<div class="inbox"><label>\' . $lang_common[\'Jump to\'] . \'<br />
<select name="id" onchange="window.location.assign(\\\'\' . $pun_config[\'o_base_url\'] . \'/wap/viewforum.php?id=\\\'+this.options[this.selectedIndex].value);">';

        $result = $db->query('SELECT c.id AS cid, c.cat_name, f.id AS fid, f.forum_name, f.redirect_url FROM ' . $db->prefix . 'categories AS c INNER JOIN ' . $db->prefix . 'forums AS f ON c.id=f.cat_id LEFT JOIN ' . $db->prefix . 'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id=' . $group_id . ') WHERE fp.read_forum IS NULL OR fp.read_forum=1 ORDER BY c.disp_position, c.id, f.disp_position', true) or error('Unable to fetch category/forum list', __FILE__, __LINE__, $db->error());

        $cur_category = 0;
        while ($cur_forum = $db->fetch_assoc($result)) {
            // A new category since last iteration?
            if ($cur_forum['cid'] != $cur_category) {
                if ($cur_category) {
                    $output .= '</optgroup>';
                }

                $output .= '<optgroup label="' . pun_htmlspecialchars($cur_forum['cat_name']) . '">';
                $cur_category = $cur_forum['cid'];
            }

            $redirect_tag = ($cur_forum['redirect_url']) ? ' &gt;&gt;&gt;' : '';
            $output .= '<option value="' . $cur_forum['fid'] . '"';

            if ($group_id == $cur_forum['fid']) {
                $output .= ' selected="selected"';
            }
            $output .= '>' . pun_htmlspecialchars($cur_forum['forum_name']) . $redirect_tag . '</option>';
        }

        $output .= '</optgroup></select><input type="submit" value="\' . $lang_common[\'Go\'] . \'" accesskey="g" /></label></div></form>\';';

        fwrite($fh, $output);
        fclose($fh);
    }
}
