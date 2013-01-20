{include file='header.tpl'}

{assign var='Desc_1' value='Desc 1'}
{assign var='Desc_2' value='Desc 2'}
{assign var='Username_legend' value='Username legend'}
{assign var='Pass_legend_1' value='Pass legend 1'}
{assign var='Confirm_pass' value='Confirm pass'}
{assign var='Pass_info' value='Pass info'}
{assign var='Image_verification' value='Image verification'}
{assign var='Image_text' value='Image text'}
{assign var='Image_info' value='Image info'}
{assign var='Email_legend_2' value='E-mail legend 2'}
{assign var='Email_legend' value='E-mail legend'}
{assign var='Email_info' value='E-mail info'}
{assign var='Email' value='E-mail'}
{assign var='Confirm_email' value='Confirm e-mail'}
{assign var='Localisation_legend' value='Localisation legend'}
{assign var='Timezone_info' value='Timezone info'}
{assign var='Language_info' value='Language info'}
{assign var='Privacy_options_legend' value='Privacy options legend'}
{assign var='Email_setting_info' value='E-mail setting info'}
{assign var='Email_setting_1' value='E-mail setting 1'}
{assign var='Email_setting_2' value='E-mail setting 2'}
{assign var='Email_setting_3' value='E-mail setting 3'}
{assign var='Save_user_pass_info' value='Save user/pass info'}
{assign var='Save_user_pass' value='Save user/pass'}



<div class="inbox">
    <a href="index.php">{$lang_common.Index}</a> &#187; <strong>{$lang_register.Register}</strong>
</div>
<form method="post" action="register.php?action=register">
    <div class="msg">{$lang_register.$Desc_1}</div>
    <div class="in2">{$lang_register.$Desc_2}</div>
    <div class="input2">
        <strong>{$lang_register.$Username_legend}</strong>
        <input type="hidden" name="form_sent" value="1"/><br/>
        <strong>{$lang_common.Username}</strong><br/>
        <input type="text" name="req_username" maxlength="25"/><br/>
        <strong>{$lang_profile.sex}</strong>
        <select name="req_sex">
            <option value="1">{$lang_profile.m}</option>
            <option value="0">{$lang_profile.w}</option>
        </select>
    </div>
{if ! $pun_config.o_regs_verify}
    <div class="input">
        <strong>{$lang_register.$Pass_legend_1}</strong><br/>
        <strong>{$lang_common.Password}</strong><br/>
        <input type="password" name="req_password1" maxlength="16"/><br/>
        <strong>{$lang_prof_reg.$Confirm_pass}</strong><br/>
        <input type="password" name="req_password2" maxlength="16"/><br/>
        {$lang_register.$Pass_info}
    </div>
{/if}

