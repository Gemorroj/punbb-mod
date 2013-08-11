{include file='header.tpl'}

{assign var='Link_separator_m' value='Link separator_m'}
{assign var='Empty_board' value='Empty board'}
{assign var='Show_new_posts' value='Show new posts'}
{assign var='Mark_all_as_read' value='Mark all as read'}
{assign var='No_of_users' value='No of users'}
{assign var='No_of_topics' value='No of topics'}
{assign var='No_of_posts' value='No of posts'}
{assign var='Users_online' value='Users online'}
{assign var='Guests_online' value='Guests online'}
{assign var='j' value=false}
{assign var='cur_category' value=0}

<div class="hd">
    {* <pun_hd> *}
    <img src="{$pun_config.o_base_url}/include/template/wap/{$pun_user.style_wap}/imgs/logo.gif" alt="{$lang_common.Forum} {$smarty.server.HTTP_HOST}"/>
    {$pun_config.o_board_title}
</div>

{if $pun_config.o_board_desc}
{* <pun_desc> *}
    <div class="hd_bott">
        {$pun_config.o_board_desc}
    </div>
{/if}

{include file='notification.tpl'}

{if $pun_config.o_announcement == 1}
{* <pun_announcement> *}
    <div class="incqbox">
        {$lang_common.Announcement}
    </div>
    <div class="msg">
        {$pun_config.o_announcement_message}
    </div>
{/if}

<div class="navlinks">
    {if $pun_user.is_guest}
        <a href="login.php">{$lang_common.Login}</a>{$lang_index.$Link_separator_m}<a href="register.php">{$lang_common.Register}</a>
    {else}
        <a href="profile.php?id={$pun_user.id}">{$lang_common.Profile}&#160;(<span style="font-weight: bold">{$pun_user.username|escape}</span>)</a>
        {if $pun_config.o_pms_enabled && $pun_user.g_pm == 1}
            {$lang_index.$Link_separator_m}<a href="message_list.php">{$lang_pms.Private}</a>
        {/if}
    {* Тут какая-то фигня. Был знак ">" и не работало. Может я когда переводил напутал сам, или так было. *}
        {if $pun_user.g_id == $smarty.const.PUN_MOD or $pun_user.g_id == $smarty.const.PUN_ADMIN}
            {$lang_index.$Link_separator_m}<a href="{$smarty.const.PUN_ROOT}admin_index.php">{$lang_common.Admin_m}</a>
        {/if}
        {$lang_index.$Link_separator_m}<a href="login.php?action=out&amp;id={$pun_user.id}&amp;csrf_token={$logout}">{$lang_common.Logout}</a>
    {/if}
</div>

{foreach from=$forums item=cur_forum}
    {if $cur_forum.cid != $cur_category}
        {assign var='cur_category' value={$cur_forum.cid}}
        <div class="cat">
            <span class="sp_cat">{$cur_forum.cat_name|escape}</span>
        </div>
    {/if}
    <div class="{if $j = ! $j}in{else}in2{/if}">
        {if $cur_forum.redirect_url}
            <a href="{$cur_forum.redirect_url|escape}">{$cur_forum.forum_name|escape}</a>
        {else}
            <a href="viewforum.php?id={$cur_forum.fid}">{$cur_forum.forum_name|escape}</a> ({$cur_forum.num_topics}/{$cur_forum.num_posts})
        {/if}
        {if $cur_forum.last_post}
            <br/>
            <span class="sub">
                &#187; <a href="viewtopic.php?pid={$cur_forum.last_post_id}#p{$cur_forum.last_post_id}">{$cur_forum.subject|escape}</a> ({$cur_forum.last_post|date_format:$date_format} {$lang_common.by} {$cur_forum.last_poster|escape})
            </span>
        {/if}
    </div>
{foreachelse}
    <div class="in">{$lang_index.$Empty_board}</div>
{/foreach}

{if ! $pun_user.is_guest}
    <div class="go_to">
        <a class="but" href="search.php?action=show_new">{$lang_common.$Show_new_posts}</a>
        <a class="but" href="misc.php?action=markread">{$lang_common.$Mark_all_as_read}</a>
    </div>
{/if}

<div class="incqbox">
    {$lang_index.$No_of_users}: {$stats.total_users}<br/>
    {$lang_index.$No_of_topics}: {$stats.total_topics}<br/>
    {$lang_index.$No_of_posts}: {$stats.total_posts}<br/>
    {if $pun_config.o_users_online == 1}
        {$lang_index.$Users_online}: {$num_users|default:'0'}<br/>
        {$lang_index.$Guests_online}: {$num_guests|default:'0'}
        {if isset($num_users) and $num_users > 0}
            </div>
            <div class="act">
            {$lang_index.Online}:
            {foreach from=$users item=pun_user_online}
                <a href="profile.php?id={$pun_user_online.user_id}">{$pun_user_online.ident|escape}</a>
            {/foreach}
        {/if}
    {/if}
</div>

{include file='footer.tpl'}