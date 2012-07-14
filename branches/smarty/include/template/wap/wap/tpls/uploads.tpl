{include file='header.tpl'}

{assign var='Not_allowed' value='Not allowed'}
{assign var='Not_allowed_mes' value='Not allowed mes'}
{assign var='Upload_file' value='Upload file'}
{assign var='File_uploaded' value='File uploaded'}
{assign var='File_deleted' value='File deleted'}
{assign var='Enable_filter' value='Enable filter'}
{assign var='Upload_warn' value='Upload warn'}
{assign var='Posted_by' value='Posted by'}
{assign var='Go_to_page' value='Go to page'}

{assign var='date_format' value='%Y-%m-%d %H:%M:%S'}

<div class="inbox">
<a href="index.php">{$lang_common.Index}</a> &#187; <a href="{$smarty.server.PHP_SELF}">{$lang_uploads.Uploader}</a>
</div>

{if ! $upl_conf.p_view}
    <div class="in">
    <strong>{$lang_uploads.$Not_allowed}</strong>
    </div>
    <div class="msg">{$lang_uploads.$Not_allowed_mes}</div>
{elseif isset($smarty.get.uploadit)}
    {if $upl_conf.p_upload == 1}
        <div class="con">
        {$lang_uploads.$Upload_file}</div>
        <form method="post" action="{$smarty.server.PHP_SELF}?" enctype="multipart/form-data">
        <div class="input">
        <strong>{$lang_uploads.$Upload_rules}</strong><br />
        {$rules}</div>
        <div class="input2">
        {$lang_uploads.File}:<br />
        <input type="file" name="file" maxlength="200" /><br/>
        {$lang_uploads.Descr}<br />
        <input type="text" name="descr" maxlength="100" />
        </div>
        <div class="go_to">
        <input type="submit" name="act" value="{$lang_uploads.$Upload_file}" /></div>
        </form>
    {else}
        <div class="red">{$lang_uploads.$Not_allowed}</div>
        <div class="msg">{$lang_uploads.$Not_allowed_mes}</div>
    {/if}
{elseif isset($smarty.post.act)}
    <div class="con">
    <strong>{$lang_uploads.$File_uploaded}</strong>
    </div>
    <div class="msg">
    <a href="{$smarty.server.PHP_SELF}?file={rawurlencode($file_name)}">{$pun_config.o_base_url}/uploads.php?file={$file_name|escape}</a></div>
    <div class="go_to">
    
    {if ! isset($smarty.get.uploadit) && $upl_conf.p_upload == 1}
        <a class="but" href="{$smarty.server.PHP_SELF}?uploadit=1">{$lang_uploads.$Upload_file}</a>
    {/if}
    </div>
{*/if*}
{elseif isset($smarty.get.del)}
    {if file_exists('`$smarty.const.PUN_ROOT`/uploaded/`$delfile`')}
        <div class="con"><strong>{$lang_uploads.Delete}</strong></div>
        <div class="msg">{$delfile|escape}{$lang_uploads.$File_deleted}</div>
    {/if}
{else}
    {if $upl_conf.p_upload == 1}
        <form method="post" action="{$smarty.server.PHP_SELF}?" enctype="multipart/form-data">
        <div class="input">{$lang_uploads.Pages}
        <select id="nump" name="nump">
        {foreach from=$pages item=i}
            <option value="{$i}"{if $s_nump == $i} selected="selected"{/if}>{$i}</option>
        {/foreach}
        </select>
        <input type="submit" name="filter" value="{$lang_uploads.$Enable_filter}" />
        </div>
        </form>
        
        {if ! isset($smarty.get.uploadit) && $upl_conf.p_upload == 1}
            <div class="go_to">
            <a class="but" href="{$smarty.server.PHP_SELF}?uploadit=1">{$lang_uploads.$Upload_file}</a>
            </div>
        {/if}
    {/if}

    {if $upl_conf.p_upload == 1}
        <div class="con"><strong>{$flist}</strong></div>
        <div class="msg">{$lang_uploads.$Upload_warn}</div>
        <div class="in">
        <a href="{$smarty.server.PHP_SELF}?u={$s_u}&amp;sort=1">{$lang_uploads.File}</a>|
        <a href="{$smarty.server.PHP_SELF}?u={$s_u}&amp;sort=2">{$lang_uploads.Size}</a>|
        <a href="{$smarty.server.PHP_SELF}?u={$s_u}&amp;sort=3">{$lang_uploads.$Posted_by}</a>|
        <a href="{$smarty.server.PHP_SELF}?u={$s_u}&amp;sort=5">{$lang_uploads.Date}</a>|
        <a href="{$smarty.server.PHP_SELF}?u={$s_u}&amp;sort=6">{$lang_uploads.Downloaded}</a>|
        <a href="{$smarty.server.PHP_SELF}?u={$s_u}&amp;sort=7">{$lang_uploads.Desc}</a>
        </div>
    {/if}
    
    {assign var='j' value='false'}
    {foreach from=$files item=info}
        <div class="{if $j = ! $j}msg{else}msg2{/if}">
        &#8226; <strong><a href="{$smarty.server.PHP_SELF}?file={rawurlencode($info.file)}">{$info.file|truncate:30:'..':true:true|escape}</a></strong>
        <span class="small">({round(filesize('`$smarty.const.PUN_ROOT`uploaded/`$info.file`') / 1024, 1)} kb,
        <strong><a href="profile.php?id={$info.uid}">{$info.user|escape}</a></strong>,
        {$info.data|date_format:$date_format}, {$lang_uploads.Downloaded}:{$info.downs};

        {if $upl_conf.p_globaldelete}
            <a class="but" href="{$smarty.server.PHP_SELF}?del={rawurlencode($info.file)}">{$lang_uploads.Delete}</a>
        {elseif $upl_conf.p_delete}
            {if $info.uid == $pun_user.id}
                <a class="but" href="{$smarty.server.PHP_SELF}?del={rawurlencode($info.file)}">{$lang_uploads.Delete}</a>
            {/if}
        {/if}
        
        <br />{$info.descr|escape}</span></div>
    {/foreach}
    
    {if $cp > 1}
        <div class="con">{$lang_uploads.$Go_to_page}
        {assign var='somepages' value=range(1, $cp)}
        {foreach from=$somepages item=i}
            {if ($i - 1) == $s_page}&#160;{$i}&#160;{else}<a href="{$smarty.server.PHP_SELF}?page={($i - 1)}">{$i}</a>{/if}
        {/foreach}
        </div>
    {/if}
{/if}

{include file='footer.tpl'}