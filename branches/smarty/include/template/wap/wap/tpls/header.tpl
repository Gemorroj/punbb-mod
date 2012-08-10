{assign var='Not_logged_in' value='Not logged in'}
{assign var='New_reports'   value='New reports'}
{assign var='Full_inbox'    value='Full inbox'}
{assign var='New_messages'  value='New messages'}
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML Basic 1.1//EN" "http://www.w3.org/TR/xhtml-basic/xhtml-basic11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="{$lang_common.lang_direction}">
<head>
    <meta http-equiv="Content-Type" content="{$pun_xhtml}; charset=UTF-8"/>
{* + <pun_head> *}
    <title>{$page_title|escape}</title>
    <link rel="stylesheet" type="text/css"
          href="{$pun_config.o_base_url}/style_wap/{if $pun_user}{$pun_user.style_wap}{else}{$pun_config.o_default_style_wap}{/if}.css"/>
{* - <pun_head> *}
{* <pun_rssname> *}
{* <pun_rss> *}
    <link rel="alternate" type="application/rss+xml" title="{$pun_config.o_board_title}"
          href="{$smarty.const.PUN_ROOT}rss.xml"/>
</head>

<body>

{if $basename == 'index.php'}
{* <pun_hd> *}
<div class="hd">
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
        {$lang_common.$Not_logged_in}
    </div>
    {/if}

    {if $pun_user.g_id < $smarty.const.PUN_GUEST}

        {if isset($result_header)} {* Query to db *}
        <div class="con">
            <a href="{$smarty.const.PUN_ROOT}admin_reports.php">{$lang_admin.$New_reports}</a>
        </div>
        {/if}

        {if $pun_config.o_maintenance == 1}
        <div class="con">
            <a href="{$smarty.const.PUN_ROOT}admin_options.php#maintenance">{$lang_admin.maintenance}</a>
        </div>
        {/if}
    {/if}

{* require PUN_ROOT . 'include/pms/wap_header_new_messages.php'; *}
    {if isset($conditions.count_new_msgs)}
    <div class="info">
        <a href="message_list.php"> {$lang_pms.$New_messages} ({$conditions.count_new_msgs}) </a>
    </div>
    {/if}

    {if isset($conditions.full_inbox)}
    <div class="red">
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
{/if}

{*
$basename == 'profile.php' ||
$basename == 'search.php' ||
$basename == 'message_list.php' ||
$basename == 'message_send.php' ||
$basename == 'message_delete.php' ||
$basename == 'filemap.php' ||

$basename == 'karma.php'
*}

{if $basename == 'misc.php'}

<div class="inbox">
    <a href="index.php">{$lang_common.Index}</a>
</div>
{/if}