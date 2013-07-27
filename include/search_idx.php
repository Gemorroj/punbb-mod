<?php
// Make sure no one attempts to run this script "directly"
if (!defined('PUN')) {
    exit;
}

//
// "Cleans up" a text string and returns an array of unique words
// This function depends on the current locale setting
//
function split_words($text)
{
    global $pun_user;
    static $noise_match, $noise_replace, $stopwords;

    if (!$noise_match) {
        $noise_match = array('[quote', '[code', '[url', '[img', '[email', '[color', '[colour', 'quote]', 'code]', 'url]', 'img]', 'email]', 'color]', 'colour]', '^', '$', '&', '(', ')', '<', '>', '`', "'", '"', '|', ',', '@', '_', '?', '%', '~', '+', '[', ']', '{', '}', ':', '\\', '/', '=', '#', ';', '!', '*');
        $noise_replace = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', ' ', ' ', ' ', ' ', ' ', ' ', ' ', '', '', ' ', ' ', ' ', ' ', '', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', '', ' ', ' ', ' ', ' ', ' ', ' ');

        $stopwords = (array)@file(PUN_ROOT . 'lang/' . $pun_user['language'] . '/stopwords.txt');
        $stopwords = array_map('trim', $stopwords);
    }

// Clean up
    $patterns[] = '#&[\#a-z0-9]+?;#iu';
    $patterns[] = '#\b[\w]+:\/\/[a-z0-9\.\-]+(\/[a-z0-9\?\.%_\-\+=&\/~]+)?#u';
    $patterns[] = '#\[\/?[a-z\*=\+\-]+(\:?[0-9a-z]+)?:[a-z0-9]{10,}(\:[a-z0-9]+)?=?.*?\]#u';
    $text = preg_replace($patterns, ' ', ' ' . mb_strtolower($text) . ' ');

// Filter out junk
    $text = str_replace($noise_match, $noise_replace, $text);

// Strip out extra whitespace between words
    $text = trim(preg_replace('#\s+#u', ' ', $text));

// Fill an array with all the words
    $words = explode(' ', $text);

    if ($words) {
        while (list($i, $word) = @each($words)) {
            $words[$i] = trim($word, '.');
            $num_chars = strlen($word);

            if ($num_chars < 3 || $num_chars > 40 || in_array($word, $stopwords)) {
                unset($words[$i]);
            }
        }
    }

    return array_unique($words);
}


