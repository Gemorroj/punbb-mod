{extends file='layout.scheme.tpl'}
{block name='center'}

{assign var='Forum_rules' value='Forum rules'}

<div class="con">
    <strong>{$lang_registration.$Forum_rules}</strong>
</div>
<div class="msg">{$pun_config.o_rules_message}</div>

{/block}