{include file='header.tpl'}

{assign var='Go_back' value='Go back'}
{assign var='Show_IP' value='Show IP'}

<div class="in">
    &#187; IP: {$ip|escape}{$whois|escape}<br />
    &#187; <a target="_blank" href="http://www.robtex.com/ip/{$ip|rawurlencode}.html">WHOIS</a><br/>
    &#187; <a href="../admin_users.php?show_users={$ip|rawurlencode}">{$lang_common.$Show_IP}</a>
</div>

<p><a href="javascript:history.go(-1);">{$lang_common.$Go_back}</a></p>

{include file='footer.tpl'}