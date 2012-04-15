<?php /* Smarty version Smarty-3.1.7, created on 2012-04-10 22:41:14
         compiled from "L:\home\punbb.mod\www\include/template/wap/wap/tpls\moderate.move_topic.tpl" */ ?>
<?php /*%%SmartyHeaderCode:264594f8025876243f9-63496720%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '930b6f76d9fb408d796e161329b18ed766ce8af7' => 
    array (
      0 => 'L:\\home\\punbb.mod\\www\\include/template/wap/wap/tpls\\moderate.move_topic.tpl',
      1 => 1334083269,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '264594f8025876243f9-63496720',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_4f8025876b91b',
  'variables' => 
  array (
    'lang_common' => 0,
    'action' => 0,
    'Move_topic' => 0,
    'lang_misc' => 0,
    'Move_topics' => 0,
    'fid' => 0,
    'topics' => 0,
    'Move_legend' => 0,
    'Move_to' => 0,
    'forums' => 0,
    'cur_forum' => 0,
    'cur_category' => 0,
    'Leave_redirect' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_4f8025876b91b')) {function content_4f8025876b91b($_smarty_tpl) {?><?php echo $_smarty_tpl->getSubTemplate ('header.tpl', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>


<?php $_smarty_tpl->tpl_vars['Move_topic'] = new Smarty_variable('Move topic', null, 0);?>
<?php $_smarty_tpl->tpl_vars['Move_topics'] = new Smarty_variable('Move topics', null, 0);?>

<div class="inbox">
    <a href="index.php"><?php echo $_smarty_tpl->tpl_vars['lang_common']->value['Index'];?>
</a> &#187; <?php if ($_smarty_tpl->tpl_vars['action']->value=='single'){?><?php echo $_smarty_tpl->tpl_vars['lang_misc']->value[$_smarty_tpl->tpl_vars['Move_topic']->value];?>
<?php }else{ ?><?php echo $_smarty_tpl->tpl_vars['lang_misc']->value[$_smarty_tpl->tpl_vars['Move_topics']->value];?>
<?php }?>
</div>

<?php $_smarty_tpl->tpl_vars['Leave_redirect'] = new Smarty_variable('Leave redirect', null, 0);?>
<?php $_smarty_tpl->tpl_vars['Move_to'] = new Smarty_variable('Move to', null, 0);?>
<?php $_smarty_tpl->tpl_vars['Move_legend'] = new Smarty_variable('Move legend', null, 0);?>

<form method="post" action="moderate.php?fid=<?php echo $_smarty_tpl->tpl_vars['fid']->value;?>
">
<div class="input">
<input type="hidden" name="topics" value="<?php echo $_smarty_tpl->tpl_vars['topics']->value;?>
" />
<strong><?php echo $_smarty_tpl->tpl_vars['lang_misc']->value[$_smarty_tpl->tpl_vars['Move_legend']->value];?>
</strong><br/>
<?php echo $_smarty_tpl->tpl_vars['lang_misc']->value[$_smarty_tpl->tpl_vars['Move_to']->value];?>
<br/>
<select name="move_to_forum">

<?php $_smarty_tpl->tpl_vars['cur_category'] = new Smarty_variable('0', null, 0);?>
<?php  $_smarty_tpl->tpl_vars['cur_forum'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['cur_forum']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['forums']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['cur_forum']->key => $_smarty_tpl->tpl_vars['cur_forum']->value){
$_smarty_tpl->tpl_vars['cur_forum']->_loop = true;
?>
<?php if ($_smarty_tpl->tpl_vars['cur_forum']->value['cid']!=$_smarty_tpl->tpl_vars['cur_category']->value){?>
<?php if ($_smarty_tpl->tpl_vars['cur_category']->value){?>
</optgroup>
<?php }?>
<optgroup label="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['cur_forum']->value['cat_name'], ENT_QUOTES, 'UTF-8', true);?>
">
<?php $_smarty_tpl->tpl_vars['cur_category'] = new Smarty_variable($_smarty_tpl->tpl_vars['cur_forum']->value['cid'], null, 0);?>
<?php }?>

<?php if ($_smarty_tpl->tpl_vars['cur_forum']->value['fid']!=$_smarty_tpl->tpl_vars['fid']->value){?>
<option value="<?php echo $_smarty_tpl->tpl_vars['cur_forum']->value['fid'];?>
"><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['cur_forum']->value['forum_name'], ENT_QUOTES, 'UTF-8', true);?>
</option>
<?php }?>
<?php } ?>
</optgroup>
</select><br/>
<input type="checkbox" name="with_redirect" value="1" <?php if ($_smarty_tpl->tpl_vars['action']->value=='single'){?>checked="checked"<?php }?>/><?php echo $_smarty_tpl->tpl_vars['lang_misc']->value[$_smarty_tpl->tpl_vars['Leave_redirect']->value];?>
</div>
<div class="go_to">
<input type="submit" name="move_topics_to" value="<?php echo $_smarty_tpl->tpl_vars['lang_misc']->value['Move'];?>
" />
</div>
</form>

<?php echo $_smarty_tpl->getSubTemplate ('footer.tpl', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>
<?php }} ?>