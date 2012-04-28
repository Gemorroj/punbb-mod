{include file='header.tpl'}

{assign var='Upload_avatar' value='Upload avatar'}
{assign var='Upload_avatar_legend' value='Upload avatar legend'}
{assign var='Avatar_desc' value='Avatar desc'}

<div class="con">
    <strong>{$lang_profile.$Upload_avatar}</strong>

<br/><s>{basename($smarty.const.__FILE__)}</s>

</div>
<form method="post" enctype="multipart/form-data" action="profile.php?action=upload_avatar2&amp;id={$id}">
<div class="input">
<strong>{$lang_profile.$Upload_avatar_legend}</strong><br/>
<input type="hidden" name="form_sent" value="1" />
<input name="req_file" type="file" size="40" /><br/>
<span class="sub">{$lang_profile.$Avatar_desc}&#160;{$pun_config.o_avatars_width}&#160;x&#160;{$pun_config.o_avatars_height}&#160;{$lang_profile.pixels}&#160;{$lang_common.and}&#160;{ceil($pun_config.o_avatars_size / 1024)}&#160;kb</span>
</div>
<div class="go_to">
<input type="submit" name="upload" value="{$lang_profile.Upload}" />
</div>
</form>

{include file='footer.tpl'}