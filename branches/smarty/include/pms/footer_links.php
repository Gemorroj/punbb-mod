<?php
if ($footer_style == 'message_list') {
    echo '<dl id="searchlinks" class="conl"><dt><strong>PM links</strong></dt>';

    if ($new_messages) {
        echo '<dd><a href="message_list.php?action=markall&amp;box=' . intval($_GET['box']) . '&amp;p=' . intval($_GET['p']) . '">' . $lang_pms['Mark all'] . '</a></dd>';
    }

    echo '</dl>';
}
