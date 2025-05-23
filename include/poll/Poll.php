<?php

/*

(C) hcs (hcs@mail.ru) http://punbb.ru 2007

Ajax Poll for punbb

poll script
PLEASE, DO NOT REMOVE LINK TO punbb.ru FROM CODE! THANKS!
 */

// define('PUN_ROOT', '../../');

// TODO : перенести названия в константы:
require_once PUN_ROOT.'lang/'.$pun_user['language'].'/poll.php';

class Poll
{
    public $errorState = false;
    public $errorDescr;
    public $cachePID;
    public $cacheUID;
    public $polled = false;

    public function create($userid)
    {
        global $db;

        if (isset($_POST['polldata'])) {
            $poll = [];
            $data = \explode('&', $_POST['polldata']);
            foreach ($data as $val) {
                $current = \explode('=', $val);
                $poll[$current[0]] = \urldecode($current[1]);
            }
        }

        if ($this->validCreateData($poll['pdescription'], $poll['pmultiselect'], $poll['pquestions'], $userid)) {
            if (!\is_int($poll['pexpire'])) {
                $poll['pexpire'] = 0;
            } elseif ($poll['pexpire'] < 0) {
                $poll['pexpire'] = 0;
            } elseif ($poll['pexpire'] > 365) {
                $poll['pexpire'] = 365;
            }

            $db->query('INSERT INTO '.$db->prefix.'polls (description, time, multiselect, data, expire, owner) VALUES(\''.$db->escape($poll['pdescription']).'\', '.\time().', \''.$db->escape($poll['pmultiselect']).'\', \''.$db->escape(\serialize($this->convertQustions($poll['pquestions']))).'\', \''.$db->escape($poll['pexpire']).'\', '.$userid.')') || \error('Unable to create poll.', __FILE__, __LINE__, $db->error());

            return $db->insert_id();
        }

        return 0;
    }

    public function deleteTopic($topics): void
    {
        global $db;

        $result = $db->query('SELECT has_poll FROM '.$db->prefix.'topics WHERE id IN ('.$topics.')');
        if (!$result) {
            \error('Unable to get poll id from topics', __FILE__, __LINE__, $db->error());
        }
        $polls_ids = '';
        while ($row = $db->fetch_row($result)) {
            if ($row[0]) {
                $polls_ids .= ($polls_ids) ? ','.$row[0] : $row[0];
            }
        }
        if ($polls_ids) {
            $db->query('DELETE FROM '.$db->prefix.'polls WHERE id IN('.$polls_ids.')') || \error('Unable to delete polls', __FILE__, __LINE__, $db->error());
            $db->query('DELETE FROM '.$db->prefix.'log_polls WHERE pid IN('.$polls_ids.')') || \error('Unable to delete info for log_polls', __FILE__, __LINE__, $db->error());
        }
    }

    public function updatePoll()
    {
        $out = '';
        foreach (\explode('&', $_POST['d']) as $val) {
            $current = \explode('=', $val);
            $out .= \urldecode($current[0]).'='.\urldecode($current[1]).'<br />';
        }

        return $out;
    }

    public function vote($pid, $q)
    {
        global $pun_user, $db;

        if (!$this->isVoted($pid, $pun_user['id'])) {
            $q = $this->convertAnswers($q);

            if (!$this->validAnswers($q)) {
                return 1;
            }

            $poll = $this->getPollDB($pid);

            foreach ($q as $value) {
                ++$poll['data'][$value][1];
            }

            $db->query('UPDATE '.$db->prefix.'polls SET data = \''.$db->escape(\serialize($poll['data'])).'\', vcount=vcount+1 WHERE id='.$pid) || \error('Unable to update polls. ', __FILE__, __LINE__, $db->error());

            $db->query('INSERT INTO '.$db->prefix.'log_polls (pid, uid) VALUES('.$pid.','.$pun_user['id'].')') || \error('Unable to update voters. ', __FILE__, __LINE__, $db->error());
            $this->setPolled($pid, $pun_user['id']);

            return 0;
        }

        return 2;
    }

