<?php

$group_id = $id;

if ($group_id !== false) {
    
    $groups[0] = $group_id;
}
else {
    // A group_id was now supplied, so we generate the quickjump cache for all groups
    $result = $db->query('SELECT g_id FROM ' . $db->prefix . 'groups') or error('Unable to fetch user group list', __FILE__, __LINE__, $db->error());
    $num_groups = $db->num_rows($result);

    for ($i = 0; $i < $num_groups; ++$i) {
        $groups[] = $db->result($result, $i);
    }
}

// Loop through the groups in $groups and output the cache for each of them
while (list(, $group_id) = each($groups)) {
    // Output wap quickjump as PHP code
    $fh = fopen(PUN_ROOT . 'cache/cache_wap_quickjump_' . $group_id . '.php', 'wb');
    if (! $fh) {
        
        error('Unable to write quickjump cache file to cache directory. Please make sure PHP has write access to the directory "cache"', __FILE__, __LINE__);
    }

    $output = '<?php if (! defined(\'PUN\')) exit(); define(\'PUN_QJ_LOADED\', 1); ?>'
            . '<form id="qjump" method="get" action="viewforum.php">'
            . '<div class="inbox">'
            . '<label>' . $lang_common['Jump to'] . '<br />'
            . '<select name="id" onchange="window.location.href=\\\'' . $pun_config['o_base_url'] . '/wap/viewforum.php?id=\\\'+this.options[this.selectedIndex].value;">';

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

    $output .= '</optgroup>'
             . '</select>'
             . '<input type="submit" value="' . $lang_common['Go'] . '" accesskey="g" />'
             . '</label>'
             . '</div>'
             . '</form>';

    fputs($fh, $output);
    fclose($fh);
}