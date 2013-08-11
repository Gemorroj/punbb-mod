<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML Basic 1.1//EN" "http://www.w3.org/TR/xhtml-basic/xhtml-basic11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="{$lang_common.lang_direction}">
<head>
    <meta name="viewport" content="width=device-width"/>
    <meta http-equiv="Content-Type" content="{$pun_xhtml}; charset=UTF-8"/>
    {* + <pun_head> *}
    <title>{$page_title|escape}</title>
    <link rel="stylesheet" type="text/css" href="{$pun_config.o_base_url}/include/template/wap/{if $pun_user}{$pun_user.style_wap}{else}{$pun_config.o_default_style_wap}{/if}/style.css"/>
    {* - <pun_head> *}
    {* <pun_rssname> *}
    {* <pun_rss> *}
    <link rel="alternate" type="application/rss+xml" title="{$pun_config.o_board_title}" href="{$smarty.const.PUN_ROOT}rss.xml"/>
</head>
<body>
{if 'index.php' != $basename}
    {include file='notification.tpl'}
{/if}