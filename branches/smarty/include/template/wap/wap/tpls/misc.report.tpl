{include file='header.tpl'}

{assign var='Report_post' value='Report_post'}
{assign var='Reason_desc' value='Reason_desc'}

<div class="red">{$lang_misc.$Report_post}</div>
<form method="post" action="misc.php?report={$post_id}">
<div class="input">
<strong>{$lang_misc.$Reason_desc}</strong><br/>
<input type="hidden" name="form_sent" value="1" />
{$lang_misc.Reason}<br />
<textarea name="req_reason" rows="4" cols="24"></textarea></div>
<div class="go_to"><input type="submit" name="submit" value="{$lang_common.Submit}" accesskey="s" />
</div>
</form>

{include file='footer.tpl'}