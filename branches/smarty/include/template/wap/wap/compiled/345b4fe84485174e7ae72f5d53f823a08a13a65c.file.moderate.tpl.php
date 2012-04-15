<?php /* Smarty version Smarty-3.1.7, created on 2012-04-10 22:41:15
         compiled from "L:\home\punbb.mod\www\include/template/wap/wap/tpls\moderate.tpl" */ ?>
<?php /*%%SmartyHeaderCode:138084f801e91790807-44716272%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '345b4fe84485174e7ae72f5d53f823a08a13a65c' => 
    array (
      0 => 'L:\\home\\punbb.mod\\www\\include/template/wap/wap/tpls\\moderate.tpl',
      1 => 1334083272,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '138084f801e91790807-44716272',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_4f801e91ad5c7',
  'variables' => 
  array (
    'lang_common' => 0,
    'cur_forum' => 0,
    'fid' => 0,
    'topics' => 0,
    'j' => 0,
    'cur_topic' => 0,
    'lang_forum' => 0,
    'Closed_icon_m' => 0,
    'Normal_icon' => 0,
    'pun_config' => 0,
    'pun_user' => 0,
    'New_icon_m' => 0,
    'date_format' => 0,
    'Empty_forum' => 0,
    'paging_links' => 0,
    'is_admmod' => 0,
    'lang_misc' => 0,
    'button_status' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_4f801e91ad5c7')) {function content_4f801e91ad5c7($_smarty_tpl) {?><?php if (!is_callable('smarty_modifier_date_format')) include 'L:\\home\\punbb.mod\\www\\include\\Smarty\\plugins\\modifier.date_format.php';
?><?php echo $_smarty_tpl->getSubTemplate ('header.tpl', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>



<div class="inbox">
    <a href="index.php"><?php echo $_smarty_tpl->tpl_vars['lang_common']->value['Index'];?>
</a>&#160;&#187;&#160;<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['cur_forum']->value['forum_name'], ENT_QUOTES, 'UTF-8', true);?>

</div>
<form method="post" action="moderate.php?fid=<?php echo $_smarty_tpl->tpl_vars['fid']->value;?>
">

<?php $_smarty_tpl->tpl_vars['date_format'] = new Smarty_variable('%d/%m/%y %H:%I:%S', null, 0);?>

<?php $_smarty_tpl->tpl_vars['Post_topic'] = new Smarty_variable('Post topic', null, 0);?>
<?php $_smarty_tpl->tpl_vars['Empty_forum'] = new Smarty_variable('Empty forum', null, 0);?>
<?php $_smarty_tpl->tpl_vars['Closed_icon_m'] = new Smarty_variable('Closed icon_m', null, 0);?>
<?php $_smarty_tpl->tpl_vars['New_icon_m'] = new Smarty_variable('New icon_m', null, 0);?>
<?php $_smarty_tpl->tpl_vars['Normal_icon'] = new Smarty_variable('Normal icon', null, 0);?>

<?php $_smarty_tpl->tpl_vars['j'] = new Smarty_variable('false', null, 0);?>

<?php  $_smarty_tpl->tpl_vars['cur_topic'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['cur_topic']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['topics']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['cur_topic']->key => $_smarty_tpl->tpl_vars['cur_topic']->value){
$_smarty_tpl->tpl_vars['cur_topic']->_loop = true;
?>

<div class="<?php if (!isset($_smarty_tpl->tpl_vars['j'])) $_smarty_tpl->tpl_vars['j'] = new Smarty_Variable(null);if ($_smarty_tpl->tpl_vars['j']->value = !$_smarty_tpl->tpl_vars['j']->value){?>msg<?php }else{ ?>msg2<?php }?>">
<input type="checkbox" name="topics[<?php echo $_smarty_tpl->tpl_vars['cur_topic']->value['id'];?>
]" value="1" />


<strong>
<?php if ($_smarty_tpl->tpl_vars['cur_topic']->value['moved_to']){?>
    <?php echo $_smarty_tpl->tpl_vars['lang_forum']->value['Moved_m'];?>

<?php }elseif($_smarty_tpl->tpl_vars['cur_topic']->value['closed']){?>
    <?php echo $_smarty_tpl->tpl_vars['lang_common']->value[$_smarty_tpl->tpl_vars['Closed_icon_m']->value];?>

<?php }else{ ?>
    <?php echo $_smarty_tpl->tpl_vars['lang_common']->value[$_smarty_tpl->tpl_vars['Normal_icon']->value];?>

<?php }?>
&#160;
<?php if ($_smarty_tpl->tpl_vars['pun_config']->value['poll_enabled']==1&&$_smarty_tpl->tpl_vars['cur_topic']->value['has_poll']){?>
    <?php echo $_smarty_tpl->tpl_vars['lang_forum']->value['poll_m'];?>

<?php }?>

<?php if ($_smarty_tpl->tpl_vars['cur_topic']->value['sticky']==1){?>
    <?php echo $_smarty_tpl->tpl_vars['lang_forum']->value['Sticky_m'];?>

<?php }?>
</strong>


<?php if (!$_smarty_tpl->tpl_vars['pun_user']->value['is_guest']&&$_smarty_tpl->tpl_vars['pun_config']->value['o_show_dot']==1&&$_smarty_tpl->tpl_vars['cur_topic']->value['has_posted']==$_smarty_tpl->tpl_vars['pun_user']->value['id']){?>
<strong>&#183;</strong>
<?php }?>

<a href="viewtopic.php?id=<?php echo (($tmp = @$_smarty_tpl->tpl_vars['cur_topic']->value['moved_to'])===null||$tmp==='' ? $_smarty_tpl->tpl_vars['cur_topic']->value['id'] : $tmp);?>
">

<?php if ($_smarty_tpl->tpl_vars['pun_config']->value['o_censoring']==1){?>
    <?php echo htmlspecialchars(censor_words($_smarty_tpl->tpl_vars['cur_topic']->value['subject']), ENT_QUOTES, 'UTF-8', true);?>

<?php }else{ ?>
    <?php echo htmlspecialchars($_smarty_tpl->tpl_vars['cur_topic']->value['subject'], ENT_QUOTES, 'UTF-8', true);?>

<?php }?>
</a>

<?php if ($_smarty_tpl->tpl_vars['cur_topic']->value['num_pages_topic']>1){?>
[<?php echo paginate($_smarty_tpl->tpl_vars['cur_topic']->value['num_pages_topic'],-1,"viewtopic.php?id=".($_smarty_tpl->tpl_vars['cur_topic']->value['id']));?>
]
<?php }?>
&#160;<?php echo $_smarty_tpl->tpl_vars['lang_common']->value['by'];?>
&#160;<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['cur_topic']->value['poster'], ENT_QUOTES, 'UTF-8', true);?>




<?php if (!$_smarty_tpl->tpl_vars['cur_topic']->value['moved_to']){?>
&#160;(<?php echo $_smarty_tpl->tpl_vars['cur_topic']->value['num_replies'];?>
/<?php echo $_smarty_tpl->tpl_vars['cur_topic']->value['num_views'];?>
)


<?php if ($_smarty_tpl->tpl_vars['cur_topic']->value['last_post']>$_smarty_tpl->tpl_vars['pun_user']->value['last_visit']){?>
&#160;<span class="red"><?php echo $_smarty_tpl->tpl_vars['lang_common']->value[$_smarty_tpl->tpl_vars['New_icon_m']->value];?>
</span>
<?php }?>
<br/>
<span class="sub">
&#187;&#160;<a href="viewtopic.php?pid=<?php echo $_smarty_tpl->tpl_vars['cur_topic']->value['last_post_id'];?>
#p<?php echo $_smarty_tpl->tpl_vars['cur_topic']->value['last_post_id'];?>
"><?php echo smarty_modifier_date_format($_smarty_tpl->tpl_vars['cur_topic']->value['last_post'],$_smarty_tpl->tpl_vars['date_format']->value);?>
</a>&#160;<?php echo $_smarty_tpl->tpl_vars['lang_common']->value['by'];?>
&#160;<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['cur_topic']->value['last_poster'], ENT_QUOTES, 'UTF-8', true);?>
;
</span>
<?php }?>
</div>

<?php }
if (!$_smarty_tpl->tpl_vars['cur_topic']->_loop) {
?>
<?php $_smarty_tpl->tpl_vars['button_status'] = new Smarty_variable('1', null, 0);?>
<div class="in"><?php echo $_smarty_tpl->tpl_vars['lang_forum']->value[$_smarty_tpl->tpl_vars['Empty_forum']->value];?>
</div>
<?php } ?>

<div class="con"><?php echo $_smarty_tpl->tpl_vars['paging_links']->value;?>
</div>

<?php if ((!$_smarty_tpl->tpl_vars['cur_forum']->value['post_topics']&&$_smarty_tpl->tpl_vars['pun_user']->value['g_post_topics']==1)||$_smarty_tpl->tpl_vars['cur_forum']->value['post_topics']==1||$_smarty_tpl->tpl_vars['is_admmod']->value){?>
<div class="go_to">
<input type="submit" name="move_topics" value="<?php echo $_smarty_tpl->tpl_vars['lang_misc']->value['Move'];?>
" <?php if ($_smarty_tpl->tpl_vars['button_status']->value){?>disabled="disabled"<?php }?> />
<input type="submit" name="delete_topics" value="<?php echo $_smarty_tpl->tpl_vars['lang_misc']->value['Delete'];?>
" <?php if ($_smarty_tpl->tpl_vars['button_status']->value){?>disabled="disabled"<?php }?> />
<input type="submit" name="open" value="<?php echo $_smarty_tpl->tpl_vars['lang_misc']->value['Open'];?>
" <?php if ($_smarty_tpl->tpl_vars['button_status']->value){?>disabled="disabled"<?php }?> />
<input type="submit" name="close" value="<?php echo $_smarty_tpl->tpl_vars['lang_misc']->value['Close'];?>
" <?php if ($_smarty_tpl->tpl_vars['button_status']->value){?>disabled="disabled"<?php }?> />
</div>
</form>
<?php }?>

<?php echo $_smarty_tpl->getSubTemplate ('footer.tpl', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>
<?php }} ?>