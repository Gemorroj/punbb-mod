<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    {* + <pun_head> *}
    <title>{$page_title|escape}</title>
    <link rel="stylesheet" type="text/css" href="{$smarty.const.PUN_ROOT}style/wap/{if $pun_user}{$pun_user.style_wap}{else}{$pun_config.o_default_style_wap}{/if}/style.css"/>
    {* - <pun_head> *}
    {* <pun_rssname> *}
    {* <pun_rss> *}
    <link rel="alternate" type="application/rss+xml" title="{$pun_config.o_board_title}" href="{$smarty.const.PUN_ROOT}rss.xml"/>
</head>
<body>
{if 'index.php' != $basename}
    {include file='notification.tpl'}
{/if}