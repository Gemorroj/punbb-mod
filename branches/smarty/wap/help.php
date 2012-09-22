<?php
//define('PUN_HELP', 1);

define('PUN_ROOT', '../');
require PUN_ROOT . 'include/common.php';


if (!$pun_user['g_read_board']) {
    wap_message($lang_common['No view']);
}

// Load the help.php language file
require PUN_ROOT . 'lang/' . $pun_user['language'] . '/help.php';
require_once PUN_ROOT . 'wap/header.php';

$smarty->assign('lang_help', $lang_help);

switch (@$_GET['id']) {
    //BBCode    
    case 1:
        $page_title = $pun_config['o_board_title'] . ' / ' . $lang_help['Help'] . ' / ' . $lang_common['BBCode'];
        $smarty->assign('page_title', $page_title);

        $smarty->display('help.1.tpl');
        break;

    //url/images
    case 2:
        $page_title = $pun_config['o_board_title'] . ' / ' . $lang_help['Help'] . ' / ' . $lang_help['Links and images'];

        $smarty->assign('page_title', $page_title);

        $smarty->display('help.2.tpl');
        break;

    //smilies
    case 3:

        $page_title = $pun_config['o_board_title'] . ' / ' . $lang_help['Help'] . ' / ' . $lang_common['Smilies'];
        // Display the smiley set
        include_once PUN_ROOT . 'include/parser.php';

        $smarty->assign('page_title', $page_title);
        $smarty->assign('smiley_text', $smiley_text);
        $smarty->assign('smiley_img', $smiley_img);

        $smarty->display('help.3.tpl');

        break;


    //url tag
    case 4:
        $page_title = $pun_config['o_board_title'] . ' / ' . $lang_help['Help'] . ' / ' . $lang_common['img tag'];

        $smarty->assign('page_title', $page_title);

        $smarty->display('help.4.tpl');
        break;


    default:
        $page_title = $pun_config['o_board_title'] . ' / ' . $lang_help['Help'];

        $smarty->assign('page_title', $page_title);

        $smarty->display('help.tpl');
        break;
}
