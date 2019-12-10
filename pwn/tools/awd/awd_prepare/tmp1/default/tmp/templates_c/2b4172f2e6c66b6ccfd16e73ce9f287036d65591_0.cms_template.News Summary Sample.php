<?php
/* Smarty version 3.1.31, created on 2019-11-07 10:14:56
  from "cms_template:News Summary Sample" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.31',
  'unifunc' => 'content_5dc3eea00f7150_16498933',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '2b4172f2e6c66b6ccfd16e73ce9f287036d65591' => 
    array (
      0 => 'cms_template:News Summary Sample',
      1 => '1573121659',
      2 => 'cms_template',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5dc3eea00f7150_16498933 (Smarty_Internal_Template $_smarty_tpl) {
if (!is_callable('smarty_function_repeat')) require_once '/home/wwwroot/default/lib/plugins/function.repeat.php';
if (!is_callable('smarty_modifier_cms_date_format')) require_once '/home/wwwroot/default/lib/plugins/modifier.cms_date_format.php';
if (!is_callable('smarty_modifier_cms_escape')) require_once '/home/wwwroot/default/lib/plugins/modifier.cms_escape.php';
if (!is_callable('smarty_function_file_url')) require_once '/home/wwwroot/default/lib/plugins/function.file_url.php';
?>
<!-- Start News Display Template -->

<ul class="list1">
<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['cats']->value, 'node');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['node']->value) {
if ($_smarty_tpl->tpl_vars['node']->value['depth'] > $_smarty_tpl->tpl_vars['node']->value['prevdepth']) {
echo smarty_function_repeat(array('string'=>"<ul>",'times'=>$_smarty_tpl->tpl_vars['node']->value['depth']-$_smarty_tpl->tpl_vars['node']->value['prevdepth']),$_smarty_tpl);?>

<?php } elseif ($_smarty_tpl->tpl_vars['node']->value['depth'] < $_smarty_tpl->tpl_vars['node']->value['prevdepth']) {
echo smarty_function_repeat(array('string'=>"</li></ul>",'times'=>$_smarty_tpl->tpl_vars['node']->value['prevdepth']-$_smarty_tpl->tpl_vars['node']->value['depth']),$_smarty_tpl);?>

</li>
<?php } elseif ($_smarty_tpl->tpl_vars['node']->value['index'] > 0) {?></li>
<?php }?>
<li<?php if ($_smarty_tpl->tpl_vars['node']->value['index'] == 0) {?> class="firstnewscat"<?php }?>>
<?php if ($_smarty_tpl->tpl_vars['node']->value['count'] > 0) {?>
	<a href="<?php echo $_smarty_tpl->tpl_vars['node']->value['url'];?>
"><?php echo $_smarty_tpl->tpl_vars['node']->value['news_category_name'];?>
</a><?php } else { ?><span><?php echo $_smarty_tpl->tpl_vars['node']->value['news_category_name'];?>
 </span><?php }
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);
?>

<?php echo smarty_function_repeat(array('string'=>"</li></ul>",'times'=>$_smarty_tpl->tpl_vars['node']->value['depth']-1),$_smarty_tpl);?>
</li>
</ul>


<?php if ($_smarty_tpl->tpl_vars['category_name']->value) {?>
<h1><?php echo $_smarty_tpl->tpl_vars['category_name']->value;?>
</h1>
<?php }?>



<?php if ($_smarty_tpl->tpl_vars['pagecount']->value > 1) {?>
  <p>
<?php if ($_smarty_tpl->tpl_vars['pagenumber']->value > 1) {
echo $_smarty_tpl->tpl_vars['firstpage']->value;?>
&nbsp;<?php echo $_smarty_tpl->tpl_vars['prevpage']->value;?>
&nbsp;
<?php }
echo $_smarty_tpl->tpl_vars['pagetext']->value;?>
&nbsp;<?php echo $_smarty_tpl->tpl_vars['pagenumber']->value;?>
&nbsp;<?php echo $_smarty_tpl->tpl_vars['oftext']->value;?>
&nbsp;<?php echo $_smarty_tpl->tpl_vars['pagecount']->value;?>

<?php if ($_smarty_tpl->tpl_vars['pagenumber']->value < $_smarty_tpl->tpl_vars['pagecount']->value) {?>
&nbsp;<?php echo $_smarty_tpl->tpl_vars['nextpage']->value;?>
&nbsp;<?php echo $_smarty_tpl->tpl_vars['lastpage']->value;?>

<?php }?>
</p>
<?php }
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['items']->value, 'entry');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['entry']->value) {
?>
<div class="NewsSummary">

<?php if ($_smarty_tpl->tpl_vars['entry']->value->postdate) {?>
	<div class="NewsSummaryPostdate">
		<?php echo smarty_modifier_cms_date_format($_smarty_tpl->tpl_vars['entry']->value->postdate);?>

	</div>
<?php }?>

<div class="NewsSummaryLink">
<a href="<?php echo $_smarty_tpl->tpl_vars['entry']->value->moreurl;?>
" title="<?php echo smarty_modifier_cms_escape($_smarty_tpl->tpl_vars['entry']->value->title,'htmlall');?>
"><?php echo smarty_modifier_cms_escape($_smarty_tpl->tpl_vars['entry']->value->title);?>
</a>
</div>

<div class="NewsSummaryCategory">
	<?php echo $_smarty_tpl->tpl_vars['category_label']->value;?>
 <?php echo $_smarty_tpl->tpl_vars['entry']->value->category;?>

</div>

<?php if ($_smarty_tpl->tpl_vars['entry']->value->author) {?>
	<div class="NewsSummaryAuthor">
		<?php echo $_smarty_tpl->tpl_vars['author_label']->value;?>
 <?php echo $_smarty_tpl->tpl_vars['entry']->value->author;?>

	</div>
<?php }?>

<?php if ($_smarty_tpl->tpl_vars['entry']->value->summary) {?>
        
	<div class="NewsSummarySummary">
		<?php echo $_smarty_tpl->tpl_vars['entry']->value->summary;?>

	</div>

	<div class="NewsSummaryMorelink">
		[<?php echo $_smarty_tpl->tpl_vars['entry']->value->morelink;?>
]
	</div>

<?php } elseif ($_smarty_tpl->tpl_vars['entry']->value->content) {?>
        
	<div class="NewsSummaryContent">
		<?php echo $_smarty_tpl->tpl_vars['entry']->value->content;?>

	</div>
<?php }?>

<?php if (isset($_smarty_tpl->tpl_vars['entry']->value->extra)) {?>
    <div class="NewsSummaryExtra">
        <?php echo $_smarty_tpl->tpl_vars['entry']->value->extra;?>

	
    </div>
<?php }
if (isset($_smarty_tpl->tpl_vars['entry']->value->fields)) {?>
  <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['entry']->value->fields, 'field');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['field']->value) {
?>
     <div class="NewsSummaryField">
        <?php if ($_smarty_tpl->tpl_vars['field']->value->type == 'file') {?>
          <?php if (isset($_smarty_tpl->tpl_vars['field']->value->value) && $_smarty_tpl->tpl_vars['field']->value->value) {?>
            <img src="<?php echo $_smarty_tpl->tpl_vars['entry']->value->file_location;?>
/<?php echo $_smarty_tpl->tpl_vars['field']->value->value;?>
"/>
          <?php }?>
        <?php } elseif ($_smarty_tpl->tpl_vars['field']->value->type == 'linkedfile') {?>
          
          <?php if (!empty($_smarty_tpl->tpl_vars['field']->value->value)) {?>
            <img src="<?php echo smarty_function_file_url(array('file'=>$_smarty_tpl->tpl_vars['field']->value->value),$_smarty_tpl);?>
" alt="<?php echo $_smarty_tpl->tpl_vars['field']->value->value;?>
"/>
          <?php }?>
        <?php } else { ?>
          <?php echo $_smarty_tpl->tpl_vars['field']->value->name;?>
:&nbsp;<?php echo $_smarty_tpl->tpl_vars['field']->value->value;?>

        <?php }?>
     </div>
  <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);
?>

<?php }?>

</div>
<?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);
?>

<!-- End News Display Template --><?php }
}
