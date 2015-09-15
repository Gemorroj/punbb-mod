{assign var='Moderate_forum' value='Moderate forum'}
{assign var='Delete_posts'   value='Delete posts'}
{assign var='Move_topic'     value='Move topic'}
{assign var='Open_topic'     value='Open topic'}
{assign var='Close_topic'    value='Close topic'}
{assign var='Unstick_topic'  value='Unstick topic'}
{assign var='Stick_topic'    value='Stick topic'}
{assign var='User_list'      value='User list'}

{assign var='Link_separator_m' value='Link separator_m'}

{if $basename == 'profile.php' ||
$basename == 'search.php' ||
$basename == 'userlist.php' ||
$basename == 'uploads.php' ||
$basename == 'message_list.php' ||
$basename == 'message_send.php' ||
$basename == 'help.php' ||
$basename == 'misc.php' ||
$basename == 'filemap.php' ||
$basename == 'karma.php' ||
$basename == 'index.php'}

<div class="navlinks">
{* Index and Userlist should always be displayed *}
    <a href="userlist.php">{$lang_common.$User_list}</a>

    {if $pun_config.o_rules == 1}
        {$lang_common.$Link_separator_m}<a href="misc.php?action=rules">{$lang_common.Rules}</a>
    {/if}
    
    {if $pun_user.g_search == 1 or $pun_user.g_id > $smarty.const.PUN_MOD}
        {$lang_common.$Link_separator_m}<a href="search.php">{$lang_common.Search}</a>
    {/if}

    {if ! $pun_user.is_guest}
        {$lang_common.$Link_separator_m}<a href="uploads.php">{$lang_common.Uploader}</a>
        {$lang_common.$Link_separator_m}<a href="filemap.php">{$lang_common.Attachments}</a>
    {/if}
</div>
{/if}

{if $pun_config.o_quickjump == 1}
    {$quickjump|default:""}
{/if}

{if isset($is_admmod) && $is_admmod}
    {if $basename == 'viewforum.php'}
        <div class="con">
            <a class="but" href="moderate.php?fid={$forum_id}&amp;p={$p}">{$lang_common.$Moderate_forum}</a>
        </div>
    {elseif $basename == 'viewtopic.php'}
        <div class="con">
            <span class="sub">
                <a href="moderate.php?fid={$forum_id}&amp;tid={$id}&amp;p={$p}">{$lang_common.$Delete_posts}</a>{$lang_common.$Link_separator_m}
                <a href="moderate.php?fid={$forum_id}&amp;move_topics={$id}">{$lang_common.$Move_topic}</a>
                {if $cur_topic.closed == 1}
                    {$lang_common.$Link_separator_m}<a href="moderate.php?fid={$forum_id}&amp;open={$id}">{$lang_common.$Open_topic}</a>
                {else}
                    {$lang_common.$Link_separator_m}<a href="moderate.php?fid={$forum_id}&amp;close={$id}">{$lang_common.$Close_topic}</a>
                {/if}

                {if $cur_topic.sticky == 1}
                    {$lang_common.$Link_separator_m}<a href="moderate.php?fid={$forum_id}&amp;unstick={$id}">{$lang_common.$Unstick_topic}</a>
                {else}
                    {$lang_common.$Link_separator_m}<a href="moderate.php?fid={$forum_id}&amp;stick={$id}">{$lang_common.$Stick_topic}</a>
                {/if}
            </span>
        </div>
    {/if}
{/if}
<div class="foot">
    <a href="/">{$smarty.server.HTTP_HOST}</a><br/>
    <a class="red" href="{$smarty.const.PUN_ROOT}">WEB</a>
</div>

<div class="copy">
    <a href="{$pun_config.o_base_url}">PunBB Mod v{$pun_config.o_show_version}</a><br/>
    <span class="red">{sprintf('%.3f', microtime(true) - $pun_start)} s</span>
</div>

</body>
</html>
