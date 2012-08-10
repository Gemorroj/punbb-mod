{include file='header.tpl'}
{* This template is completed! *}
<div class="inbox">
{assign var='Report_post' value='Report post'}
<a href="index.php">{$lang_common.Index}</a>&#160;&#187;&#160;{$lang_misc.$Report_post}
</div>
<form method="post" action="misc.php?report={$post_id}">
<div class="input">
{assign var='Reason_desc' value='Reason desc'}
<strong>{$lang_misc.$Reason_desc}</strong><br/>
<input type="hidden" name="form_sent" value="1"/>
{$lang_misc.Reason}<br/>
<textarea name="req_reason" rows="4" cols="24"></textarea></div>
<div class="go_to"><input type="submit" name="submit" value="{$lang_common.Submit}" accesskey="s"/>
</div>
</form>

{include file='footer.tpl'}