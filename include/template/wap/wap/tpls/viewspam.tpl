{assign var='Antispam_despository' value='Antispam despository'}
{assign var='Antispam_close_window' value='Antispam close window'}

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta name="viewport" content="width=device-width"/>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <title>{$mod_title} AntiSPAM</title>
    <link rel="stylesheet" type="text/css" href="{$pun_config.o_base_url}/style_wap/{if $pun_user}{$pun_user.style_wap}{else}{$pun_config.o_default_style_wap}{/if}.css"/>
</head>
<body>
<div id="punwrap">
    <div id="punmessage_list" class="pun">
        <div class="block">
            <div class="blockform">
                <h2><span>{$lang_misc.$Antispam_despository}</span></h2>

                <div class="box" style="text-align:justify">
                    <p>{$return.message}<br/></p>
                    <ul>
                        <li><a href="javascript:window.close();">{$lang_misc.$Antispam_close_window}</a></li>
                    </ul>
                </div>
            </div>
            <div class="clearer"></div>
        </div>
    </div>
</div>
</body>
</html>