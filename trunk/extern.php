<?php
// The maximum number of topics that will be displayed
$show_max_topics = 60;

// The length at which topic subjects will be truncated (for HTML output)
$max_subject_length = 30;


// DO NOT EDIT ANYTHING BELOW THIS LINE! (unless you know what you are doing)


define('PUN_ROOT', './');
require_once PUN_ROOT  .'config.php';

// If PUN isn't defined, config.php is missing or corrupt
if (!defined('PUN')) {
    exit('The file "config.php" does not exist or is corrupt. Please run install.php to install PunBB first.');
}

// Make sure PHP reports all errors except E_NOTICE
//error_reporting(E_ALL ^ E_NOTICE);
error_reporting(0);


// Load the functions script
require PUN_ROOT . 'include/functions.php';

// Load DB abstraction layer and try to connect
require PUN_ROOT . 'include/dblayer/common_db.php';

// Load cached config
@include PUN_ROOT . 'cache/cache_config.php';
if (!defined('PUN_CONFIG_LOADED')) {
    include PUN_ROOT . 'include/cache.php';
    generate_config_cache();
    include PUN_ROOT . 'cache/cache_config.php';
}

// Make sure we (guests) have permission to read the forums
$result = $db->query('SELECT g_read_board FROM '.$db->prefix.'groups WHERE g_id=3') or error('Unable to fetch group info', __FILE__, __LINE__, $db->error());
if (!$db->result($result)) {
    exit('No permission');
}

// Attempt to load the common language file
@include PUN_ROOT . 'lang/' . $pun_config['o_default_lang'] . '/common.php';
if (!$lang_common) {
    exit('There is no valid language pack "' . $pun_config['o_default_lang'] . '" installed. Please reinstall a language of that name.');
}

// Check if we are to display a maintenance message
if ($pun_config['o_maintenance'] && !defined('PUN_TURN_OFF_MAINT')) {
    maintenance_message();
}

if (!$_GET['action']) {
    exit('No parameters supplied. See extern.php for instructions.');
}

//
// Converts the CDATA end sequence ]]> into ]]&gt;
//
function escape_cdata($str) {
    return str_replace(']]>', ']]&gt;', $str);
}


