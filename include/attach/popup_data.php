<?php
/*

Build necessary data for Javascript popup.
Included when $pun_config['file_popup_info'] == '1' (i.e. "Popup")
This file is part of Elektra File Upload mod for PunBB.

Copyright (C) 2002-2005 Rickard Andersson (rickard@punbb.org)
Copyright (C) 2007 artoodetoo (master@1wd.ru)

Included from: edit.php, filemap.php, viewtopic.php

Incoming variables:
$attachments: array - cache of attachments records
 */

if (@$attachments) {
    $thumb_height = $pun_config['file_thumb_height'];
    $thumb_width = $pun_config['file_thumb_width'];
    $pview_height = $pun_config['file_preview_height'];
    $pview_width = $pun_config['file_preview_width'];

    $tmp = array();
    foreach ($attachments as $post_attachments) {
        foreach ($post_attachments as $attachment) {
            // generate preview images just-in-time
            if (\preg_match('/^image\/(.*)$/i', $attachment['mime'], $regs)) {
                $pview_fname = require_thumb($attachment['id'], $attachment['location'], $pview_width, $pview_height, false);
                $thumb_fname = require_thumb($attachment['id'], $attachment['location'], $thumb_width, $thumb_height, true);
                $img_size = ' ('.$regs[1].' '.$attachment['image_dim'].')';
            } else {
                $thumb_fname = $img_size = null;
            }

            $tmp[] = "'".$attachment['id']."': ["."'".format_time($attachment['uploaded']).
                "',"."'".pun_htmlspecialchars($attachment['filename'])."',"."'".$lang_fu['Size'].
                ': '.\round($attachment['size'] / 1024, 1).'kb '.$img_size.' '.$lang_fu['Downloads'].
                ': '.$attachment['downloads']."','".$thumb_fname."',".\intval($attachment['can_download'] ?? $can_download).']';
        }
    }

    JsHelper::getInstance()->addInternal('ATTACH_DATA={'.\implode(',', $tmp).'};');
    unset($tmp);
}

JsHelper::getInstance()->add(PUN_ROOT.'js/popup_data.js');

echo '<div id="pun-popup" class="punpopup"><p id="pun-title" class="popup-title">title</p><p id="pun-desc" class="popup-desc">Description</p><p id="pun-body" class="popup-body">Body</p></div>';