//
// Updates the search index with the contents of $post_id (and $subject)
//
function update_search_index($mode, $post_id, $message, $subject = null)
{
    global $db;

// Split old and new post/subject to obtain array of 'words'
    $words_message = split_words($message);
    $words_subject = ($subject) ? split_words($subject) : array();

    if ($mode == 'edit') {
        $result = $db->query('SELECT w.id, w.word, m.subject_match FROM ' . $db->prefix . 'search_words AS w INNER JOIN ' . $db->prefix . 'search_matches AS m ON w.id=m.word_id WHERE m.post_id=' . $post_id, true) or error('Unable to fetch search index words', __FILE__, __LINE__, $db->error());

// Declare here to stop array_keys() and array_diff() from complaining if not set
        $cur_words['post'] = array();
        $cur_words['subject'] = array();

        while ($row = $db->fetch_row($result)) {
            $match_in = ($row[2]) ? 'subject' : 'post';
            $cur_words[$match_in][$row[1]] = $row[0];
        }

        $db->free_result($result);

        $words['add']['post'] = array_diff($words_message, array_keys($cur_words['post']));
        $words['add']['subject'] = array_diff($words_subject, array_keys($cur_words['subject']));
        $words['del']['post'] = array_diff(array_keys($cur_words['post']), $words_message);
        $words['del']['subject'] = array_diff(array_keys($cur_words['subject']), $words_subject);
    } else {
        $words['add']['post'] = $words_message;
        $words['add']['subject'] = $words_subject;
        $words['del']['post'] = array();
        $words['del']['subject'] = array();
    }

    unset($words_message);
    unset($words_subject);

// Get unique words from the above arrays
    $unique_words = array_unique(array_merge($words['add']['post'], $words['add']['subject']));

    if ($unique_words) {
        $result = $db->query('SELECT id, word FROM ' . $db->prefix . 'search_words WHERE word IN(' . implode(',', preg_replace('#^(.*)$#u', '\'\1\'', $unique_words)) . ')', true) or error('Unable to fetch search index words', __FILE__, __LINE__, $db->error());

        $word_ids = array();
        while ($row = $db->fetch_row($result)) {
            $word_ids[$row[1]] = $row[0];
        }

        $db->free_result($result);

        $new_words = array_unique(array_diff($unique_words, array_keys($word_ids)));
        unset($unique_words);

        if ($new_words) {
            $db->query('INSERT INTO ' . $db->prefix . 'search_words (word) VALUES' . implode(',', preg_replace('#^(.*)$#u', '(\'\1\')', $new_words))) or error('Unable to insert search index words', __FILE__, __LINE__, $db->error());
        }

        unset($new_words);
    }

// Delete matches (only if editing a post)
    while (list($match_in, $wordlist) = @each($words['del'])) {
        $subject_match = ($match_in == 'subject') ? 1 : 0;

        if ($wordlist) {
            $sql = null;
            while (list(, $word) = @each($wordlist)) {
                $sql .= (($sql) ? ',' : '') . $cur_words[$match_in][$word];
            }

            $db->query('DELETE FROM ' . $db->prefix . 'search_matches WHERE word_id IN(' . $sql . ') AND post_id=' . $post_id . ' AND subject_match=' . $subject_match) or error('Unable to delete search index word matches', __FILE__, __LINE__, $db->error());
        }
    }

// Add new matches
    while (list($match_in, $wordlist) = @each($words['add'])) {
        $subject_match = ($match_in == 'subject') ? 1 : 0;

        if ($wordlist) {
            $db->query('INSERT INTO ' . $db->prefix . 'search_matches (post_id, word_id, subject_match) SELECT ' . $post_id . ', id, ' . $subject_match . ' FROM ' . $db->prefix . 'search_words WHERE word IN(' . implode(',', preg_replace('#^(.*)$#', '\'\1\'', $wordlist)) . ')') or error('Unable to insert search index word matches', __FILE__, __LINE__, $db->error());
        }
    }

    unset($words);
}


//
// Strip search index of indexed words in $post_ids
//
function strip_search_index($post_ids)
{
    global $db;

    $result = $db->query('SELECT word_id FROM ' . $db->prefix . 'search_matches WHERE post_id IN(' . $post_ids . ') GROUP BY word_id') or error('Unable to fetch search index word match', __FILE__, __LINE__, $db->error());

    if ($db->num_rows($result)) {
        $word_ids = null;
        while ($row = $db->fetch_row($result)) {
            $word_ids .= ($word_ids) ? ',' . $row[0] : $row[0];
        }

        $result = $db->query('SELECT word_id FROM ' . $db->prefix . 'search_matches WHERE word_id IN(' . $word_ids . ') GROUP BY word_id HAVING COUNT(word_id)=1') or error('Unable to fetch search index word match', __FILE__, __LINE__, $db->error());

        if ($db->num_rows($result)) {
            $word_ids = null;
            while ($row = $db->fetch_row($result)) {
                $word_ids .= ($word_ids) ? ',' . $row[0] : $row[0];
            }

            $db->query('DELETE FROM ' . $db->prefix . 'search_words WHERE id IN(' . $word_ids . ')') or error('Unable to delete search index word', __FILE__, __LINE__, $db->error());
        }
    }


    $db->query('DELETE FROM ' . $db->prefix . 'search_matches WHERE post_id IN(' . $post_ids . ')') or error('Unable to delete search index word match', __FILE__, __LINE__, $db->error());
}
