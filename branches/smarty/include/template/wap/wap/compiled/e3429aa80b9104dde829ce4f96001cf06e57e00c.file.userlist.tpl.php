<?php /* Smarty version Smarty-3.1.7, created on 2012-04-07 20:44:40
         compiled from "L:\home\punbb.mod\www\include/template/wap/wap/tpls\userlist.tpl" */ ?>
<?php /*%%SmartyHeaderCode:239024f57e7b43b5ee8-27368126%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'e3429aa80b9104dde829ce4f96001cf06e57e00c' => 
    array (
      0 => 'L:\\home\\punbb.mod\\www\\include/template/wap/wap/tpls\\userlist.tpl',
      1 => 1331199812,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '239024f57e7b43b5ee8-27368126',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_4f57e7b444546',
  'variables' => 
  array (
    'User_search' => 0,
    'lang_search' => 0,
    'User_find_legend' => 0,
    'lang_ul' => 0,
    'pun_user' => 0,
    'lang_common' => 0,
    'username' => 0,
    'User_group' => 0,
    'show_group' => 0,
    'All_users' => 0,
    'groups' => 0,
    'cur_group' => 0,
    'Sort_by' => 0,
    'sort_by' => 0,
    'show_post_count' => 0,
    'No_of_posts' => 0,
    'Sort_order' => 0,
    'sort_dir' => 0,
    'User_search_info' => 0,
    'User_list' => 0,
    'users' => 0,
    'j' => 0,
    'user_data' => 0,
    'date_format' => 0,
    'No_hits' => 0,
    'paging_links' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_4f57e7b444546')) {function content_4f57e7b444546($_smarty_tpl) {?><?php if (!is_callable('smarty_modifier_date_format')) include 'L:\\home\\punbb.mod\\www\\include\\Smarty\\plugins\\modifier.date_format.php';
?><?php echo $_smarty_tpl->getSubTemplate ('header.tpl', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>



<?php $_smarty_tpl->tpl_vars['date_format'] = new Smarty_variable('%d/%m/%y %H:%I:%S', null, 0);?>

<?php $_smarty_tpl->tpl_vars['User_search'] = new Smarty_variable('User search', null, 0);?>
<?php $_smarty_tpl->tpl_vars['User_find_legend'] = new Smarty_variable('User find legend', null, 0);?>
<?php $_smarty_tpl->tpl_vars['User_group'] = new Smarty_variable('User group', null, 0);?>
<?php $_smarty_tpl->tpl_vars['All_users'] = new Smarty_variable('All users', null, 0);?>

<?php $_smarty_tpl->tpl_vars['Sort_by'] = new Smarty_variable('Sort by', null, 0);?>
<?php $_smarty_tpl->tpl_vars['No_of_posts'] = new Smarty_variable('No of posts', null, 0);?>
<?php $_smarty_tpl->tpl_vars['Sort_order'] = new Smarty_variable('Sort order', null, 0);?>
<?php $_smarty_tpl->tpl_vars['User_search_info'] = new Smarty_variable('User search info', null, 0);?>
<?php $_smarty_tpl->tpl_vars['User_list'] = new Smarty_variable('User list', null, 0);?>
<?php $_smarty_tpl->tpl_vars['No_hits'] = new Smarty_variable('No hits', null, 0);?>

<div class="con">
    <strong><?php echo $_smarty_tpl->tpl_vars['lang_search']->value[$_smarty_tpl->tpl_vars['User_search']->value];?>
</strong>
</div>

<form method="get" action="userlist.php?">
<div class="input">
<strong><?php echo $_smarty_tpl->tpl_vars['lang_ul']->value[$_smarty_tpl->tpl_vars['User_find_legend']->value];?>
</strong><br/>

<?php if ($_smarty_tpl->tpl_vars['pun_user']->value['g_search_users']==1){?>
    <?php echo $_smarty_tpl->tpl_vars['lang_common']->value['Username'];?>
<br/>
    <input type="text" name="username" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['username']->value, ENT_QUOTES, 'UTF-8', true);?>
" maxlength="25" /><br/>
<?php }?>

<?php echo $_smarty_tpl->tpl_vars['lang_ul']->value[$_smarty_tpl->tpl_vars['User_group']->value];?>
<br/>
<select name="show_group"><option value="-1"<?php if ($_smarty_tpl->tpl_vars['show_group']->value==-1){?> selected="selected"<?php }?>><?php echo $_smarty_tpl->tpl_vars['lang_ul']->value[$_smarty_tpl->tpl_vars['All_users']->value];?>
</option>


<?php  $_smarty_tpl->tpl_vars['cur_group'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['cur_group']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['groups']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['cur_group']->key => $_smarty_tpl->tpl_vars['cur_group']->value){
$_smarty_tpl->tpl_vars['cur_group']->_loop = true;
?>
    <option value="<?php echo $_smarty_tpl->tpl_vars['cur_group']->value['g_id'];?>
"<?php if ($_smarty_tpl->tpl_vars['cur_group']->value['g_id']==$_smarty_tpl->tpl_vars['show_group']->value){?> selected="selected"<?php }?>><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['cur_group']->value['g_title'], ENT_QUOTES, 'UTF-8', true);?>
</option>
<?php } ?>

</select><br/>
<?php echo $_smarty_tpl->tpl_vars['lang_search']->value[$_smarty_tpl->tpl_vars['Sort_by']->value];?>
<br/>
<select name="sort_by">
<option value="username"<?php if ($_smarty_tpl->tpl_vars['sort_by']->value=='username'){?> selected="selected"<?php }?>><?php echo $_smarty_tpl->tpl_vars['lang_common']->value['Username'];?>
</option>
<option value="registered"<?php if ($_smarty_tpl->tpl_vars['sort_by']->value=='registered'){?> selected="selected"<?php }?>><?php echo $_smarty_tpl->tpl_vars['lang_common']->value['Registered'];?>
</option>
<?php if ($_smarty_tpl->tpl_vars['show_post_count']->value){?>
<option value="num_posts"<?php if ($_smarty_tpl->tpl_vars['sort_by']->value=='num_posts'){?> selected="selected"<?php }?>><?php echo $_smarty_tpl->tpl_vars['lang_ul']->value[$_smarty_tpl->tpl_vars['No_of_posts']->value];?>
</option>
<?php }?>

</select><br/>
<?php echo $_smarty_tpl->tpl_vars['lang_search']->value[$_smarty_tpl->tpl_vars['Sort_order']->value];?>
<br/>
<select name="sort_dir">
<option value="ASC"<?php if ($_smarty_tpl->tpl_vars['sort_dir']->value=='ASC'){?> selected="selected"<?php }?>><?php echo $_smarty_tpl->tpl_vars['lang_search']->value['Ascending'];?>
</option>
<option value="DESC"<?php if ($_smarty_tpl->tpl_vars['sort_dir']->value=='DESC'){?> selected="selected"<?php }?>><?php echo $_smarty_tpl->tpl_vars['lang_search']->value['Descending'];?>
</option>
</select>
</div>

<div class="input2"><?php echo $_smarty_tpl->tpl_vars['lang_ul']->value[$_smarty_tpl->tpl_vars['User_search_info']->value];?>
</div>
<div class="go_to">
    <input type="submit" name="search" value="<?php echo $_smarty_tpl->tpl_vars['lang_common']->value['Submit'];?>
" accesskey="s" />
</div>
</form>

<div class="con"><strong><?php echo $_smarty_tpl->tpl_vars['lang_common']->value[$_smarty_tpl->tpl_vars['User_list']->value];?>
</strong></div>
<div class="navlinks"><?php echo $_smarty_tpl->tpl_vars['lang_common']->value['Username'];?>


<?php if ($_smarty_tpl->tpl_vars['show_post_count']->value){?>
&#160;|&#160;<?php echo $_smarty_tpl->tpl_vars['lang_common']->value['Posts'];?>

<?php }?>
&#160;|&#160;<?php echo $_smarty_tpl->tpl_vars['lang_common']->value['Title'];?>
&#160;|&#160;<?php echo $_smarty_tpl->tpl_vars['lang_common']->value['Registered'];?>
</div>

<?php  $_smarty_tpl->tpl_vars['user_data'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['user_data']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['users']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['user_data']->key => $_smarty_tpl->tpl_vars['user_data']->value){
$_smarty_tpl->tpl_vars['user_data']->_loop = true;
?>
<div class="<?php if (!isset($_smarty_tpl->tpl_vars['j'])) $_smarty_tpl->tpl_vars['j'] = new Smarty_Variable(null);if ($_smarty_tpl->tpl_vars['j']->value = !$_smarty_tpl->tpl_vars['j']->value){?>in<?php }else{ ?>in2<?php }?>">
<strong><a href="profile.php?id=<?php echo $_smarty_tpl->tpl_vars['user_data']->value['id'];?>
"><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['user_data']->value['username'], ENT_QUOTES, 'UTF-8', true);?>
</a></strong>&#160;
<?php if ($_smarty_tpl->tpl_vars['show_post_count']->value){?>
[<?php echo $_smarty_tpl->tpl_vars['user_data']->value['num_posts'];?>
]
<?php }?>

          
<?php echo get_title($_smarty_tpl->tpl_vars['user_data']->value);?>
 (<?php echo smarty_modifier_date_format($_smarty_tpl->tpl_vars['user_data']->value['registered'],$_smarty_tpl->tpl_vars['date_format']->value);?>
)</div>

<?php }
if (!$_smarty_tpl->tpl_vars['user_data']->_loop) {
?>
<div class="msg"><?php echo $_smarty_tpl->tpl_vars['lang_search']->value[$_smarty_tpl->tpl_vars['No_hits']->value];?>
</div>
<?php } ?>

<div class="con"><?php echo $_smarty_tpl->tpl_vars['paging_links']->value;?>
</div>

<?php echo $_smarty_tpl->getSubTemplate ('footer.tpl', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>
<?php }} ?>