    public function convertQustions($value)
    {
        $questions = [];

        foreach (\explode("\n", $value) as $val) {
            $val = \trim($val);
            if ($val && "\n" !== $val && "\t" !== $val) {
                $questions[] = [$val, 0];
            }
        }

        return $questions;
    }

    public function convertAnswers($value)
    {
        $answers = [];

        if (\is_int($value)) {
            $answers[] = $value;
        } else {
            foreach (\explode('&', $value) as $val) {
                $result2 = \explode('=', $val);
                $answers[] = $result2[1];
            }
        }

        return $answers;
    }

    public function validAnswers($value)
    {
        foreach ($value as $answ) {
            if (!\is_numeric($answ)) {
                $this->errorState = true;
                $this->errorDescr = 'Invalid answer value';

                return false;
            }
        }
        $this->errorState = false;

        return true;
    }

    public function validCreateData($description, $multiselect, $questions, $userid)
    {
        $this->errorState = true;
        if ($multiselect && 1 != $multiselect) {
            $this->errorDescr = 'Invalid multiselect value';

            return false;
        }
        if ('' === $description) {
            $this->errorDescr = 'Invalid description value';

            return false;
        }
        if ('' === $questions) {
            $this->errorDescr = 'Invalid answer value';

            return false;
        }
        $this->errorState = false;

        return true;
    }

    public function showPoll($pollid)
    {
        global $pun_config, $pun_user, $lang_poll;

        $poll = $this->getPollDB($pollid);
        if (!$poll['error']) {
            $total = 0;
            foreach ($poll['data'] as $quest) {
                $total += $quest[1];
            }

            if (!$total) {
                $q = 100;
            } else {
                $q = 100 / $total;
            }

            $pieces = '';

            if ($pun_user['is_guest'] || ($poll['expire'] && $poll['expire'] < \time()) || $this->isVoted($pollid, $pun_user['id'])) {
                if ($pun_user['is_guest']) {
                    $pieces .= '<p style="text-align:right;font-size:7px">'.$lang_poll['guest'].'</p>';
                }

                return $this->showResult($pollid, $poll, $q, $total, $pieces);
            }

            return $this->showQuest($pollid, $poll, $pieces);
        }

        return $poll['error'];
    }

    public function wap_showPoll($pollid, $warning = null)
    {
        global $pun_config, $pun_user, $lang_poll;

        $poll = $this->getPollDB($pollid);
        if (!$poll['error']) {
            $total = 0;
            foreach ($poll['data'] as $quest) {
                $total += $quest[1];
            }
            if (!$total) {
                $q = 100;
            } else {
                $q = 100 / $total;
            }

            $pieces = '';

            if ($pun_user['is_guest'] || ($poll['expire'] && $poll['expire'] < \time()) || $this->isVoted($pollid, $pun_user['id'])) {
                if ($pun_user['is_guest']) {
                    $pieces .= '<p style="text-align:right;font-size:7px">'.$lang_poll['guest'].'</p>';
                }

                return $this->wap_showResult($pollid, $poll, $q, $total, $pieces);
            }

            return $this->wap_showQuest($pollid, $poll, $pieces, $warning);
        }

        return $poll['error'];
    }

    public function wap_showResult($pollid, $poll, $q, $total, $pieces = '')
    {
        global $lang_poll, $pun_user, $lang_common;

        $out = '<div class="in"><strong>'.$lang_poll['poll'].'</strong>: '.\pun_htmlspecialchars($poll['description']).'</div><div class="msg2"><span class="sub">';

        foreach ($poll['data'] as $quest) {
            $out .= '<strong>'.\pun_htmlspecialchars($quest[0]).'</strong> ['.$quest[1].'] '.\round($quest[1] * $q, 1).'%<br />';
        }

        $out .= $lang_poll['total voters'].': '.$poll['vcount'].' | '.$lang_poll['votes'].': '.$total.' '.$pieces.'</span></div>';

        return $out;
    }

