<?php

\define('PUN_ROOT', './');

require PUN_ROOT.'include/common.php';

if (isset($_GET['poll'])) {
    \header('Content-Type: text/html; charset=UTF-8');

    include_once PUN_ROOT.'include/poll/poll.inc.php';

    switch ($_GET['poll']) {
        case 'sres': // SEND RESULT
            $Poll->vote($_POST['p'], $_POST['q']);
            echo $Poll->showPoll($_POST['p']);

            break;

        case 'gcfrm': // GET FORM FOR CREATE POLL
            echo $Poll->showForm();

            break;

        case 'gefrm': // GET FORM FOR EDIT POLL
            echo $Poll->showEditForm(@$_GET['pid']);

            break;

        case 'update': // Update POLL
            echo $Poll->updatePoll();

            break;
    }
} elseif (isset($_GET['quote'])) {
    \header('Content-Type: text/html; charset=UTF-8');

    $result = $db->query('SELECT poster, message FROM '.$db->prefix.'posts WHERE id='.\intval($_GET['quote'])) or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
    $cur_post = $db->fetch_assoc($result);

    echo '[quote='.$cur_post['poster'].']'.$cur_post['message'].'[/quote]'."\n";
} elseif (isset($_GET['informer'], $_GET['method'])) {
    \header('Content-Type: application/json; charset=UTF-8');

    try {
        switch ($_GET['method']) {
            case 'getMessage':
            case 'getPrivateMessage':
            case 'getPrivateMessages':
            case 'getConfig':
            case 'setMessage':
            case 'getForums':
                include PUN_ROOT.'include/informer/Informer.inc.php';
                $obj = new Informer($db, $pun_user, $lang_common, $pun_config);

                $result = $obj->{$_GET['method']}($_GET);

                break;

            default:
                throw new Exception($lang_common['Bad request']);
                break;
        }

        echo \json_encode(['status' => true, 'data' => $result]);
    } catch (Exception $e) {
        echo \json_encode(['status' => false, 'data' => $e->getMessage()]);
    }
}
