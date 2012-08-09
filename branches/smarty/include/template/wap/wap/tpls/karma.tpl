{include file='header.tpl'}

{* Навигация: Главная / Пользователи *}
<div class="inbox">
    <a href="index.php">{$lang_common.Index}</a>&#160;&#187;&#160;{$lang_common.Karma}
</div>

{*
<div class="con">
    {$lang_common.Karma}
    {if $id != $pun_user.id}
        <strong>{$username|escape}</strong>
    {/if}
</div>
*}

<div class="in">
{$lang_common.Username} | {$lang_common.Vote} | {$lang_common.Date}
</div>

{foreach from=$array item=cur_karma}
<div class="{if $j = !$j}msg{else}msg2{/if}">
    {if $cur_karma.from}
        <a href="profile.php?id={$cur_karma.id}">{$cur_karma.from|escape}</a>
        {else}
        {$lang_common.Deleted}
    {/if}

    {if $cur_karma.vote > 0}
        <span class="green">+</span>
        {else}
        <span class="red">-</span>
    {/if}
    [{$cur_karma.time|date_format:$date_format}]
</div>
    {foreachelse}
<div class="in">
    {$lang_common.Karma}: {$karma.karma}
</div>
{/foreach}

{if $page_links}
<div class="con">{$lang_common.Pages}: {$page_links}</div>
{/if}

{include file='footer.tpl'}