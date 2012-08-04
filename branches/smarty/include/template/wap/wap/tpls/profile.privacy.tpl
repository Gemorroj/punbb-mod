{include file='header.tpl'}
{include file='profile.navi.tpl'}

{assign var='Section_privacy' value='Section privacy'}
{assign var='Privacy_options_legend' value='Privacy options legend'}
{assign var='Email_setting_info' value='E-mail setting info'}
{assign var='Email_setting_1' value='E-mail setting 1'}
{assign var='Email_setting_2' value='E-mail setting 2'}
{assign var='Email_setting_3' value='E-mail setting 3'}
{assign var='Save_user_pass_info' value='Save user/pass info'}
{assign var='Save_user_pass' value='Save user/pass'}
{assign var='Notify_full_info' value='Notify full info'}
{assign var='Notify_full' value='Notify full'}


<div class="con">
    <strong>{$user.username|escape} - {$lang_profile.$Section_privacy}</strong>
</div>

<form method="post" action="profile.php?section=privacy&amp;id={$id}">
    <div class="input">
        <strong>{$lang_prof_reg.$Privacy_options_legend}</strong><br/>
        <input type="hidden" name="form_sent" value="1"/>
        {$lang_prof_reg.$Email_setting_info}<br/>
        <input type="radio" name="form[email_setting]" value="0"{if !$user.email_setting}
               checked="checked"{/if}/>{$lang_prof_reg.$Email_setting_1}<br/>
        <input type="radio" name="form[email_setting]" value="1"{if $user.email_setting == 1}
               checked="checked"{/if}/>{$lang_prof_reg.$Email_setting_2}<br/>
        <input type="radio" name="form[email_setting]" value="2"{if $user.email_setting == 2}
               checked="checked"{/if}/>{$lang_prof_reg.$Email_setting_3}
    </div>
    <div class="input2">{$lang_prof_reg.$Save_user_pass_info}<br/>
        <input type="checkbox" name="form[save_pass]" value="1"{if $user.save_pass == 1}
               checked="checked"{/if}/>{$lang_prof_reg.$Save_user_pass}
    </div>
    <div class="input2">{$lang_profile.$Notify_full_info}<br/>
        <input type="checkbox" name="form[notify_with_post]" value="1"{if $user.notify_with_post == 1}
               checked="checked"{/if}/>{$lang_profile.$Notify_full}
    </div>
    <div class="go_to">
        <input type="submit" name="update" value="{$lang_common.Submit}"/>
    </div>
</form>

{include file='footer.tpl'}