    public function wap_showQuest($pollid, $poll, $pieces, $warning = null)
    {
        global $lang_poll, $pun_user, $lang_common;

        if (2 == $warning) {
            $warning = $lang_poll['voted'];
        } elseif (1 == $warning) {
            $warning = $lang_poll['answer must select'];
        } else {
            $warning = null;
        }

        $out = '<div class="in"><strong>'.$lang_poll['poll'].'</strong>: '.\pun_htmlspecialchars($poll['description']).'</div>
<div id="warning">'.\pun_htmlspecialchars($warning).'</div>
<form action="viewtopic.php?'.\pun_htmlspecialchars($_SERVER['QUERY_STRING']).'" method="post">
<div class="input2">
<input type="hidden" name="pollid" value="'.$pollid.'"/>';

        $i = -1;
        foreach ($poll['data'] as $quest) {
            ++$i;

            $out .= '<label for="poll_'.$i.'">';
            if (!$poll['multiselect']) {
                $out .= '<input id="poll_'.$i.'" type="radio" name="poll_vote" value="'.$i.'" />';
            } else {
                $out .= '<input id="poll_'.$i.'" type="checkbox" name="poll_vote['.$i.']" value="'.$i.'" />';
            }
            $out .= ' '.\pun_htmlspecialchars($quest[0]).'<br /></label>';
        }
        $out .= '</div><div class="go_to"><input type="submit" value="'.$lang_poll['vote'].'"/></div></form>'.$pieces;

        return $out;
    }

    public function showResult($pollid, $poll, $q, $total, $pieces = '')
    {
        global $lang_poll, $pun_user, $lang_common;

        $out = '<div class="p_cnt p_cnt_'.$pollid.'"><fieldset><legend>'.$lang_poll['poll'].'</legend><div class="cnt_'.$pollid.'"><table><tr><td colspan="3" style="text-align:center;">'.\pun_htmlspecialchars($poll['description']).'</td></tr>';

        $bg_switch = false;
        foreach ($poll['data'] as $quest) {
            $bg_switch = ($bg_switch) ? $bg_switch = false : $bg_switch = true;
            $vtbg = ($bg_switch) ? 'roweven' : 'rowodd';

            $out .= '<tr><td class="col1 '.$vtbg.'">'.\pun_htmlspecialchars($quest[0]).' ['.$quest[1].']</td><td class="col2 '.$vtbg.'"><div style="width:'.\ceil($quest[1] * $q).'%;"></div></td><td class="col3 '.$vtbg.'"> '.\round($quest[1] * $q, 1).'% </td></tr>';
        }

        $out .= '<tr><td class="'.((!$bg_switch) ? 'roweven' : 'rowodd').'" colspan="3" style="text-align:center;">'.$lang_poll['total voters'].': '.$poll['vcount'].' / '.$lang_poll['votes'].': '.$total.'</td></tr></table></div>'.$pieces.'</fieldset></div><br class="clearb" />';

        if (PUN_ADMIN == $pun_user['g_id'] || PUN_MOD == $pun_user['g_id']) {
            JsHelper::getInstance()->add(PUN_ROOT.'js/jquery.punmodalbox.js');
            JsHelper::getInstance()->add(PUN_ROOT.'js/apoll.js');
        }

        return $out;
    }

