<?php
if ($attachments) {
    echo '<ul class="attach_list"><li>' . $lang_fu['Attachments'] . '</li>';
    $basename = basename($_SERVER['PHP_SELF']);

    foreach ($attachments as $attachment) {
        $title = pun_htmlspecialchars($attachment['filename']);
        $aid = $attachment['id'];
        $downloads = $attachment['downloads'];
        $location = $attachment['location'];

        // in edit.php attachments has checkboxes to delete
        if ($basename == 'edit.php') {
            $check = '<br /><label><input type="checkbox" name="delete_image[]" value="' . $aid . '" />' . $lang_fu['Mark to Delete'] . '</label>';
        } else {
            $check = null;
        }


        $link_events = null;
        $att_info = ($attachment['size'] >= 1048576) ? (round($attachment['size'] / 1048576, 0) . 'mb') : (round($attachment['size'] / 1024, 0) . 'kb');

        if (preg_match('/^image\/(.*)$/i', $attachment['mime'], $regs)) {
            $att_info .= ',' . $regs[1] . ' ' . $attachment['image_dim'] . '<br />' . $lang_fu['Downloads'] . ': ' . $attachment['downloads'];
        } else {
            $att_info .= ' ' . $lang_fu['Downloads'] . ': ' . $attachment['downloads'];
        }


        if ($can_download) {
            echo '<li><a href="' . $pun_config['o_base_url'] . '/download.php?aid=' . $aid . '">' . $title . '</a> ' . $att_info . $check . '</li>';
        } else {
            echo '<li><span class="red">' . $title . '</span> ' . $att_info . '</li>';
        }
    }

    echo '</ul>';
}

?>