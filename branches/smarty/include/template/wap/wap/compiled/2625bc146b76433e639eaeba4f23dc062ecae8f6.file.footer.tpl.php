<?php /* Smarty version Smarty-3.1.7, created on 2012-04-14 22:13:53
         compiled from "L:\home\punbb.mod\www\include/template/wap/wap/tpls\footer.tpl" */ ?>
<?php /*%%SmartyHeaderCode:273894f587b1301e137-03463091%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '2625bc146b76433e639eaeba4f23dc062ecae8f6' => 
    array (
      0 => 'L:\\home\\punbb.mod\\www\\include/template/wap/wap/tpls\\footer.tpl',
      1 => 1334426660,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '273894f587b1301e137-03463091',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_4f587b133b840',
  'variables' => 
  array (
    'basename' => 0,
    'User_list' => 0,
    'lang_common' => 0,
    'pun_config' => 0,
    'Link_separator_m' => 0,
    'lang_topic' => 0,
    'pun_user' => 0,
    'forum_id' => 0,
    'is_admmod' => 0,
    'p' => 0,
    'Moderate_forum' => 0,
    'id' => 0,
    'Delete_posts' => 0,
    'Move_topic' => 0,
    'cur_topic' => 0,
    'Open_topic' => 0,
    'Close_topic' => 0,
    'Unstick_topic' => 0,
    'Stick_topic' => 0,
    'pun_start' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_4f587b133b840')) {function content_4f587b133b840($_smarty_tpl) {?><?php if (!is_callable('smarty_function_fetch')) include 'L:\\home\\punbb.mod\\www\\include\\Smarty\\plugins\\function.fetch.php';
?><?php $_smarty_tpl->tpl_vars['Moderate_forum'] = new Smarty_variable('Moderate forum', null, 0);?>
<?php $_smarty_tpl->tpl_vars['Delete_posts'] = new Smarty_variable('Delete posts', null, 0);?>
<?php $_smarty_tpl->tpl_vars['Move_topic'] = new Smarty_variable('Move topic', null, 0);?>
<?php $_smarty_tpl->tpl_vars['Open_topic'] = new Smarty_variable('Open topic', null, 0);?>
<?php $_smarty_tpl->tpl_vars['Close_topic'] = new Smarty_variable('Close topic', null, 0);?>
<?php $_smarty_tpl->tpl_vars['Unstick_topic'] = new Smarty_variable('Unstick topic', null, 0);?>
<?php $_smarty_tpl->tpl_vars['Stick_topic'] = new Smarty_variable('Stick topic', null, 0);?>
<?php $_smarty_tpl->tpl_vars['User_list'] = new Smarty_variable('User list', null, 0);?>

<?php $_smarty_tpl->tpl_vars['Link_separator_m'] = new Smarty_variable('Link separator_m', null, 0);?>

<?php if ($_smarty_tpl->tpl_vars['basename']->value=='profile.php'||$_smarty_tpl->tpl_vars['basename']->value=='search.php'||$_smarty_tpl->tpl_vars['basename']->value=='userlist.php'||$_smarty_tpl->tpl_vars['basename']->value=='uploads.php'||$_smarty_tpl->tpl_vars['basename']->value=='message_list.php'||$_smarty_tpl->tpl_vars['basename']->value=='message_send.php'||$_smarty_tpl->tpl_vars['basename']->value=='help.php'||$_smarty_tpl->tpl_vars['basename']->value=='misc.php'||$_smarty_tpl->tpl_vars['basename']->value=='filemap.php'||$_smarty_tpl->tpl_vars['basename']->value=='karma.php'||$_smarty_tpl->tpl_vars['basename']->value=='index.php'){?>
    
    <div class="navlinks">
        
        <a href="userlist.php"><?php echo $_smarty_tpl->tpl_vars['lang_common']->value[$_smarty_tpl->tpl_vars['User_list']->value];?>
</a>
        
        <?php if ($_smarty_tpl->tpl_vars['pun_config']->value['o_rules']==1){?>
            <?php echo $_smarty_tpl->tpl_vars['lang_topic']->value[$_smarty_tpl->tpl_vars['Link_separator_m']->value];?>
<a href="misc.php?action=rules"><?php echo $_smarty_tpl->tpl_vars['lang_common']->value['Rules'];?>
</a>
        <?php }?>
        
        <?php if ($_smarty_tpl->tpl_vars['pun_user']->value['g_search']==1&&$_smarty_tpl->tpl_vars['pun_user']->value['g_id']>@PUN_MOD){?>
           <?php echo $_smarty_tpl->tpl_vars['lang_topic']->value[$_smarty_tpl->tpl_vars['Link_separator_m']->value];?>
<a href="search.php"><?php echo $_smarty_tpl->tpl_vars['lang_common']->value['Search'];?>
</a>
        <?php }?>
        
        <?php if (!$_smarty_tpl->tpl_vars['pun_user']->value['is_guest']){?>
            <?php echo $_smarty_tpl->tpl_vars['lang_topic']->value[$_smarty_tpl->tpl_vars['Link_separator_m']->value];?>
<a href="uploads.php"><?php echo $_smarty_tpl->tpl_vars['lang_common']->value['Uploader'];?>
</a>
            <?php echo $_smarty_tpl->tpl_vars['lang_topic']->value[$_smarty_tpl->tpl_vars['Link_separator_m']->value];?>
<a href="filemap.php"><?php echo $_smarty_tpl->tpl_vars['lang_common']->value['Attachments'];?>
</a>
        <?php }?>
    </div>
<?php }?>

<?php if ($_smarty_tpl->tpl_vars['pun_config']->value['o_quickjump']==1&&$_smarty_tpl->tpl_vars['basename']->value=='viewforum.php'||$_smarty_tpl->tpl_vars['basename']->value=='viewtopic.php'){?>
    <?php echo smarty_function_fetch(array('file'=>(@PUN_ROOT)."cache/cache_wap_quickjump_".($_smarty_tpl->tpl_vars['forum_id']->value).".php"),$_smarty_tpl);?>

<?php }?>

<?php if ($_smarty_tpl->tpl_vars['is_admmod']->value){?>
    <?php if ($_smarty_tpl->tpl_vars['basename']->value=='viewforum.php'){?>
        <div class="con">
            <a class="but" href="moderate.php?fid=<?php echo $_smarty_tpl->tpl_vars['forum_id']->value;?>
&amp;p=<?php echo $_smarty_tpl->tpl_vars['p']->value;?>
"><?php echo $_smarty_tpl->tpl_vars['lang_common']->value[$_smarty_tpl->tpl_vars['Moderate_forum']->value];?>
</a>
        </div>
    <?php }elseif($_smarty_tpl->tpl_vars['basename']->value=='viewtopic.php'){?>
        <div class="con">
            <span class="sub">
                <a href="moderate.php?fid=<?php echo $_smarty_tpl->tpl_vars['forum_id']->value;?>
&amp;tid=<?php echo $_smarty_tpl->tpl_vars['id']->value;?>
&amp;p=<?php echo $_smarty_tpl->tpl_vars['p']->value;?>
"><?php echo $_smarty_tpl->tpl_vars['lang_common']->value[$_smarty_tpl->tpl_vars['Delete_posts']->value];?>
</a><?php echo $_smarty_tpl->tpl_vars['lang_topic']->value[$_smarty_tpl->tpl_vars['Link_separator_m']->value];?>

                <a href="moderate.php?fid=<?php echo $_smarty_tpl->tpl_vars['forum_id']->value;?>
&amp;move_topics=<?php echo $_smarty_tpl->tpl_vars['id']->value;?>
"><?php echo $_smarty_tpl->tpl_vars['lang_common']->value[$_smarty_tpl->tpl_vars['Move_topic']->value];?>
</a>
            <?php if ($_smarty_tpl->tpl_vars['cur_topic']->value['closed']==1){?>
                <?php echo $_smarty_tpl->tpl_vars['lang_topic']->value[$_smarty_tpl->tpl_vars['Link_separator_m']->value];?>
<a href="moderate.php?fid=<?php echo $_smarty_tpl->tpl_vars['forum_id']->value;?>
&amp;open=<?php echo $_smarty_tpl->tpl_vars['id']->value;?>
"><?php echo $_smarty_tpl->tpl_vars['lang_common']->value[$_smarty_tpl->tpl_vars['Open_topic']->value];?>
</a>
            <?php }else{ ?>
                <?php echo $_smarty_tpl->tpl_vars['lang_topic']->value[$_smarty_tpl->tpl_vars['Link_separator_m']->value];?>
<a href="moderate.php?fid=<?php echo $_smarty_tpl->tpl_vars['forum_id']->value;?>
&amp;close=<?php echo $_smarty_tpl->tpl_vars['id']->value;?>
"><?php echo $_smarty_tpl->tpl_vars['lang_common']->value[$_smarty_tpl->tpl_vars['Close_topic']->value];?>
</a>
            <?php }?>

            <?php if ($_smarty_tpl->tpl_vars['cur_topic']->value['sticky']==1){?>
                <?php echo $_smarty_tpl->tpl_vars['lang_topic']->value[$_smarty_tpl->tpl_vars['Link_separator_m']->value];?>
<a href="moderate.php?fid=<?php echo $_smarty_tpl->tpl_vars['forum_id']->value;?>
&amp;unstick=<?php echo $_smarty_tpl->tpl_vars['id']->value;?>
"><?php echo $_smarty_tpl->tpl_vars['lang_common']->value[$_smarty_tpl->tpl_vars['Unstick_topic']->value];?>
</a>
            <?php }else{ ?>
                <?php echo $_smarty_tpl->tpl_vars['lang_topic']->value[$_smarty_tpl->tpl_vars['Link_separator_m']->value];?>
<a href="moderate.php?fid=<?php echo $_smarty_tpl->tpl_vars['forum_id']->value;?>
&amp;stick=<?php echo $_smarty_tpl->tpl_vars['id']->value;?>
"><?php echo $_smarty_tpl->tpl_vars['lang_common']->value[$_smarty_tpl->tpl_vars['Stick_topic']->value];?>
</a>
            <?php }?>
            </span>
        </div>
    <?php }?>
<?php }?>
<div class="foot">
    <a href="/"><?php echo $_SERVER['HTTP_HOST'];?>
</a><br/>
    <a class="red" href="<?php echo @PUN_ROOT;?>
">WEB</a>
</div>

<div class="copy">
    <a href="http://wapinet.ru/forum/wap/">PunBB Mod Gemorroj</a><br/>
    <span class="red"><?php echo sprintf('%.3f',microtime(true)-$_smarty_tpl->tpl_vars['pun_start']->value);?>
 s</span>
</div>

</body>
</html><?php }} ?>