<?php

// Make sure no one attempts to run this script "directly"
if (!\defined('PUN')) {
    exit;
}

$footer_style = $footer_style ?? '';
$tpl_temp = \trim(\ob_get_contents());
$tpl_main = \str_replace('<pun_main>', $tpl_temp, $tpl_main);
\ob_end_clean();
// END SUBST - <pun_main>

// JS_HELPER MOD BEGIN
if (JsHelper::getInstance()->count() > 0 || JsHelper::getInstance()->countInternal() > 0) {
    JsHelper::getInstance()->addFirst('//code.jquery.com/jquery-3.6.3.min.js');
    $tpl_main = \str_replace('<pun_js_helper>', JsHelper::getInstance()->headerOut(), $tpl_main);
} else {
    $tpl_main = \str_replace('<pun_js_helper>', '', $tpl_main);
}
// JS_HELPER MOD END

// START SUBST - <pun_footer>
\ob_start();

echo '<div id="brdfooter" class="block"><h2><span>'.$lang_common['Board footer'].'</span></h2><div class="box"><div class="inbox">';

//PMS MOD BEGIN
require PUN_ROOT.'include/pms/footer_links.php';
//PMS MOD END

if ('index' == $footer_style || 'search' == $footer_style) {
    if (!$pun_user['is_guest']) {
        echo '<dl id="searchlinks" class="conl"> <dt><strong>'.$lang_common['Search links'].'</strong></dt> <dd><a href="search.php?action=show_24h">'.$lang_common['Show recent posts'].'</a></dd> <dd><a href="search.php?action=show_unanswered">'.$lang_common['Show unanswered posts'].'</a></dd>';

        if (1 == $pun_config['o_subscriptions']) {
            echo '<dd><a href="search.php?action=show_subscriptions">'.$lang_common['Show subscriptions'].'</a></dd>';
        }

        echo '<dd><a href="search.php?action=show_user&amp;user_id='.$pun_user['id'].'">'.$lang_common['Show your posts'].'</a></dd> </dl>';
    } else {
        if (1 == $pun_user['g_search']) {
            echo '<dl id="searchlinks" class="conl"> <dt><strong>'.$lang_common['Search links'].'</strong></dt><dd><a href="search.php?action=show_24h">'.$lang_common['Show recent posts'].'</a></dd> <dd><a href="search.php?action=show_unanswered">'.$lang_common['Show unanswered posts'].'</a></dd> </dl>';
        }
    }
} elseif ('viewforum' == $footer_style || 'viewtopic' == $footer_style) {
    echo '<div class="conl">';

    // Display the "Jump to" drop list
    if (1 == $pun_config['o_quickjump']) {
        // Load cached quickjump
        echo include PUN_ROOT.'include/quickjump.php';
    }

    if ('viewforum' == $footer_style && $is_admmod) {
        echo '<p id="modcontrols"><a href="moderate.php?fid='.$forum_id.'&amp;p='.$p.'">'.$lang_common['Moderate forum'].'</a></p>';
    } elseif ('viewtopic' == $footer_style && $is_admmod) {
        echo '<dl id="modcontrols"><dt><strong>'.$lang_topic['Mod controls'].'</strong></dt><dd><a href="moderate.php?fid='.$forum_id.'&amp;tid='.$id.'&amp;p='.$p.'">'.$lang_common['Delete posts'].'</a></dd><dd><a href="moderate.php?fid='.$forum_id.'&amp;move_topics='.$id.'">'.$lang_common['Move topic'].'</a></dd>';

        if (1 == $cur_topic['closed']) {
            echo '<dd><a href="moderate.php?fid='.$forum_id.'&amp;open='.$id.'">'.$lang_common['Open topic'].'</a></dd>';
        } else {
            echo '<dd><a href="moderate.php?fid='.$forum_id.'&amp;close='.$id.'">'.$lang_common['Close topic'].'</a></dd>';
        }

        if (1 == $cur_topic['sticky']) {
            echo '<dd><a href="moderate.php?fid='.$forum_id.'&amp;unstick='.$id.'">'.$lang_common['Unstick topic'].'</a></dd></dl>';
        } else {
            echo '<dd><a href="moderate.php?fid='.$forum_id.'&amp;stick='.$id.'">'.$lang_common['Stick topic'].'</a></dd></dl>';
        }
    }

    echo '</div>';
}

// $db->get_num_queries() - show sql queries
echo '<p class="conr"><strong><a href="/">'.\parse_url($pun_config['o_base_url'], \PHP_URL_HOST).'</a></strong></p><p class="conr">PunBB Mod v'.\pun_htmlspecialchars($pun_config['o_show_version']).'<br />'.\sprintf('%.3f', \microtime(true) - $pun_start).' s</p><div class="clearer"></div></div></div></div>';

// Display executed queries (if enabled)
if (\defined('PUN_SHOW_QUERIES')) {
    \display_saved_queries();
}

$tpl_temp = \trim(\ob_get_contents());
$tpl_main = \str_replace('<pun_footer>', $tpl_temp, $tpl_main);
\ob_end_clean();
// END SUBST - <pun_footer>

// Close the db connection (and free up any result data)
$db->close();

// Spit out the page
exit($tpl_main);