    public function showQuest($pollid, $poll, $pieces)
    {
        global $lang_poll, $pun_user, $lang_common;

        if (PUN_ADMIN == $pun_user['g_id'] || PUN_MOD == $pun_user['g_id']) {
            JsHelper::getInstance()->add(PUN_ROOT.'js/jquery.punmodalbox.js');
            JsHelper::getInstance()->add(PUN_ROOT.'js/apoll.js');
        }

        $out = '<div class="p_cnt p_cnt_'.$pollid.'"><fieldset><legend>'.$lang_poll['poll'].'</legend><div id="warning" style="display:none;"></div><table><tr><td colspan="2"><center>'.\pun_htmlspecialchars($poll['description']).'</center></td></tr>';

        $i = -1;
        $bg_switch = false;
        foreach ($poll['data'] as $quest) {
            ++$i;
            $bg_switch = ($bg_switch) ? $bg_switch = false : $bg_switch = true;
            $vtbg = ($bg_switch) ? ' roweven' : ' rowodd';

            $out .= '<tr><td class="col1'.$vtbg.'">';

            if (!$poll['multiselect']) {
                $out .= '<input type="radio" name="poll_vote" value="'.$i.'" id="poll_vote_'.$i.'"/>';
            } else {
                $out .= '<input type="checkbox" name="poll_vote['.$i.']" value="'.$i.'" id="poll_vote_'.$i.'"/>';
            }

            $out .= '</td><td class="col3'.$vtbg.'"><label for="poll_vote_'.$i.'"> '.\pun_htmlspecialchars($quest[0]).'</label></td></tr>';
        }

        $out .= '<tr><td class="submit '.((!$bg_switch) ? 'roweven' : 'rowodd').'" colspan="2"><center class="pl"><input type="submit" name="submit" onclick="poll.vote('.$pollid.'); return false;" value="'.$lang_poll['vote'].'"/></center></td></tr></table>'.$pieces;

        JsHelper::getInstance()->add(PUN_ROOT.'js/poll.js');

        $out .= '</fieldset></div><br class="clearb" />';

        return $out;
    }

    public function isVoted($pid, $uid)
    {
        global $db;

        if ($this->cachePID != $pid || $this->cacheUID != $uid) {
            $result = $db->query('SELECT * FROM '.$db->prefix.'log_polls WHERE pid='.$pid.' AND uid='.$uid);
            if (!$result) {
                \error('Unable to check polled user', __FILE__, __LINE__, $db->error());
            }
            if (!$db->num_rows($result)) {
                $this->polled = false;
            } else {
                $this->polled = true;
            }
            $this->cachePID = $pid;
            $this->cacheUID = $uid;
        }

        return $this->polled;
    }

    public function setPolled($pid, $uid): void
    {
        $this->polled = true;
        $this->cachePID = $pid;
        $this->cacheUID = $uid;
    }

    public function getPollDB($pollId)
    {
        global $db;

        $result = $db->query('SELECT * FROM '.$db->prefix.'polls WHERE id='.(int) $pollId);
        if (!$result) {
            \error('Unable to fetch poll', __FILE__, __LINE__, $db->error());
        }

        if (!$db->num_rows($result)) {
            $poll = $db->fetch_assoc($result);
            $poll['error'] = 1; // no result
        } else {
            $poll = $db->fetch_assoc($result);
            $poll['data'] = \unserialize($poll['data'], ['allowed_classes' => false]);
            $poll['error'] = 0;
        }

        return $poll;
    }

    public function showForm()
    {
        global $lang_poll, $pun_user, $lang_common;

        return '<div class="pun blockform"><form id="pollcreate" method="post" action="#"><fieldset><div class="inform infldset"><div id="warning" style="display:none;"></div><table class="aligntop" cellspacing="0"><tr><th scope="row">'.$lang_poll['quest'].'</th><td><textarea name="pdescription" class="wide" id="pdescription" wrap="off"></textarea><span>'.$lang_poll['quest description'].'</span></td></tr><tr><th scope="row">'.$lang_poll['allow multiselect'].'</th><td><input type="radio" name="pmultiselect" value="1" id="poll_yes" /><label for="poll_yes" style="display: inline;"> <strong>'.$lang_poll['yes'].'</strong></label> <input type="radio" name="pmultiselect" value="0" checked="checked" id="poll_no"/><label for="poll_no" style="display: inline;"> <strong>'.$lang_poll['no'].'</strong></label><br/><span>'.$lang_poll['multiselect description'].'</span></td></tr><tr><th scope="row">'.$lang_poll['how long'].'</th><td><input name="pexpire" class="wide" id="pexpire" type="text" value="" /><span>'.$lang_poll['how long description'].'</span></td></tr><tr><th scope="row">'.$lang_poll['list answers'].'</th><td><textarea class="wide" rows="8" name="pquestions" id="pquestions" wrap="off"></textarea><span>'.$lang_poll['list answers description'].'</span></td></tr></table><p class="submitend" id="fpcrt_cnt"><input type="submit" name="fpcreate" id="fpcreate" onclick="poll.pForm(); return false" value="'.$lang_poll['create'].'" /></p></div></fieldset></form></div>';
    }

