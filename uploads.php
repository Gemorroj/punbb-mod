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
define('PUN_ROOT', './');


require PUN_ROOT . 'include/common.php';

$page_title = pun_htmlspecialchars($pun_config['o_board_title']) . ' &#187; Upload';
// Load the viewtopic.php language file
require PUN_ROOT . 'lang/' . $pun_user['language'] . '/topic.php';
require PUN_ROOT . 'lang/' . $pun_user['language'] . '/uploads.php';

// Check permissions
$upl_conf = $db->fetch_assoc($db->query('SELECT * FROM ' . $db->prefix . 'uploads_conf WHERE g_id = ' . $pun_user['g_id']));
if (!$upl_conf) {
    $upl_conf = $db->fetch_assoc($db->query('SELECT * FROM ' . $db->prefix . 'uploads_conf WHERE g_id = 0'));
}


$result = $db->query('SELECT g_id, g_title FROM `' . $db->prefix . 'groups`') or error('Unable to get usergroups', __FILE__, __LINE__, $db->error());
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
        message($lang_common['No permission']);
    }
    if (!$upl_conf['p_globalview']) {
        // check if user can access this file
        $result = $db->query('SELECT uid FROM ' . $db->prefix . 'uploaded WHERE file=\'' . $db->escape($file_name) . '\' AND uid = ' . $pun_user['id'] . ' LIMIT 1') or error('Error getting this file', __FILE__, __LINE__, $db->error());
        if (!$db->fetch_assoc($result)) {
            message($lang_common['No permission']);
        }
    }

    // update number of downloads
    $result = $db->query('UPDATE ' . $db->prefix . 'uploaded SET downs=downs+1 WHERE file=\'' . $db->escape($file_name) . '\' LIMIT 1') or error($lang_uploads['Err counter'], __FILE__, __LINE__, $db->error());

    if (!is_file(PUN_ROOT . 'uploaded/' . $file_name)) {
        message($lang_common['Bad request']);
    } else {
        redirect('uploaded/' . $file_name);
    }
    exit;
}


require_once PUN_ROOT . 'header.php';

echo '<strong><a href="index.php">' . pun_htmlspecialchars($pun_config['o_board_title']) . '</a> &#187; <a href="' . $_SERVER['PHP_SELF'] . '">' . $lang_uploads['Uploader'] . '</a>';
if (!isset($_GET['uploadit']) && $upl_conf['p_upload'] == 1) {
    echo ' &#187; <a href="' . $_SERVER['PHP_SELF'] . '?uploadit=1">' . $lang_uploads['Upload file'] . '</a>';
}
echo '</strong><br /><br /><div class="block"><h2><span>' . $lang_uploads['Uploader'] . '</span></h2><div class="box"><div class="inbox">';

