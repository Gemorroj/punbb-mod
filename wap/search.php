<?php

\define('PUN_ROOT', '../');

require_once PUN_ROOT.'include/common.php';

require_once PUN_ROOT.'wap/header.php';

// Load the search.php language file
require_once PUN_ROOT.'lang/'.$pun_user['language'].'/search.php';

require_once PUN_ROOT.'include/parser.php';

if (!$pun_user['g_read_board']) {
    \wap_message($lang_common['No view']);
} elseif (!$pun_user['g_search']) {
    \wap_message($lang_search['No search permission']);
}

// Figure out what to do :-)
if (isset($_GET['action']) || isset($_GET['search_id'])) {
    $forum = (isset($_GET['forum'])) ? (int) ($_GET['forum']) : -1;
    $sort_dir = (isset($_GET['sort_dir'])) ? (('DESC' == $_GET['sort_dir']) ? 'DESC' : 'ASC') : 'DESC';
    if (isset($search_id)) {
        unset($search_id);
    }

    // If a search_id was supplied
    if (isset($_GET['search_id'])) {
        $search_id = (int) $_GET['search_id'];
        if ($search_id < 1) {
            \wap_message($lang_common['Bad request']);
        }
    } elseif ('search' == $_GET['action']) {
        // If it's a regular search (keywords and/or author)

        // UTF FIX BEGIN
        $keywords = (isset($_GET['keywords'])) ? \mb_strtolower(\trim($_GET['keywords'])) : null;
        $author = (isset($_GET['author'])) ? \mb_strtolower(\trim($_GET['author'])) : null;

        if (\preg_match('#^[\*%]+$#', $keywords) || \mb_strlen(\str_replace(['*', '%'], '', $keywords)) < 3) {
            $keywords = null;
        }

        if (\preg_match('#^[\*%]+$#', $author) || \mb_strlen(\str_replace(['*', '%'], '', $author)) < 3) {
            $author = null;
        }
        // UTF FIX END

        if (!$keywords && !$author) {
            \wap_message($lang_search['No terms']);
        }

        if ($author) {
            $author = \str_replace('*', '%', $author);
        }

        $show_as = (isset($_GET['show_as'])) ? $_GET['show_as'] : 'posts';
        $sort_by = (int) $_GET['sort_by'];
        $search_in = (!isset($_GET['search_in']) || 'all' == $_GET['search_in']) ? 0 : (('message' == $_GET['search_in']) ? 1 : -1);
    } elseif ('show_user' == $_GET['action']) {
        // If it's a user search (by id)
        $user_id = (int) $_GET['user_id'];
        if ($user_id < 2) {
            \wap_message($lang_common['Bad request']);
        }
    } else {
        if ('show_new' != $_GET['action'] && 'show_24h' != $_GET['action'] && 'show_unanswered' != $_GET['action'] && 'show_subscriptions' != $_GET['action']) {
            \wap_message($lang_common['Bad request']);
        }
    }

    // If a valid search_id was supplied we attempt to fetch the search results from the db
    if (@$search_id) {
        $ident = ($pun_user['is_guest']) ? \get_remote_address() : $pun_user['username'];

        $result = $db->query('
            SELECT search_data
            FROM '.$db->prefix.'search_cache
            WHERE id='.$search_id.'
            AND ident=\''.$db->escape($ident).'\'
        ');
        if (!$result) {
            \error('Unable to fetch search results', __FILE__, __LINE__, $db->error());
        }

        if ($row = $db->fetch_assoc($result)) {
            $temp = \unserialize($row['search_data'], ['allowed_classes' => false]);

            $search_results = $temp['search_results'];
            $num_hits = $temp['num_hits'];
            $sort_by = $temp['sort_by'];
            $sort_dir = $temp['sort_dir'];
            $show_as = $temp['show_as'];

            unset($temp);
        } else {
            \wap_message($lang_search['No hits']);
        }
    } else {
        $keyword_results = $author_results = [];

        // Search a specific forum?
        $forum_sql = (-1 != $forum || (-1 == $forum && !$pun_config['o_search_all_forums'] && $pun_user['g_id'] >= PUN_GUEST)) ? ' AND t.forum_id = '.$forum : '';

        if (@$author || @$keywords) {
            // If it's a search for keywords
            if ($keywords) {
                $stopwords = \file(PUN_ROOT.'lang/'.$pun_user['language'].'/stopwords.txt');
                $stopwords = \array_map('trim', $stopwords);

                // Filter out non-alphabetical chars
                $keywords = \str_replace(
                    ['^', '$', '&', '(', ')', '<', '>', '`', "'", '"', '|', ',', '@', '_', '?', '%', '~', '[', ']', '{', '}', ':', '\\', '/', '=', '#', "'", ';', '!', \chr(239)],
                    [' ', ' ', ' ', ' ', ' ', ' ', ' ', '', '', ' ', ' ', ' ', ' ', '', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', '', ' ', ' ', ' ', ' ', ' ', ' ', ' '],
                    $keywords
                );

                // Strip out excessive whitespace
                $keywords = \trim(\preg_replace('#\s+#', ' ', $keywords));

                // Fill an array with all the words
                $keywords_array = \explode(' ', $keywords);

                if (!$keywords_array) {
                    \wap_message($lang_search['No hits']);
                }

                foreach ($keywords_array as $i => $word) {
                    $num_chars = \mb_strlen($word);

                    if ('or' !== $word && ($num_chars < 3 || $num_chars > 20 || \in_array($word, $stopwords, true))) {
                        unset($keywords_array[$i]);
                    }
                }

                // Should we search in message body or topic subject specifically?
                $search_in_cond = ($search_in) ? (($search_in > 0) ? ' AND m.subject_match = 0' : ' AND m.subject_match = 1') : '';

                $word_count = 0;
                $match_type = 'and';
                $result_list = [];
                \reset($keywords_array);
                foreach ($keywords_array as $cur_word) {
                    switch ($cur_word) {
                        case 'and':
                        case 'or':
                        case 'not':
                            $match_type = $cur_word;

                            break;

                        default:
                            $cur_word = $db->escape(\str_replace('*', '%', $cur_word));
                            $sql = '
                                SELECT m.post_id
                                FROM '.$db->prefix.'search_words AS w
                                INNER JOIN '.$db->prefix.'search_matches AS m ON m.word_id = w.id
                                WHERE w.word LIKE \''.$cur_word.'\''.$search_in_cond;

                            $result = $db->query($sql);
                            if (!$result) {
                                \error('Unable to search for posts', __FILE__, __LINE__, $db->error());
                            }

                            $row = [];
                            while ($temp = $db->fetch_row($result)) {
                                $row[$temp[0]] = 1;

                                if (!$word_count) {
                                    $result_list[$temp[0]] = 1;
                                } elseif ('or' == $match_type) {
                                    $result_list[$temp[0]] = 1;
                                } elseif ('not' == $match_type) {
                                    $result_list[$temp[0]] = 0;
                                }
                            }

                            if ('and' == $match_type && $word_count) {
                                \reset($result_list);
                                foreach ($result_list as $post_id => $post) {
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

                \reset($result_list);
                foreach ($result_list as $post_id => $matches) {
                    if ($matches) {
                        $keyword_results[] = $post_id;
                    }
                }

                unset($result_list);
            }

            // If it's a search for author name (and that author name isn't Guest)
            if ($author && \strcasecmp($author, 'Guest') && \strcasecmp($author, $lang_common['Guest'])) {
                $result = $db->query('
                    SELECT id
                    FROM '.$db->prefix.'users
                    WHERE username LIKE \''.$db->escape($author).'\'
                ');
                if (!$result) {
                    \error('Unable to fetch users', __FILE__, __LINE__, $db->error());
                }

                if ($db->num_rows($result)) {
                    $user_ids = '';
                    while ($row = $db->fetch_row($result)) {
                        $user_ids .= (($user_ids) ? ',' : '').$row[0];
                    }

                    $result = $db->query('
                        SELECT id
                        FROM '.$db->prefix.'posts
                        WHERE poster_id IN('.$user_ids.')
                    ');
                    if (!$result) {
                        \error('Unable to fetch matched posts list', __FILE__, __LINE__, $db->error());
                    }

                    $search_ids = [];
                    while ($row = $db->fetch_row($result)) {
                        $author_results[] = $row[0];
                    }

                    $db->free_result($result);
                }
            }

            if ($author && $keywords) {
                // If we searched for both keywords and author name we want the intersection between the results
                $search_ids = \array_intersect($keyword_results, $author_results);
                unset($keyword_results, $author_results);
            } elseif ($keywords) {
                $search_ids = $keyword_results;
            } else {
                $search_ids = $author_results;
            }

            if (!$search_ids) {
                \wap_message($lang_search['No hits']);
            }

            if ('topics' === $show_as) {
                $result = $db->query('
                    SELECT t.id
                    FROM '.$db->prefix.'posts AS p
                    INNER JOIN '.$db->prefix.'topics AS t ON t.id=p.topic_id
                    INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id
                    LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$pun_user['g_id'].')
                    WHERE (fp.read_forum IS NULL OR fp.read_forum=1)
                    AND p.id IN('.\implode(',', $search_ids).')
                    '.$forum_sql.'
                    GROUP BY t.id
                ');
                if (!$result) {
                    \error('Unable to fetch topic list', __FILE__, __LINE__, $db->error());
                }

                $search_ids = [];
                while ($row = $db->fetch_row($result)) {
                    $search_ids[] = $row[0];
                }

                $db->free_result($result);

                $num_hits = \count($search_ids);
            } else {
                $result = $db->query(
                    '
                    SELECT p.id
                    FROM '.$db->prefix.'posts AS p
                    INNER JOIN '.$db->prefix.'topics AS t ON t.id=p.topic_id
                    INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id
                    LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$pun_user['g_id'].')
                    WHERE (fp.read_forum IS NULL OR fp.read_forum=1)
                    AND p.id IN('.\implode(',', $search_ids).')
                    '.$forum_sql
                );
                if (!$result) {
                    \error('Unable to fetch topic list', __FILE__, __LINE__, $db->error());
                }

                $search_ids = [];
                while ($row = $db->fetch_row($result)) {
                    $search_ids[] = $row[0];
                }

                $db->free_result($result);

                $num_hits = \count($search_ids);
            }
        } elseif ('show_new' == $_GET['action'] || 'show_24h' == $_GET['action'] || 'show_user' == $_GET['action'] || 'show_subscriptions' == $_GET['action'] || 'show_unanswered' == $_GET['action']) {
            // If it's a search for new posts
            if ('show_new' == $_GET['action']) {
                if ($pun_user['is_guest']) {
                    \wap_message($lang_common['No permission']);
                }

                $result = $db->query('
                    SELECT t.id
                    FROM '.$db->prefix.'topics AS t
                    INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id
                    LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$pun_user['g_id'].')
                    WHERE (fp.read_forum IS NULL OR fp.read_forum=1)
                    AND t.last_post>'.$pun_user['last_visit'].'
                    AND t.moved_to IS NULL
                ');
                if (!$result) {
                    \error('Unable to fetch topic list', __FILE__, __LINE__, $db->error());
                }
                $num_hits = $db->num_rows($result);

                if (!$num_hits) {
                    \wap_message($lang_search['No new posts']);
                }
            } elseif ('show_24h' == $_GET['action']) {
                // If it's a search for todays posts
                $result = $db->query('
                    SELECT t.id
                    FROM '.$db->prefix.'topics AS t
                    INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id
                    LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$pun_user['g_id'].')
                    WHERE (fp.read_forum IS NULL OR fp.read_forum=1)
                    AND t.last_post>'.($_SERVER['REQUEST_TIME'] - 86400).'
                    AND t.moved_to IS NULL
                ');
                if (!$result) {
                    \error('Unable to fetch topic list', __FILE__, __LINE__, $db->error());
                }
                $num_hits = $db->num_rows($result);

                if (!$num_hits) {
                    \wap_message($lang_search['No recent posts']);
                }
            } elseif ('show_user' == $_GET['action']) {
                // If it's a search for posts by a specific user ID
                $result = $db->query('
                    SELECT t.id
                    FROM '.$db->prefix.'topics AS t
                    INNER JOIN '.$db->prefix.'posts AS p ON t.id=p.topic_id
                    INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id
                    LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$pun_user['g_id'].')
                    WHERE (fp.read_forum IS NULL OR fp.read_forum=1)
                    AND p.poster_id='.$user_id.'
                    GROUP BY t.id
                ');
                if (!$result) {
                    \error('Unable to fetch topic list', __FILE__, __LINE__, $db->error());
                }
                $num_hits = $db->num_rows($result);

                if (!$num_hits) {
                    \wap_message($lang_search['No user posts']);
                }
            } elseif ('show_subscriptions' == $_GET['action']) {
                // If it's a search for subscribed topics
                if ($pun_user['is_guest']) {
                    \wap_message($lang_common['Bad request']);
                }

                $result = $db->query('
                    SELECT t.id
                    FROM '.$db->prefix.'topics AS t
                    INNER JOIN '.$db->prefix.'subscriptions AS s ON (t.id=s.topic_id AND s.user_id='.$pun_user['id'].')
                    INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id
                    LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$pun_user['g_id'].')
                    WHERE (fp.read_forum IS NULL OR fp.read_forum=1)
                ');
                if (!$result) {
                    \error('Unable to fetch topic list', __FILE__, __LINE__, $db->error());
                }
                $num_hits = $db->num_rows($result);

                if (!$num_hits) {
                    \wap_message($lang_search['No subscriptions']);
                }
            } else {
                // If it's a search for unanswered posts
                $result = $db->query('
                    SELECT t.id
                    FROM '.$db->prefix.'topics AS t
                    INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id
                    LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$pun_user['g_id'].')
                    WHERE (fp.read_forum IS NULL OR fp.read_forum=1)
                    AND t.num_replies=0
                    AND t.moved_to IS NULL
                ');
                if (!$result) {
                    \error('Unable to fetch topic list', __FILE__, __LINE__, $db->error());
                }
                $num_hits = $db->num_rows($result);

                if (!$num_hits) {
                    \wap_message($lang_search['No unanswered']);
                }
            }

            // We want to sort things after last post
            $sort_by = 4;

            $search_ids = [];
            while ($row = $db->fetch_row($result)) {
                $search_ids[] = $row[0];
            }

            $db->free_result($result);

            $show_as = 'topics';
        } else {
            \wap_message($lang_common['Bad request']);
        }

        // Prune "old" search results
        $old_searches = [];
        $result = $db->query('SELECT ident FROM '.$db->prefix.'online');
        if (!$result) {
            \error('Unable to fetch online list', __FILE__, __LINE__, $db->error());
        }

        if ($db->num_rows($result)) {
            while ($row = $db->fetch_row($result)) {
                $old_searches[] = '\''.$db->escape($row[0]).'\'';
            }

            $db->query('DELETE FROM '.$db->prefix.'search_cache WHERE ident NOT IN('.\implode(',', $old_searches).')') || \error('Unable to delete search results', __FILE__, __LINE__, $db->error());
        }

        // Final search results
        $search_results = \implode(',', $search_ids);

        // Fill an array with our results and search properties
        $temp['search_results'] = $search_results;
        $temp['num_hits'] = $num_hits;
        $temp['sort_by'] = $sort_by;
        $temp['sort_dir'] = $sort_dir;
        $temp['show_as'] = $show_as;
        $temp = \serialize($temp);
        $search_id = \random_int(1, \mt_getrandmax());

        $ident = ($pun_user['is_guest']) ? \get_remote_address() : $pun_user['username'];

        $db->query('INSERT INTO '.$db->prefix.'search_cache (id, ident, search_data) VALUES('.$search_id.', \''.$db->escape($ident).'\', \''.$db->escape($temp).'\')') || \error('Unable to insert search results', __FILE__, __LINE__, $db->error());

        if ('show_new' != $_GET['action'] && 'show_24h' != $_GET['action']) {
            $db->close();

            // Redirect the user to the cached result page
            \wap_redirect('search.php?search_id='.$search_id);
        }
    }

    // Fetch results to display
    if ($search_results) {
        switch ($sort_by) {
            case 1:
                $sort_by_sql = ('topics' == $show_as) ? 't.poster' : 'p.poster';

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
                $sort_by_sql = ('topics' == $show_as) ? 't.posted' : 'p.posted';

                break;
        }

        if ('posts' == $show_as) {
            $sql = '
                SELECT p.id AS pid, p.poster AS pposter, p.posted AS pposted, p.poster_id, p.message AS message, t.id AS tid, t.poster, t.subject, t.last_post, t.last_post_id, t.last_poster, t.num_replies, t.forum_id
                FROM '.$db->prefix.'posts AS p
                INNER JOIN '.$db->prefix.'topics AS t ON t.id=p.topic_id
                WHERE p.id IN('.$search_results.')
                ORDER BY '.$sort_by_sql;
        } else {
            $sql = '
                SELECT t.id AS tid, t.poster, t.subject, t.last_post, t.last_post_id, t.last_poster, t.num_replies, t.closed, t.forum_id
                FROM '.$db->prefix.'topics AS t
                WHERE t.id IN('.$search_results.')
                ORDER BY '.$sort_by_sql;
        }

        // + Pagination
        // Determine the topic or post offset (based on $_GET['p'])
        $per_page = ('posts' == $show_as) ? $pun_user['disp_posts'] : $pun_user['disp_topics'];
        $num_pages = \ceil($num_hits / $per_page);
        $p = (isset($_GET['p']) && 1 < $_GET['p'] && $num_pages >= $_GET['p']) ? (int) $_GET['p'] : 1;
        $start_from = $per_page * ($p - 1);

        // Generate paging links
        if ('all' == @$_GET['action']) {
            $p = $num_pages + 1;
            $per_page = $num_hits;
            $start_from = 0;
        }

        $paging_links = \paginate($num_pages, $p, 'search.php?search_id='.$search_id);
        // - Pagination

        $sql .= ' '.$sort_dir.' LIMIT '.$start_from.', '.$per_page;

        $result = $db->query($sql);
        if (!$result) {
            \error('Unable to fetch search results', __FILE__, __LINE__, $db->error());
        }

        $search_set = [];
        while ($row = $db->fetch_assoc($result)) {
            if ('posts' == $show_as) {
                if (1 == $pun_config['o_censoring']) {
                    $row['message'] = \censor_words($row['message']);
                }
                $row['message'] = \parse_message($row['message'], 0, $row['pid']);
            }
            if (1 == $pun_config['o_censoring']) {
                $row['subject'] = \censor_words($row['subject']);
            }
            $search_set[] = $row;
        }
        $db->free_result($result);

        $page_title = $pun_config['o_board_title'].' / '.$lang_search['Search results'];
        //        require_once PUN_ROOT . 'wap/header.php';

        // Set background switching on for show as posts
        $bg_switch = true;

        /*if ($show_as == 'topics') {
            echo '<div class="in">'.$lang_common['Forum'].' | '.$lang_common['Topic'].' | '.$lang_common['Replies'].' | '.$lang_common['Last post'].'</div>';
        }*/

        // Fetch the list of forums
        $result = $db->query('SELECT `id`, `forum_name` FROM `'.$db->prefix.'forums`');
        if (!$result) {
            \error('Unable to fetch forum list', __FILE__, __LINE__, $db->error());
        }

        $forum_list = [];
        while ($row = $db->fetch_row($result)) {
            $forum_list[] = $row;
        }

        $smarty->assign('page_title', $page_title);
        $smarty->assign('search_set', $search_set);

        $smarty->assign('forum_list', $forum_list);
        $smarty->assign('show_as', $show_as);

        $smarty->assign('lang_search', $lang_search);
        $smarty->assign('paging_links', $paging_links);

        $smarty->display('search.result.tpl');

        exit;
    }
    \wap_message($lang_search['No hits']);
}

$result = $db->query('
    SELECT c.id AS cid, c.cat_name, f.id AS fid, f.forum_name, f.redirect_url
    FROM '.$db->prefix.'categories AS c
    INNER JOIN '.$db->prefix.'forums AS f ON c.id=f.cat_id
    LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$pun_user['g_id'].')
    WHERE (fp.read_forum IS NULL OR fp.read_forum=1)
    AND f.redirect_url IS NULL
    ORDER BY c.disp_position, c.id, f.disp_position
');
if (!$result) {
    \error('Unable to fetch category/forum list', __FILE__, __LINE__, $db->error());
}

$forums = [];
while ($cur_forum = $db->fetch_assoc($result)) {
    $forums[] = $cur_forum;
}

$page_title = $pun_config['o_board_title'].' / '.$lang_search['Search'];
$smarty->assign('page_title', $page_title);

$smarty->assign('forums', $forums);
$smarty->assign('lang_search', $lang_search);

$smarty->display('search.tpl');
