<?php if (! defined('PUN') or ! defined('PUN_ROOT')) exit();

if ('viewtopic.php' == $basename or 'viewforum.php' == $basename and $pun_config['o_quickjump'] == 1) {
    // Load cached quickjump // included quickjump
    ob_start();
    @include PUN_ROOT . 'cache/cache_wap_quickjump_' . $id . '.php';
    ob_end_clean();
    
    if (! defined('PUN_QJ_LOADED')) {
        include PUN_ROOT . 'include/quickjump.php';
    }
}