{if $pun_config.o_regs_verify_image == 1}
    <div class="input2">
        <strong>{$lang_register.$Image_verification}</strong><br/>
        <img src="{$pun_config.o_base_url}/include/captcha/captcha.php?{session_name()}={session_id()}" alt=""/><br/>
        <strong>{$lang_register.$Image_text}</strong><br/>
        <input type="text" name="req_image_" maxlength="4"/><br/>
        {$lang_register.$Image_info}
    </div>
{/if}

    <div class="input">
        <strong>
        {if $pun_config.o_regs_verify == 1}
            {$lang_prof_reg.$Email_legend_2}
        {else}
            {$lang_prof_reg.$Email_legend}
        {/if}
        </strong><br/>
    {if $pun_config.o_regs_verify == 1}
        {$lang_register.$Email_info}<br/>
    {/if}
        <strong>{$lang_common.$Email}</strong><br/>
        <input type="text" name="req_email1" maxlength="50"/><br/>

    {if $pun_config.o_regs_verify == 1}
        <strong>{$lang_register.$Confirm_email}</strong><br/>
        <input type="text" name="req_email2" maxlength="50"/>
    {/if}
    </div>
    <div class="input2">
        <strong>{$lang_prof_reg.$Localisation_legend}</strong><br/>
    {$lang_prof_reg.Timezone}: {$lang_prof_reg.$Timezone_info}<br/>
        <select name="timezone">
            <option value="-12"{if $pun_config.o_server_timezone == -12} selected="selected"{/if}>-12</option>
            <option value="-11"{if $pun_config.o_server_timezone == -11} selected="selected"{/if}>-11</option>
            <option value="-10"{if $pun_config.o_server_timezone == -10} selected="selected"{/if}>-10</option>
            <option value="-9.5"{if $pun_config.o_server_timezone == -9.5} selected="selected"{/if}>-9.5</option>
            <option value="-9"{if $pun_config.o_server_timezone == -9} selected="selected"{/if}>-09</option>
            <option value="-8.5"{if $pun_config.o_server_timezone == -8.5} selected="selected"{/if}>-8.5</option>
            <option value="-8"{if $pun_config.o_server_timezone == -8} selected="selected"{/if}>-08 PST</option>
            <option value="-7"{if $pun_config.o_server_timezone == -7} selected="selected"{/if}>-07 MST</option>
            <option value="-6"{if $pun_config.o_server_timezone == -6} selected="selected"{/if}>-06 CST</option>
            <option value="-5"{if $pun_config.o_server_timezone == -5} selected="selected"{/if}>-05 EST</option>
            <option value="-4"{if $pun_config.o_server_timezone == -4} selected="selected"{/if}>-04 AST</option>
            <option value="-3.5"{if $pun_config.o_server_timezone == -3.5} selected="selected"{/if}>-3.5</option>
            <option value="-3"{if $pun_config.o_server_timezone == -3} selected="selected"{/if}>-03 ADT</option>
            <option value="-2"{if $pun_config.o_server_timezone == -2} selected="selected"{/if}>-02</option>
            <option value="-1"{if $pun_config.o_server_timezone == -1} selected="selected"{/if}>-01</option>
            <option value="0"{if $pun_config.o_server_timezone == 0} selected="selected"{/if}>00 GMT</option>
            <option value="1"{if $pun_config.o_server_timezone == 1} selected="selected"{/if}>+01 CET</option>
            <option value="2"{if $pun_config.o_server_timezone == 2 } selected="selected"{/if}>+02</option>
            <option value="3"{if $pun_config.o_server_timezone == 3 } selected="selected"{/if}>+03</option>
            <option value="3.5"{if $pun_config.o_server_timezone == 3.5} selected="selected"{/if}>+03.5</option>
            <option value="4"{if $pun_config.o_server_timezone == 4 } selected="selected"{/if}>+04</option>
            <option value="4.5"{if $pun_config.o_server_timezone == 4.5} selected="selected"{/if}>+04.5</option>
            <option value="5"{if $pun_config.o_server_timezone == 5 } selected="selected"{/if}>+05</option>
            <option value="5.5"{if $pun_config.o_server_timezone == 5.5} selected="selected"{/if}>+05.5</option>
            <option value="6"{if $pun_config.o_server_timezone == 6} selected="selected"{/if}>+06</option>
            <option value="6.5"{if $pun_config.o_server_timezone == 6.5} selected="selected"{/if}>+06.5</option>
            <option value="7"{if $pun_config.o_server_timezone == 7} selected="selected"{/if}>+07</option>
            <option value="8"{if $pun_config.o_server_timezone == 8} selected="selected"{/if}>+08</option>
            <option value="9"{if $pun_config.o_server_timezone == 9} selected="selected"{/if}>+09</option>
            <option value="9.5"{if $pun_config.o_server_timezone == 9.5} selected="selected"{/if}>+09.5</option>
            <option value="10"{if $pun_config.o_server_timezone == 10} selected="selected"{/if}>+10</option>
            <option value="10.5"{if $pun_config.o_server_timezone == 10.5} selected="selected"{/if}>+10.5</option>
            <option value="11"{if $pun_config.o_server_timezone == 11} selected="selected"{/if}>+11</option>
            <option value="11.5"{if $pun_config.o_server_timezone == 11.5} selected="selected"{/if}>+11.5</option>
            <option value="12"{if $pun_config.o_server_timezone == 12} selected="selected"{/if}>+12</option>
            <option value="13"{if $pun_config.o_server_timezone == 13} selected="selected"{/if}>+13</option>
            <option value="14"{if $pun_config.o_server_timezone == 14} selected="selected"{/if}>+14</option>
        </select>
    </div>
    <div class="input">

    {if sizeof($languages) > 1}
        <strong>{$lang_prof_reg.Language}</strong>: {$lang_prof_reg.$Language_info}<br/>
        <select name="language">
            {foreach from=$languages item=temp}
                <option value="{$temp}"{if $pun_config.o_default_lang == $temp} selected="selected"{/if}>{$temp}</option>
            {/foreach}
        </select>
    {/if}

    </div>
    <div class="input2">
        <strong>{$lang_prof_reg.$Privacy_options_legend}</strong><br/>
        {$lang_prof_reg.$Email_setting_info}<br/>
        <label><input type="radio" name="email_setting" value="0"/>{$lang_prof_reg.$Email_setting_1}</label><br/>
        <label><input type="radio" name="email_setting" value="1" checked="checked"/>{$lang_prof_reg.$Email_setting_2}</label><br/>
        <label><input type="radio" name="email_setting" value="2"/>{$lang_prof_reg.$Email_setting_3}</label><br/>
        {$lang_prof_reg.$Save_user_pass_info}<br/>
        <label><input type="checkbox" name="save_pass" value="1" checked="checked"/>{$lang_prof_reg.$Save_user_pass}</label>
    </div>
    <div class="go_to">
        <input type="submit" name="register" value="{$lang_register.Register}"/>
    </div>
</form>

{include file='footer.tpl'}