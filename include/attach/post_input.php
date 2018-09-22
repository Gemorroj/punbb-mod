<?php

echo '<div class="infldset txtarea"><div id="divAttachRules" style="display:none;"><em>';
printf($lang_fu['File Upload limits'], $file_limit, $pun_config['file_max_size'] / 1024, ($pun_config['file_max_width'] . 'x' . $pun_config['file_max_height']), $pun_config['file_allowed_ext']);
echo '</em>';

if (defined('PUN_DEBUG')) {
    echo '<br /><em>';
    printf($lang_fu['Personal stats'], $pun_user['g_file_limit'], $pun_user['file_bonus'], $pun_user['num_files'], $forum_file_limit, $uploaded_to_forum);
    echo '</em>';
}

echo '</div>
<div id="divImage1">
<label style="display:inline;"><strong id="lblFileOne">' . $lang_fu['Choose a file'] . '</strong><strong id="lblFileFive" style="display:none;">' . $lang_fu['Choose a few files'] . '</strong><strong id="lblFileTwenty" style="display:none;">' . $lang_fu['Choose a whole bunch of files'] . '</strong></label>
<span id="lblShowQuota" style="display:inline;">(<a href="javascript:void(0);" onclick="toggle(\'lblHideQuota\', \'lblShowQuota\', \'divAttachRules\');">' . $lang_fu['show quota'] . '</a>)</span>
<span id="lblHideQuota" style="display:none;">(<a href="javascript:void(0);" onclick="toggle(\'lblShowQuota\', \'lblHideQuota\', \'divAttachRules\');">' . $lang_fu['hide quota'] . '</a>)</span>
<br class="clearb" />
<div class="floated" id="input_1"><span><a href="#" onclick="return insert_text(\'\',\' ::thumb$1:: \');">#1</a> </span><input type="file" name="attach[]" size="50" /></div>';

if ($num_to_upload >= 2) {
    echo '<div id="addMoreFiles1" class="fine_print">
<a href="javascript:void(0);" onclick="toggle(\'lblFileOne\', \'lblFileFive\', \'addMoreFiles1\', \'divImage2\');">' . $lang_fu['Add more files'] . '</a>
</div>
</div>
<div id="divImage2" style="display:none;">';

    for ($i = 2, $a = min(5, $num_to_upload); $i <= $a; ++$i) {
        echo '<div class="floated" id="input_' . $i . '"><span><a href="#" onclick="return insert_text(\'\',\' ::thumb$' . $i . ':: \');">#' . $i . '</a> </span><input type="file" name="attach[]" size="50" /></div>';
    } ?>
<div id="addMoreFiles2" class="fine_print">
<?php if ($num_to_upload > 5) {
        ?>
        <a href="javascript:void(0);" onclick="toggle('lblFileFive', 'lblFileTwenty', 'addMoreFiles2', 'divImage3');"><?php echo $lang_fu['Add even more here']; ?></a>
        (<?php echo $lang_fu['or just']; ?> <a href="javascript:void(0);" onclick="toggle('lblFileOne', 'lblFileFive', 'addMoreFiles1', 'divImage2');"><?php echo $lang_fu['one slot']; ?></a>)
        <?php
    } else {
        ?>
        <a href="javascript:void(0);" onclick="toggle('lblFileOne', 'lblFileFive', 'addMoreFiles1', 'divImage2');"><?php echo $lang_fu['Upload just one']; ?></a>
        <?php
    }

    echo '</div></div>';

    if ($num_to_upload > 5) {
        echo '<div id="divImage3" class="inputArea" style="display:none;">';

        for ($i = 6; $i <= $num_to_upload; ++$i) {
            echo '<div class="floated" id="input_' . $i . '"><span><a href="#" onclick="return insert_text(\'\',\' ::thumb$' . $i . ':: \');">#' . $i . '</a> </span><input type="file" name="attach[]" size="50" /></div>';
        }

        echo '<div id="addMoreFiles3" class="fine_print"><a href="javascript:void(0);" onclick="toggle(\'lblFileOne\', \'lblFileTwenty\', \'addMoreFiles1\', \'addMoreFiles2\', \'divImage2\', \'divImage3\');">' . $lang_fu['Upload just one'] . '</a></div></div>';
    }
} else {
    echo '</div>';
}

echo '<div class="clearer"></div></div>';