//////////////////////////////////////////////////////
$result = $db->query('SELECT id,type,exts FROM ' . $db->prefix . 'uploads_types') or error('Unable to get types', __FILE__, __LINE__, $db->error());
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
    echo '<div id="announce" class="block"><h2><span><strong>' . $lang_uploads['Not allowed'] . '</strong></span></h2><div class="box"><div class="inbox"><div><strong>' . $lang_uploads['Not allowed mes'] . '</strong></div></div></div></div>';
} else if (isset($_GET['uploadit'])) {
    if ($upl_conf['p_upload'] == 1) {
        $maxsize = $upl_conf['u_fsize'];
        $rules = str_replace('%SIZE%', $maxsize, $lang_uploads['Upload rules mes']);
        $rules = str_replace('%EXT%', $exts, $rules);

        echo '<div class="inform"><fieldset><legend>' . $lang_uploads['Upload file'] . '</legend><div class="infldset"><form method="post" action="' . $_SERVER['PHP_SELF'] . '?" enctype="multipart/form-data"><div><strong>' . $lang_uploads['Upload rules'] . '</strong><br /><br />' . $rules . '<br /><br /><table><tr><td align="right">' . $lang_uploads['File'] . ':</td><td><input type="file" name="file" size="70" maxlength="200" /></td></tr><tr><td align="right">' . $lang_uploads['Descr'] . '</td><td><input type="text" name="descr" size="70" maxlength="100" /></td></tr><tr><td align="center"><input type="submit" name="act" value="' . $lang_uploads['Upload file'] . '" /></td><td> </td></tr></table></div></form></div></fieldset></div>';
    } else {
        echo '<div id="announce" class="block"><h2><span><strong>' . $lang_uploads['Not allowed'] . '</strong></span></h2><div class="box"><div class="inbox"><div><strong>' . $lang_uploads['Not allowed mes'] . '</strong></div></div></div></div>';
    }
} else if (isset($_POST['act'])) {
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
    } else if (file_exists(PUN_ROOT . 'uploaded/' . $file_name)) {
        error($lang_uploads['Err file exists'], __FILE__, __LINE__, $db->error());
    } else if ($file_size > $upl_conf['u_fsize']) {
        error($lang_uploads['Err file big'], __FILE__, __LINE__, $db->error());
    } else if (!in_array('.' . strtolower(pathinfo($file_name, PATHINFO_EXTENSION)), explode(' ', $exts))) {
        error($lang_uploads['Err file type'], __FILE__, __LINE__, $db->error());
    } else {
        // file matches
        if (!move_uploaded_file($temp_name, PUN_ROOT . 'uploaded/' . $file_name) || !filesize(PUN_ROOT . 'uploaded/' . $file_name)) {
            error('{' . pun_htmlspecialchars($file_name) . '} - ' . $lang_uploads['Err file couldnot'], __FILE__, __LINE__, $db->error());
        }

        // lets deal with description
        $descript = mb_substr($_POST['descr'], 0, 100);
        $deslist = explode(' ', $descript);
        for ($i = 0, $all = count($deslist); $i < $all; ++$i) {
            $deslist[$i] = trim($deslist[$i]);
            if (mb_strlen($deslist[$i]) > 22) {
                $deslist[$i] = mb_substr($deslist[$i], 0, 22);
            }
        }
        $descript = implode(' ', $deslist);

        $result = $db->query('
            INSERT INTO ' . $db->prefix . 'uploaded (
                `file`, `user`, `uid`, `user_stat`, `data`, `size`, `downs`, `descr`
            ) VALUES (
                "' . $db->escape(mb_substr($file_name, 0, 255)) . '", "' . $db->escape($pun_user['username']) . '", "' . $pun_user['id'] . '", "' . $db->escape($pun_user['g_user_title']) . '", ' . $_SERVER['REQUEST_TIME'] . ', ' . $file_size . ', 0, "' . $db->escape($descript) . '"
            )
        ') or error('Unable to add upload data', __FILE__, __LINE__, $db->error());


        echo '<div class="inform"><fieldset><legend>' . $lang_uploads['Upload file'] . '</legend><div class="infldset"><div><strong>' . $lang_uploads['File uploaded'] . '<a href="' . $_SERVER['PHP_SELF'] . '?file=' . rawurlencode($file_name) . '">' . $pun_config['o_base_url'] . '/uploads.php?file=' . pun_htmlspecialchars($file_name) . '</a></strong></div></div></fieldset></div>';
    }
} else if (isset($_GET['del'])) {
    $delfile = $_GET['del'];
    $delfile = strtr($delfile, '/', ' '); // убираем любые слыши и бэкслэши, которые используются в Lin-Win в качестве пути
    $delfile = strtr($delfile, '\\', ' ');
    if (($upl_conf['p_delete'] != 1) && ($upl_conf['p_globaldelete'] != 1)) {
        error($lang_uploads['Not allowed'], __FILE__, __LINE__, $db->error());
    }
    if (!$upl_conf['p_globaldelete']) {
        $result = $db->query('SELECT uid FROM ' . $db->prefix . 'uploaded WHERE file = \'' . $db->escape($delfile) . '\' AND uid = ' . $pun_user['id'] . ' LIMIT 1') or error('Error getting this file', __FILE__, __LINE__, $db->error());
        if (!$db->fetch_assoc($result)) {
            error($lang_uploads['Not allowed'], __FILE__, __LINE__, $db->error());
        }
    }
    if (!file_exists(PUN_ROOT . 'uploaded/' . $delfile)) {
        error($lang_uploads['Err file not found'], __FILE__, __LINE__, $db->error());
    } else {
        @unlink(PUN_ROOT . 'uploaded/' . $delfile);
        $result = $db->query('DELETE FROM ' . $db->prefix . 'uploaded WHERE file=\'' . $db->escape($delfile) . '\'') or error('Unable to delete file from table', __FILE__, __LINE__, $db->error());

        echo '<div class="inform"><fieldset><legend>' . $lang_uploads['Delete'] . '</legend><div class="infldset"><div>' . pun_htmlspecialchars($delfile) . $lang_uploads['File deleted'] . '</div></div></fieldset></div>';
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
        if (count($extens) > 0) {
            $sql .= ' AND (file LIKE "%' . $extens[0] . '"';
            for ($i = 1, $all = count($extens); $i < $all; ++$i) {
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
    if ($s < 1 || $s >= count($sorters)) {
        $s = 1;
    }
    $sorto .= $sorters[$s];
    if ($s_u) {
        $sorto .= ' DESC';
    }
    if ($s != 5) {
        $sorto .= ', data DESC';
    }
    $pages = array(10, 20, 30, 50, 100);


    echo '<div class="inform"><fieldset><legend>' . $lang_uploads['Filter'] . '</legend><div class="infldset"><form method="post" action="' . $_SERVER['PHP_SELF'] . '?" enctype="multipart/form-data"><table><tr><td>' . $lang_uploads['Pages'] . '</td><td>' . $lang_uploads['Categ'] . '</td><td>' . $lang_uploads['Part'] . '</td><td>' . $lang_uploads['Posted by'] . '</td><td>' . $lang_uploads['Desc'] . '</td></tr><tr><td><select id="nump" name="nump">';

    for ($i = 0, $all = count($pages); $i < $all; ++$i) {
        echo '<option value="' . $pages[$i] . '"';
        if ($s_nump == $pages[$i]) {
            echo ' selected="selected"';
        }
        echo '>' . $pages[$i] . '</option>';
    }

    echo '</select></td><td><select id="cat" name="cat"><option value="0">' . $lang_uploads['All'] . '</option>';

    for ($i = 0, $all = count($cats); $i < $all; ++$i) {
        echo '<option value="' . $ids[$i] . '"';
        if ($s_cat == $ids[$i]) {
            echo ' selected="selected"';
        }
        echo '>' . $cats[$i] . '</option>';
    }

    echo '</select></td><td><input type="text" id="file" name="file" size="20" maxlength="200" value="' . pun_htmlspecialchars($s_file) . '" /></td><td><input type="text" id="user" name="user" size="20" maxlength="100" value="' . pun_htmlspecialchars($s_user) . '" /></td><td><input type="text" id="desc" name="desc" size="20" maxlength="200" value="' . pun_htmlspecialchars($s_desc) . '" /></td></tr><tr><td colspan="5"><input type="hidden" name="page" value="0" /><input type="submit" name="filter" value="' . $lang_uploads['Enable filter'] . '" />&#160; &#160; &#160; <input name="filter" type="submit" onclick="nump.value=\'20\';cat.value=\'0\';file.value=\'\';user.value=\'\';desc.value=\'\';window.location=\'' . $_SERVER['PHP_SELF'] . '\';" value="' . $lang_uploads['Reset filter'] . '" /></td></tr></table></form></div></fieldset></div>';

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
    $flist = str_replace('%NUM%', $allrec, $lang_uploads['File list']);
    $flist = str_replace('%CUR%', $temppage, $flist);
    $flist = str_replace('%ALL%', $cp, $flist);

    echo '<div class="inform"><fieldset><legend>' . $flist . '</legend><div class="infldset">' . $lang_uploads['Upload warn'] . '<br /><br /><table class="punmain" cellspacing="1" cellpadding="4"><tr class="punhead"><th class="punhead" style="width:15%">' . $refr . '1">' . $lang_uploads['File'] . '</a></th><th class="punhead" style="width:8%">' . $refr . '2">' . $lang_uploads['Size'] . '</a></th><th class="punhead" style="width:14%">' . $refr . '3">' . $lang_uploads['Posted by'] . '</a></th><th class="punhead" style="width:14%">' . $refr . '4">' . $lang_uploads['Rang'] . '</a></th><th class="punhead" style="width:10%">' . $refr . '5">' . $lang_uploads['Date'] . '</a></th><th class="punhead" style="width:5%">' . $refr . '6">' . $lang_uploads['Downloaded'] . '</a></th><th class="punhead" style="width:24%;white-space:normal">' . $refr . '7">' . $lang_uploads['Desc'] . '</a></th>';

    if ($upl_conf['p_delete']) {
        echo '<th class="punhead" style="width:6%">' . $lang_uploads['Delete'] . '</th>';
    }

    echo '</tr>';


    if ($upl_conf['p_globalview']) {
        $result = $db->query('SELECT * FROM ' . $db->prefix . 'uploaded WHERE ' . $sql . $sorto . ' LIMIT ' . $currec . ',' . $s_nump) or error('Error getting file list', __FILE__, __LINE__, $db->error());
    } else {
        $result = $db->query('SELECT * FROM ' . $db->prefix . 'uploaded WHERE ' . $sql . ' AND uid = ' . $pun_user['id'] . $sorto . ' LIMIT ' . $currec . ',' . $s_nump) or error('Error getting file list', __FILE__, __LINE__, $db->error());
    }

    // fetching file list
    while ($info = $db->fetch_assoc($result)) {
        echo '<tr class="puntopic">';

        // lets do some words wrapping
        if (mb_strlen($info['file']) > 30) {
            $fl = $info['file'];
            // try to split it
            $ext = explode('.', $fl);
            $fn = mb_strlen($ext[count($ext) - 1]);
            if ($fn > 0 && $fn < 20) {
                $fl = mb_substr($fl, 0, 27 - $fn) . '...' . $ext[count($ext) - 1];
            } else {
                $fl = mb_substr($fl, 0, 30); // just first 30 letters of file name
            }
            $fl = pun_htmlspecialchars($fl);
        } else {
            $fl = pun_htmlspecialchars($info['file']);
        }
        $ds = pun_htmlspecialchars($info['descr']);

        echo '<td class="puncon1"><a href="' . $_SERVER['PHP_SELF'] . '?file=' . rawurlencode($info['file']) . '">' . $fl . '</a></td><td class="puncon2" align="center">' . round(@filesize(PUN_ROOT . 'uploaded/' . $info['file']) / 1024, 1) . ' kb</td><td class="puncon1" align="center"><a href="profile.php?id=' . $info['uid'] . '">' . pun_htmlspecialchars($info['user']) . '</a></td><td class="puncon1">' . $info['user_stat'] . '</td><td class="puncon1" align="center"><span title="' . date('H:i:s', $info['data']) . '">' . format_time($info['data'], true) . '</span></td><td class="puncon1" align="center">' . $info['downs'] . '</td><td class="puncon1">' . $ds . '</td>';

        if ($upl_conf['p_globaldelete']) {
            echo '<td class="puncon1" align="center"><a href="' . $_SERVER['PHP_SELF'] . '?del=' . rawurlencode($info['file']) . '">' . $lang_uploads['Delete'] . '</a></td>';
        } else if ($upl_conf['p_delete']) {
            if ($info['uid'] == $pun_user['id']) {
                echo '<td class="puncon1" align="center"><a href="' . $_SERVER['PHP_SELF'] . '?del=' . rawurlencode($info['file']) . '">' . $lang_uploads['Delete'] . '</a></td>';
            } else {
                echo '<td class="puncon1" align="center">----</td>';
            }
        }

        echo '</tr>';
    } // while

    echo '</table><br />';

    if ($cp > 1) {
        echo $lang_uploads['Go to page'];
        for ($i = 1; $i <= $cp; ++$i) {
            if (($i - 1) == $s_page) {
                echo ' ' . $i . ' ';
            } else {
                echo ' <a href="' . $_SERVER['PHP_SELF'] . '?page=' . ($i - 1) . '">' . $i . '</a> ';
            }
        }
    }

    echo '</div></fieldset></div>';
}
echo '</div></div></div>';

$footer_style = 'index';
require_once PUN_ROOT . 'footer.php';

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
