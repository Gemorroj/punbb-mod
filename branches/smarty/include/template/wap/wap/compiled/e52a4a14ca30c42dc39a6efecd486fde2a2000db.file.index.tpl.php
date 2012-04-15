<?php /* Smarty version Smarty-3.1.7, created on 2012-04-11 22:14:16
         compiled from "L:\home\punbb.mod\www\include/template/wap/wap/tpls\index.tpl" */ ?>
<?php /*%%SmartyHeaderCode:314034f5a5095880ed5-81062920%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'e52a4a14ca30c42dc39a6efecd486fde2a2000db' => 
    array (
      0 => 'L:\\home\\punbb.mod\\www\\include/template/wap/wap/tpls\\index.tpl',
      1 => 1334168053,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '314034f5a5095880ed5-81062920',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_4f5a5095910e3',
  'variables' => 
  array (
    'pun_user' => 0,
    'lang_common' => 0,
    'Link_separator_m' => 0,
    'pun_config' => 0,
    'lang_pms' => 0,
    'logout' => 0,
    'forums' => 0,
    'cur_forum' => 0,
    'cur_category' => 0,
    'j' => 0,
    'date_format' => 0,
    'Empty_board' => 0,
    'lang_index' => 0,
    'Show_new_posts' => 0,
    'Mark_all_as_read' => 0,
    'No_of_users' => 0,
    'stats' => 0,
    'No_of_topics' => 0,
    'No_of_posts' => 0,
    'Users_online' => 0,
    'num_users' => 0,
    'Guests_online' => 0,
    'num_guests' => 0,
    'users' => 0,
    'pun_user_online' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_4f5a5095910e3')) {function content_4f5a5095910e3($_smarty_tpl) {?><?php if (!is_callable('smarty_modifier_date_format')) include 'L:\\home\\punbb.mod\\www\\include\\Smarty\\plugins\\modifier.date_format.php';
?><?php echo $_smarty_tpl->getSubTemplate ('header.tpl', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>


<?php $_smarty_tpl->tpl_vars['date_format'] = new Smarty_variable('%d/%m/%y %H:%I:%S', null, 0);?>
<?php $_smarty_tpl->tpl_vars['Show_new_posts'] = new Smarty_variable('Show new posts', null, 0);?>
<?php $_smarty_tpl->tpl_vars['Mark_all_as_read'] = new Smarty_variable('Mark all as read', null, 0);?>
<?php $_smarty_tpl->tpl_vars['Empty_board'] = new Smarty_variable('Empty board', null, 0);?>

<?php $_smarty_tpl->tpl_vars['Link_separator_m'] = new Smarty_variable('|', null, 0);?>

<div class="navlinks">
    <?php if ($_smarty_tpl->tpl_vars['pun_user']->value['is_guest']){?>
        <a href="login.php"><?php echo $_smarty_tpl->tpl_vars['lang_common']->value['Login'];?>
</a><?php echo $_smarty_tpl->tpl_vars['Link_separator_m']->value;?>
<a href="register.php"><?php echo $_smarty_tpl->tpl_vars['lang_common']->value['Register'];?>
</a>
    <?php }else{ ?>
        <?php if ($_smarty_tpl->tpl_vars['pun_user']->value['g_id']>@PUN_MOD){?>
            <a href="profile.php?id=<?php echo $_smarty_tpl->tpl_vars['pun_user']->value['id'];?>
"><?php echo $_smarty_tpl->tpl_vars['lang_common']->value['Profile'];?>
 (<span style="font-weight: bold"><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['pun_user']->value['username'], ENT_QUOTES, 'UTF-8', true);?>
</span>)</a>
            <?php if ($_smarty_tpl->tpl_vars['pun_config']->value['o_pms_enabled']&&$_smarty_tpl->tpl_vars['pun_user']->value['g_pm']==1){?>
                <?php echo $_smarty_tpl->tpl_vars['Link_separator_m']->value;?>
<a href="message_list.php"><?php echo $_smarty_tpl->tpl_vars['lang_pms']->value['Private'];?>
</a>
            <?php }?>
        <?php }else{ ?>
            <a href="<?php echo @PUN_ROOT;?>
admin_index.php"><?php echo $_smarty_tpl->tpl_vars['lang_common']->value['Admin_m'];?>
</a>
        <?php }?>
        
        <?php echo $_smarty_tpl->tpl_vars['Link_separator_m']->value;?>
<a href="login.php?action=out&amp;id=<?php echo $_smarty_tpl->tpl_vars['pun_user']->value['id'];?>
&amp;csrf_token=<?php echo $_smarty_tpl->tpl_vars['logout']->value;?>
"><?php echo $_smarty_tpl->tpl_vars['lang_common']->value['Logout'];?>
</a>
    <?php }?>
</div>

<?php $_smarty_tpl->tpl_vars['j'] = new Smarty_variable(false, null, 0);?>
<?php $_smarty_tpl->tpl_vars['cur_category'] = new Smarty_variable('', null, 0);?>
<?php  $_smarty_tpl->tpl_vars['cur_forum'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['cur_forum']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['forums']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['cur_forum']->key => $_smarty_tpl->tpl_vars['cur_forum']->value){
$_smarty_tpl->tpl_vars['cur_forum']->_loop = true;
?>

    <?php if ($_smarty_tpl->tpl_vars['cur_forum']->value['cid']!=$_smarty_tpl->tpl_vars['cur_category']->value){?>
    <?php ob_start();?><?php echo $_smarty_tpl->tpl_vars['cur_forum']->value['cid'];?>
<?php $_tmp1=ob_get_clean();?><?php $_smarty_tpl->tpl_vars['cur_category'] = new Smarty_variable($_tmp1, null, 0);?>

        
        <div class="cat">
            <span class="sp_cat"><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['cur_forum']->value['cat_name'], ENT_QUOTES, 'UTF-8', true);?>
</span>
        </div>
    <?php }?>
    
    <div class="<?php if (!isset($_smarty_tpl->tpl_vars['j'])) $_smarty_tpl->tpl_vars['j'] = new Smarty_Variable(null);if ($_smarty_tpl->tpl_vars['j']->value = !$_smarty_tpl->tpl_vars['j']->value){?>in<?php }else{ ?>in2<?php }?>">
    <?php if ($_smarty_tpl->tpl_vars['cur_forum']->value['redirect_url']){?>
    <a href="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['cur_forum']->value['redirect_url'], ENT_QUOTES, 'UTF-8', true);?>
"><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['cur_forum']->value['forum_name'], ENT_QUOTES, 'UTF-8', true);?>
</a>
    <?php }else{ ?>
        <a href="viewforum.php?id=<?php echo $_smarty_tpl->tpl_vars['cur_forum']->value['fid'];?>
"><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['cur_forum']->value['forum_name'], ENT_QUOTES, 'UTF-8', true);?>
</a> (<?php echo $_smarty_tpl->tpl_vars['cur_forum']->value['num_topics'];?>
/<?php echo $_smarty_tpl->tpl_vars['cur_forum']->value['num_posts'];?>
)
    <?php }?>
    
    <?php if ($_smarty_tpl->tpl_vars['cur_forum']->value['last_post']){?>
        <br/>
        <span class="sub">
        &#187; <a href="viewtopic.php?pid=<?php echo $_smarty_tpl->tpl_vars['cur_forum']->value['last_post_id'];?>
#p<?php echo $_smarty_tpl->tpl_vars['cur_forum']->value['last_post_id'];?>
"><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['cur_forum']->value['subject'], ENT_QUOTES, 'UTF-8', true);?>
</a>&#160;(<?php echo smarty_modifier_date_format($_smarty_tpl->tpl_vars['cur_forum']->value['last_post'],$_smarty_tpl->tpl_vars['date_format']->value);?>
&#160;<?php echo $_smarty_tpl->tpl_vars['lang_common']->value['by'];?>
&#160;<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['cur_forum']->value['last_poster'], ENT_QUOTES, 'UTF-8', true);?>
)
        </span>
    <?php }?>
    </div>

<?php }
if (!$_smarty_tpl->tpl_vars['cur_forum']->_loop) {
?>
<div class="in"><?php echo $_smarty_tpl->tpl_vars['lang_index']->value[$_smarty_tpl->tpl_vars['Empty_board']->value];?>
</div>
<?php } ?>

<?php if (!$_smarty_tpl->tpl_vars['pun_user']->value['is_guest']){?>
<div class="go_to">
<a class="but" href="search.php?action=show_new"><?php echo $_smarty_tpl->tpl_vars['lang_common']->value[$_smarty_tpl->tpl_vars['Show_new_posts']->value];?>
</a>
<a class="but" href="misc.php?action=markread"><?php echo $_smarty_tpl->tpl_vars['lang_common']->value[$_smarty_tpl->tpl_vars['Mark_all_as_read']->value];?>
</a>
</div>
<?php }?>

<?php $_smarty_tpl->tpl_vars['No_of_users'] = new Smarty_variable('No of users', null, 0);?>
<?php $_smarty_tpl->tpl_vars['No_of_topics'] = new Smarty_variable('No of topics', null, 0);?>
<?php $_smarty_tpl->tpl_vars['No_of_posts'] = new Smarty_variable('No of posts', null, 0);?>
<?php $_smarty_tpl->tpl_vars['Users_online'] = new Smarty_variable('Users online', null, 0);?>
<?php $_smarty_tpl->tpl_vars['Guests_online'] = new Smarty_variable('Guests online', null, 0);?>

<div class="incqbox">
<?php echo $_smarty_tpl->tpl_vars['lang_index']->value[$_smarty_tpl->tpl_vars['No_of_users']->value];?>
: <?php echo $_smarty_tpl->tpl_vars['stats']->value['total_users'];?>
<br/>
<?php echo $_smarty_tpl->tpl_vars['lang_index']->value[$_smarty_tpl->tpl_vars['No_of_topics']->value];?>
: <?php echo $_smarty_tpl->tpl_vars['stats']->value['total_topics'];?>
<br/>
<?php echo $_smarty_tpl->tpl_vars['lang_index']->value[$_smarty_tpl->tpl_vars['No_of_posts']->value];?>
: <?php echo $_smarty_tpl->tpl_vars['stats']->value['total_posts'];?>
<br/>

<?php if ($_smarty_tpl->tpl_vars['pun_config']->value['o_users_online']==1){?>

<?php echo $_smarty_tpl->tpl_vars['lang_index']->value[$_smarty_tpl->tpl_vars['Users_online']->value];?>
: <?php echo (($tmp = @$_smarty_tpl->tpl_vars['num_users']->value)===null||$tmp==='' ? '0' : $tmp);?>
<br/>
<?php echo $_smarty_tpl->tpl_vars['lang_index']->value[$_smarty_tpl->tpl_vars['Guests_online']->value];?>
: <?php echo (($tmp = @$_smarty_tpl->tpl_vars['num_guests']->value)===null||$tmp==='' ? '0' : $tmp);?>


<?php if ($_smarty_tpl->tpl_vars['num_users']->value){?>
</div>
<div class="act">
<?php echo $_smarty_tpl->tpl_vars['lang_index']->value['Online'];?>
:
<?php  $_smarty_tpl->tpl_vars['pun_user_online'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['pun_user_online']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['users']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['pun_user_online']->key => $_smarty_tpl->tpl_vars['pun_user_online']->value){
$_smarty_tpl->tpl_vars['pun_user_online']->_loop = true;
?>
<a href="profile.php?id=<?php echo $_smarty_tpl->tpl_vars['pun_user_online']->value['user_id'];?>
"><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['pun_user_online']->value['ident'], ENT_QUOTES, 'UTF-8', true);?>
</a>
<?php } ?>
<?php }?>
</div>
<?php }?>

<?php echo $_smarty_tpl->getSubTemplate ('footer.tpl', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>
<?php }} ?>