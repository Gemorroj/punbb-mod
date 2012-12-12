{include file='header.tpl'}

{assign var='cur_category' value=0}
{assign var='cur_forum' value=0}
{assign var='cur_topic' value=0}
{assign var='j' value=false}

{* +Самоделка *}
{assign var='addIndex1' value='Вложения'}
{assign var='addIndex2' value='Вложения отсутствуют.'}

{* Навигация *}
<div class="inbox">
    <a href="index.php">{$lang_common.Index}</a>&#160;&#187;&#160;{$addIndex1}
</div>

{* -Самоделка *}

{if isset($_GET.user_id)}
<div class="con">
    {$lang_common.Member} - {$lang_common.Info}
</div>
<div class="msg">
    {$lang_common.Username}: <strong>{$user.username|escape}</strong><br/>
    {$lang_common.Files}: {$user.num_files}<br/>
</div>
{/if}

{foreach from=$attachments item=attachment}

    {if $attachment.cat_id != $cur_category || $attachment.forum_id != $cur_forum}
    <div class="cat">{$categories[$attachment.cat_id]|escape} &#187; <a
            href="viewforum.php?id={$attachment.forum_id}">{$forums[$attachment.forum_id].forum_name|escape}</a></div>
        {assign var='cur_category' value=$attachment.cat_id}
        {assign var='cur_forum' value=$attachment.forum_id}
    {/if}

{*// A new topic since last iteration?*}
    {if $attachment.tid != $cur_topic}
    <div class="in">
        <a href="viewtopic.php?id={$attachment.tid}">{$attachment.subject|escape}</a> <span class="small">({$attachment.posted})</span></div>
        {assign var='cur_topic' value=$attachment.tid}
    {/if}

<div class="{if $j = !$j}msg{else}msg2{/if}">

    {if $attachment.can_download}
        <a href="{$smarty.const.PUN_ROOT}download.php?aid={$attachment.id}">{$attachment.filename|escape}</a>
        {else}
        {$attachment.filename|escape}
    {/if}

{*<span style="font-style:italic;font-size:smaller;"></span>*}

{include file='attachments.info.tpl'}

</div>

    {foreachelse}
<div class="red">&#160;{$addIndex2}</div>
{/foreach}

<div class="con">{$lang_common.Pages}:&#160;{$paging_links}</div>

{include file='footer.tpl'}