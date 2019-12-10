<?php
/* Smarty version 3.1.31, created on 2019-11-07 10:14:24
  from "cms_template:Simplex Main Navigation" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.31',
  'unifunc' => 'content_5dc3ee80131dc8_65516722',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'c57b9d2f578a81d2320a0c971c355eefcf787ce6' => 
    array (
      0 => 'cms_template:Simplex Main Navigation',
      1 => '1573121659',
      2 => 'cms_template',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5dc3ee80131dc8_65516722 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->smarty->ext->_tplFunction->registerTplFunctions($_smarty_tpl, array (
  'do_class' => 
  array (
    'compiled_filepath' => '/home/wwwroot/default/tmp/templates_c/^c57b9d2f578a81d2320a0c971c355eefcf787ce6_0.cms_template.Simplex Main Navigation.php',
    'uid' => 'c57b9d2f578a81d2320a0c971c355eefcf787ce6',
    'call_name' => 'smarty_template_function_do_class_4681227705dc3ee800db4c1_46701472',
  ),
  'Simplex_menu' => 
  array (
    'compiled_filepath' => '/home/wwwroot/default/tmp/templates_c/^c57b9d2f578a81d2320a0c971c355eefcf787ce6_0.cms_template.Simplex Main Navigation.php',
    'uid' => 'c57b9d2f578a81d2320a0c971c355eefcf787ce6',
    'call_name' => 'smarty_template_function_Simplex_menu_4681227705dc3ee800db4c1_46701472',
  ),
));
$_smarty_tpl->_assignInScope('main_id', ' id=\'main-menu\'');
if (isset($_smarty_tpl->tpl_vars['nodes']->value)) {
$_smarty_tpl->smarty->ext->_tplFunction->callTemplateFunction($_smarty_tpl, 'Simplex_menu', array('data'=>$_smarty_tpl->tpl_vars['nodes']->value,'depth'=>'0','ul_class'=>'cf'), true);
}
}
/* smarty_template_function_do_class_4681227705dc3ee800db4c1_46701472 */
if (!function_exists('smarty_template_function_do_class_4681227705dc3ee800db4c1_46701472')) {
function smarty_template_function_do_class_4681227705dc3ee800db4c1_46701472($_smarty_tpl,$params) {
foreach ($params as $key => $value) {
$_smarty_tpl->tpl_vars[$key] = new Smarty_Variable($value, $_smarty_tpl->isRenderingCache);
}
if (count($_smarty_tpl->tpl_vars['classes']->value) > 0) {?> class='<?php echo implode(' ',$_smarty_tpl->tpl_vars['classes']->value);?>
'<?php }
}}
/*/ smarty_template_function_do_class_4681227705dc3ee800db4c1_46701472 */
/* smarty_template_function_Simplex_menu_4681227705dc3ee800db4c1_46701472 */
if (!function_exists('smarty_template_function_Simplex_menu_4681227705dc3ee800db4c1_46701472')) {
function smarty_template_function_Simplex_menu_4681227705dc3ee800db4c1_46701472($_smarty_tpl,$params) {
$params = array_merge(array('depth'=>'1'), $params);
foreach ($params as $key => $value) {
$_smarty_tpl->tpl_vars[$key] = new Smarty_Variable($value, $_smarty_tpl->isRenderingCache);
}?><ul<?php echo $_smarty_tpl->tpl_vars['main_id']->value;
if (isset($_smarty_tpl->tpl_vars['ul_class']->value) && $_smarty_tpl->tpl_vars['ul_class']->value != '') {?> class="<?php echo $_smarty_tpl->tpl_vars['ul_class']->value;?>
"<?php }?>><?php $_smarty_tpl->_assignInScope('main_id', '');
$_smarty_tpl->_assignInScope('ul_class', '');
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['data']->value, 'node');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['node']->value) {
$_smarty_tpl->_assignInScope('list_class', array());
$_smarty_tpl->_assignInScope('href_class', array('cf'));
$_smarty_tpl->_assignInScope('parent_indicator', '');
$_smarty_tpl->_assignInScope('aria_support', '');
if ($_smarty_tpl->tpl_vars['node']->value->current || $_smarty_tpl->tpl_vars['node']->value->parent) {
$_tmp_array = isset($_smarty_tpl->tpl_vars['list_class']) ? $_smarty_tpl->tpl_vars['list_class']->value : array();
if (!is_array($_tmp_array) || $_tmp_array instanceof ArrayAccess) {
settype($_tmp_array, 'array');
}
$_tmp_array[] = 'current';
$_smarty_tpl->_assignInScope('list_class', $_tmp_array);
$_tmp_array = isset($_smarty_tpl->tpl_vars['href_class']) ? $_smarty_tpl->tpl_vars['href_class']->value : array();
if (!is_array($_tmp_array) || $_tmp_array instanceof ArrayAccess) {
settype($_tmp_array, 'array');
}
$_tmp_array[] = 'current';
$_smarty_tpl->_assignInScope('href_class', $_tmp_array);
}
if ($_smarty_tpl->tpl_vars['node']->value->children_exist) {
$_tmp_array = isset($_smarty_tpl->tpl_vars['list_class']) ? $_smarty_tpl->tpl_vars['list_class']->value : array();
if (!is_array($_tmp_array) || $_tmp_array instanceof ArrayAccess) {
settype($_tmp_array, 'array');
}
$_tmp_array[] = 'parent';
$_smarty_tpl->_assignInScope('list_class', $_tmp_array);
$_smarty_tpl->_assignInScope('aria_support', ' aria-haspopup=\'true\'');
$_smarty_tpl->_assignInScope('parent_indicator', ' <i class=\'icon-arrow-left\' aria-hidden=\'true\'></i>');
}
if ($_smarty_tpl->tpl_vars['node']->value->type == 'sectionheader') {
$_tmp_array = isset($_smarty_tpl->tpl_vars['list_class']) ? $_smarty_tpl->tpl_vars['list_class']->value : array();
if (!is_array($_tmp_array) || $_tmp_array instanceof ArrayAccess) {
settype($_tmp_array, 'array');
}
$_tmp_array[] = 'sectionheader';
$_smarty_tpl->_assignInScope('list_class', $_tmp_array);
?><li<?php $_smarty_tpl->smarty->ext->_tplFunction->callTemplateFunction($_smarty_tpl, 'do_class', array('classes'=>$_smarty_tpl->tpl_vars['list_class']->value), true);
echo $_smarty_tpl->tpl_vars['aria_support']->value;?>
><span><?php echo $_smarty_tpl->tpl_vars['node']->value->menutext;
echo $_smarty_tpl->tpl_vars['parent_indicator']->value;?>
</span><?php if (isset($_smarty_tpl->tpl_vars['node']->value->children)) {
$_smarty_tpl->smarty->ext->_tplFunction->callTemplateFunction($_smarty_tpl, 'Simplex_menu', array('data'=>$_smarty_tpl->tpl_vars['node']->value->children,'depth'=>$_smarty_tpl->tpl_vars['depth']->value+1), true);
}?></li><?php } elseif ($_smarty_tpl->tpl_vars['node']->value->type == 'separator') {
$_tmp_array = isset($_smarty_tpl->tpl_vars['list_class']) ? $_smarty_tpl->tpl_vars['list_class']->value : array();
if (!is_array($_tmp_array) || $_tmp_array instanceof ArrayAccess) {
settype($_tmp_array, 'array');
}
$_tmp_array[] = 'separator';
$_smarty_tpl->_assignInScope('list_class', $_tmp_array);
?><li<?php $_smarty_tpl->smarty->ext->_tplFunction->callTemplateFunction($_smarty_tpl, 'do_class', array('classes'=>$_smarty_tpl->tpl_vars['list_class']->value), true);?>
'><hr class='separator'/></li><?php } else { ?><li<?php $_smarty_tpl->smarty->ext->_tplFunction->callTemplateFunction($_smarty_tpl, 'do_class', array('classes'=>$_smarty_tpl->tpl_vars['list_class']->value), true);
echo $_smarty_tpl->tpl_vars['aria_support']->value;?>
><a<?php $_smarty_tpl->smarty->ext->_tplFunction->callTemplateFunction($_smarty_tpl, 'do_class', array('classes'=>$_smarty_tpl->tpl_vars['href_class']->value), true);?>
 href='<?php echo $_smarty_tpl->tpl_vars['node']->value->url;?>
'<?php if ($_smarty_tpl->tpl_vars['node']->value->target != '') {?> target='<?php echo $_smarty_tpl->tpl_vars['node']->value->target;?>
'<?php }?>><?php echo $_smarty_tpl->tpl_vars['node']->value->menutext;
echo $_smarty_tpl->tpl_vars['parent_indicator']->value;?>
</a><?php if (isset($_smarty_tpl->tpl_vars['node']->value->children)) {
$_smarty_tpl->smarty->ext->_tplFunction->callTemplateFunction($_smarty_tpl, 'Simplex_menu', array('data'=>$_smarty_tpl->tpl_vars['node']->value->children,'depth'=>$_smarty_tpl->tpl_vars['depth']->value+1), true);
}?></li><?php }
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);
?>
</ul><?php
}}
/*/ smarty_template_function_Simplex_menu_4681227705dc3ee800db4c1_46701472 */
}
