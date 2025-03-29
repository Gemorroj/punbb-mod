<?php

if (!\defined('PUN_ROOT')) {
    exit;
}

// Makes sure the mod is properly configured.
function check_mod_config()
{
    global $pun_config;

    if (!isset($pun_config['file_upload_path'])) {
        return false;
    }

    if (!\is_dir(PUN_ROOT.$pun_config['file_upload_path'])) {
        return false;
    }

    if (!\is_writable(PUN_ROOT.$pun_config['file_upload_path'])) {
        return false;
    }

    return true;
}

// Gets the extension of a file. This can either be a filename, or a full path to a file.
function get_file_extension($filename)
{
    $filename = \basename($filename);
    if (false === ($p = \strrpos($filename, '.'))) {
        return $filename;
    }

    return \substr($filename, $p + 1);
}

// Is given file an image. Check by extension only.
function is_image_filename($filename)
{
    global $pun_config;

    return \in_array(\strtolower(\get_file_extension($filename)), \explode(',', $pun_config['file_image_ext']), true);
}

// Returns the contents of a directory (including files and subdirectories.
function get_dir_contents($dir)
{
    $contents = [];

    if (!\is_dir($dir)) {
        return $contents;
    }

    $dh = \opendir($dir);
    while (false !== ($file = \readdir($dh))) {
        if ('.' === $file[0]) {
            continue;
        }
        $contents[] = $file;
    }
    \closedir($dh);

    \natsort($contents);

    return $contents;
}

// Returns number of files there are in a directory.
function get_dir_file_count($dir)
{
    return \count(\get_dir_contents($dir));
}

// Used to check the upload directory for any problems.
function show_problems()
{
    global $pun_config;

    $log = [];

    $files_dir = PUN_ROOT.$pun_config['file_upload_path'];
    $thumb_dir = PUN_ROOT.$pun_config['file_thumb_path'];

    if (!\is_dir($files_dir)) {
        $log[] = 'Upload directory not found';
    } elseif (!\is_writable($files_dir)) {
        $log[] = 'Upload directory not writable';
    }

    if (!\is_dir($thumb_dir)) {
        $log[] = 'Thumbnails directory not found';
    } elseif (!\is_writable($thumb_dir)) {
        $log[] = 'Thumbnails directory not writable';
    }

    if (!\count($log)) {
        $log[] = 'No problems Found!';
    }

    return $log;
}

/*
********************************************************************************
** Deletes any files that do not belong to a valid post.
** Delete broken links to missing files.
********************************************************************************
*/
function delete_orphans()
{
    global $pun_config, $db;

    $log = [];

    if (!\check_mod_config()) {
        $log[] = 'File Upload is not configured correctly!';

        return $log;
    }

    // collect files
    $files_dir = $pun_config['file_upload_path'];
    $files = \get_dir_contents(PUN_ROOT.$files_dir);
    for ($i = 0, $l = \count($files); $i < $l; ++$i) {
        $files[$i] = $files_dir.$files[$i];
    }

    $result = $db->query('SELECT a.id, a.post_id, a.location, p.id AS pid FROM '.$db->prefix.'attachments AS a LEFT JOIN '.$db->prefix.'posts AS p ON a.post_id=p.id');
    if (!$result) {
        \error('Unable to execute query', __FILE__, __LINE__, $db->error());
    }

    // check every record
    while ($attachment = $db->fetch_assoc($result)) {
        // missing post for attachment
        if (!$attachment['pid']) {
            $log[] = 'Attachment #'.$attachment['id'].': No related post - Deleted';
            $db->query('DELETE FROM '.$db->prefix.'attachments WHERE id='.$attachment['id']) || \error('Unable delete attachment(s)', __FILE__, __LINE__, $db->error());
        } else {
            $idx = \array_search($attachment['location'], $files, true);

            // file not exists but record in 'attachments' is
            if (false === $idx) {
                $log[] = 'File "'.$attachment['location'].'" not found. Attathment #'.$attachment['id'].' from post #'.$attachment['post_id'].' will removed';
                $db->query('DELETE FROM '.$db->prefix.'attachments WHERE id='.$attachment['id']) || \error('Unable delete attachment(s)', __FILE__, __LINE__, $db->error());
            } else {
                // allright, this file attached well. remove them from list
                \array_splice($files, $idx, 1);
            }
        }
    }

    // delete all orhpaned files in upload folder
    while (\count($files) > 0) {
        $file = $files[0];
        if ('index.html' !== $file && '.htaccess' !== $file) {
            $log[] = 'File "'.$file.'": No related record - Deleted';
            \unlink($file);
        }
        \array_splice($files, 0, 1);
    }

    if (!\count($log)) {
        $log[] = 'No problems Found!';
    }

    return $log;
}

