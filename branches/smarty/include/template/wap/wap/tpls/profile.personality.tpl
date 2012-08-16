{include file='header.tpl'}
{include file='profile.navi.tpl'}

{assign var='Section_personality' value='Section personality'}
{assign var='Avatar_legend' value='Avatar legend'}
{assign var='Avatar_info' value='Avatar info'}
{assign var='Use_avatar' value='Use avatar'}
{assign var='Change_avatar' value='Change avatar'}
{assign var='Upload_avatar' value='Upload avatar'}
{assign var='Delete_avatar' value='Delete avatar'}

{assign var='Signature_legend' value='Signature legend'}
{assign var='Signature_info' value='Signature info'}
{assign var='Sig_max_length' value='Sig max length'}
{assign var='Sig_max_lines' value='Sig max lines'}
{assign var='Sig_preview' value='Sig preview'}
{assign var='img_tag' value='img tag'}
{assign var='No_sig' value='No sig'}

<div class="con">
    <strong>{$user.username|escape} - {$lang_profile.$Section_personality}</strong>
</div>
<form method="post" action="profile.php?section=personality&amp;id={$id}">
    <div class="input">
        <input type="hidden" name="form_sent" value="1"/>
    {if $pun_config.o_avatars == 1}
        <strong>{$lang_profile.$Avatar_legend}</strong>

        <div class="zag_in">
            {if $user_avatar}
                {$user_avatar}
            {/if}
                {$lang_profile.$Avatar_info}<br/>
            <input type="checkbox" name="form[use_avatar]" value="1"{if $user.use_avatar == 1} checked="checked"{/if}/>
            {$lang_profile.$Use_avatar}
        </div>
    {/if}
        <a href="profile.php?action=upload_avatar&amp;id={$id}">{$lang_profile.$Change_avatar}</a> |
    {if $user_avatar}
        <a href="profile.php?action=delete_avatar&amp;id={$id}">{$lang_profile.$Delete_avatar}</a>
        {else}
        <a href="profile.php?action=upload_avatar&amp;id={$id}">{$lang_profile.$Upload_avatar}</a>
    {/if}
    </div>

    <div class="input2">
        <strong>{$lang_profile.$Signature_legend}</strong><br/>
        <span class="sub">{$lang_profile.$Signature_info}</span><br/>
    {$lang_profile.$Sig_max_length}: {$pun_config.p_sig_length} / {$lang_profile.$Sig_max_lines}
        : {$pun_config.p_sig_lines}<br/>
        <textarea name="signature" rows="4" cols="24">{$user.signature|escape}</textarea><br/>

        <a href="help.php?id=3">{$lang_common.Smilies}</a>
    {if $pun_config.o_smilies_sig}
        <span class="green">{$lang_common.on_m}</span>;
        {else}
        <span class="grey">{$lang_common.off_m}</span>;
    {/if}

        <a href="help.php?id=1">{$lang_common.BBCode}</a>
    {if $pun_config.p_sig_bbcode}
        <span class="green">{$lang_common.on_m}</span>;
        {else}
        <span class="grey">{$lang_common.off_m}</span>;
    {/if}

        <a href="help.php?id=4">{$lang_common.$img_tag}</a>
    {if $pun_config.p_sig_img_tag}
        <span class="green">{$lang_common.on_m}</span>
        {else}
        <span class="grey">{$lang_common.off_m}</span>
    {/if}

    </div>

    <div class="input">
    {if $user.signature}
        {$lang_profile.$Sig_preview}
        <div class="hr">{$parsed_signature}</div>
        {else}
        {$lang_profile.$No_sig}
    {/if}
    </div>

    <div class="go_to">
        <input type="submit" name="update" value="{$lang_common.Submit}"/>
    </div>
</form>

{include file='footer.tpl'}