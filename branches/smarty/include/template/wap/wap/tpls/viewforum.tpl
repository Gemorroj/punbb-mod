{include file='header.tpl'}

{* Навигация: Главная / Форум / Тема *}
<div class="inbox">
    <a href="index.php">{$lang_common.Index}</a>&#160;&#187;&#160;{$cur_forum.forum_name|escape}
</div>



{assign var='Post_topic' value='Post topic'}
{assign var='Empty_forum' value='Empty forum'}
{assign var='Closed_icon_m' value='Closed icon_m'}
{assign var='New_icon_m' value='New icon_m'}
{assign var='Normal_icon' value='Normal icon'}

{assign var='j' value='false'}

{foreach from=$topics item=cur_topic}

<div class="{if $j = ! $j}msg{else}msg2{/if}">

{* +SUBJECT *}

    <strong>
        {if $cur_topic.moved_to}
            {$lang_forum.Moved_m}
            {elseif $cur_topic.closed}
            {$lang_common.$Closed_icon_m}
            {else}
            {$lang_common.$Normal_icon}
        {/if}
        &#160;
        {if $pun_config.poll_enabled == 1 && $cur_topic.has_poll}
            {$lang_forum.poll_m}
        {/if}

        {if $cur_topic.sticky == 1}
            {$lang_forum.Sticky_m}
        {/if}
    </strong>

{* // Should we display the dot or not? :) *}
    {if ! $pun_user.is_guest && $pun_config.o_show_dot == 1 and $cur_topic.has_posted == $pun_user.id}
        <strong>&#183;</strong>
    {/if}

    <a href="viewtopic.php?id={$cur_topic.moved_to|default:$cur_topic.id}">

        {if $pun_config.o_censoring == 1}
    {censor_words($cur_topic.subject)|escape}
{else}
    {$cur_topic.subject|escape}
{/if}
    </a>

    {if $cur_topic.num_pages_topic > 1}
        [{paginate($cur_topic.num_pages_topic, -1, "viewtopic.php?id={$cur_topic.id}")}]
    {/if}
    &#160;{$lang_common.by}&#160;{$cur_topic.poster|escape}

{* -SUBJECT *}

    {if ! $cur_topic.moved_to}
        &#160;({$cur_topic.num_replies}/{$cur_topic.num_views})

    {*  ! $cur_topic.moved_to &&*}
        {if ! $pun_user.is_guest &&
        $cur_topic.last_poster != $pun_user.username &&
        ! is_reading($cur_topic.log_time, $cur_topic.last_post) &&
        $cur_topic.last_post > $cur_topic.mark_read &&
        ($cur_topic.last_post > $pun_user.last_visit ||
        ($smarty.server.REQUEST_TIME - $cur_topic.last_post < $pun_user.mark_after)
        )}
            &#160;<span class="red">{$lang_common.$New_icon_m}</span>
        {/if}
        <br/>
<span class="sub">
&#187;&#160;<a
        href="viewtopic.php?pid={$cur_topic.last_post_id}#p{$cur_topic.last_post_id}">{$cur_topic.last_post|date_format:$date_format}</a>&#160;{$lang_common.by}
    &#160;{$cur_topic.last_poster|escape};
</span>
    {/if}
</div>

    {foreachelse}
<div class="in">{$lang_forum.$Empty_forum}</div>
{/foreach}

<div class="con">{$paging_links}</div>

{if (! $cur_forum.post_topics && $pun_user.g_post_topics == 1) || $cur_forum.post_topics == 1 || $is_admmod}
<div class="go_to">
    <a class="but" href="post.php?fid={$id}">{$lang_forum.$Post_topic}</a>
</div>
{/if}

{include file='footer.tpl'}