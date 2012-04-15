<?php
define('MAX_DIR_UPLOAD', 100); // here is constant for maximum size of all uploaded files in the dir uploaded/ (in Mbytes)
session_name('bunbb_upload');
session_start();

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
$s_cat  = $_SESSION['cat'];
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

define('PUN_ROOT', '../');

require PUN_ROOT . 'include/common.php';

// Load the viewtopic.php language file
require PUN_ROOT . 'lang/' . $pun_user['language'] . '/topic.php';
require PUN_ROOT . 'lang/' . $pun_user['language'] . '/uploads.php';

$page_title = pun_htmlspecialchars($pun_config['o_board_title']) . ' &#187; ' . $lang_uploads['Uploader'];

// Check permissions
$upl_conf = $db->fetch_assoc($db->query('SELECT * FROM ' . $db->prefix . 'uploads_conf WHERE g_id = ' . $pun_user['g_id']));
if (!$upl_conf) {
    $upl_conf= $db->fetch_assoc($db->query('SELECT * FROM ' . $db->prefix . 'uploads_conf WHERE g_id = 0'));
}


$result = $db->query('SELECT g_id, g_title FROM ' . $db->prefix . 'groups') or error('Unable to get usergroups', __FILE__, __LINE__, $db->error());
$i = 0;
while ($i < $db->num_rows($result)) {
    $groups[$i] = $db->fetch_assoc($result);
    $perms[$i] = $db->fetch_assoc($db->query('SELECT * FROM ' . $db->prefix . 'uploads_conf WHERE g_id = ' . $groups[$i]['g_id']));
    if (!$perms[$i]) {
        $perms[$i] = $db->fetch_assoc($db->query('SELECT * FROM ' . $db->prefix . 'uploads_conf WHERE g_id = 0'));
    }
    $i++;
}


if (isset($_GET['file'])) {
    // This block was taken from attachment.php (great thanks to Frank Hagstrom (frank.hagstrom+punbb@gmail.com),
    // an author of Attachment mod).
    // lets download a file
    $file_name = $_GET['file'];
    $file_name = strtr($file_name, '/', ' '); // убираем любые слэши и бэкслэши, которые могут использоваться в Lin-Win в качестве пути
    $file_name = strtr($file_name, '\\', ' ');

    if (!$upl_conf['p_view']) {
        wap_message($lang_common['No permission']);
    }
    if (!$upl_conf['p_globalview']) {
        // check if user can access this file
        $result = $db->query('SELECT uid FROM ' . $db->prefix . 'uploaded WHERE file=\'' . $db->escape($file_name) . '\' AND uid = ' . $pun_user['id'] . ' LIMIT 1') or error('Error getting this file', __FILE__, __LINE__, $db->error());
        if (!$db->fetch_assoc($result)) {
            wap_message($lang_common['No permission']);
        }
    }

    // update number of downloads
    $result = $db->query('UPDATE ' . $db->prefix . 'uploaded SET downs=downs+1 WHERE file=\'' . $db->escape($file_name) . '\' LIMIT 1') or error($lang_uploads['Err counter'], __FILE__, __LINE__, $db->error());
    
    if (!is_file(PUN_ROOT . 'uploaded/' . $file_name)) {
        wap_message($lang_common['Bad request']);
    } else {
        header('Location: ' . PUN_ROOT . 'uploaded/' . $file_name, true, 301);
    }
    
    // include_once PUN_ROOT.'include/functions.php';
    // getf(file_get_contents(PUN_ROOT.'uploaded/'.$file_name),$file_name,1,0,0);
    exit;
}

require_once PUN_ROOT . 'wap/header.php';

echo '<div class="inbox"><a href="index.php">' . $lang_common['Index'] . '</a> &#187; <a href="' . $_SERVER['PHP_SELF'] . '">' . $lang_uploads['Uploader'] . '</a></div>';

