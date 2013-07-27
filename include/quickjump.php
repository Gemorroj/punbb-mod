<?php
if (!defined('PUN') or ! defined('PUN_ROOT')) exit();

$quickjump = @include PUN_ROOT . 'cache/cache_quickjump_' . $forum_id . '.php';

if (!$quickjump) {
    include_once PUN_ROOT . 'include/cache.php';
    generate_quickjump_cache($forum_id);
    $quickjump = include PUN_ROOT . 'cache/cache_quickjump_' . $forum_id . '.php';
}

return $quickjump;