/*
********************************************************************************
** Deletes any files that do not belong to a valid post.
** Delete broken links to missing files.
********************************************************************************
*/
function delete_all_thumbnails()
{
    global $pun_config;

    $log = [];

    if (!\check_mod_config()) {
        $log[] = 'File Upload is not configured correctly!';

        return $log;
    }

    $thumbs_dir = $pun_config['file_thumb_path'];
    $thumbs = \get_dir_contents(PUN_ROOT.$thumbs_dir);

    while (\count($thumbs) > 0) {
        $file = $thumbs[0];
        if ('index.html' !== $file && '.htaccess' !== $file && $file[0].$file[1].$file[2].$file[3] !== 'err_') {
            $log[] = $file.' - Deleted';
            \unlink(PUN_ROOT.$thumbs_dir.$file);
        }
        \array_splice($thumbs, 0, 1);
    }

    if (!\count($log)) {
        $log[] = 'No files Found!';
    }

    return $log;
}

/*
********************************************************************************
** Deletes any files that do not belong to a valid post.
** Delete broken links to missing files.
**
** NOTE: I'm too lazy to implements step-by-step fixing. For big forums
** time limit overflow is possible.
********************************************************************************
*/
function fix_user_counters()
{
    global $pun_config, $db;

    $counters = [];
    $result = $db->query('SELECT poster_id, count(*) FROM '.$db->prefix.'attachments GROUP BY poster_id');
    if (!$result) {
        \error('Unable to count attachments.', __FILE__, __LINE__, $db->error());
    }
    while ($row = $db->fetch_row($result)) {
        $counters[] = $row;
    }
    $db->free_result($result);

    // erase counters for all users
    $db->query('UPDATE '.$db->prefix.'users SET num_files=0') || \error('Unable to clear user counters', __FILE__, __LINE__, $db->error());

    $updated = 0;
    $sizeof = \count($counters);
    $log = [];
    $log[] = $sizeof.' users has attachments';

    if ($sizeof) {
        for ($i = 0; $i < $sizeof; ++$i) {
            // update
            $db->query('UPDATE '.$db->prefix.'users SET num_files='.$counters[$i][1].' WHERE id='.$counters[$i][0]) || \error('Unable to update users', __FILE__, __LINE__, $db->error());
        }
    }

    return $log;
}

// Avoid to override existing files
function generate_unique_filename($dir, $postfix)
{
    while (true) {
        $newname = \md5(\uniqid('', true)).$postfix;
        if (!\is_file($dir.$newname)) {
            return $newname;
        }
    }
}

