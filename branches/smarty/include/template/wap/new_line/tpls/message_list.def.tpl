{include file='header.tpl'}


<div class="con">
    <strong>{$name}</strong>
</div>

{if isset($smarty.get.id)}
    <div class="msg">
        <div class="zag_in">
            {if $cur_post.poster_id > 1 and $pun_config.o_avatars == 1 && $cur_post.use_avatar == 1 && $pun_user.show_avatars}
            {* Аватарка *}
                <img src="{$smarty.const.PUN_ROOT}{$pun_config.o_avatars_dir}/{$cur_post.poster_id}.{$cur_post.avatar_type}" alt="*"/>
            {/if}
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
            {$cur_post.posted}<br/>

            {if $smarty.get.id > 0}
                {if ! $status}
                    <a href="message_send.php?id={$cur_post.id}&amp;reply={$cur_post.mid}">{$lang_pms.Reply_m}</a>
                    <a href="message_send.php?id={$cur_post.id}&amp;quote={$cur_post.mid}">{$lang_pms.Quote_m}</a>
                {/if}
                <a href="message_delete.php?id={$cur_post.mid}&amp;box={$box}&amp;p={$p}">{$lang_pms.Delete_m}</a>
            {else}
                <a href="message_delete.php?id={$cur_post.id}&amp;box={$box}&amp;p={$p}">{$lang_pms.Delete}</a>
            {/if}
        </div>{$cur_post.message}</div>
{/if}

<form method="post" action="message_list.php?">
    {* Fetch messages *}
    {assign var='j' value='false'}
    {foreach from=$messages item=cur_mess}
        <div class="{if $j = ! $j}in{else}in2{/if}">
            {if isset($smarty.get.id) && $cur_mess.id == $smarty.get.id}{assign var='strong' value='1'}{/if}
            &#187;
            {if $strong}<strong>{/if}
                <a href="message_list.php?id={$cur_mess.id}&amp;p={$p}&amp;box={$box}">{$cur_mess.subject|escape}</a>
            {if $strong}</strong>{/if}
            (<a href="profile.php?id={$cur_mess.sender_id}">{$cur_mess.sender}</a>)
            {($cur_mess.posted|date_format:$date_format)}

            {assign var='New_icon_m' value='New icon_m'}
            {if $cur_mess.showed == '0'}
                <span class="red">{$lang_common.$New_icon_m}</span>
            {/if}
            <input type="checkbox" name="delete_messages[]" value="{$cur_mess.id}"/>
        </div>
    {foreachelse}
        {assign var='No_messages' value='No messages'}
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