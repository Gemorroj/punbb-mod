<?php

\define('PUN_ROOT', '../');

require_once PUN_ROOT.'include/common.php';

// Наверно лучше сделать для конкретного действия отдельный шаблон.
// Загрузка/Результат/Показ

/*
 * Maximum size of all uploaded files in the dir uploaded.
 * Size in Mbytes.
*/
\define('MAX_DIR_UPLOAD', 100);

\session_name('bunbb_upload');
\session_start();

// is it first time we run uploader?
if (!isset($_SESSION['firsttime'])) {
    // yep - init values
    $_SESSION['firsttime'] = true;
    $_SESSION['nump'] = 20;
    $_SESSION['cat'] = 0;
    $_SESSION['file'] = '';
    $_SESSION['user'] = '';
    $_SESSION['desc'] = '';

    $_SESSION['sort'] = 5;
    $_SESSION['u'] = 1;
    $_SESSION['page'] = 0;
} else {
    if (isset($_POST['nump'])) {
        $_SESSION['nump'] = $_POST['nump'];
    }
    if (isset($_POST['cat'])) {
        $_SESSION['cat'] = $_POST['cat'];
    }
    if (isset($_POST['file'])) {
        $_SESSION['file'] = $_POST['file'];
    }
    if (isset($_POST['user'])) {
        $_SESSION['user'] = $_POST['user'];
    }
    if (isset($_POST['desc'])) {
        $_SESSION['desc'] = $_POST['desc'];
    }
    if (isset($_GET['sort'])) {
        $_SESSION['sort'] = $_GET['sort'];
    }
    if (isset($_GET['u'])) {
        $_SESSION['u'] = !$_SESSION['u'];
    }
    if (isset($_REQUEST['page'])) {
        $_SESSION['page'] = $_REQUEST['page'];
    }
}
// setup locale variables

$s_nump = $_SESSION['nump'];
$s_cat = $_SESSION['cat'];
$s_file = $_SESSION['file'];
$s_user = $_SESSION['user'];
$s_desc = $_SESSION['desc'];

$s_sort = $_SESSION['sort'];
$s_u = $_SESSION['u'];
$s_page = $_SESSION['page'];

////////////// lets do some checks - you never know what there will be...
if ($s_page < 0) {
    $s_page = 0;
}
if ($s_nump < 5 || $s_nump > 200) {
    $nump = 10;
}
/////////////////////////////////////////

// Check permissions
$upl_conf = $db->fetch_assoc($db->query('SELECT * FROM '.$db->prefix.'uploads_conf WHERE g_id = '.$pun_user['g_id']));
if (!$upl_conf) {
    $upl_conf = $db->fetch_assoc($db->query('SELECT * FROM '.$db->prefix.'uploads_conf WHERE g_id = 0'));
}

$result = $db->query('SELECT g_id, g_title FROM `'.$db->prefix.'groups`') or \error('Unable to get usergroups', __FILE__, __LINE__, $db->error());
$i = 0;
while ($i < $db->num_rows($result)) {
    $groups[$i] = $db->fetch_assoc($result);
    $perms[$i] = $db->fetch_assoc($db->query('SELECT * FROM '.$db->prefix.'uploads_conf WHERE g_id = '.$groups[$i]['g_id']));
    if (!$perms[$i]) {
        $perms[$i] = $db->fetch_assoc($db->query('SELECT * FROM '.$db->prefix.'uploads_conf WHERE g_id = 0'));
    }
    ++$i;
}

require_once PUN_ROOT.'lang/'.$pun_user['language'].'/uploads.php';

if (isset($_GET['file'])) {
    // This block was taken from attachment.php (great thanks to Frank Hagstrom (frank.hagstrom+punbb@gmail.com),
    // an author of Attachment mod).
    // lets download a file
    $file_name = $_GET['file'];
    $file_name = \str_replace(['/', '\\'], '_', $file_name); // убираем слэши и бэкслэши, которые могут использоваться в Lin-Win в качестве пути

    if (!$upl_conf['p_view']) {
        \wap_message($lang_common['No permission']);
    }
    if (!$upl_conf['p_globalview']) {
        // check if user can access this file
        $result = $db->query('SELECT uid FROM '.$db->prefix.'uploaded WHERE file=\''.$db->escape($file_name).'\' AND uid = '.$pun_user['id'].' LIMIT 1') or \error('Error getting this file', __FILE__, __LINE__, $db->error());
        if (!$db->fetch_assoc($result)) {
            \wap_message($lang_common['No permission']);
        }
    }

    if (!\is_file(PUN_ROOT.'uploaded/'.$file_name)) {
        \wap_message($lang_common['Bad request']);
    }

    // update number of downloads
    $result = $db->query('UPDATE '.$db->prefix.'uploaded SET downs=downs+1 WHERE file=\''.$db->escape($file_name).'\' LIMIT 1') or \error($lang_uploads['Err counter'], __FILE__, __LINE__, $db->error());

    \download(PUN_ROOT.'uploaded/'.$file_name, $file_name);
}