// Used when uploading images for a post.
function process_uploaded_files($tid, $pid, &$total_uploaded)
{
    global $pun_config, $lang_common, $lang_fu, $db, $file_limit, $message;

    if (!isset($_FILES['attach']['error']) || !\check_mod_config()) {
        return null;
    }

    $total_uploaded = 0;
    $result = null;

    $dest = $pun_config['file_upload_path'];
    $thmb = $pun_config['file_thumb_path'];
    $allowed_ext = $pun_config['file_allowed_ext'];
    $allowed_ext = \explode(',', $allowed_ext);
    $image_ext = $pun_config['file_image_ext'];
    $image_ext = \explode(',', $image_ext);

    // Upload all files
    $i = 0;
    $thumb_from = $thumb_to = [];

    foreach ($_FILES['attach']['error'] as $key => $error) {
        ++$i;
        if (\UPLOAD_ERR_OK === $error) {
            if ($file_limit <= 0) {
                break;
            }

            // Grab the tmp file location, and the original file name
            $mime = $_FILES['attach']['type'][$key];
            $tmp_name = $_FILES['attach']['tmp_name'][$key];
            // there are some PHP exploits with fake filenames
            // file_exists() is not secure in this case!
            if (!\is_uploaded_file($tmp_name)) {
                continue;
            }
            $orig_name = $_FILES['attach']['name'][$key];
            $size = $_FILES['attach']['size'][$key];
            $file_ext = \strtolower(\get_file_extension($orig_name));

            // Skip files with banned extensions
            if (!$file_ext || !\in_array($file_ext, $allowed_ext, true)) {
                $result .= $orig_name.' '.$lang_fu['Extension Banned'].'.<br />';

                continue;
            }

            // Skip files larger then max file size
            if ($size > $pun_config['file_max_size']) {
                $result .= $orig_name.' '.$lang_fu['Size Too Big'].'.<br />';

                continue;
            }

            if (\in_array($file_ext, $image_ext, true)) {
                // Skip files that have larger then allowed dimensions
                [$width, $height, $type, $attr] = \getimagesize($tmp_name);
                if (!$width || !$height) {
                    $result .= $orig_name.' '.$lang_fu['Not Image'].'.<br />';

                    continue;
                }
                if ($width > $pun_config['file_max_width'] || $height > $pun_config['file_max_height']) {
                    $result .= $orig_name.' '.$lang_fu['Dim Too Big'].'.<br />';

                    continue;
                }
                $dim = $width.'x'.$height;
            } else {
                $dim = null;
            }

            // save file to upload directory
            $store_name = \generate_unique_filename(PUN_ROOT.$dest, '.ext');
            \move_uploaded_file($tmp_name, PUN_ROOT.$dest.$store_name);
            \chmod($dest.$store_name, 0o644);

            // NOTE: post author and attachment author may differ (if attach in edit)
            $attach_poster = $GLOBALS['pun_user']['id'];
            $now = \time();
            $db->query('INSERT INTO '.$db->prefix.'attachments (poster_id, topic_id, post_id, uploaded, filename, mime, location, size, image_dim) VALUES (\''.$attach_poster.'\', \''.$tid.'\', \''.$pid.'\', '.$now.', \''.$db->escape($orig_name).'\', \''.$db->escape($mime).'\', \''.$db->escape($dest.$store_name).'\', \''.$size.'\', \''.$dim.'\')') || \error('Unable to insert attachment record into database.', __FILE__, __LINE__, $db->error());
            $aid = $db->insert_id();

            $thumb_from[] = '::thumb$'.$i.'::';
            $thumb_to[] = '::thumb'.$aid.'::';

            ++$total_uploaded;
            --$file_limit;
        } else {
            switch ($error) {
                case \UPLOAD_ERR_INI_SIZE:
                    $result .= 'File #'.$i.' - ERROR: exceeds the upload_max_filesize<br />';

                    break;

                case \UPLOAD_ERR_FORM_SIZE:
                    $result .= 'File #'.$i.' - ERROR: exceeds the form MAX_FILE_SIZE<br />';

                    break;

                case \UPLOAD_ERR_PARTIAL:
                    $result .= 'File #'.$i.' - ERROR: partially uploaded<br />';

                    break;

                case \UPLOAD_ERR_NO_FILE:
                    // no file specified in input field
                    break;

                default:
                    $result .= 'File #'.$i.' - ERROR: '.$error.'<br />';

                    break;
            }
        }
    }

    if ($total_uploaded) {
        $result .= '<br />'.$lang_fu['Uploaded'].' '.$total_uploaded.' '.$lang_fu['files'].'<br /><br />';
    }

    // translate #i to ::thumbNN::
    if (\str_contains($message, '::thumb$')) {
        $message = \str_replace($thumb_from, $thumb_to, $message);
        $db->query('UPDATE '.$db->prefix.'posts SET message=\''.$db->escape($message).'\' WHERE id='.$pid) || \error('Unable to update post', __FILE__, __LINE__, $db->error());
    }

    return $result;
}

