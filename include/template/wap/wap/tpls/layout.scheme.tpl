{extends file='layout.html.tpl'}

{block name='head'}
<meta name="viewport" content="width=device-width"/>
<meta http-equiv="Content-Type" content="{$pun_xhtml}; charset=UTF-8"/>
<title>{$page_title|escape}</title>
<link rel="stylesheet" type="text/css" href="{$pun_config.o_base_url}/style/wap/{if $pun_user}{$pun_user.style_wap}{else}{$pun_config.o_default_style_wap}{/if}/style.css"/>
<link rel="alternate" type="application/rss+xml" title="{$pun_config.o_board_title}" href="{$smarty.const.PUN_ROOT}rss.xml"/>
{/block}

{block name='body'}
{block name='logo'}{/block}
{include file='notification.tpl'}
{block name='center'}{/block}

{assign var='User_list'      value='User list'}
{assign var='Link_separator_m' value='Link separator_m'}

{if in_array($basename, array(
    'profile.php', 'search.php',       'userlist.php',
    'uploads.php', 'message_list.php', 'message_send.php',
    'help.php',    'misc.php',         'filemap.php',
    'karma.php',   'index.php'
))}
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

<div class="foot">
    <a href="{$pun_config.o_base_url}">{$lang_common.Index}</a><br/>
    <a class="red" href="{$smarty.const.PUN_ROOT}">WEB</a>
</div>

<div class="copy">
    <a href="http://forum.wapinet.ru">PunBB Mod v{$pun_config.o_show_version}</a><br/>
    <span class="red">{sprintf('%.3f', microtime(true) - $pun_start)} s</span>
</div>
{/block}