/*if (!isset($_GET['uploadit']) && $upl_conf['p_upload'] == 1) {
    echo ' &#187; <a href="' . $_SERVER['PHP_SELF'] . '?uploadit=1">' . $lang_uploads['Upload file'] . '</a>';
}*/

//////////////////////////////////////////////////////
$result = $db->query('SELECT id,type,exts FROM ' . $db->prefix.'uploads_types') or error('Unable to get types', __FILE__, __LINE__, $db->error());
$exts = '';
$cats = $ids = array();
while ($ar = $db->fetch_assoc($result)) {
    $exts .= $ar['exts'] . ' ';
    $cats[] .= $ar['type'];
    $ids[] .= $ar['id'];
}
/////////////////////////////////

$exts = trim($exts); // now we have all file types in one string
if (!$upl_conf['p_view']) {
    echo '<div class="in"><strong>' . $lang_uploads['Not allowed'] . '</strong></div><div class="msg">' . $lang_uploads['Not allowed mes'] . '</div>';
} else if (isset($_GET['uploadit'])) {
    if ($upl_conf['p_upload'] == 1) {
        $maxsize = $upl_conf['u_fsize'];
        $rules = str_replace('%SIZE%', $maxsize, $lang_uploads['Upload rules mes']);
        $rules = str_replace('%EXT%', $exts, $rules);

        echo '<div class="con">' . $lang_uploads['Upload file'] . '</div>
<form method="post" action="' . $_SERVER['PHP_SELF'] . '?" enctype="multipart/form-data">
<div class="input">
<strong>' . $lang_uploads['Upload rules'] . '</strong><br />
' . $rules . '</div>
<div class="input2">
' . $lang_uploads['File'] . ':<br /><input type="file" name="file" maxlength="200" /><br/>' . $lang_uploads['Descr'] . '<br /><input type="text" name="descr" maxlength="100" /></div>
<div class="go_to">
<input type="submit" name="act" value="' . $lang_uploads['Upload file'] . '" /></div></form>';
    } else {
        echo '<div class="red">' . $lang_uploads['Not allowed'] . '</div><div class="msg">' . $lang_uploads['Not allowed mes'] . '</div>';
    }
} else if(isset($_POST['act'])) {
    // try to upload a file
    $temp_name = $_FILES['file']['tmp_name'];
    $file_name = $_FILES['file']['name'];
    $file_type = $_FILES['file']['type'];
    $file_size = @round(($_FILES['file']['size']) / 1024);

    $result = $_FILES['file']['error'];
    if ($upl_conf['p_upload'] != 1) {
        error($lang_uploads['Not allowed'], __FILE__, __LINE__, $db->error());
    }

    // Here could be check of MAX_DIR_UPLOAD > 100 Mbytes, for example
    if (round((dir_size(PUN_ROOT . 'uploaded') + $file_size) / 1048576) > MAX_DIR_UPLOAD) {
        error('The directory is full. Contact administrator, please.', __FILE__, __LINE__, $db->error());
    } else if (!$file_name) {
        error($lang_uploads['Err no file'], __FILE__, __LINE__, $db->error());
    } else if(file_exists(PUN_ROOT . 'uploaded/' . $file_name)) {
        error($lang_uploads['Err file exists'], __FILE__, __LINE__, $db->error());
    } else if($file_size > $upl_conf['u_fsize']) {
        error($lang_uploads['Err file big'], __FILE__, __LINE__, $db->error());
    } else if(!in_array('.' . strtolower(pathinfo($file_name, PATHINFO_EXTENSION)), explode(' ', $exts))) {
        error($lang_uploads['Err file type'], __FILE__, __LINE__, $db->error());
    } else {
        // file matches
        if (!move_uploaded_file($temp_name, PUN_ROOT . 'uploaded/' . $file_name) || !filesize(PUN_ROOT . 'uploaded/' . $file_name)) {
            error('{' . pun_htmlspecialchars($file_name) . '} - ' . $lang_uploads['Err file couldnot'], __FILE__, __LINE__, $db->error());
        }

        // lets deal with description
        $descript = iconv_substr($_POST['descr'], 0, 100);
        $deslist = explode(' ', $descript);
        for ($i = 0, $all = sizeof($deslist); $i < $all; ++$i) {
            $deslist[$i] = trim($deslist[$i]);
            if (iconv_strlen($deslist[$i]) > 22) {
                $deslist[$i] = iconv_substr($deslist[$i], 0, 22);
            }
        }
        $descript = implode(' ', $deslist);

        $result = $db->query('
            INSERT INTO ' . $db->prefix . 'uploaded (
                `file`, `user`, `uid`, `user_stat`, `data`, `size`, `downs`, `descr`
            ) VALUES (
                "' . $db->escape(iconv_substr($file_name, 0, 200)) . '", "' . $db->escape($pun_user['username']) . '", "' . $pun_user['id'] . '", "' . $db->escape($pun_user['g_user_title']) . '", ' . $_SERVER['REQUEST_TIME'] . ', ' . $file_size . ', 0, "' . $db->escape($descript) . '"
            )
        ') or error('Unable to add upload data', __FILE__, __LINE__, $db->error());

        echo '<div class="con"><strong>' . $lang_uploads['File uploaded'] . '</strong></div>
<div class="msg"><a href="' . $_SERVER['PHP_SELF'] . '?file=' . rawurlencode($file_name) . '">' . $pun_config['o_base_url'] . '/uploads.php?file=' . pun_htmlspecialchars($file_name) . '</a></div>
<div class="go_to">';

        if (!isset($_GET['uploadit']) && $upl_conf['p_upload'] == 1) {
            echo ' <a class="but" href="' . $_SERVER['PHP_SELF'] . '?uploadit=1">' . $lang_uploads['Upload file'] . '</a>';
        }

        echo '</div>';
    }
} else if(isset($_GET['del'])) {
    $delfile = $_GET['del'];
    $delfile = strtr($delfile, '/', ' '); // убираем любые слыши и бэкслэши, которые используются в Lin-Win в качестве пути
    $delfile = strtr($delfile, '\\', ' ');
    if (($upl_conf['p_delete'] != 1) && ($upl_conf['p_globaldelete'] != 1)) {
        error($lang_uploads['Not allowed'], __FILE__, __LINE__, $db->error());
    }
    if (!$upl_conf['p_globaldelete']) {
        $result = $db->query('SELECT uid FROM ' . $db->prefix . 'uploaded WHERE file=\'' . $db->escape($delfile) . '\' AND uid = ' . $pun_user['id'] . ' LIMIT 1') or error('Error getting this file', __FILE__, __LINE__, $db->error());
        if (!$db->fetch_assoc($result)) {
            error($lang_uploads['Not allowed'], __FILE__, __LINE__, $db->error());
        }
    }
    if (!file_exists(PUN_ROOT . 'uploaded/' . $delfile)) {
        error($lang_uploads['Err file not found'], __FILE__, __LINE__, $db->error());
    } else {
        @unlink(PUN_ROOT . 'uploaded/' . $delfile);
        $result = $db->query('DELETE FROM ' . $db->prefix . 'uploaded WHERE file=\'' . $db->escape($delfile) . '\'') or error('Unable to delete file from table', __FILE__, __LINE__, $db->error());
//File deleted
        echo '<div class="con"><strong>' . $lang_uploads['Delete'] . '</strong></div><div class="msg">' . pun_htmlspecialchars($delfile) . $lang_uploads['File deleted'] . '</div>';
    }
} else {
    $refr = '<a href="' . $_SERVER['PHP_SELF'] . '?u=' . $s_u . '&amp;sort=';
    $sql = 1;
    // lets try to filter records
    if (strlen($s_file) > 0) {
        $sql .= ' AND file LIKE "%' . $db->escape($s_file) . '%"';
    }
    if (strlen($s_user) > 0) {
        $sql .= ' AND user LIKE "%' . $db->escape($s_user) . '%"';
    }
    if (strlen($s_desc) > 0) {
        $sql .= ' AND descr LIKE "%' . $db->escape($s_desc) . '%"';
    }
    $cat = intval($s_cat);
    if ($cat > 0) {
        $result = $db->query('SELECT exts FROM ' . $db->prefix . 'uploads_types WHERE id = ' . $cat) or error('Unable to get types', __FILE__, __LINE__, $db->error());
        $extens = array();
        if ($ar = $db->fetch_assoc($result)) {
            $extens = explode(' ', $ar['exts']);
        }
        if (sizeof($extens) > 0) {
            $sql .= ' AND (file LIKE "%' . $extens[0] . '"';
            for ($i = 1, $all = sizeof($extens); $i < $all; ++$i) {
                $sql .= ' OR file LIKE "%' . $extens[$i] . '"';
            }
            $sql .= ')';
        }
    }

    $sorto = ' ORDER BY data DESC';
    // try to sort on specified column
    $s = intval($s_sort);
    $sorto = ' ORDER BY ';
    $sorters = array('id', 'file', 'size', 'user', 'user_stat', 'data', 'downs', 'descr');
    if ($s < 1 || $s >= sizeof($sorters)) {
        $s = 1;
    }
    $sorto .= $sorters[$s];
    if ($s_u) {
        $sorto .= ' DESC';
    }
    if ($s != 5) {
        $sorto .= ', data DESC';
    }
    $pages = array(5, 10, 20, 30, 50, 100);


    if ($upl_conf['p_upload'] == 1) {
        echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '?" enctype="multipart/form-data">
<div class="input">' . $lang_uploads['Pages'] . '
<select id="nump" name="nump">';

        for ($i = 0, $all = sizeof($pages); $i < $all; ++$i) {
            echo '<option value="' . $pages[$i] . '"';
            if ($s_nump == $pages[$i]) {
                echo ' selected="selected"';
            }
            echo '>' . $pages[$i] . '</option>';
        }

        echo '</select><input type="submit" name="filter" value="' . $lang_uploads['Enable filter'] . '" /></div></form>';

        if (!isset($_GET['uploadit']) && $upl_conf['p_upload'] == 1) {
            echo '<div class="go_to"><a class="but" href="' . $_SERVER['PHP_SELF'] . '?uploadit=1">' . $lang_uploads['Upload file'] . '</a></div>';
        }
    }

    if ($upl_conf['p_globalview']) {
        $result = $db->query('SELECT COUNT(1) FROM ' . $db->prefix . 'uploaded WHERE ' . $sql . $sorto) or error('Error getting file list', __FILE__, __LINE__, $db->error());
    } else {
        $result = $db->query('SELECT COUNT(1) FROM ' . $db->prefix . 'uploaded WHERE ' . $sql . ' AND uid = ' . $pun_user['id'] . $sorto) or error('Error getting file list', __FILE__, __LINE__, $db->error());
    }
    $allrec = $db->result($result); // amount of all records satisfying our query
    $currec = $s_page * $s_nump;
    $kolvop = ceil($allrec / $s_nump); // number of pages
    $cp = ($kolvop == 0 ? 1 : $kolvop); //real
    $temppage = $s_page + 1;
    $j = false;
    $flist = str_replace('%NUM%', $allrec, $lang_uploads['File list']);
    $flist = str_replace('%CUR%', $temppage, $flist);
    $flist = str_replace('%ALL%', $cp, $flist);


    if ($upl_conf['p_upload'] == 1) {
        echo ' <div class="con"><strong>' . $flist . '</strong></div><div class="msg">' . $lang_uploads['Upload warn'] . '</div><div class="in">' . $refr . '1">' . $lang_uploads['File'] . '</a>|' . $refr . '2">' . $lang_uploads['Size'] . '</a>|' . $refr . '3">' . $lang_uploads['Posted by'] . '</a>|' . $refr . '5">' . $lang_uploads['Date'] . '</a>|' . $refr . '6">' . $lang_uploads['Downloaded'] . '</a>|' . $refr . '7">' . $lang_uploads['Desc'] . '</a></div>';

        /*if ($upl_conf['p_delete']) {
            echo '|' . $lang_uploads['Delete'];
        }*/
    }

    if ($upl_conf['p_globalview']) {
        $result = $db->query('SELECT * FROM ' . $db->prefix . 'uploaded WHERE ' . $sql . $sorto . ' LIMIT ' . $currec . ',' . $s_nump) or error('Error getting file list', __FILE__, __LINE__, $db->error());
    } else {
        $result = $db->query('SELECT * FROM ' . $db->prefix . 'uploaded WHERE ' . $sql . ' AND uid = ' . $pun_user['id'] . $sorto . ' LIMIT ' . $currec . ',' . $s_nump) or error('Error getting file list', __FILE__, __LINE__, $db->error());
    }

    // fetching file list
    while ($info = $db->fetch_assoc($result)) {

        // lets do some words wrapping
        if (iconv_strlen($info['file']) > 30) {
            $fl = $info['file'];
            // try to split it
            $ext = explode('.', $fl);
            $fn = iconv_strlen($ext[sizeof($ext) - 1]);
            if ($fn > 0 && $fn < 20) {
                $fl = iconv_substr($fl, 0, 27 - $fn) . '...' . $ext[sizeof($ext) - 1];
            } else {
                $fl = iconv_substr($fl, 0, 30); // just first 30 letters of file name
            }
            $fl = pun_htmlspecialchars($fl);
        } else {
            $fl = pun_htmlspecialchars($info['file']);
        }
        $ds = pun_htmlspecialchars($info['descr']);

        echo'<div class="' . (($j = !$j) ? 'msg' : 'msg2') . '">&#8226; <strong><a href="' . $_SERVER['PHP_SELF'] . '?file=' . rawurlencode($info['file']) . '">' . $fl. '</a></strong> <span class="small">(' . round(filesize(PUN_ROOT . 'uploaded/' . $info['file']) / 1024, 1) . ' kb, <strong><a href="profile.php?id=' . $info['uid'] . '">' . pun_htmlspecialchars($info['user']) . '</a></strong>, ' . format_time($info['data'], true) . ' ' . date('H:i:s', $info['data']) . ', ' . $lang_uploads['Downloaded'] .':' . $info['downs'] . ' )';

        if ($upl_conf['p_globaldelete']) {
            echo ' <a class="but" href="' . $_SERVER['PHP_SELF'] . '?del=' . rawurlencode($info['file']) . '">' . $lang_uploads['Delete'] . '</a>';
        } else if ($upl_conf['p_delete']) {
            if ($info['uid'] == $pun_user['id']) {
                echo ' <a class="but" href="' . $_SERVER['PHP_SELF'] . '?del=' . rawurlencode($info['file']) . '">' . $lang_uploads['Delete'] . '</a>';
            }
        }

        echo  '<br />' . $ds . '</span></div>';
    } // while

    if ($cp > 1) {
        echo '<div class="con">' . $lang_uploads['Go to page'];
        for ($i = 1; $i <= $cp; ++$i) {
            if (($i - 1) == $s_page) {
                echo ' ' . $i . ' ';
            } else {
                echo ' <a href="' . $_SERVER['PHP_SELF'] . '?page=' . ($i - 1) . '">' . $i . '</a> ';
            }
        }
        echo '</div>';
    }
}

$footer_style = 'index';
require_once PUN_ROOT . 'wap/footer.php';

// Get dir size
function dir_size($dir)
{
    $sz = 0;
    if ($str = @opendir($dir)) {
        while (($fnm = readdir($str)) !== false) {
            if ($fnm[0] != '.') {
                if (is_file($dir . '/' . $fnm)) {
                    $sz += filesize($dir . '/' . $fnm);
                } else if (is_dir($dir . '/' . $fnm)) {
                    $sz += dir_size($dir . '/' . $fnm);
                }
            }
        }
    }
    closedir($str);
    return $sz;
}

?>