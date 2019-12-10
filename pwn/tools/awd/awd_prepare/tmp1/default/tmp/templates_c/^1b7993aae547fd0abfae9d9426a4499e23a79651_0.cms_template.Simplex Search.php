<?php
/* Smarty version 3.1.31, created on 2019-11-07 10:14:24
  from "cms_template:Simplex Search" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.31',
  'unifunc' => 'content_5dc3ee8014fab0_18542439',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '1b7993aae547fd0abfae9d9426a4499e23a79651' => 
    array (
      0 => 'cms_template:Simplex Search',
      1 => '1573121659',
      2 => 'cms_template',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5dc3ee8014fab0_18542439 (Smarty_Internal_Template $_smarty_tpl) {
if (!is_callable('smarty_function_form_start')) require_once '/home/wwwroot/default/lib/plugins/function.form_start.php';
if (!is_callable('smarty_function_form_end')) require_once '/home/wwwroot/default/lib/plugins/function.form_end.php';
?>
<div class='five-col search noprint' role='search'>
<?php echo smarty_function_form_start(array('action'=>'dosearch','method'=>$_smarty_tpl->tpl_vars['form_method']->value,'returnid'=>$_smarty_tpl->tpl_vars['destpage']->value,'inline'=>$_smarty_tpl->tpl_vars['inline']->value),$_smarty_tpl);?>

   <label for='<?php echo $_smarty_tpl->tpl_vars['search_actionid']->value;?>
searchinput' class='visuallyhidden'><?php echo $_smarty_tpl->tpl_vars['searchprompt']->value;?>
:</label>
   <input type='search' class='search-input' id='<?php echo $_smarty_tpl->tpl_vars['search_actionid']->value;?>
searchinput' name='<?php echo $_smarty_tpl->tpl_vars['search_actionid']->value;?>
searchinput' size='20' maxlength='50' value='' placeholder='<?php echo $_smarty_tpl->tpl_vars['searchtext']->value;?>
' /><i class='icon-search' aria-hidden='true'></i>
   <?php if (isset($_smarty_tpl->tpl_vars['hidden']->value)) {
echo $_smarty_tpl->tpl_vars['hidden']->value;
}
echo smarty_function_form_end(array(),$_smarty_tpl);?>

</div><?php }
}
