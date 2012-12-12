<?php
/***********************************************************************

Get list of attachments.
This file is part of Elektra File Upload mod for PunBB.

Copyright (C) 2002-2005 Rickard Andersson (rickard@punbb.org)
Copyright (C) 2007 artoodetoo (master@1wd.ru)

Included from: edit.php, viewtopic.php

Incoming variables;

Outgoing variables:
$attachments: array - cache of attachments records
 ************************************************************************/

// there are different sources to include fetch.php
switch (basename($_SERVER['PHP_SELF'])) {
    case 'viewtopic.php':
        $att_sql = 'SELECT * FROM ' . $db->prefix . 'attachments WHERE topic_id=' . intval($id) . ' AND post_id in (' . implode(',', $pids) . ')';
        break;

    case 'edit.php':
        $att_sql = 'SELECT * FROM ' . $db->prefix . 'attachments WHERE post_id=' . intval($id);
        break;

    default:
        break;
}

// prepare attachments cache data
$attachments = array();
$result = $db->query($att_sql, true); // or error('Unable to fetch attachments', __FILE__, __LINE__, $db->error());
while ($attachment = $db->fetch_assoc($result)) {
    if ($attachment['size'] >= 1048576) {
        $attachment['size']['mb'] = round($attachment['size'] / 1048576, 0);
    } else {
        $attachment['size']['kb'] = round($attachment['size'] / 1024, 0);
    }
    $attachments[$attachment['post_id']][] = $attachment;
}
$db->free_result($result);

?>