    public function showContainer()
    {
        global $lang_poll;
        JsHelper::getInstance()->add(PUN_ROOT.'js/jquery.punmodalbox.js');
        JsHelper::getInstance()->add(PUN_ROOT.'js/poll.js');

        return '<fieldset><legend>'.$lang_poll['poll'].'</legend><div class="infldset txtarea"><input type="hidden" name="has_poll" id="has_poll" value="0" /><label><a id="apcreate" class="crtpoll" href="#">'.$lang_poll['create'].'</a></label><div id="ppreview" style="display:none;position:relative;"></div></div></fieldset><br class="clearb" />';
    }

    public function wap_showContainer()
    {
        global $lang_poll;

        return '<fieldset><legend>'.$lang_poll['poll'].'</legend><input type="hidden" name="has_poll" value="1" /><textarea name="pdescription" rows="1" cols="12"></textarea><br/>'.$lang_poll['allow multiselect'].'<br/><label for="multiselect_yes"><input type="radio" id="multiselect_yes" name="pmultiselect" value="1"/>'.$lang_poll['yes'].'</label> <label for="multiselect_no"><input type="radio" id="multiselect_no" name="pmultiselect" value="0" checked="checked"/>'.$lang_poll['no'].'</label><br/>'.$lang_poll['how long'].'<br/><input name="pexpire" type="text" value=""/><br/>'.$lang_poll['list answers'].'<br/><textarea rows="2" cols="12" name="pquestions"></textarea></fieldset>';
    }

    public function showEditForm($pid)
    {
        global $lang_poll, $pun_user, $lang_common;

        $poll = $this->getPollDB($pid);

        $out = '<div class="pun blockform"><form id="polledit" method="post" action="#" enctype="multipart/form-data"><input type="hidden" name="poll_id" id="poll_id" value="'.$pid.'" /><fieldset><div class="inform infldset"><div id="warning" style="display:none;"></div><dl style="width:98%"><dt><strong>'.$lang_poll['quest'].'</strong></dt><dd><textarea name="pdescription" id="pdescription" rows="5" cols="20" wrap="off">'.$poll['description'].'</textarea></dd><dt><strong>'.$lang_poll['allow multiselect'].'</strong></dt><dd><input type="radio" name="pmultiselect" value="1" '.((1 == $poll['multiselect']) ? ' checked="checked"' : '').' /> <strong>'.$lang_poll['yes'].'</strong>  <input type="radio" name="pmultiselect" value="0" '.((!$poll['multiselect']) ? ' checked="checked"' : '').' /> <strong>'.$lang_poll['no'].'</strong></dd><dt><strong>'.$lang_poll['how long'].'</strong></dt><dd><input class="longinput" name="pexpire" id="pexpire" type="text" value="" /></dd><dd>'.$lang_poll['how long description'].'</dd><fieldset><legend><strong>'.$lang_poll['list answers'].'</strong></legend><ul>';

        foreach ($poll['data'] as $key => $value) {
            $out .= '<li><input name="q['.$key.'][0]" id="q['.$key.'][0]" type="text" value="'.$value[0].'" /><input name="q['.$key.'][1]" id="q['.$key.'][1]" type="text" value="'.$value[1].'" /></li>';
        }

        $out .= '</ul></fieldset><div class="clearer"></div><p class="submitend" id="fpcrt_cnt"><input type="submit" name="fpcreate" id="fpcreate" onclick="poll.admin.update('.$pid.'); return false" value="'.$lang_poll['create'].'" /></p></div></fieldset></form></div>';

        return $out;
    }
}

$Poll = new Poll();
