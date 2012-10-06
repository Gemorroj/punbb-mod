{include file='header.tpl'}

<div class="inbox">
    <a href="index.php">{$lang_common.Index}</a> &#187; <a href="help.php">{$lang_help.Help}</a>
    &#187; {$lang_common.Smilies}</div>

{assign var='j' value=false}

{foreach from=$smiley_text item=cur_text key=i}
{* // Is there a smiley at the current index? *}
    {if isset($smiley_text.$i)}
    {* // Save the current text and image *}

    {* //Вывод строк со смайлами *}

    <div class="{if $j = ! $j}msg{else}msg2{/if}">
        <img src="{$smarty.const.PUN_ROOT}img/smilies/{$smiley_img.$i}" alt="{$cur_text}"/>
        <input type="text" value="{$cur_text}" size="5"/>

    {*
    // Loop through the rest of the array and see if there are any duplicate images
    // (more than one text representation for one image)

    {foreach $next = $i + 1; $next < $num_smilies; ++$next) {
        // Did we find a dupe?
        if (isset($smiley_img[$next]) && $smiley_img[$i] == $smiley_img[$next]) {
            echo  ' '.$lang_common['and'] . ' <input type="text" value="' . $smiley_text[$next] . '" size="5" />';

            // Remove the dupe so we won't display it twice
            unset($smiley_img[$next]);
            unset($smiley_text[$next]);
        }
    *}

    </div>
    {/if}
{/foreach}

{include file='footer.tpl'}