//
// Show recent discussions
//
if ($_GET['action'] == 'active' || $_GET['action'] == 'new') {
    $order_by = ($_GET['action'] == 'active') ? 't.last_post' : 't.posted';
    $forum_sql = null;

    // Was any specific forum ID's supplied?
    if ($_GET['fid']) {
        $fids = explode(',', trim($_GET['fid']));
        $fids = array_map('intval', $fids);

        if ($fids) {
            $forum_sql = ' AND f.id IN(' . implode(',', $fids) . ')';
        }
    }

    // Any forum ID's to exclude?
    if ($_GET['nfid']) {
        $nfids = explode(',', trim($_GET['nfid']));
        $nfids = array_map('intval', $nfids);

        if ($nfids) {
            $forum_sql = ' AND f.id NOT IN(' . implode(',', $nfids) . ')';
        }
    }

    // Should we output this as RSS?
    if (strtoupper($_GET['type']) == 'RSS') {
        $rss_description = ($_GET['action'] == 'active') ? $lang_common['RSS Desc Active'] : $lang_common['RSS Desc New'];
        $url_action = ($_GET['action'] == 'active') ? '&amp;action=new' : '';

        // Send XML/no cache headers
        header('Content-Type: text/xml');
        header('Expires: ' . date('r') . ' GMT');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');

        // It's time for some syndication!
        echo '<?xml version="1.0" encoding="' . $lang_common['lang_encoding'] . '"?>' . "\n".
        '<!DOCTYPE rss PUBLIC "-//Netscape Communications//DTD RSS 0.91//EN" "http://my.netscape.com/publish/formats/rss-0.91.dtd">' . "\n" .
        '<rss version="0.91"><channel><title>' . pun_htmlspecialchars($pun_config['o_board_title']) . '</title><link>' . $pun_config['o_base_url'] . '/</link><description>' . pun_htmlspecialchars($rss_description . ' ' . $pun_config['o_board_title']) . '</description><language>en-us</language>' . "\r\n";

        // Fetch 15 topics
        $result = $db->query('SELECT t.id, t.poster, t.subject, t.posted, t.last_post, f.id AS fid, f.forum_name FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id=3) WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND t.moved_to IS NULL'.$forum_sql.' ORDER BY '.$order_by.' DESC LIMIT 15') or error('Unable to fetch topic list', __FILE__, __LINE__, $db->error());

        while ($cur_topic = $db->fetch_assoc($result)) {
            if ($pun_config['o_censoring'] == '1') {
                $cur_topic['subject'] = censor_words($cur_topic['subject']);
            }

            echo '<item><title>' . pun_htmlspecialchars($cur_topic['subject']) . '</title><link>' . $pun_config['o_base_url'] . '/viewtopic.php?id=' . $cur_topic['id'] . $url_action . '</link><description><![CDATA[' . escape_cdata($lang_common['Forum'] . ': <a href="' . $pun_config['o_base_url'] . '/viewforum.php?id=' . $cur_topic['fid'] . '">' . $cur_topic['forum_name'] . '</a><br />' . "\r\n" . $lang_common['Author'] . ': ' . $cur_topic['poster'] . '<br />' . "\r\n" . $lang_common['Posted'] . ': ' . date('r', $cur_topic['posted']) . '<br />' . "\r\n" . $lang_common['Last post'] . ': ' . date('r', $cur_topic['last_post'])) . ']]></description></item>' . "\r\n";
        }

        echo '</channel></rss>';
    } else {
        // Output regular HTML
        $show = intval($_GET['show']);
        if ($show < 1 || $show > 50) {
            $show = 15;
        }

        // Fetch $show topics
        $result = $db->query('SELECT t.id, t.subject FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id=3) WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND t.moved_to IS NULL'.$forum_sql.' ORDER BY '.$order_by.' DESC LIMIT '.$show) or error('Unable to fetch topic list', __FILE__, __LINE__, $db->error());

        while ($cur_topic = $db->fetch_assoc($result)) {
            if ($pun_config['o_censoring'] == 1) {
                $cur_topic['subject'] = censor_words($cur_topic['subject']);
            }
            if (mb_strlen($cur_topic['subject']) > $max_subject_length) {
                $subject_truncated = pun_htmlspecialchars(trim(substr($cur_topic['subject'], 0, ($max_subject_length-5)))) . ' &hellip;';
            } else {
                $subject_truncated = pun_htmlspecialchars($cur_topic['subject']);
            }

            echo '<li><a href="'.$pun_config['o_base_url'].'/viewtopic.php?id='.$cur_topic['id'].'&amp;action=new" title="'.pun_htmlspecialchars($cur_topic['subject']).'">'.$subject_truncated.'</a></li>';
        }
    }

    return;
} else if($_GET['action'] == 'online' || $_GET['action'] == 'online_full') {
    //
    // Show users online
    //

    // Load the index.php language file
    require PUN_ROOT.'lang/'.$pun_config['o_default_lang'].'/index.php';

    // Fetch users online info and generate strings for output
    $num_guests = $num_users = 0;
    $users = array();
    $result = $db->query('SELECT user_id, ident FROM '.$db->prefix.'online WHERE idle=0 ORDER BY ident', true) or error('Unable to fetch online list', __FILE__, __LINE__, $db->error());

    while ($pun_user_online = $db->fetch_assoc($result)) {
        if ($pun_user_online['user_id'] > 1) {
            $users[] = '<a href="'.$pun_config['o_base_url'].'/profile.php?id='.$pun_user_online['user_id'].'">'.pun_htmlspecialchars($pun_user_online['ident']).'</a>';
            ++$num_users;
        } else {
            ++$num_guests;
        }
    }

    echo $lang_index['Guests online'].': '.$num_guests.'<br />';

    if ($_GET['action'] == 'online_full') {
        echo $lang_index['Users online'].': '.implode(', ', $users).'<br />';
    } else {
        echo $lang_index['Users online'].': '.$num_users.'<br />';
    }

    return;
} else if($_GET['action'] == 'stats') {
    //
    // Show board statistics
    //

    // Load the index.php language file
    include PUN_ROOT.'lang/'.$pun_config['o_default_lang'].'/index.php';

    // Collect some statistics from the database
    $result = $db->query('SELECT COUNT(id)-1 FROM '.$db->prefix.'users') or error('Unable to fetch total user count', __FILE__, __LINE__, $db->error());
    $stats['total_users'] = $db->result($result);

    $result = $db->query('SELECT id, username FROM '.$db->prefix.'users ORDER BY registered DESC LIMIT 1') or error('Unable to fetch newest registered user', __FILE__, __LINE__, $db->error());
    $stats['last_user'] = $db->fetch_assoc($result);

    $result = $db->query('SELECT SUM(num_topics), SUM(num_posts) FROM '.$db->prefix.'forums') or error('Unable to fetch topic/post count', __FILE__, __LINE__, $db->error());
    list($stats['total_topics'], $stats['total_posts']) = $db->fetch_row($result);

    echo $lang_index['No of users'].': '.$stats['total_users'].'<br />
    '.$lang_index['Newest user'].': <a href="'.$pun_config['o_base_url'].'/profile.php?id='.$stats['last_user']['id'].'">'.pun_htmlspecialchars($stats['last_user']['username']).'</a><br />
    '.$lang_index['No of topics'].': '.$stats['total_topics'].'<br />
    '.$lang_index['No of posts'].': '.$stats['total_posts'];
    
    return;
} else {
    exit('Bad request');
}

?>