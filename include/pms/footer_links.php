<?php

if ('message_list' === $footer_style) {
    echo '<dl id="searchlinks" class="conl"><dt><strong>PM links</strong></dt>';

    if (isset($new_messages) && $new_messages) {
        echo '<dd><a href="message_list.php?action=markall&amp;box='.(int) $_GET['box'].'&amp;p='.(int) $_GET['p'].'">'.$lang_pms['Mark all'].'</a></dd>';
    }

    echo '</dl>';
}
