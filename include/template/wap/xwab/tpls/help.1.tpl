{include file='header.tpl'}

{assign var='Text_style' value='Text style'}
{assign var='Text_style_info' value='Text style info'}
{assign var='Bold_text' value='Bold text'}
{assign var='Underlined_text' value='Underlined text'}
{assign var='Italic_text' value='Italic text'}
{assign var='Red_text' value='Red text'}
{assign var='Blue_text' value='Blue text'}
{assign var='Hide_text' value='Hide text'}
{assign var='Quote_text' value='Quote text'}
{assign var='Quotes_info' value='Quotes info'}
{assign var='produces_quote_box' value='produces quote box'}
{assign var='Quotes_info_2' value='Quotes info 2'}
{assign var='Code_info' value='Code info'}
{assign var='Code_text' value='Code text'}
{assign var='Nested_tags' value='Nested tags'}
{assign var='Nested_tags_info' value='Nested tags info'}
{assign var='Bold_underlined_text' value='Bold, underlined text'}

<div class="inbox">
    <a href="index.php">{$lang_common.Index}</a> &#187; <a href="help.php">{$lang_help.Help}</a>
    &#187; {$lang_common.BBCode}</div>
<div class="con">{$lang_help.$Text_style}</div>
<div class="msg">
    <strong>{$lang_help.$Text_style_info}</strong><br/>
    [b]{$lang_help.$Bold_text}[/b] - <strong>{$lang_help.$Bold_text}</strong>
    <input type="text" value="[b][/b]" size="5"/><br/>
    [u]{$lang_help.$Underlined_text}[/u] - <span class="bbu">{$lang_help.$Underlined_text}</span>
    <input type="text" value="[u][/u]" size="5"/><br/>
    [i]{$lang_help.$Italic_text}[/i] - <em>{$lang_help.$Italic_text}</em>
    <input type="text" value="[i][/i]" size="5"/><br/>
    [color=#F00]{$lang_help.$Red_text}[/color] - <span style="color: #f00">{$lang_help.$Red_text}</span>
    <input type="text" value="[color=#F00][/color]" size="15"/><br/>
    [color=blue]{$lang_help.$Blue_text}[/color] - <span style="color: blue">{$lang_help.$Blue_text}</span>
    <input type="text" value="[color=blue][/color]" size="15"/><br/>
    [hide]{$lang_help.$Hide_text}[/hide] - {$lang_help.hidden}
    <input type="text" value="[hide][/hide]" size="11"/><br/>
    [hide=1]{$lang_help.$Hide_text}[/hide] - {$lang_help.hidden}
    <input type="text" value="[hide=1][/hide]" size="11"/></div>
<div class="con">{$lang_help.Quotes}</div>
<div class="msg">
    <strong>{$lang_help.$Quotes_info}</strong><br/>
    [quote=James]{$lang_help.$Quote_text}[/quote]
    <input type="text" value="[quote=James][/quote]" size="18"/><br/>
    {$lang_help.$produces_quote_box}<br/>

    <div class="quote">James {$lang_common.wrote}:<br/>
    {$lang_help.$Quote_text}</div>
    <strong>{$lang_help.$Quotes_info_2}</strong><br/>
    [quote]{$lang_help.$Quote_text}[/quote]
    <input type="text" value="[quote][/quote]" size="11"/><br/>
    {$lang_help.$produces_quote_box}<br/>

    <div class="quote">{$lang_help.$Quote_text}</div>
</div>
<div class="con">{$lang_help.Code}</div>
<div class="msg">
    <strong>{$lang_help.$Code_info}</strong><br/>
    [code]{$lang_help.$Code_text}[/code]
    <input type="text" value="[code][/code]" size="10"/><br/>
    {$lang_help.$produces_code_box}<br/>

    <div class="code">{$lang_common.Code}:<br/><table class="p_cnt" style="white-space: pre;font-family:'Courier New';font-size:8pt;"><tr><td>Hello world!</td></tr></table></div>
</div>
<div class="con">{$lang_help.$Nested_tags}</div>
<div class="msg">
    <strong>{$lang_help.$Nested_tags_info}</strong><br/>
    [b][u]{$lang_help.$Bold_underlined_text}[/u][/b] - <strong><span class="bbu">{$lang_help.$Bold_underlined_text}</span></strong>

    <input type="text" value="[b][u][/u][/b]" size="9"/>
</div>

{include file='footer.tpl'}