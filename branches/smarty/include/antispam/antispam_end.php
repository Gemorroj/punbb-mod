<?php
if ($is_spam) {
    $db->query('INSERT INTO ' . $db->prefix . 'spam_repository (post_id, message, pattern) VALUES(' . $new_pid . ', \'' . $db->escape($original_message) . '\', \'' . $db->escape($checkedspam['regexpr']) . '\' )') or error('Unable to save spam into repository', __FILE__, __LINE__, $db->error());
    $db->query('UPDATE ' . $db->prefix . 'spam_regexp SET matches = matches + 1 WHERE id=' . $checkedspam['id']) or error('Unable to save group id', __FILE__, __LINE__, $db->error());
    if (!$pun_user['is_guest']) {
        $new_spamid = $db->insert_id();
        $db->query('UPDATE ' . $db->prefix . 'spam_repository SET last_gid=' . $pun_user['g_id'] . ' WHERE id=' . $new_spamid) or error('Unable to save group id', __FILE__, __LINE__, $db->error());
        $db->query('UPDATE ' . $db->prefix . 'users SET group_id=' . $pun_config['o_spam_gid'] . ' WHERE id=' . $pun_user['id']) or error('Unable to update group id', __FILE__, __LINE__, $db->error());
    }
}
