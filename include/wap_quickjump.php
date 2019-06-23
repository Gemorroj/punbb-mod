<?php

if (!defined('PUN') || !defined('PUN_ROOT')) {
    exit();
}

$quickjump = @include PUN_ROOT.'cache/cache_wap_quickjump_'.$forum_id.'.php';

if (!$quickjump) {
    include_once PUN_ROOT.'include/cache.php';
    generate_wap_quickjump_cache($forum_id);
    $quickjump = include PUN_ROOT.'cache/cache_wap_quickjump_'.$forum_id.'.php';
}

return $quickjump;
