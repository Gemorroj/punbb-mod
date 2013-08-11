{include file='header.tpl'}

{assign var='Private_Messages' value='Private Messages'}
{assign var='Link_separator_m' value='Link separator_m'}
{assign var='New_icon_m' value='New icon_m'}
{assign var='No_messages' value='No messages'}
{assign var='j' value=false}

{* Навигация: Главная / Пользователи *}
<div class="inbox">
    <a href="index.php">{$lang_common.Index}</a>&#160;&#187;&#160;{$lang_pms.$Private_Messages}
</div>

<div class="con">
    <strong>{$name}</strong>
</div>

{if isset($smarty.get.id)}
<div class="msg">
    <div class="zag_in">
        {* Аватарка *}
            {$cur_post.user_avatar}
        <strong>
            {if $smarty.get.id > 0}
                <a href="profile.php?id={$cur_post.id}">{$cur_post.username|escape}</a>
            {else}
                {$cur_post.sender|escape}
            {/if}
        </strong>

        {if $cur_post.is_online == $cur_post.id}
            <span class="green">{$lang_topic.Online_m}</span>
            {else}
            <span class="grey">{$lang_topic.Offline_m}</span>
        {/if}
        <br/>
        {$cur_post.posted|date_format:$date_format}<br/>

        {if $smarty.get.id > 0}
            {if ! $status}
                <a href="message_send.php?id={$cur_post.id}&amp;reply={$cur_post.mid}">{$lang_pms.Reply_m}</a>{$lang_topic.$Link_separator_m}
                <a href="message_send.php?id={$cur_post.id}&amp;quote={$cur_post.mid}">{$lang_pms.Quote_m}</a>{$lang_topic.$Link_separator_m}
            {/if}
            <a href="message_delete.php?id={$cur_post.mid}&amp;box={$box}&amp;p={$p}">{$lang_pms.Delete_m}</a>
            {else}
            <a href="message_delete.php?id={$cur_post.id}&amp;box={$box}&amp;p={$p}">{$lang_pms.Delete}</a>
        {/if}
    </div>{$cur_post.message}</div>
{/if}

<form method="post" action="message_list.php?">
{* Fetch messages *}
{foreach from=$messages item=cur_mess}
    <div class="{if $j = ! $j}in{else}in2{/if}">
        {if isset($smarty.get.id) && $cur_mess.id == $smarty.get.id}{assign var='strong' value='1'}{else}{assign var='strong' value='0'}{/if}
        <a href="profile.php?id={$cur_mess.sender_id}">{$cur_mess.sender}</a>
        ({$cur_mess.posted|date_format:$date_format})<br/>
        <input type="checkbox" name="delete_messages[]" value="{$cur_mess.id}"/>  {if $strong}<strong>{/if}<a href="message_list.php?id={$cur_mess.id}&amp;p={$p}&amp;box={$box}">{$cur_mess.subject|escape}</a>{if $strong}
    </strong>{/if}

        {if $cur_mess.showed == '0'}
            <span class="red">{$lang_common.$New_icon_m}</span>
        {/if}
    </div>
{foreachelse}
    <div class="in">{$lang_pms.$No_messages}</div>
{/foreach}

{if $messages}
    <div class="go_to">
        <input type="hidden" name="box" value="{$box}"/>
        {if $all}
            <input type="submit" value="{$lang_pms.Delete}"/>
        {/if}
    </div>
{/if}
</form>
<div class="con">{$lang_common.Pages}: {$page_links}</div>

{include file='message_list.navlinks.tpl'}
{include file='footer.tpl'}