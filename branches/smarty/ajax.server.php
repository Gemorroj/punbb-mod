<?php
define('PUN_ROOT', './');
require PUN_ROOT . 'include/common.php';

if (isset($_GET['poll'])) {
    include PUN_ROOT . 'include/poll/poll.inc.php';

    switch ($_GET['poll']) {
        case 'sres': // SEND RESULT
            $Poll->vote($_POST['p'], $_POST['q']);
            $Poll->showPoll($_POST['p'], true);
            break;


        case 'gcfrm': // GET FORM FOR CREATE POLL
            $Poll->showForm(true);
            break;


        case 'gefrm': // GET FORM FOR EDIT POLL
            $Poll->showEditForm(true, @$_GET['pid']);
            break;


        case 'update': // Update POLL
            $Poll->updatePoll(true);
            break;
    }
} else if (isset($_GET['quote'])) {
    header('Content-Type: text/html; charset=' . $lang_common['lang_encoding']);

    $result = $db->query('SELECT poster, message FROM ' . $db->prefix . 'posts WHERE id=' . intval($_GET['quote'])) or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
    $cur_post = $db->fetch_assoc($result);

    echo '[quote=' . $cur_post['poster'] . ']' . $cur_post['message'] . '[/quote]' . "\n";
} else if (isset($_GET['informer']) && isset($_GET['method'])) {
    header('Content-Type: application/json; charset=UTF-8');

    try {
        switch ($_GET['method']) {
            case 'getMessage':
            case 'getPrivateMessage':
            case 'getPrivateMessages':
            case 'getConfig':
            case 'setMessage':
            case 'getForums':
                include PUN_ROOT . 'include/informer/Informer.inc.php';
                $obj = new Informer($db, $pun_user, $lang_common, $pun_config);

                $result = $obj->$_GET['method']($_GET);
                break;


            default:
                throw new Exception($lang_common['Bad request']);
                break;
        }

        echo json_encode(array('status' => true, 'data' => $result));
    } catch (Exception $e) {
        echo json_encode(array('status' => false, 'data' => $e->getMessage()));
    }
}

?>