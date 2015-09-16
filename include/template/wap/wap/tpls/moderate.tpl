{extends file='layout.scheme.tpl'}
{block name='center'}

{assign var='Post_topic' value='Post topic'}
{assign var='Empty_forum' value='Empty forum'}
{assign var='Closed_icon_m' value='Closed icon_m'}
{assign var='New_icon_m' value='New icon_m'}
{assign var='Normal_icon' value='Normal icon'}
{assign var='j' value=false}
{assign var='button_status' value=true}

{* Навигация: Главная / Форум / Тема *}
<div class="inbox">
    <a href="index.php">{$lang_common.Index}</a>&#160;&#187;&#160;{$cur_forum.forum_name|escape}
</div>
<form method="post" action="moderate.php?fid={$fid}">

    {foreach from=$topics item=cur_topic}
        <div class="{if $j = ! $j}msg{else}msg2{/if}">
            <input type="checkbox" name="topics[{$cur_topic.id}]" value="1"/>
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

            <a href="viewtopic.php?id={$cur_topic.moved_to|default:$cur_topic.id}">{$cur_topic.subject|escape}</a>

            {if isset($cur_topic.num_pages_topic) and $cur_topic.num_pages_topic > 1}
                [{paginate($cur_topic.num_pages_topic, -1, "viewtopic.php?id={$cur_topic.id}")}]
            {/if}
            &#160;{$lang_common.by}&#160;{$cur_topic.poster|escape}

            {* -SUBJECT *}

            {if ! $cur_topic.moved_to}
                &#160;({$cur_topic.num_replies}/{$cur_topic.num_views})

            {*  ! $cur_topic.moved_to &&*}
                {if $cur_topic.last_post > $pun_user.last_visit}
                    &#160;
                    <span class="red">{$lang_common.$New_icon_m}</span>
                {/if}
                <br/>
                <span class="sub">
                    <a href="viewtopic.php?pid={$cur_topic.last_post_id}#p{$cur_topic.last_post_id}">{$cur_topic.last_post|date_format:$date_format}</a> {$lang_common.by} {$cur_topic.last_poster|escape}
                </span>
            {/if}
        </div>
    {foreachelse}
        {assign var='button_status' value=false}
        <div class="in">{$lang_forum.$Empty_forum}</div>
    {/foreach}
    <div class="con">{$paging_links}</div>

    {if (! isset($cur_forum.post_topics) && $pun_user.g_post_topics == 1) || $cur_forum.post_topics == 1 || $is_admmod}
    <div class="go_to">
        <input type="submit" name="move_topics" value="{$lang_misc.Move}" {if !$button_status}disabled="disabled"{/if} />
        <input type="submit" name="delete_topics" value="{$lang_misc.Delete}" {if !$button_status}disabled="disabled"{/if} />
        <input type="submit" name="open" value="{$lang_misc.Open}" {if !$button_status}disabled="disabled"{/if} />
        <input type="submit" name="close" value="{$lang_misc.Close}" {if !$button_status}disabled="disabled"{/if} />
    </div>
</form>
{/if}

{/block}