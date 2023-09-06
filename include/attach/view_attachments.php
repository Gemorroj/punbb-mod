<?php

if (isset($attachments[$cur_post['id']])) {
    echo '<ul class="attach_list">';

    $is_inplace = (2 == $pun_config['file_popup_info']);
    $basename = \basename($_SERVER['PHP_SELF']);

    foreach ($attachments[$cur_post['id']] as $attachment) {
        $title = \pun_htmlspecialchars($attachment['filename']);
        $aid = $attachment['id'];
        $downloads = $attachment['downloads'];
        $location = $attachment['location'];

        // in edit.php attachments has checkboxes to delete
        if ('edit.php' === $basename) {
            $check = '<br /><label><input type="checkbox" name="delete_image[]" value="'.$aid.'" />'.$lang_fu['Mark to Delete'].'</label>';
        } else {
            $check = null;
        }

        if (1 == $pun_config['file_popup_info']) {
            $link_events = ' onmouseover="downloadPopup(event,\''.$aid.'\')"';
            $att_info = null;
        } else {
            $link_events = null;
            if ($is_inplace) {
                $att_info = '<br />'.(($attachment['size'] >= 1048576) ? (\round($attachment['size'] / 1048576, 0).'mb') : (\round($attachment['size'] / 1024, 0).'kb'));

                if (\preg_match('/^image\/(.*)$/i', $attachment['mime'], $regs)) {
                    $att_info .= ','.$regs[1].' '.$attachment['image_dim'].'<br />'.$lang_fu['Downloads'].': '.$attachment['downloads'];
                    $thumbnail = '<img src="'.PUN_ROOT.\require_thumb($attachment['id'], $attachment['location'], $pun_config['file_thumb_width'], $pun_config['file_thumb_height'], true).'">';
                    if ($can_download) {
                        // $thumbnail = '<a href="'.$pun_config['o_base_url'].'/download.php?aid='.$aid.'">'.$thumbnail.'</a>';
                        $thumbnail = '<a href="javascript:void(0);" onclick="{a=\'::thumb'.$aid.'::\';window.prompt(\'BBcode\',a);}">'.$thumbnail.'</a>';
                    }
                    $att_info .= '<br />'.$thumbnail;
                } else {
                    $att_info .= '<br />'.$lang_fu['Downloads'].': '.$attachment['downloads'];
                }
            } else {
                $att_info = null;
            }
        }

        if ($can_download) {
            echo '<li'.(($is_inplace) ? ' class="att_info"' : '').'><a href="'.$pun_config['o_base_url'].'/download.php?aid='.$aid.'"'.$link_events.' class="att_filename">'.$title.'</a>'.$att_info.$check.'</li>';
        } else {
            echo '<li'.(($is_inplace) ? ' class="att_info"' : '').$link_events.'><span class="att_filename">'.$title.'</span>'.$att_info.$check.'</li>';
        }
    }

    echo '</ul><div class="clearer"></div>';
}
