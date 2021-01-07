<?php
/*

Get list of attachments.
This file is part of Elektra File Upload mod for PunBB.

Copyright (C) 2002-2005 Rickard Andersson (rickard@punbb.org)
Copyright (C) 2007 artoodetoo (master@1wd.ru)

Included from: edit.php, viewtopic.php

Incoming variables;

Outgoing variables:
$attachments: array - cache of attachments records
 */

// there are different sources to include fetch.php
switch (\basename($_SERVER['PHP_SELF'])) {
    case 'viewtopic.php':
        $att_sql = 'SELECT * FROM '.$db->prefix.'attachments WHERE topic_id='.\intval($id).' AND post_id IN ('.\implode(',', \array_map('intval', $pids)).')';

        break;

    case 'hide.php':
        $att_sql = 'SELECT * FROM '.$db->prefix.'attachments WHERE topic_id='.\intval($id).' AND post_id = '.\intval($cur_post['id']);

        break;

    case 'edit.php':
        $att_sql = 'SELECT * FROM '.$db->prefix.'attachments WHERE post_id='.\intval($id);

        break;

    default:
        $att_sql = null;

        break;
}

// prepare attachments cache data
$attachments = [];
if ($att_sql) {
    $result = $db->query($att_sql); // or error('Unable to fetch attachments', __FILE__, __LINE__, $db->error());
    while ($attachment = $db->fetch_assoc($result)) {
        $attachments[$attachment['post_id']][] = $attachment;
    }
    $db->free_result($result);
}