//////////////////////////////////////////////////////
$result = $db->query('SELECT id,type,exts FROM '.$db->prefix.'uploads_types') or \error('Unable to get types', __FILE__, __LINE__, $db->error());
$exts = '';
$cats = $ids = [];
while ($ar = $db->fetch_assoc($result)) {
    $exts .= $ar['exts'].' ';
    $cats[] .= $ar['type'];
    $ids[] .= $ar['id'];
}
/////////////////////////////////

$exts = \trim($exts); // now we have all file types in one string

if (isset($_GET['uploadit'])) {
    if (1 == $upl_conf['p_upload']) {
        $maxsize = $upl_conf['u_fsize'];
        $rules = \str_replace('%SIZE%', $maxsize, $lang_uploads['Upload rules mes']);
        $rules = \str_replace('%EXT%', $exts, $rules);
    }
} elseif (isset($_POST['act'])) {
    // try to upload a file
    $temp_name = $_FILES['file']['tmp_name'];
    $file_name = $_FILES['file']['name'];
    $file_type = $_FILES['file']['type'];
    $file_size = @\round(($_FILES['file']['size']) / 1024);

    $result = $_FILES['file']['error'];
    if (1 != $upl_conf['p_upload']) {
        \error($lang_uploads['Not allowed'], __FILE__, __LINE__, $db->error());
    }

    // Here could be check of MAX_DIR_UPLOAD > 100 Mbytes, for example
    if (\round((\dir_size(PUN_ROOT.'uploaded') + $file_size) / 1048576) > MAX_DIR_UPLOAD) {
        \error('The directory is full. Contact administrator, please.', __FILE__, __LINE__, $db->error());
    } elseif (!$file_name) {
        \error($lang_uploads['Err no file'], __FILE__, __LINE__, $db->error());
    } elseif (\file_exists(PUN_ROOT.'uploaded/'.$file_name)) {
        \error($lang_uploads['Err file exists'], __FILE__, __LINE__, $db->error());
    } elseif ($file_size > $upl_conf['u_fsize']) {
        \error($lang_uploads['Err file big'], __FILE__, __LINE__, $db->error());
    } elseif (!\in_array('.'.\strtolower(\pathinfo($file_name, \PATHINFO_EXTENSION)), \explode(' ', $exts), true)) {
        \error($lang_uploads['Err file type'], __FILE__, __LINE__, $db->error());
    } elseif (\mb_strlen($file_name) > 255) {
        \error($lang_uploads['Err file name big'], __FILE__, __LINE__, $db->error());
    } else {
        // file matches
        if (!\move_uploaded_file($temp_name, PUN_ROOT.'uploaded/'.$file_name) || !\is_file(PUN_ROOT.'uploaded/'.$file_name)) {
            \error('{'.\pun_htmlspecialchars($file_name).'} - '.$lang_uploads['Err file couldnot'], __FILE__, __LINE__, $db->error());
        }

        // lets deal with description
        $descript = \mb_substr($_POST['descr'], 0, 1000);

        $result = $db->query('
            INSERT INTO '.$db->prefix.'uploaded (
                `file`, `user`, `uid`, `user_stat`, `data`, `size`, `downs`, `descr`
            ) VALUES (
                "'.$db->escape($file_name).'", "'.$db->escape($pun_user['username']).'", "'.$pun_user['id'].'", "'.$db->escape($pun_user['g_user_title']).'", '.$_SERVER['REQUEST_TIME'].', '.$file_size.', 0, "'.$db->escape($descript).'"
            )
        ') or \error('Unable to add upload data', __FILE__, __LINE__, $db->error());
    }
} elseif (isset($_GET['del'])) {
    $delfile = $_GET['del'];
    $delfile = \str_replace(['/', '\\'], '_', $delfile); // убираем слэши и бэкслэши, которые могут использоваться в Lin-Win в качестве пути

    if ((1 != $upl_conf['p_delete']) && (1 != $upl_conf['p_globaldelete'])) {
        \error($lang_uploads['Not allowed'], __FILE__, __LINE__, $db->error());
    }
    if (!$upl_conf['p_globaldelete']) {
        $result = $db->query('SELECT uid FROM '.$db->prefix.'uploaded WHERE file=\''.$db->escape($delfile).'\' AND uid = '.$pun_user['id'].' LIMIT 1') or \error('Error getting this file', __FILE__, __LINE__, $db->error());
        if (!$db->fetch_assoc($result)) {
            \error($lang_uploads['Not allowed'], __FILE__, __LINE__, $db->error());
        }
    }

    if (!\file_exists(PUN_ROOT.'uploaded/'.$delfile)) {
        \error($lang_uploads['Err file not found'], __FILE__, __LINE__, $db->error());
    } else {
        @\unlink(PUN_ROOT.'uploaded/'.$delfile);
        $result = $db->query('DELETE FROM '.$db->prefix.'uploaded WHERE file=\''.$db->escape($delfile).'\'') or \error('Unable to delete file from table', __FILE__, __LINE__, $db->error());
    }
} else {
    $sql = 1;
    // lets try to filter records
    if (\strlen($s_file) > 0) {
        $sql .= ' AND file LIKE "%'.$db->escape($s_file).'%"';
    }
    if (\strlen($s_user) > 0) {
        $sql .= ' AND user LIKE "%'.$db->escape($s_user).'%"';
    }
    if (\strlen($s_desc) > 0) {
        $sql .= ' AND descr LIKE "%'.$db->escape($s_desc).'%"';
    }
    $cat = \intval($s_cat);
    if ($cat > 0) {
        $result = $db->query('SELECT exts FROM '.$db->prefix.'uploads_types WHERE id = '.$cat) or \error('Unable to get types', __FILE__, __LINE__, $db->error());
        $extens = [];
        if ($ar = $db->fetch_assoc($result)) {
            $extens = \explode(' ', $ar['exts']);
        }
        if (\count($extens) > 0) {
            $sql .= ' AND (file LIKE "%'.$extens[0].'"';
            for ($i = 1, $all = \count($extens); $i < $all; ++$i) {
                $sql .= ' OR file LIKE "%'.$extens[$i].'"';
            }
            $sql .= ')';
        }
    }

    $sorto = ' ORDER BY data DESC';
    // try to sort on specified column
    $s = \intval($s_sort);
    $sorto = ' ORDER BY ';
    $sorters = ['id', 'file', 'size', 'user', 'user_stat', 'data', 'downs', 'descr'];
    if ($s < 1 || $s >= \count($sorters)) {
        $s = 1;
    }
    $sorto .= $sorters[$s];
    if ($s_u) {
        $sorto .= ' DESC';
    }
    if (5 != $s) {
        $sorto .= ', data DESC';
    }

    $pages = [5, 10, 20, 30, 50, 100];

    if ($upl_conf['p_globalview']) {
        $result = $db->query('SELECT COUNT(1) FROM '.$db->prefix.'uploaded WHERE '.$sql.$sorto) or \error('Error getting file list', __FILE__, __LINE__, $db->error());
    } else {
        $result = $db->query('SELECT COUNT(1) FROM '.$db->prefix.'uploaded WHERE '.$sql.' AND uid = '.$pun_user['id'].$sorto) or \error('Error getting file list', __FILE__, __LINE__, $db->error());
    }
    $allrec = $db->result($result); // amount of all records satisfying our query
    $currec = $s_page * $s_nump;
    $kolvop = \ceil($allrec / $s_nump); // number of pages
    $cp = (0 == $kolvop ? 1 : $kolvop); //real
    $temppage = $s_page + 1;

    $flist = \str_replace('%NUM%', $allrec, $lang_uploads['File list']);
    $flist = \str_replace('%CUR%', $temppage, $flist);
    $flist = \str_replace('%ALL%', $cp, $flist);

    if ($upl_conf['p_globalview']) {
        $result = $db->query('SELECT * FROM '.$db->prefix.'uploaded WHERE '.$sql.$sorto.' LIMIT '.$currec.','.$s_nump) or \error('Error getting file list', __FILE__, __LINE__, $db->error());
    } else {
        $result = $db->query('SELECT * FROM '.$db->prefix.'uploaded WHERE '.$sql.' AND uid = '.$pun_user['id'].$sorto.' LIMIT '.$currec.','.$s_nump) or \error('Error getting file list', __FILE__, __LINE__, $db->error());
    }

    while ($info = $db->fetch_assoc($result)) {
        $info['sizeValue'] = 'kb';
        $files[] = $info;
    }
}

// Get dir size
function dir_size($dir)
{
    $sz = 0;
    if ($str = @\opendir($dir)) {
        while (false !== ($fnm = \readdir($str))) {
            if ('.' != $fnm[0]) {
                if (\is_file($dir.'/'.$fnm)) {
                    $sz += \filesize($dir.'/'.$fnm);
                } elseif (\is_dir($dir.'/'.$fnm)) {
                    $sz += \dir_size($dir.'/'.$fnm);
                }
            }
        }
    }
    \closedir($str);

    return $sz;
}

//+ Language
require_once PUN_ROOT.'lang/'.$pun_user['language'].'/topic.php';
// string #97 $lang_uploads
//- Language

require_once PUN_ROOT.'wap/header.php';

$page_title = $pun_config['o_board_title'].' / '.$lang_uploads['Uploader'];
$smarty->assign('page_title', $page_title);
$smarty->assign('lang_uploads', $lang_uploads);
$smarty->assign('upl_conf', $upl_conf);
$smarty->assign('rules', @$rules);
$smarty->assign('file_name', @$file_name);
$smarty->assign('delfile', @$delfile);
$smarty->assign('pages', @$pages);
$smarty->assign('s_nump', $s_nump);
$smarty->assign('s_page', $s_page);
$smarty->assign('flist', @$flist);
$smarty->assign('files', @$files);
$smarty->assign('cp', @$cp);
$smarty->assign('s_u', @$s_u);
//$smarty->assign('',           $);

$smarty->display('uploads.tpl');
