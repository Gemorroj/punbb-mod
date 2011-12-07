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
} else if (isset($_GET['informer'])) {
    include PUN_ROOT . 'include/informer/Informer.inc.php';
    header('Content-Type: application/json; charset=UTF-8');

    try {
        $obj = new Informer($db, $pun_user, $lang_common, $pun_config);

        if (isset($_GET['getMessage']) && isset($_GET['getPrivatMessage'])) {
            echo json_encode(array('status' => true, 'forum' => $obj->getMessage($_GET['getMessage']), 'privat' => $obj->getPrivateMessage($_GET['getPrivatMessage'])));
        } else if (isset($_GET['getPrivatMessage'])) {
            echo json_encode(array('status' => true, 'privat' => $obj->getPrivateMessage($_GET['getPrivatMessage'])));
        } else if (isset($_GET['getMessage'])) {
            echo json_encode(array('status' => true, 'forum' => $obj->getMessage($_GET['getMessage'])));
        } else if (isset($_GET['getPrivatMessages'])) {
            echo json_encode(array('status' => true, 'privat' => $obj->getPrivateMessages($_GET['getPrivatMessages'])));
        } else if (isset($_GET['getConfig'])) {
            echo json_encode(array('status' => true, 'config' => $obj->getConfig()));
        } else if (isset($_GET['setMessage']) && isset($_GET['setMessageTopicId'])) {
            echo json_encode(array('status' => true, 'forum' => $obj->setMessage($_GET['setMessage'], $_GET['setMessageTopicId'])));
        } else {
            echo json_encode(array('status' => true, 'forum' => $obj->getForums()));
        }
    } catch (Exception $e) {
        echo json_encode(array('status' => false, 'forum' => $e->getMessage()));
    }
}

?>