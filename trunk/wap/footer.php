<?php
// Make sure no one attempts to run this script "directly"
if (!defined('PUN')) {
    exit;
}

if ($basename == 'profile.php' || $basename == 'search.php' || $basename == 'userlist.php' || $basename == 'uploads.php' || $basename == 'message_list.php' || $basename == 'message_send.php' || $basename == 'help.php' || $basename == 'misc.php' || $basename == 'filemap.php' || $basename == 'karma.php') {
    echo '<div class="navlinks">'.generate_wap_navlinks().'</div>';
}

$tpl_temp = trim(ob_get_contents());

$tpl_main = str_replace('<pun_main>', $tpl_temp, $tpl_main);

ob_end_clean();
// END SUBST - <pun_main>


// START SUBST - <pun_footer>
ob_start();


//PMS MOD BEGIN
//require(PUN_ROOT.'include/pms/footer_links.php');
//PMS MOD END


if ($footer_style == 'index' || $footer_style == 'search') {
/*
    if (!$pun_user['is_guest']) {
        echo '<div id="searchlinks" class="con">'.$lang_common['Search links'].'<br/></div>
        <div class="con"><a href="search.php?action=show_24h">'.$lang_common['Show recent posts'].'</a><br/></div>
        <div class="con"><a href="search.php?action=show_unanswered">'.$lang_common['Show unanswered posts'].'</a><br/></div>';

        if ($pun_config['o_subscriptions'] == '1') {
            echo '<div class="con"><a href="search.php?action=show_subscriptions">'.$lang_common['Show subscriptions'].'</a><br/></div>';
        }

        echo '<div class="con"><a href="search.php?action=show_user&amp;user_id='.$pun_user['id'].'">'.$lang_common['Show your posts'].'</a><br/></div>';
    } else {
        if ($pun_user['g_search'] == '1') {
            echo '<div id="searchlinks" class="con"> <strong>'.$lang_common['Search links'].'</strong><br/></div>
            <div class="con"><a href="search.php?action=show_24h">'.$lang_common['Show recent posts'].'</a><br/></div>
            <div class="con"><a href="search.php?action=show_unanswered">'.$lang_common['Show unanswered posts'].'</a><br/></div>';
        }
    }
*/
} else if ($footer_style == 'viewforum' || $footer_style == 'viewtopic') {
    // Display the "Jump to" drop list
    if (false/*$pun_config['o_quickjump'] == 1*/) {
        // Load cached quickjump
        @include PUN_ROOT . 'cache/cache_quickjump_' . $forum_id . '.php';
        if (!defined('PUN_QJ_LOADED')) {
            include PUN_ROOT . 'include/cache.php';
            generate_quickjump_cache($forum_id);
            include PUN_ROOT.'cache/cache_quickjump_' . $forum_id . '.php';
        }
    }

    if ($footer_style == 'viewforum' && $is_admmod) {
        echo '<div class="con"><a href="moderate.php?fid='.$forum_id.'&amp;p='.$p.'">'.$lang_common['Moderate forum'].'</a><br/></div>';
    } else if ($footer_style == 'viewtopic' && $is_admmod) {
        echo '<div class="con"><a href="moderate.php?fid='.$forum_id.'&amp;tid='.$id.'&amp;p='.$p.'">'.$lang_common['Delete posts'].'</a><br/></div><div class="con"><a href="moderate.php?fid='.$forum_id.'&amp;move_topics='.$id.'">'.$lang_common['Move topic'].'</a><br/></div>';

        if ($cur_topic['closed'] == 1) {
            echo '<div class="con"><a href="moderate.php?fid='.$forum_id.'&amp;open='.$id.'">'.$lang_common['Open topic'].'</a><br/></div>';
        } else {
            echo '<div class="con"><a href="moderate.php?fid='.$forum_id.'&amp;close='.$id.'">'.$lang_common['Close topic'].'</a><br/></div>';
        }

        if ($cur_topic['sticky'] == 1) {
            echo '<div class="con"><a href="moderate.php?fid='.$forum_id.'&amp;unstick='.$id.'">'.$lang_common['Unstick topic'].'</a><br/></div>';
        } else {
            echo '<div class="con"><a href="moderate.php?fid='.$forum_id.'&amp;stick='.$id.'">'.$lang_common['Stick topic'].'</a><br/></div>';
        }
    }

}

// $db->get_num_queries() - show sql queries
echo '
<div class="foot"><a href="/">' . $_SERVER['HTTP_HOST'] . '</a><br/><a class="red" href="' . PUN_ROOT . '">WEB</a></div>
<div class="copy"><a href="http://wapinet.ru/forum/wap/">PunBB Mod Gemorroj</a><br/>
<span class="red">' . sprintf('%.3f', microtime(true) - $pun_start) . ' s</span></div>';

// End the transaction
$db->end_transaction();

// Display executed queries (if enabled)
if (defined('PUN_SHOW_QUERIES')) {
    display_saved_queries();
}

$tpl_temp = trim(ob_get_contents());
$tpl_main = str_replace('<pun_footer>', $tpl_temp, $tpl_main);
ob_end_clean();
// END SUBST - <pun_footer>


// Close the db connection (and free up any result data)
$db->close();

// Spit out the page
exit($tpl_main);

?>