// Deletes files during a post edit (if the user marks any images for deletion)
function process_deleted_files($pid, &$total_deleted)
{
    global $pun_config, $db, $lang_fu, $file_limit;

    $result = '';

    if (!isset($_POST['delete_image']) || !\check_mod_config()) {
        return $result;
    }

    $aid_list_str = \implode(',', \array_map('intval', $_POST['delete_image']));
    $thumb_dir = PUN_ROOT.$pun_config['file_thumb_path'];
    $thumb_files = \get_dir_contents($thumb_dir);

    // check post_id to prevent hack
    $result_attach = $db->query('SELECT af.id, af.location FROM '.$db->prefix.'attachments AS af WHERE af.post_id='.$pid.' AND af.id IN ('.$aid_list_str.')');
    if (!$result_attach) {
        \error('Unable to fetch attachments to delete', __FILE__, __LINE__, $db->error());
    }
    $aid_list = [];

    $total_deleted = 0;
    while ([$aid, $location] = $db->fetch_row($result_attach)) {
        $aid_list[] = $aid;
        // Remove attachment
        \unlink($location);
        // Remove all it's thumbnails
        foreach ($thumb_files as $thumb_file) {
            if (\preg_match('/^'.$aid.'(-[0-9x]*)?\.jpg$/i', $thumb_file)) {
                \unlink(PUN_ROOT.$pun_config['file_thumb_path'].$thumb_file);
            }
        }
    }

    if ($aid_list) {
        $total_deleted = \count($aid_list);
        $aid_list = \implode(',', $aid_list);
        $db->query('DELETE FROM '.$db->prefix.'attachments WHERE id IN ('.$aid_list.')') || \error('Unable delete attachment(s)', __FILE__, __LINE__, $db->error());
        ++$file_limit;
    }

    if ($total_deleted) {
        $result .= '<br />'.$lang_fu['Deleted'].' '.$total_deleted.' '.$lang_fu['files'].'<br /><br />';
    }

    return $result;
}

// Deletes all images related to a post.
function delete_files($pid): void
{
    global $pun_config, $db;

    if (!\check_mod_config()) {
        return;
    }

    $thumb_dir = PUN_ROOT.$pun_config['file_thumb_path'];
    $thumb_files = \get_dir_contents($thumb_dir);

    $result_attach = $db->query('SELECT af.id, af.location FROM '.$db->prefix.'attachments AS af WHERE af.post_id='.$pid);
    if (!$result_attach) {
        \error('Unable to fetch attachments to delete', __FILE__, __LINE__, $db->error());
    }

    while ([$aid, $location] = $db->fetch_row($result_attach)) {
        // Remove attachment
        \unlink($location);
        // Remove all it's thumbnails
        foreach ($thumb_files as $thumb_file) {
            if (\preg_match('/^'.$aid.'(-[0-9x]*)?\.jpg$/i', $thumb_file)) {
                \unlink($thumb_dir.$thumb_file);
            }
        }
    }
    $db->query('DELETE FROM '.$db->prefix.'attachments WHERE post_id='.$pid) || \error('Unable delete attachment(s)', __FILE__, __LINE__, $db->error());
}

//
// Delete all attachments linked to posts
// in: $post_ids - one post_id or comma-separated list of post_ids
//
function delete_post_attachments($post_ids): void
{
    global $db;

    if (!\str_contains($post_ids, ',')) {
        $result = $db->query('SELECT id, poster_id, location FROM '.$db->prefix.'attachments WHERE post_id='.$post_ids);
        if (!$result) {
            \error('Unable to fetch attachments', __FILE__, __LINE__, $db->error());
        }
    } else {
        $result = $db->query('SELECT id, poster_id, location FROM '.$db->prefix.'attachments WHERE post_id IN('.$post_ids.')');
        if (!$result) {
            \error('Unable to fetch attachments', __FILE__, __LINE__, $db->error());
        }
    }

    if ($db->num_rows($result)) {
        $att_ids = '';
        $poster_ids = [];
        $thumb_dir = PUN_ROOT.$GLOBALS['pun_config']['file_thumb_path'];
        $thumb_files = \get_dir_contents($thumb_dir);

        while ($row = $db->fetch_assoc($result)) {
            $att_ids .= ($att_ids) ? ','.$row['id'] : $row['id'];
            if (isset($poster_ids[$row['poster_id']])) {
                ++$poster_ids[$row['poster_id']];
            } else {
                $poster_ids[$row['poster_id']] = 1;
            }

            // Delete file and all it's possible thumbnails
            \unlink(PUN_ROOT.$row['location']);
            foreach ($thumb_files as $thumb_file) {
                if (\preg_match('/^'.$row['id'].'-[0-9x]+.*/i', $thumb_file)) {
                    \unlink($thumb_dir.$thumb_file);
                }
            }
        }
        $db->free_result($result);

        if ($att_ids) {
            // Delete attachment records
            $db->query('DELETE FROM '.$db->prefix.'attachments WHERE id IN('.$att_ids.')') || \error('Unable to delete attachments', __FILE__, __LINE__, $db->error());
            foreach ($poster_ids as $poster_id => $num_files) {
                // Fix user file counter
                $db->query('UPDATE '.$db->prefix.'users SET num_files=IF((num_files-'.$num_files.')>0, num_files-'.$num_files.', 0) WHERE id='.$poster_id) || \error('Unable to update users file counter', __FILE__, __LINE__, $db->error());
            }
        }
    }
}

