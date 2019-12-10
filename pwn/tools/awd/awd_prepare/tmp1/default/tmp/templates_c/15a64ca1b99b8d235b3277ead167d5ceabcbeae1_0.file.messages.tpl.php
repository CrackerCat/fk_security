<?php
/* Smarty version 3.1.31, created on 2019-11-07 10:24:09
  from "/home/wwwroot/default/admin/themes/OneEleven/templates/messages.tpl" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.31',
  'unifunc' => 'content_5dc3f0c9233b80_87529718',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '15a64ca1b99b8d235b3277ead167d5ceabcbeae1' => 
    array (
      0 => '/home/wwwroot/default/admin/themes/OneEleven/templates/messages.tpl',
      1 => 1573121655,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5dc3f0c9233b80_87529718 (Smarty_Internal_Template $_smarty_tpl) {
if (isset($_smarty_tpl->tpl_vars['errors']->value) && $_smarty_tpl->tpl_vars['errors']->value[0] != '') {?><aside class="message pageerrorcontainer" role="alert"><?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['errors']->value, 'error');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['error']->value) {
if ($_smarty_tpl->tpl_vars['error']->value) {?><p><?php echo $_smarty_tpl->tpl_vars['error']->value;?>
</p><?php }
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);
?>
</aside><?php }
if (isset($_smarty_tpl->tpl_vars['messages']->value) && $_smarty_tpl->tpl_vars['messages']->value[0] != '') {?><aside class="message pagemcontainer" role="status"><?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['messages']->value, 'message');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['message']->value) {
if ($_smarty_tpl->tpl_vars['message']->value) {?><p><?php echo $_smarty_tpl->tpl_vars['message']->value;?>
</p><?php }
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);
?>
</aside><?php }
}
}
