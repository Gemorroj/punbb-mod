<?php
define('PUN_ROOT', './');
require PUN_ROOT . 'include/common.php';


// Load the search.php language file
require PUN_ROOT . 'lang/' . $pun_user['language'] . '/search.php';


if (!$pun_user['g_read_board']) {
    message($lang_common['No view']);
} else if (!$pun_user['g_search']) {
    message($lang_search['No search permission']);
}


// Figure out what to do :-)
if (isset($_GET['action']) || isset($_GET['search_id'])) {
    $forum = (isset($_GET['forum'])) ? intval($_GET['forum']) : -1;
    $sort_dir = (isset($_GET['sort_dir'])) ? (($_GET['sort_dir'] == 'DESC') ? 'DESC' : 'ASC') : 'DESC';
    if (isset($search_id)) {
        unset($search_id);
    }

    // If a search_id was supplied
    if (isset($_GET['search_id'])) {
        $search_id = intval($_GET['search_id']);
        if ($search_id < 1) {
            message($lang_common['Bad request']);
        }
    } else if ($_GET['action'] == 'search') {
        // If it's a regular search (keywords and/or author)

        // UTF FIX BEGIN
        $keywords = (isset($_GET['keywords'])) ? mb_strtolower(trim($_GET['keywords'])) : null;
        $author = (isset($_GET['author'])) ? mb_strtolower(trim($_GET['author'])) : null;

        if (preg_match('#^[\*%]+$#', $keywords) || mb_strlen(str_replace(array('*', '%'), '', $keywords)) < 3) {
            $keywords = null;
        }

        if (preg_match('#^[\*%]+$#', $author) || mb_strlen(str_replace(array('*', '%'), '', $author)) < 3) {
            $author = null;
        }
        // UTF FIX END


        if (!$keywords && !$author) {
            message($lang_search['No terms']);
        }

        if ($author) {
            $author = str_replace('*', '%', $author);
        }

        $show_as = (isset($_GET['show_as'])) ? $_GET['show_as'] : 'posts';
        $sort_by = intval($_GET['sort_by']);
        $search_in = (!isset($_GET['search_in']) || $_GET['search_in'] == 'all') ? 0 : (($_GET['search_in'] == 'message') ? 1 : -1);
    } else if ($_GET['action'] == 'show_user') {
        // If it's a user search (by id)
        $user_id = intval($_GET['user_id']);
        if ($user_id < 2) {
            message($lang_common['Bad request']);
        }
    } else {
        if ($_GET['action'] != 'show_new' && $_GET['action'] != 'show_24h' && $_GET['action'] != 'show_unanswered' && $_GET['action'] != 'show_subscriptions') {
            message($lang_common['Bad request']);
        }
    }


    // If a valid search_id was supplied we attempt to fetch the search results from the db
    if (isset($search_id)) {
        $ident = ($pun_user['is_guest']) ? get_remote_address() : $pun_user['username'];

        $result = $db->query('
            SELECT search_data
            FROM ' . $db->prefix . 'search_cache
            WHERE id=' . $search_id . '
            AND ident=\'' . $db->escape($ident) . '\'
        ') or error('Unable to fetch search results', __FILE__, __LINE__, $db->error());

        if ($row = $db->fetch_assoc($result)) {
            $temp = unserialize($row['search_data']);

            $search_results = $temp['search_results'];
            $num_hits = $temp['num_hits'];
            $sort_by = $temp['sort_by'];
            $sort_dir = $temp['sort_dir'];
            $show_as = $temp['show_as'];

            unset($temp);
        } else {
            message($lang_search['No hits']);
        }
    } else {
        $keyword_results = $author_results = array();

        // Search a specific forum?
        $forum_sql = ($forum != -1 || ($forum == -1 && !$pun_config['o_search_all_forums'] && $pun_user['g_id'] >= PUN_GUEST)) ? ' AND t.forum_id = ' . $forum : '';

        if (isset($author) && $author != null || isset($keywords) && $keywords != null) {
            // If it's a search for keywords
            if (isset($keywords) && $keywords != null) {
                $stopwords = file(PUN_ROOT . 'lang/' . $pun_user['language'] . '/stopwords.txt');
                $stopwords = array_map('trim', $stopwords);

                // Filter out non-alphabetical chars
                $keywords = str_replace(
                    array('^', '$', '&', '(', ')', '<', '>', '`', "'", '"', '|', ',', '@', '_', '?', '%', '~', '[', ']', '{', '}', ':', '\\', '/', '=', '#', "'", ';', '!', chr(239)),
                    array(' ', ' ', ' ', ' ', ' ', ' ', ' ', '', '', ' ', ' ', ' ', ' ', '', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', '', ' ', ' ', ' ', ' ', ' ', ' ', ' '),
                    $keywords
                );

                // Strip out excessive whitespace
                $keywords = trim(preg_replace('#\s+#', ' ', $keywords));

                // Fill an array with all the words
                $keywords_array = explode(' ', $keywords);

                if (!$keywords_array) {
                    message($lang_search['No hits']);
                }

                while (list($i, $word) = each($keywords_array)) {
                    $num_chars = mb_strlen($word);

                    if ($word !== 'or' && ($num_chars < 3 || $num_chars > 20 || in_array($word, $stopwords))) {
                        unset($keywords_array[$i]);
                    }
                }

                // Should we search in message body or topic subject specifically?
                $search_in_cond = ($search_in) ? (($search_in > 0) ? ' AND m.subject_match = 0' : ' AND m.subject_match = 1') : '';


                $word_count = 0;
                $match_type = 'and';
                $result_list = array();
                reset($keywords_array);
                while (list(, $cur_word) = each($keywords_array)) {
                    switch ($cur_word) {
                        case 'and':
                        case 'or':
                        case 'not':
                            $match_type = $cur_word;
                            break;

                        default:
                            $cur_word = $db->escape(str_replace('*', '%', $cur_word));
                            $sql = '
                                SELECT m.post_id
                                FROM ' . $db->prefix . 'search_words AS w
                                INNER JOIN ' . $db->prefix . 'search_matches AS m ON m.word_id = w.id
                                WHERE w.word LIKE \'' . $cur_word . '\'' . $search_in_cond;


                            $result = $db->query($sql, true) or error('Unable to search for posts', __FILE__, __LINE__, $db->error());

                            $row = array();
                            while ($temp = $db->fetch_row($result)) {
                                $row[$temp[0]] = 1;

                                if (!$word_count) {
                                    $result_list[$temp[0]] = 1;
                                } else if ($match_type == 'or') {
                                    $result_list[$temp[0]] = 1;
                                } else if ($match_type == 'not') {
                                    $result_list[$temp[0]] = 0;
                                }
                            }

                            if ($match_type == 'and' && $word_count) {
                                reset($result_list);
                                while (list($post_id,) = each($result_list)) {
                                    if (!isset($row[$post_id])) {
                                        $result_list[$post_id] = 0;
                                    }
                                }
                            }

                            ++$word_count;
                            $db->free_result($result);
                            break;
                    }
                }

                reset($result_list);
                while (list($post_id, $matches) = each($result_list)) {
                    if ($matches) {
                        $keyword_results[] = $post_id;
                    }
                }

                unset($result_list);
            }

            // If it's a search for author name (and that author name isn't Guest)
            if ($author && strcasecmp($author, 'Guest') && strcasecmp($author, $lang_common['Guest'])) {
                $result = $db->query('
                    SELECT id
                    FROM ' . $db->prefix . 'users
                    WHERE username LIKE \'' . $db->escape($author) . '\'
                ') or error('Unable to fetch users', __FILE__, __LINE__, $db->error());

                if ($db->num_rows($result)) {
                    $user_ids = '';
                    while ($row = $db->fetch_row($result)) {
                        $user_ids .= (($user_ids) ? ',' : '') . $row[0];
                    }

                    $result = $db->query('
                        SELECT id
                        FROM ' . $db->prefix . 'posts
                        WHERE poster_id IN(' . $user_ids . ')
                    ') or error('Unable to fetch matched posts list', __FILE__, __LINE__, $db->error());

                    $search_ids = array();
                    while ($row = $db->fetch_row($result)) {
                        $author_results[] = $row[0];
                    }

                    $db->free_result($result);
                }
            }


            if ($author && $keywords) {
                // If we searched for both keywords and author name we want the intersection between the results
                $search_ids = array_intersect($keyword_results, $author_results);
                unset($keyword_results, $author_results);
            } else if ($keywords) {
                $search_ids = $keyword_results;
            } else {
                $search_ids = $author_results;
            }

            if (!$search_ids) {
                message($lang_search['No hits']);
            }

            if ($show_as == 'topics') {
                $result = $db->query('
                    SELECT t.id
                    FROM ' . $db->prefix . 'posts AS p
                    INNER JOIN ' . $db->prefix . 'topics AS t ON t.id=p.topic_id
                    INNER JOIN ' . $db->prefix . 'forums AS f ON f.id=t.forum_id
                    LEFT JOIN ' . $db->prefix . 'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id=' . $pun_user['g_id'] . ')
                    WHERE (fp.read_forum IS NULL OR fp.read_forum=1)
                    AND p.id IN(' . implode(',', $search_ids) . ')
                    ' . $forum_sql . '
                    GROUP BY t.id
                ', true) or error('Unable to fetch topic list', __FILE__, __LINE__, $db->error());

                $search_ids = array();
                while ($row = $db->fetch_row($result)) {
                    $search_ids[] = $row[0];
                }

                $db->free_result($result);

                $num_hits = sizeof($search_ids);
            } else {
                $result = $db->query('
                    SELECT p.id
                    FROM ' . $db->prefix . 'posts AS p
                    INNER JOIN ' . $db->prefix . 'topics AS t ON t.id=p.topic_id
                    INNER JOIN ' . $db->prefix . 'forums AS f ON f.id=t.forum_id
                    LEFT JOIN ' . $db->prefix . 'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id=' . $pun_user['g_id'] . ')
                    WHERE (fp.read_forum IS NULL OR fp.read_forum=1)
                    AND p.id IN(' . implode(',', $search_ids) . ')
                    ' . $forum_sql
                    , true) or error('Unable to fetch topic list', __FILE__, __LINE__, $db->error());

                $search_ids = array();
                while ($row = $db->fetch_row($result)) {
                    $search_ids[] = $row[0];
                }

                $db->free_result($result);

                $num_hits = sizeof($search_ids);
            }
        } else if ($_GET['action'] == 'show_new' || $_GET['action'] == 'show_24h' || $_GET['action'] == 'show_user' || $_GET['action'] == 'show_subscriptions' || $_GET['action'] == 'show_unanswered') {
            // If it's a search for new posts
            if ($_GET['action'] == 'show_new') {
                if ($pun_user['is_guest']) {
                    message($lang_common['No permission']);
                }

                $result = $db->query('
                    SELECT t.id
                    FROM ' . $db->prefix . 'topics AS t
                    INNER JOIN ' . $db->prefix . 'forums AS f ON f.id=t.forum_id
                    LEFT JOIN ' . $db->prefix . 'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id=' . $pun_user['g_id'] . ')
                    WHERE (fp.read_forum IS NULL OR fp.read_forum=1)
                    AND t.last_post>' . $pun_user['last_visit'] . '
                    AND t.moved_to IS NULL
                ') or error('Unable to fetch topic list', __FILE__, __LINE__, $db->error());
                $num_hits = $db->num_rows($result);

                if (!$num_hits) {
                    message($lang_search['No new posts']);
                }
            } else if ($_GET['action'] == 'show_24h') {
                // If it's a search for todays posts
                $result = $db->query('
                    SELECT t.id
                    FROM ' . $db->prefix . 'topics AS t
                    INNER JOIN ' . $db->prefix . 'forums AS f ON f.id=t.forum_id
                    LEFT JOIN ' . $db->prefix . 'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id=' . $pun_user['g_id'] . ')
                    WHERE (fp.read_forum IS NULL OR fp.read_forum=1)
                    AND t.last_post>' . ($_SERVER['REQUEST_TIME'] - 86400) . '
                    AND t.moved_to IS NULL
                ') or error('Unable to fetch topic list', __FILE__, __LINE__, $db->error());
                $num_hits = $db->num_rows($result);

                if (!$num_hits) {
                    message($lang_search['No recent posts']);
                }
            } else if ($_GET['action'] == 'show_user') {
                // If it's a search for posts by a specific user ID
                $result = $db->query('
                    SELECT t.id
                    FROM ' . $db->prefix . 'topics AS t
                    INNER JOIN ' . $db->prefix . 'posts AS p ON t.id=p.topic_id
                    INNER JOIN ' . $db->prefix . 'forums AS f ON f.id=t.forum_id
                    LEFT JOIN ' . $db->prefix . 'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id=' . $pun_user['g_id'] . ')
                    WHERE (fp.read_forum IS NULL OR fp.read_forum=1)
                    AND p.poster_id=' . $user_id . '
                    GROUP BY t.id
                ') or error('Unable to fetch topic list', __FILE__, __LINE__, $db->error());
                $num_hits = $db->num_rows($result);

                if (!$num_hits) {
                    message($lang_search['No user posts']);
                }
            } else if ($_GET['action'] == 'show_subscriptions') {
                // If it's a search for subscribed topics
                if ($pun_user['is_guest']) {
                    message($lang_common['Bad request']);
                }

                $result = $db->query('
                    SELECT t.id
                    FROM ' . $db->prefix . 'topics AS t
                    INNER JOIN ' . $db->prefix . 'subscriptions AS s ON (t.id=s.topic_id AND s.user_id=' . $pun_user['id'] . ')
                    INNER JOIN ' . $db->prefix . 'forums AS f ON f.id=t.forum_id
                    LEFT JOIN ' . $db->prefix . 'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id=' . $pun_user['g_id'] . ')
                    WHERE (fp.read_forum IS NULL OR fp.read_forum=1)
                ') or error('Unable to fetch topic list', __FILE__, __LINE__, $db->error());
                $num_hits = $db->num_rows($result);

                if (!$num_hits) {
                    message($lang_search['No subscriptions']);
                }
            } else {
                // If it's a search for unanswered posts
                $result = $db->query('
                    SELECT t.id
                    FROM ' . $db->prefix . 'topics AS t
                    INNER JOIN ' . $db->prefix . 'forums AS f ON f.id=t.forum_id
                    LEFT JOIN ' . $db->prefix . 'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id=' . $pun_user['g_id'] . ')
                    WHERE (fp.read_forum IS NULL OR fp.read_forum=1)
                    AND t.num_replies=0
                    AND t.moved_to IS NULL
                ') or error('Unable to fetch topic list', __FILE__, __LINE__, $db->error());
                $num_hits = $db->num_rows($result);

                if (!$num_hits) {
                    message($lang_search['No unanswered']);
                }
            }

            // We want to sort things after last post
            $sort_by = 4;

            $search_ids = array();
            while ($row = $db->fetch_row($result)) {
                $search_ids[] = $row[0];
            }

            $db->free_result($result);

            $show_as = 'topics';
        } else {
            message($lang_common['Bad request']);
        }

        // Prune "old" search results
        $old_searches = array();
        $result = $db->query('SELECT ident FROM ' . $db->prefix . 'online') or error('Unable to fetch online list', __FILE__, __LINE__, $db->error());

        if ($db->num_rows($result)) {
            while ($row = $db->fetch_row($result)) {
                $old_searches[] = '\'' . $db->escape($row[0]) . '\'';
            }

            $db->query('DELETE FROM ' . $db->prefix . 'search_cache WHERE ident NOT IN(' . implode(',', $old_searches) . ')') or error('Unable to delete search results', __FILE__, __LINE__, $db->error());
        }

        // Final search results
        $search_results = implode(',', $search_ids);

        // Fill an array with our results and search properties
        $temp['search_results'] = $search_results;
        $temp['num_hits'] = $num_hits;
        $temp['sort_by'] = $sort_by;
        $temp['sort_dir'] = $sort_dir;
        $temp['show_as'] = $show_as;
        $temp = serialize($temp);
        $search_id = mt_rand(1, mt_getrandmax());

        $ident = ($pun_user['is_guest']) ? get_remote_address() : $pun_user['username'];

        $db->query('INSERT INTO ' . $db->prefix . 'search_cache (id, ident, search_data) VALUES(' . $search_id . ', \'' . $db->escape($ident) . '\', \'' . $db->escape($temp) . '\')') or error('Unable to insert search results', __FILE__, __LINE__, $db->error());

        if ($_GET['action'] != 'show_new' && $_GET['action'] != 'show_24h') {
            $db->end_transaction();
            $db->close();

            // Redirect the user to the cached result page
            redirect('search.php?search_id=' . $search_id);
        }
    }


    // Fetch results to display
    if ($search_results) {
        switch ($sort_by) {
            case 1:
                $sort_by_sql = ($show_as == 'topics') ? 't.poster' : 'p.poster';
                break;

            case 2:
                $sort_by_sql = 't.subject';
                break;

            case 3:
                $sort_by_sql = 't.forum_id';
                break;

            case 4:
                $sort_by_sql = 't.last_post';
                break;

            default:
                $sort_by_sql = ($show_as == 'topics') ? 't.posted' : 'p.posted';
                break;
        }

        if ($show_as == 'posts') {
            $sql = '
                SELECT p.id AS pid, p.poster AS pposter, p.posted AS pposted, p.poster_id, p.message AS message, t.id AS tid, t.poster, t.subject, t.last_post, t.last_post_id, t.last_poster, t.num_replies, t.forum_id
                FROM ' . $db->prefix . 'posts AS p
                INNER JOIN ' . $db->prefix . 'topics AS t ON t.id=p.topic_id
                WHERE p.id IN(' . $search_results . ')
                ORDER BY ' . $sort_by_sql;
        } else {
            $sql = '
                SELECT t.id AS tid, t.poster, t.subject, t.last_post, t.last_post_id, t.last_poster, t.num_replies, t.closed, t.forum_id
                FROM ' . $db->prefix . 'topics AS t
                WHERE t.id IN(' . $search_results . ')
                ORDER BY ' . $sort_by_sql;
        }

        // Determine the topic or post offset (based on $_GET['p'])
        $per_page = ($show_as == 'posts') ? $pun_user['disp_posts'] : $pun_user['disp_topics'];
        $num_pages = ceil($num_hits / $per_page);

        $_GET['p'] = isset($_GET['p']) ? intval($_GET['p']) : 1;
        $p = ($_GET['p'] <= 1 || $_GET['p'] > $num_pages) ? 1 : $_GET['p'];
        $start_from = $per_page * ($p - 1);

        // Generate paging links
        if (isset($_GET['action']) && $_GET['action'] == 'all') {
            $p = $num_pages + 1;
            $per_page = $num_hits;
        }

        $paging_links = $lang_common['Pages'] . ': ' . paginate($num_pages, $p, 'search.php?search_id=' . $search_id);

        $sql .= ' ' . $sort_dir . ' LIMIT ' . $start_from . ', ' . $per_page;

        $result = $db->query($sql) or error('Unable to fetch search results', __FILE__, __LINE__, $db->error());

        $search_set = array();
        while ($row = $db->fetch_assoc($result)) {
            $search_set[] = $row;
        }
        $db->free_result($result);

        $page_title = pun_htmlspecialchars($pun_config['o_board_title']) . ' / ' . $lang_search['Search results'];
        require_once PUN_ROOT . 'header.php';


        echo '<div class="linkst"><div class="inbox"><p class="pagelink">' . $paging_links . '</p></div></div>';


        //Set background switching on for show as posts
        $bg_switch = true;

        if ($show_as == 'topics') {
            echo '<div id="vf" class="blocktable"><h2><span>' . $lang_search['Search results'] . '</span></h2><div class="box"><div class="inbox"><table cellspacing="0"><thead><tr><th class="tcl" scope="col">' . $lang_common['Topic'] . '</th><th class="tc2" scope="col">' . $lang_common['Forum'] . '</th><th class="tc3" scope="col">' . $lang_common['Replies'] . '</th><th class="tcr" scope="col">' . $lang_common['Last post'] . '</th></tr></thead><tbody>';
        }

        // Fetch the list of forums
        $result = $db->query('SELECT `id`, `forum_name` FROM `' . $db->prefix . 'forums`') or error('Unable to fetch forum list', __FILE__, __LINE__, $db->error());

        $forum_list = array();
        while ($row = $db->fetch_row($result)) {
            $forum_list[] = $row;
        }

        // Finally, lets loop through the results and output them
        for ($i = 0, $all = sizeof($search_set); $i < $all; ++$i) {
            reset($forum_list);
            while (list(, $temp) = each($forum_list)) {
                if ($temp[0] == $search_set[$i]['forum_id']) {
                    $forum = '<a href="viewforum.php?id=' . $temp[0] . '">' . pun_htmlspecialchars($temp[1]) . '</a>';
                }
            }

            if ($pun_config['o_censoring'] == 1) {
                $search_set[$i]['subject'] = censor_words($search_set[$i]['subject']);
            }

            if ($show_as == 'posts') {
                $icon = '<div class="icon"><div class="nosize">' . $lang_common['Normal icon'] . '</div></div>';

                $subject = '<a href="viewtopic.php?id=' . $search_set[$i]['tid'] . '">' . pun_htmlspecialchars($search_set[$i]['subject']) . '</a>';
                if (!$pun_user['is_guest'] && $search_set[$i]['last_post'] > $pun_user['last_visit']) {
                    $icon = '<div class="icon inew"><div class="nosize">' . $lang_common['New icon'] . '</div></div>';
                }

                if ($pun_config['o_censoring'] == 1) {
                    $search_set[$i]['message'] = censor_words($search_set[$i]['message']);
                }

                include_once PUN_ROOT . 'include/parser.php';
                $message = parse_message($search_set[$i]['message'], 0);

                $pposter = pun_htmlspecialchars($search_set[$i]['pposter']);

                if ($search_set[$i]['poster_id'] > 1) {
                    $pposter = '<strong><a href="profile.php?id=' . $search_set[$i]['poster_id'] . '">' . $pposter . '</a></strong>';
                }

                $vtpost1 = (!$i) ? ' vtp1' : '';

                // Switch the background color for every message.
                $bg_switch = !$bg_switch;
                $vtbg = ($bg_switch) ? ' rowodd' : ' roweven';

                echo '<div class="blockpost searchposts' . $vtbg . '"><h2>' . $forum . ' &#187; ' . $subject . ' &#187; <a href="viewtopic.php?pid=' . $search_set[$i]['pid'] . '#p' . $search_set[$i]['pid'] . '">' . format_time($search_set[$i]['pposted']) . '</a></h2><div class="box"><div class="inbox"><div class="postleft"><dl><dt>' . $pposter . '</dt><dd>' . $lang_search['Replies'] . ': ' . $search_set[$i]['num_replies'] . '</dd><dd>' . $icon . '</dd><dd><p class="clearb"><a href="viewtopic.php?pid=' . $search_set[$i]['pid'] . '#p' . $search_set[$i]['pid'] . '">' . $lang_search['Go to post'] . '</a></p></dd></dl></div><div class="postright"><div class="postmsg">' . $message . '</div></div><div class="clearer"></div></div></div></div>';
            } else {
                $icon = '<div class="icon"><div class="nosize">' . $lang_common['Normal icon'] . '</div></div>';

                $icon_text = $lang_common['Normal icon'];
                $item_status = '';
                $icon_type = 'icon';

                $subject = '<a href="viewtopic.php?id=' . $search_set[$i]['tid'] . '">' . pun_htmlspecialchars($search_set[$i]['subject']) . '</a> <span class="byuser">' . $lang_common['by'] . '&#160;' . pun_htmlspecialchars($search_set[$i]['poster']) . '</span>';

                if ($search_set[$i]['closed']) {
                    $icon_text = $lang_common['Closed icon'];
                    $item_status = 'iclosed';
                }

                if (!$pun_user['is_guest'] && $search_set[$i]['last_post'] > $pun_user['last_visit']) {
                    $icon_text .= ' ' . $lang_common['New icon'];
                    $item_status .= ' inew';
                    $icon_type = 'icon inew';
                    $subject = '<strong>' . $subject . '</strong>';
                    $subject_new_posts = '<span class="newtext">[ <a href="viewtopic.php?id=' . $search_set[$i]['tid'] . '&amp;action=new" title="' . $lang_common['New posts info'] . '">' . $lang_common['New posts'] . '</a> ]</span>';
                } else {
                    $subject_new_posts = null;
                }


                $num_pages_topic = ceil(($search_set[$i]['num_replies'] + 1) / $pun_user['disp_posts']);

                if ($num_pages_topic > 1) {
                    $subject_multipage = '[ ' . paginate($num_pages_topic, -1, 'viewtopic.php?id=' . $search_set[$i]['tid']) . ' ]';
                } else {
                    $subject_multipage = null;
                }

                // Should we show the "New posts" and/or the multipage links?
                if (!empty($subject_new_posts) || !empty($subject_multipage)) {
                    $subject .= '&#160; ' . (!empty($subject_new_posts) ? $subject_new_posts : '');
                    $subject .= !empty($subject_multipage) ? ' ' . $subject_multipage : '';
                }

                echo '<tr' . ($item_status ? ' class="' . trim($item_status) . '"' : '') . '><td class="tcl"><div class="intd"><div class="' . $icon_type . '"><div class="nosize">' . trim($icon_text) . '</div></div><div class="tclcon">' . $subject . '</div></div></td><td class="tc2">' . $forum . '</td><td class="tc3">' . $search_set[$i]['num_replies'] . '</td><td class="tcr"><a href="viewtopic.php?pid=' . $search_set[$i]['last_post_id'] . '#p' . $search_set[$i]['last_post_id'] . '">' . format_time($search_set[$i]['last_post']) . '</a> ' . $lang_common['by'] . '&#160;' . pun_htmlspecialchars($search_set[$i]['last_poster']) . '</td></tr>';

            }
        }

        if ($show_as == 'topics') {
            echo '</tbody></table></div></div></div>';
        }


        echo '<div class="' . (($show_as == 'topics') ? 'linksb' : 'postlinksb') . '"><div class="inbox"><p class="pagelink">' . $paging_links . '</p></div></div>';

        $footer_style = 'search';
        require_once PUN_ROOT . 'footer.php';
    } else {
        message($lang_search['No hits']);
    }
}


$page_title = pun_htmlspecialchars($pun_config['o_board_title']) . ' / ' . $lang_search['Search'];
$focus_element = array('search', 'keywords');
require_once PUN_ROOT . 'header.php';


echo '<div id="searchform" class="blockform"><h2><span>' . $lang_search['Search'] . '</span></h2><div class="box"><form id="search" method="get" action="search.php?"><div class="inform"><fieldset><legend>' . $lang_search['Search criteria legend'] . '</legend><div class="infldset"><input type="hidden" name="action" value="search" /><label class="conl">' . $lang_search['Keyword search'] . '<br /><input type="text" name="keywords" size="40" maxlength="100" /><br /></label><label class="conl">' . $lang_search['Author search'] . '<br /><input id="author" type="text" name="author" size="25" maxlength="25" /><br /></label><p class="clearb">' . $lang_search['Search info'] . '</p></div></fieldset></div><div class="inform"><fieldset><legend>' . $lang_search['Search in legend'] . '</legend><div class="infldset"><label class="conl">' . $lang_search['Forum search'] . '<br /><select id="forum" name="forum">';

if ($pun_config['o_search_all_forums'] == 1 || $pun_user['g_id'] < PUN_GUEST) {
    echo '<option value="-1">' . $lang_search['All forums'] . '</option>';
}

$result = $db->query('
    SELECT c.id AS cid, c.cat_name, f.id AS fid, f.forum_name, f.redirect_url
    FROM ' . $db->prefix . 'categories AS c
    INNER JOIN ' . $db->prefix . 'forums AS f ON c.id=f.cat_id
    LEFT JOIN ' . $db->prefix . 'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id=' . $pun_user['g_id'] . ')
    WHERE (fp.read_forum IS NULL OR fp.read_forum=1)
    AND f.redirect_url IS NULL
    ORDER BY c.disp_position, c.id, f.disp_position
', true) or error('Unable to fetch category/forum list', __FILE__, __LINE__, $db->error());

if ($db->num_rows($result)) {
    $cur_category = 0;
    while ($cur_forum = $db->fetch_assoc($result)) {
        // A new category since last iteration?
        if ($cur_forum['cid'] != $cur_category) {
            if ($cur_category) {
                echo '</optgroup>';
            }

            echo '<optgroup label="' . pun_htmlspecialchars($cur_forum['cat_name']) . '">';
            $cur_category = $cur_forum['cid'];
        }

        echo '<option value="' . $cur_forum['fid'] . '">' . pun_htmlspecialchars($cur_forum['forum_name']) . '</option>';
    }
    echo '</optgroup>';
}

echo '</select><br /></label><label class="conl">' . $lang_search['Search in'] . '<br /><select id="search_in" name="search_in"><option value="all">' . $lang_search['Message and subject'] . '</option><option value="message">' . $lang_search['Message only'] . '</option><option value="topic">' . $lang_search['Topic only'] . '</option></select><br /></label><p class="clearb">' . $lang_search['Search in info'] . '</p></div></fieldset></div><div class="inform"><fieldset><legend>' . $lang_search['Search results legend'] . '</legend><div class="infldset"><label class="conl">' . $lang_search['Sort by'] . '<br /><select name="sort_by"><option value="0">' . $lang_search['Sort by post time'] . '</option><option value="1">' . $lang_search['Sort by author'] . '</option><option value="2">' . $lang_search['Sort by subject'] . '</option><option value="3">' . $lang_search['Sort by forum'] . '</option></select><br /></label><label class="conl">' . $lang_search['Sort order'] . '<br /><select name="sort_dir"><option value="DESC">' . $lang_search['Descending'] . '</option><option value="ASC">' . $lang_search['Ascending'] . '</option></select><br /></label><label class="conl">' . $lang_search['Show as'] . '<br /><select name="show_as"><option value="posts">' . $lang_search['Show as posts'] . '</option><option value="topics">' . $lang_search['Show as topics'] . '</option></select><br /></label><p class="clearb">' . $lang_search['Search results info'] . '</p></div></fieldset></div><p><input type="submit" name="search" value="' . $lang_common['Submit'] . '" accesskey="s" /></p></form></div></div>';

require_once PUN_ROOT . 'footer.php';
