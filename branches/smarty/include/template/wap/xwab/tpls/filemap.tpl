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

{foreach from=$attachments item=row}

    {if $row.cat_id != $cur_category || $row.forum_id != $cur_forum}
    <div class="cat">{$categories[$row.cat_id]|escape} &#187; <a
            href="viewforum.php?id={$row.forum_id}">{$forums[$row.forum_id].forum_name|escape}</a></div>
        {assign var='cur_category' value=$row.cat_id}
        {assign var='cur_forum' value=$row.forum_id}
    {/if}

{*// A new topic since last iteration?*}
    {if $row.tid != $cur_topic}
    <div class="in">
        <a href="viewtopic.php?id={$row.tid}">{$row.subject|escape}</a> <span class="small">({$row.posted})</span></div>
        {assign var='cur_topic' value=$row.tid}
    {/if}

<div class="{if $j = !$j}msg{else}msg2{/if}">

    {if $row.can_download}
        <a href="{$smarty.const.PUN_ROOT}download.php?aid={$row.id}">{$row.filename|escape}</a>
        {else}
        {$row.filename|escape}
    {/if}

{*<span style="font-style:italic;font-size:smaller;"></span>*}

    {round($row.size/1024,1)}kb, {if preg_match('|^image/(.*)$|i', $row.mime, $regs)}{$regs[1]} {$row.image_dim}, {/if}
    downloads: {$row.downloads}

</div>

    {foreachelse}
<div class="red">&#160;{$addIndex2}</div>
{/foreach}

<div class="con">{$lang_common.Pages}:&#160;{$paging_links}</div>

{include file='footer.tpl'}