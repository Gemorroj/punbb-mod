{include file='header.tpl'}

<div class="hd">
{* <pun_hd> *}
<img src="{$pun_config.o_base_url}/style_wap/{$pun_user.style_wap}/logo.gif"
     title="{$lang_common.Forum} {$smarty.server.HTTP_HOST}" alt="{$lang_common.Forum} {$smarty.server.HTTP_HOST}"/>
</div>

{if $pun_config.o_board_desc}
{* <pun_desc> *}
<div class="hd_bott">
{$pun_config.o_board_desc}
</div>
{/if}

{* + <pun_status> *}
{if $pun_user.is_guest}
<div class="con">
{assign var='Not_logged_in' value='Not logged in'}
{$lang_common.$Not_logged_in}
</div>
{/if}

{if $pun_user.g_id < $smarty.const.PUN_GUEST}
{if isset($conditions.reports)}
{* Результат расчитывается в /wap/header.php *}
<div class="con">
{assign var='New_reports' value='New reports'}
<a href="{$smarty.const.PUN_ROOT}admin_reports.php">{$lang_admin.$New_reports}&#160;({$conditions.reports})</a>
</div>
{/if}
{if $pun_config.o_maintenance == 1}
<div class="con">
<a href="{$smarty.const.PUN_ROOT}admin_options.php#maintenance">{$lang_admin.maintenance}</a>
</div>
{/if}
{/if}

{if isset($conditions.count_new_msgs)}
{* Результат расчитывается в /include/pms/wap_header_new_messages.php *}
<div class="info">
{assign var='New_messages' value='New messages'}
<a href="message_list.php">{$lang_pms.$New_messages}&#160;({$conditions.count_new_msgs})</a>
</div>
{/if}

{if isset($conditions.full_inbox)}
{* Результат расчитывается в /include/pms/wap_header_new_messages.php *}
<div class="red">
{assign var='Full_inbox'   value='Full inbox'}
<a href="message_list.php">{$lang_pms.$Full_inbox}</a>
</div>
{/if}
{* - <pun_status> *}

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
{assign var='Link_separator' value='Link separator'}
{if $pun_user.is_guest}
<a href="login.php">{$lang_common.Login}</a>{$lang_common.$Link_separator}<a href="register.php">{$lang_common.Register}</a>
{else}
<a href="profile.php?id={$pun_user.id}">{$lang_common.Profile}&#160;(<span style="font-weight: bold">{$pun_user.username|escape}</span>)</a>
{if $pun_config.o_pms_enabled && $pun_user.g_pm == 1}
{$lang_common.$Link_separator}<a href="message_list.php">{$lang_pms.Private}</a>
{/if}
{* Тут какая-то фигня. Был знак ">" и не работало. Может я когда переводил напутал сам, или так было. *}
{if $pun_user.g_id == $smarty.const.PUN_MOD or $pun_user.g_id == $smarty.const.PUN_ADMIN}
{$lang_common.$Link_separator}<a href="{$smarty.const.PUN_ROOT}admin_index.php">{$lang_common.Admin_m}</a>
{/if}
{$lang_common.$Link_separator}<a href="login.php?action=out&amp;id={$pun_user.id}&amp;csrf_token={$logout}">{$lang_common.Logout}</a>
{/if}
</div>

{assign var='j' value=false}
{assign var='cur_category' value=''}
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
&#187; <a href="viewtopic.php?pid={$cur_forum.last_post_id}#p{$cur_forum.last_post_id}">{$cur_forum.subject|escape}</a>&#160;({$cur_forum.last_post|date_format:$date_format}
&#160;{$lang_common.by}&#160;{$cur_forum.last_poster|escape})
</span>
{/if}
</div>
{foreachelse}
{assign var='Empty_board' value='Empty board'}
<div class="in">{$lang_index.$Empty_board}</div>
{/foreach}

{if ! $pun_user.is_guest}
<div class="go_to">
{assign var='Show_new_posts' value='Show new posts'}
<a class="but" href="search.php?action=show_new">{$lang_common.$Show_new_posts}</a>
{assign var='Mark_all_as_read' value='Mark all as read'}
<a class="but" href="misc.php?action=markread">{$lang_common.$Mark_all_as_read}</a>
</div>
{/if}

<div class="incqbox">
{assign var='No_of_users' value='No of users'}
{$lang_index.$No_of_users}: {$stats.total_users}<br/>
{assign var='No_of_topics' value='No of topics'}
{$lang_index.$No_of_topics}: {$stats.total_topics}<br/>
{assign var='No_of_posts' value='No of posts'}
{$lang_index.$No_of_posts}: {$stats.total_posts}<br/>

{if $pun_config.o_users_online == 1}
{assign var='Users_online' value='Users online'}
{$lang_index.$Users_online}: {$num_users|default:'0'}<br/>
{assign var='Guests_online' value='Guests online'}
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