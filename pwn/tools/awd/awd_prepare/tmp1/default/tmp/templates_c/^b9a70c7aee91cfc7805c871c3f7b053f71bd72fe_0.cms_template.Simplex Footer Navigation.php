<?php
/* Smarty version 3.1.31, created on 2019-11-07 10:14:24
  from "cms_template:Simplex Footer Navigation" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.31',
  'unifunc' => 'content_5dc3ee8028cdd2_46540885',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'b9a70c7aee91cfc7805c871c3f7b053f71bd72fe' => 
    array (
      0 => 'cms_template:Simplex Footer Navigation',
      1 => '1573121659',
      2 => 'cms_template',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5dc3ee8028cdd2_46540885 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->smarty->ext->_tplFunction->registerTplFunctions($_smarty_tpl, array (
  'do_footer_class' => 
  array (
    'compiled_filepath' => '/home/wwwroot/default/tmp/templates_c/^b9a70c7aee91cfc7805c871c3f7b053f71bd72fe_0.cms_template.Simplex Footer Navigation.php',
    'uid' => 'b9a70c7aee91cfc7805c871c3f7b053f71bd72fe',
    'call_name' => 'smarty_template_function_do_footer_class_13911629035dc3ee802510b9_60436088',
  ),
  'Simplex_footer_menu' => 
  array (
    'compiled_filepath' => '/home/wwwroot/default/tmp/templates_c/^b9a70c7aee91cfc7805c871c3f7b053f71bd72fe_0.cms_template.Simplex Footer Navigation.php',
    'uid' => 'b9a70c7aee91cfc7805c871c3f7b053f71bd72fe',
    'call_name' => 'smarty_template_function_Simplex_footer_menu_13911629035dc3ee802510b9_60436088',
  ),
));
$_smarty_tpl->_assignInScope('main_id', ' id=\'footer-menu\'');
if (isset($_smarty_tpl->tpl_vars['nodes']->value)) {
$_smarty_tpl->smarty->ext->_tplFunction->callTemplateFunction($_smarty_tpl, 'Simplex_footer_menu', array('data'=>$_smarty_tpl->tpl_vars['nodes']->value,'depth'=>'0','ul_class'=>'cf'), true);
}
}
/* smarty_template_function_do_footer_class_13911629035dc3ee802510b9_60436088 */
if (!function_exists('smarty_template_function_do_footer_class_13911629035dc3ee802510b9_60436088')) {
function smarty_template_function_do_footer_class_13911629035dc3ee802510b9_60436088($_smarty_tpl,$params) {
foreach ($params as $key => $value) {
$_smarty_tpl->tpl_vars[$key] = new Smarty_Variable($value, $_smarty_tpl->isRenderingCache);
}
if (count($_smarty_tpl->tpl_vars['classes']->value) > 0) {?> class='<?php echo implode(' ',$_smarty_tpl->tpl_vars['classes']->value);?>
'<?php }
}}
/*/ smarty_template_function_do_footer_class_13911629035dc3ee802510b9_60436088 */
/* smarty_template_function_Simplex_footer_menu_13911629035dc3ee802510b9_60436088 */
if (!function_exists('smarty_template_function_Simplex_footer_menu_13911629035dc3ee802510b9_60436088')) {
function smarty_template_function_Simplex_footer_menu_13911629035dc3ee802510b9_60436088($_smarty_tpl,$params) {
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
$_smarty_tpl->_assignInScope('href_class', array());
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
}
if ($_smarty_tpl->tpl_vars['node']->value->type == 'sectionheader') {
$_tmp_array = isset($_smarty_tpl->tpl_vars['list_class']) ? $_smarty_tpl->tpl_vars['list_class']->value : array();
if (!is_array($_tmp_array) || $_tmp_array instanceof ArrayAccess) {
settype($_tmp_array, 'array');
}
$_tmp_array[] = 'sectionheader';
$_smarty_tpl->_assignInScope('list_class', $_tmp_array);
?><li<?php $_smarty_tpl->smarty->ext->_tplFunction->callTemplateFunction($_smarty_tpl, 'do_footer_class', array('classes'=>$_smarty_tpl->tpl_vars['list_class']->value), true);?>
><span><?php echo $_smarty_tpl->tpl_vars['node']->value->menutext;?>
</span><?php if (isset($_smarty_tpl->tpl_vars['node']->value->children)) {
$_smarty_tpl->smarty->ext->_tplFunction->callTemplateFunction($_smarty_tpl, 'Simplex_footer_menu', array('data'=>$_smarty_tpl->tpl_vars['node']->value->children,'depth'=>$_smarty_tpl->tpl_vars['depth']->value+1), true);
}?></li><?php } elseif ($_smarty_tpl->tpl_vars['node']->value->type == 'separator') {
$_tmp_array = isset($_smarty_tpl->tpl_vars['list_class']) ? $_smarty_tpl->tpl_vars['list_class']->value : array();
if (!is_array($_tmp_array) || $_tmp_array instanceof ArrayAccess) {
settype($_tmp_array, 'array');
}
$_tmp_array[] = 'separator';
$_smarty_tpl->_assignInScope('list_class', $_tmp_array);
?><li<?php $_smarty_tpl->smarty->ext->_tplFunction->callTemplateFunction($_smarty_tpl, 'do_footer_class', array('classes'=>$_smarty_tpl->tpl_vars['list_class']->value), true);?>
'><hr class='separator'/></li><?php } else { ?><li<?php $_smarty_tpl->smarty->ext->_tplFunction->callTemplateFunction($_smarty_tpl, 'do_footer_class', array('classes'=>$_smarty_tpl->tpl_vars['list_class']->value), true);?>
><a<?php $_smarty_tpl->smarty->ext->_tplFunction->callTemplateFunction($_smarty_tpl, 'do_footer_class', array('classes'=>$_smarty_tpl->tpl_vars['href_class']->value), true);?>
 href='<?php echo $_smarty_tpl->tpl_vars['node']->value->url;?>
'<?php if ($_smarty_tpl->tpl_vars['node']->value->target != '') {?> target='<?php echo $_smarty_tpl->tpl_vars['node']->value->target;?>
'<?php }?>><?php echo $_smarty_tpl->tpl_vars['node']->value->menutext;?>
</a><?php if (isset($_smarty_tpl->tpl_vars['node']->value->children)) {
$_smarty_tpl->smarty->ext->_tplFunction->callTemplateFunction($_smarty_tpl, 'Simplex_footer_menu', array('data'=>$_smarty_tpl->tpl_vars['node']->value->children,'depth'=>$_smarty_tpl->tpl_vars['depth']->value+1), true);
}?></li><?php }
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);
?>
</ul><?php
}}
/*/ smarty_template_function_Simplex_footer_menu_13911629035dc3ee802510b9_60436088 */
}
