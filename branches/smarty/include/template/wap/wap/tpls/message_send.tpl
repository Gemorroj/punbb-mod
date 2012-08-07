{include file='header.tpl'}

{assign var='cur_index' value='1'}
{assign var='Send_a_message' value='Send a message'}
{assign var='Write_message_legend' value='Write message legend'}
{assign var='Send_to' value='Send to'}
{assign var='img_tag' value='img tag'}
{assign var='Hide_smilies' value='Hide smilies'}
{assign var='Save_message' value='Save message'}
{assign var='New_message' value='New message'}

<div class="con">
    <strong>{$lang_pms.$Send_a_message}</strong>
</div>
<form method="post" id="post" action="message_send.php?action=send">
    <div class="input">
        <strong>{$lang_common.$Write_message_legend}</strong><br/>
        <input type="hidden" name="form_sent" value="1"/>
        <input type="hidden" name="topic_redirect" value="{if isset($smarty.get.tid)}{intval($smarty.get.tid)}{/if}"/>
        <input type="hidden" name="topic_redirect"
               value="{if isset($smarty.post.from_profile)}{intval($smarty.post.from_profile)}{/if}"/>
        <input type="hidden" name="form_user"
               value="{if ! $pun_user.is_guest}{$pun_user.username|escape}{else}Guest{/if}"/>
        {$lang_pms.$Send_to}<br/>
        <input type="text" name="req_username" maxlength="25" value="{$username|escape}"
               tabindex="{assign var='cur_index' value='`$cur_index` + 1'}"/><br/>
        {$lang_common.Subject}<br/>
        <input class="longinput" type="text" name="req_subject" value="{$subject|escape}" maxlength="70"
               tabindex="{assign var='cur_index' value='`$cur_index` + 1'}"/><br/>
        {$lang_common.Message}<br/>
        <textarea name="req_message" rows="4" cols="24"
                  tabindex="{assign var='cur_index' value='`$cur_index` + 1'}">{$quote|escape}</textarea><br/>

        <a href="help.php?id=3">{$lang_common.Smilies}</a>
        {if $pun_config.o_smilies == 1}<span class="green">{$lang_common.on_m}</span>{else}<span
            class="grey">{$lang_common.off_m}</span>{/if};
        <a href="help.php?id=1">{$lang_common.BBCode}</a>
        {if $pun_config.p_message_bbcode == 1}<span class="green">{$lang_common.on_m}</span>{else}<span
            class="grey">{$lang_common.off_m}</span>{/if};
        <a href="help.php?id=4">{$lang_common.$img_tag}</a>
        {if $pun_config.p_message_img_tag == 1}<span class="green">{$lang_common.on_m}</span>{else}<span
            class="grey">{$lang_common.off_m}</span>{/if}<br/>

    {if $pun_config.o_smilies == 1}
        <label for="hide_smilies"><input type="checkbox" id="hide_smilies" name="hide_smilies" value="1"
               tabindex="{assign var='cur_index' value='`$cur_index` + 1'}"{if isset($smarty.post.hide_smilies)}
               checked="checked"{/if} />{$lang_post.$Hide_smilies}<br/></label>
    {/if}
        <label for="savemessage"><input type="checkbox" id="savemessage" name="savemessage" value="1" checked="checked"
               tabindex="{assign var='cur_index' value='`$cur_index` + 1'}"/>{$lang_pms.$Save_message}</label>
    </div>
    <div class="go_to">
        <input type="submit" name="submit" value="{$lang_pms.Send}"
               tabindex="{assign var='cur_index' value='`$cur_index` + 1'}" accesskey="s"/>
    </div>
</form>

<div class="navlinks">
    <a href="message_list.php?box=0">{$lang_pms.Inbox}</a> | <a href="message_list.php?box=1">{$lang_pms.Outbox}</a> |
    <a href="message_list.php?box=2">{$lang_pms.Options}</a> | <a href="message_send.php">{$lang_pms.$New_message}</a>
</div>

{include file='footer.tpl'}