/*
********************************************************************************
** Create thumbnail image and save result to file. Needs GD library!
** Thumbnail fits to size. JPEG used for output.
** When
** $do_cut==true - fit by short side and cut long side
** $do_cut==false - fit into box w/o any cuts
********************************************************************************
*/
function create_thumbnail($orig_fname, $thum_fname, $thumb_width = 100, $thumb_height = 100, $do_cut = false)
{
    $rgb = 0xFFFFFF;
    $quality = 80;
    $size = @\getimagesize($orig_fname);
    $src_x = $src_y = 0;

    if (false === $size) {
        return false;
    }

    $format = \strtolower(\substr($size['mime'], \strpos($size['mime'], '/') + 1));
    $icfunc = 'imagecreatefrom'.$format;
    if (!\function_exists($icfunc)) {
        return false;
    }

    $orig_img = $icfunc($orig_fname);
    if (($size[0] <= $thumb_width) && ($size[1] <= $thumb_height)) {
        // use original size
        $width = $size[0];
        $height = $size[1];
    } else {
        $width = $thumb_width;
        $height = $thumb_height;

        // calculate fit ratio
        $ratio_width = $size[0] / $thumb_width;
        $ratio_height = $size[1] / $thumb_height;

        if ($ratio_width < $ratio_height) {
            if ($do_cut) {
                $src_y = ($size[1] - $thumb_height * $ratio_width) / 2;
                $size[1] = $thumb_height * $ratio_width;
            } else {
                $width = $size[0] / $ratio_height;
                $height = $thumb_height;
            }
        } else {
            if ($do_cut) {
                $src_x = ($size[0] - $thumb_width * $ratio_height) / 2;
                $size[0] = $thumb_width * $ratio_height;
            } else {
                $width = $thumb_width;
                $height = $size[1] / $ratio_width;
            }
        }
    }

    $thum_img = \imagecreatetruecolor($width, $height);
    \imagefill($thum_img, 0, 0, $rgb);
    \imagecopyresampled($thum_img, $orig_img, 0, 0, $src_x, $src_y, $width, $height, $size[0], $size[1]);

    \imagejpeg($thum_img, $thum_fname, $quality);
    \flush();
    \imagedestroy($orig_img);
    \imagedestroy($thum_img);

    return true;
}

/*
********************************************************************************
** Wrappers for create_thumbnail()
**
** require_thumb_name - Compose name for thumbnail.
** require_thumb - Compose name for thumbnail and create it.
** handle_thumb_tag - Make sure that thumbnail created at post pre-parse time
********************************************************************************
*/
function require_thumb_name($aid, $width = 100, $height = 100, $do_cut = false)
{
    global $pun_config;

    return $pun_config['file_thumb_path'].$aid.'-'.$width.'x'.$height.(($do_cut) ? '-cut' : '').'.jpg';
}

function require_thumb($aid, $location, $width = 100, $height = 100, $do_cut = false)
{
    global $pun_config;

    if ($aid && \is_file($location)) {
        $thum_fname = \require_thumb_name($aid, $width, $height, $do_cut);
        if (!\is_file($thum_fname)) {
            @\set_time_limit(10);
            // if any error, create_thumbnail() will not call again, but
            // thumbnail will be copy of err_thumb.gif
            \copy(PUN_ROOT.$pun_config['file_thumb_path'].'err_thumb.gif', $thum_fname);
            \create_thumbnail($location, $thum_fname, $width, $height, $do_cut);
        }

        return $thum_fname;
    }

    return $pun_config['file_thumb_path'].'err_none.gif';
}

function handle_thumb_tag($aid)
{
    global $db;

    $result = $db->query('SELECT location FROM '.$db->prefix.'attachments WHERE id='.$aid);
    if (!$result) {
        \error('Unable to fetch attachment', __FILE__, __LINE__, $db->error());
    }
    if ($db->num_rows($result)) {
        $width = $GLOBALS['pun_config']['file_preview_width'];
        $height = $GLOBALS['pun_config']['file_preview_height'];
        [$location] = $db->fetch_row($result);
        \require_thumb($aid, $location, $width, $height);
    }

    return '::thumb'.$aid.'::'; // really tag not translated :)
}
