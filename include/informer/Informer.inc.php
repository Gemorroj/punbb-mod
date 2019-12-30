<?php

class Informer
{
    private $_db;
    private $_pun_user;
    private $_lang;
    private $_pun_config;

    /**
     * Constructor.
     *
     * @param array $pun_user
     * @param array $lang
     * @param array $pun_config
     */
    public function __construct(DBLayer $db, $pun_user, $lang, $pun_config)
    {
        $this->_db = $db;
        $this->_pun_user = $pun_user;
        $this->_lang = $lang;
        $this->_pun_config = $pun_config;
    }

    /**
     * getConfig.
     *
     * @param array $args
     *
     * @throws Exception
     *
     * @return array
     */
    public function getConfig($args = null)
    {
        return array(
            'timezone' => $this->_pun_user['timezone'],
            'username' => $this->_pun_user['username'],
            'is_guest' => $this->_pun_user['is_guest'],
        );
    }

    /**
     * getForums.
     *
     * @param array $args
     *
     * @throws Exception
     *
     * @return array
     */
    public function getForums($args = null)
    {
        if (!$this->_pun_user['g_read_board']) {
            throw new Exception($this->_lang['No view']);
        }

        $r = $this->_db->query('
            SELECT f.id AS fid, f.last_post, f.last_post_id, f.last_poster, t.subject

            FROM '.$this->_db->prefix.'categories AS c
            INNER JOIN '.$this->_db->prefix.'forums AS f ON c.id=f.cat_id
            LEFT JOIN '.$this->_db->prefix.'topics AS t ON f.last_post_id=t.last_post_id
            LEFT JOIN '.$this->_db->prefix.'forum_perms AS fp ON (
                fp.forum_id=f.id
                AND fp.group_id='.$this->_pun_user['g_id'].'
                AND (fp.read_forum IS NULL OR fp.read_forum=1)
            )

            WHERE fp.read_forum IS NULL OR fp.read_forum=1

            ORDER BY NULL
        ');
        if (!$r) {
            throw new Exception($this->_db->error());
        }
        if (!$this->_db->num_rows($r)) {
            throw new Exception($this->_lang['Bad request']);
        }

        $data = array();
        while ($forum = $this->_db->fetch_assoc($r)) {
            $data[$forum['fid']] = array(
                'last_post_id' => $forum['last_post_id'],
                'subject' => $forum['subject'],
                'last_post_time' => $forum['last_post'],
                'last_poster' => $forum['last_poster'],
            );
        }

        return $data;
    }

    /**
     * setMessage.
     *
     * @param array $args
     *
     * @throws Exception
     *
     * @return array
     */
    public function setMessage($args)
    {
        $message = $args['message'];
        $topicId = $args['topicId'];
        $hideSmiles = $args['hideSmiles'];

        $topicId = intval($topicId);
        if ($topicId <= 0) {
            throw new Exception($this->_lang['Bad request']);
        }
        if ($this->_pun_user['is_guest']) {
            throw new Exception($this->_lang['Bad request']);
        }
        if ($this->_pun_user['last_post'] && ($_SERVER['REQUEST_TIME'] - $this->_pun_user['last_post']) < $this->_pun_user['g_post_flood']) {
            throw new Exception($this->_lang['Bad request']);
        }

        // Clean up message
        $message = pun_linebreaks(pun_trim($message));

        if (!$message) {
            throw new Exception($this->_lang['Bad request']);
        }
        if (mb_strlen($message) > 65535) {
            throw new Exception($this->_lang['Bad request']);
        }
        if (!$this->_pun_config['p_message_all_caps'] && mb_strtoupper($message) == $message && $this->_pun_user['g_id'] > PUN_MOD) {
            $message = ucwords(mb_strtolower($message));
        }
        convert_forum_url($message);

        // Insert the new post
        $r = $this->_db->query('
            INSERT INTO '.$this->_db->prefix.'posts (
                poster, poster_id, poster_ip, message, hide_smilies, posted, topic_id
            ) VALUES (
                \''.$this->_db->escape($this->_pun_user['username']).'\',
                '.$this->_pun_user['id'].',
                \''.get_remote_address().'\',
                \''.$this->_db->escape($message).'\',
                \''.intval($hideSmiles).'\',
                '.$_SERVER['REQUEST_TIME'].',
                '.$topicId.'
            )
        ');

        if (!$r) {
            throw new Exception($this->_db->error());
        }
        if (!$this->_db->affected_rows()) {
            throw new Exception($this->_lang['Bad request']);
        }

        $id = $this->_db->insert_id();

        // Count number of replies in the topic
        $result = $this->_db->query(
            '
            SELECT COUNT(1)
            FROM '.$this->_db->prefix.'posts
            WHERE topic_id='.$topicId
        );
        $num_replies = $this->_db->result($result, 0) - 1;

        // Update topic
        $this->_db->query(
            '
            UPDATE '.$this->_db->prefix.'topics
            SET num_replies='.$num_replies.',
            last_post='.$_SERVER['REQUEST_TIME'].',
            last_post_id='.$id.',
            last_poster=\''.$this->_db->escape($this->_pun_user['username']).'\'
            WHERE id='.$topicId
        );

        //update_search_index('post', $id, $message);

        $result = $this->_db->query(
            '
            SELECT f.id
            FROM '.$this->_db->prefix.'topics AS t
            INNER JOIN '.$this->_db->prefix.'forums AS f ON f.id=t.forum_id
            LEFT JOIN '.$this->_db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$this->_pun_user['g_id'].')
            WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND t.id='.$topicId
        );
        $forumId = $this->_db->result($result, 0);

        update_forum($forumId);

        return array(
            'message' => $this->_parseMessage($message, $hideSmiles),
            'poster' => $this->_pun_user['username'],
            'posted' => $_SERVER['REQUEST_TIME'],
        );
    }

    /**
     * getMessage.
     *
     * @param array $args
     *
     * @throws Exception
     *
     * @return array
     */
    public function getMessage($args)
    {
        $id = $args['id'];

        if (!$this->_pun_user['g_read_board']) {
            throw new Exception($this->_lang['No view']);
        }
        if (!$id || $id < 1 || !is_numeric($id)) {
            throw new Exception($this->_lang['Bad request']);
        }

        $r = $this->_db->query(
            '
            SELECT p.poster, p.message, p.hide_smilies, p.posted, p.topic_id
            FROM '.$this->_db->prefix.'posts AS p
            INNER JOIN '.$this->_db->prefix.'topics AS t ON t.id = p.topic_id
            LEFT JOIN '.$this->_db->prefix.'forum_perms AS fp ON (
                fp.forum_id = t.forum_id
                AND fp.group_id = '.$this->_pun_user['g_id'].'
                AND (fp.read_forum IS NULL OR fp.read_forum = 1)
            )
            WHERE p.id = '.$id
        );

        if (!$r) {
            throw new Exception($this->_db->error());
        }
        if (!$this->_db->num_rows($r)) {
            throw new Exception($this->_lang['Bad request']);
        }

        $data = $this->_db->fetch_assoc($r);

        return array(
            'message' => $this->_parseMessage($data['message'], $data['hide_smilies']),
            'poster' => $data['poster'],
            'posted' => $data['posted'],
            'topic_id' => $data['topic_id'],
        );
    }

    /**
     * getPrivateMessage.
     *
     * @param array $args
     *
     * @throws Exception
     *
     * @return array
     */
    public function getPrivateMessage($args)
    {
        $id = $args['id'];

        if ($this->_pun_user['is_guest'] || !$this->_pun_user['g_pm'] || !$this->_pun_user['messages_enable'] || !$this->_pun_config['o_pms_enabled']) {
            throw new Exception($this->_lang['No view']);
        }
        if (!$id || $id < 1 || !is_numeric($id)) {
            throw new Exception($this->_lang['Bad request']);
        }

        $r = $this->_db->query(
            '
            SELECT m.subject, m.message, m.smileys, m.posted, m.sender
            FROM '.$this->_db->prefix.'messages AS m
            WHERE m.owner = '.$this->_pun_user['id'].'
            AND m.id = '.$id
        );

        if (!$r) {
            throw new Exception($this->_db->error());
        }
        if (!$this->_db->num_rows($r)) {
            throw new Exception($this->_lang['Bad request']);
        }

        $data = $this->_db->fetch_assoc($r);

        return array(
            'subject' => $data['subject'],
            'message' => $this->_parseMessage($data['message'], $data['smileys']),
            'poster' => $data['sender'],
            'posted' => $data['posted'],
        );
    }

    /**
     * getPrivateMessages.
     *
     * @param array $args
     *
     * @throws Exception
     *
     * @return array
     */
    public function getPrivateMessages($args)
    {
        $limit = $args['limit'];

        if ($this->_pun_user['is_guest'] || !$this->_pun_user['g_pm'] || !$this->_pun_user['messages_enable'] || !$this->_pun_config['o_pms_enabled']) {
            throw new Exception($this->_lang['No view']);
        }
        if (!$limit || $limit < 1 || !is_numeric($limit)) {
            throw new Exception($this->_lang['Bad request']);
        }

        $r = $this->_db->query(
            '
            SELECT m.id, m.subject, m.message, m.smileys, m.posted, m.sender
            FROM '.$this->_db->prefix.'messages AS m
            WHERE m.owner = '.$this->_pun_user['id'].'
            ORDER BY m.id DESC
            LIMIT '.$limit
        );

        if (!$r) {
            throw new Exception($this->_db->error());
        }
        if (!$this->_db->num_rows($r)) {
            return array();
        }

        $out = array();
        while ($data = $this->_db->fetch_assoc($r)) {
            $out[$data['id']] = array(
                'subject' => $data['subject'],
                'message' => $this->_parseMessage($data['message'], $data['smileys']),
                'poster' => $data['sender'],
                'posted' => $data['posted'],
            );
        }

        return $out;
    }

    /**
     * _parseMessage.
     *
     * @param string $message
     * @param bool   $hide_smilies
     *
     * @return string
     */
    private function _parseMessage($message, $hide_smilies = false)
    {
        require_once dirname(__FILE__).'/../parser.php';

        return parse_message($message, $hide_smilies);
    }
}
