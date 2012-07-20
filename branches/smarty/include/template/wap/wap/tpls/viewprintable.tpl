{assign var='date_format' value='%Y-%m-%d %H:%M:%S'}

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html dir="{$lang_common.lang_direction}">
<head>
<meta http-equiv="Content-Type" content="text/html; charset={$lang_common.lang_encoding}" />
<link rel="stylesheet" href="../printable.css" type="text/css">
<title>{$page_title|escape}</title>
</head>
<body>
<div>{$smarty.const.__FILE__}</div>
<table class="links" align="center">
<tr><td>
<strong> &raquo; {$pun_config.o_board_title}</strong><br />{$pun_config.o_base_url}/index.php<br />
<strong> &raquo; {$cur_topic.forum_name}</strong><br /> {$pun_config.o_base_url}/viewforum.php?id={$cur_topic.forum_id}<br />
<strong> &raquo; {$cur_topic.subject}</strong><br /> {$pun_config.o_base_url}/viewtopic.php?id={$id}
</td></tr>
</table><br />

<table align="center" cellspacing="0" cellpadding="3">
<tbody>
{foreach from=$posts item=cur_post}
<tr><td style="border-bottom: 0px;">
<strong>{$cur_post.username|escape} &raquo; {$cur_post.posted|date_format:$date_format}</strong></td></tr>
<tr><td style="border-bottom: 1px solid #333;">{parse_message($cur_post.message, true)}</td>
</tr>
{/foreach}
</tbody>
</table>
</body>
</html>