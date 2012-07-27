{include file='header.tpl'}

<div class="inbox">
<a href="index.php">{$lang_common.Index}</a> &#187;

{if $tid}
<a href="viewforum.php?id={$cur_posting.id}">{$cur_posting.forum_name|escape}</a>
{elseif $fid}
{$cur_posting.forum_name|escape}
{/if}

{if isset($cur_posting.subject)}
&#187; {$cur_posting.subject|escape}
{/if}
</div>

{if $errors}

{assign var='Post_errors' value='Post errors'}
<div class="red">{$lang_post.$Post_errors}</div>

<div class="msg">
{foreach from=$errors item=cur_error}
&#187; {$cur_error}<br/>
{/foreach}
</div>

{elseif isset($smarty.post.preview)}

{assign var='Post_preview' value='Post preview'}
<div class="info">{$lang_post.$Post_preview}</div>
<div class="msg">{* parse_message($message, $hide_smilies) *}</div>
{/if}

{assign var='Post_a_reply' value='Post a reply'}
{assign var='Post_new_topic' value='Post new topic'}


<div class="con">
{if $tid}
    {$lang_post.$Post_a_reply}
{elseif $fid}
    {$lang_post.$Post_new_topic}
{/if}
</div>

{if $tid}
<form method="post" action="post.php?action=post&amp;tid={$tid}" {if $file_limit}enctype="multipart/form-data"{/if}>
{elseif $fid}
<form method="post" action="post.php?action=post&amp;fid={$fid}" enctype="multipart/form-data">
{/if}

<div class="input">

{*
if ($pun_config['poll_enabled'] == 1 && $fid) {
    if (!$_GET['poll']) {
        include PUN_ROOT.'lang/'.$pun_user['language'].'/poll.php';
        echo '<a href="post.php?fid='.$fid.'&amp;poll=1">'.$lang_poll['poll'].'</a><br/>';
    } else {
        include_once PUN_ROOT.'include/poll/poll.inc.php';
        $Poll->wap_showContainer();
        $cur_index = 8;
    }
}
*}

<input type="hidden" name="form_sent" value="1" />
<input type="hidden" name="form_user" value="{$pun_user.username|escape|default:'Guest'}" />

{if $pun_user.is_guest}
    
    {assign var='Guest_name' value='Guest name'}
    
    <strong>{$lang_post.$Guest_name}</strong><br />
    <input type="text" name="req_username" value="{$username|escape}" tabindex="{assign var='cur_index' value=$cur_index+1}" /><br />
    {if $pun_config.p_force_guest_email == 1}<strong>{$lang_common.E-mail}</strong>{else}{$lang_common.E-mail}{/if}<br />
    <input type="text" name="{if $pun_config.p_force_guest_email == 1}req_email{else}email{/if}" value="{$email|escape}" tabindex="{assign var='cur_index' value=$cur_index+1}" /><br />
{/if}

{if $fid}
    <strong>{$lang_common.Subject}</strong><br />
    <input type="text" name="req_subject" value="{$subject|escape}" maxlength="70" tabindex="{assign var='cur_index' value=$cur_index+1}" /><br />
{/if}

<textarea name="req_message" rows="4" cols="24" tabindex="{assign var='cur_index' value=$cur_index+1}">
{if $smarty.post.req_message}
{$message|escape}
{elseif $quote}
{$quote}
{/if}
</textarea><br />

<a href="help.php?id=3">{$lang_common.Smilies}</a>
{if $pun_config.o_smilies == 1}<span class="green">{$lang_common.on_m}</span>{else}<span class="grey">{$lang_common.off_m}</span>{/if}
<a href="help.php?id=1">{$lang_common.BBCode}</a>
{if $pun_config.p_message_bbcode == 1}<span class="green">{$lang_common.on_m}</span>{else}<span class="grey">{$lang_common.off_m}</span>{/if}
{assign var='img_tag' value='img tag'}
<a href="help.php?id=4">{$lang_common.$img_tag}</a>
{if $pun_config.p_message_img_tag == 1}<span class="green">{$lang_common.on_m}</span>{else}<span class="grey">{$lang_common.off_m}</span>{/if}
</div>

<div class="input2">

{if $pun_user.g_post_replies == 2}
<img src="{$pun_config.o_base_url}/include/captcha/captcha.php?{session_name()}={session_id()}" alt="" /><br />
{assign var='Image_text' value='Image text'}
{$lang_post.$Image_text}<br />
<input type="text" name="req_image_" size="16" maxlength="16" /><br />
{/if}

{assign var='num_to_upload' value=min($file_limit, 20)}

{if $can_upload && $num_to_upload > 0}
    {$lang_fu.Attachments}<br/>
    {include file='{$smarty.const.PUN_ROOT}include/attach/wap_post_input.php'}
{/if}

{assign var='Hide_smilies' value='Hide smilies'}
{assign var='Merge_posts'  value='Merge posts'}

{if ! $pun_user.is_guest}
    {if $pun_config.o_smilies == 1}
    <input type="checkbox" name="hide_smilies" value="1" tabindex="{assign var='cur_index' value=$cur_index+1}" {if isset($smarty.post.hide_smilies)}checked="checked"{/if} />{$lang_post.$Hide_smilies}<br/>
    {/if}
    
    {if $is_admmod}
    <input type="checkbox" name="merge" value="1" checked="checked" />{$lang_post.$Merge_posts}<br/>
    {/if}
    
    {if $pun_config.o_subscriptions == 1}
    <input type="checkbox" name="subscribe" value="1" tabindex="{assign var='cur_index' value=$cur_index+1}" {if isset($smarty.post.subscribe)}checked="checked"{/if} />{$lang_post.Subscribe}<br/>
    {/if}
{elseif $pun_config.o_smilies == 1}
<input type="checkbox" name="hide_smilies" value="1" tabindex="{assign var='cur_index' value=$cur_index+1}" {if isset($smary.post.hide_smilies)}checked="checked"{/if} />{$lang_post.$Hide_smilies}
{/if}

</div>
<div class="go_to">
<input type="hidden" name="form_t" value="{$smarty.server.REQUEST_TIME}" />
<input type="submit" name="submit" value="{$lang_common.Submit}" tabindex="{assign var='cur_index' value=$cur_index+1}" accesskey="s" />
<input type="submit" name="preview" value="{$lang_post.Preview}" tabindex="{assign var='cur_index' value=$cur_index+1}" accesskey="p" />
</div>
</form>

{include file='footer.tpl'}
