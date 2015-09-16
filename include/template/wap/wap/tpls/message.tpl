{extends file='layout.scheme.tpl'}
{block name='center'}

{assign var='Go_back' value='Go back'}

<div class="in">{$message|escape}</div>
{if !$no_back_link}<p><a href="javascript:history.go(-1);">{$lang_common.$Go_back}</a></p>{/if}

{/block}