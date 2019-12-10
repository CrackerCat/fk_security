<?php
/* Smarty version 3.1.31, created on 2019-11-07 10:14:24
  from "tpl_body:10" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.31',
  'unifunc' => 'content_5dc3ee8009dbd7_20717734',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'd99cf3aa25268e7ae5585236412acde3dfbfebc9' => 
    array (
      0 => 'tpl_body:10',
      1 => '1573121657',
      2 => 'tpl_body',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5dc3ee8009dbd7_20717734 (Smarty_Internal_Template $_smarty_tpl) {
if (!is_callable('smarty_function_anchor')) require_once '/home/wwwroot/default/lib/plugins/function.anchor.php';
if (!is_callable('smarty_function_root_url')) require_once '/home/wwwroot/default/lib/plugins/function.root_url.php';
if (!is_callable('smarty_function_sitename')) require_once '/home/wwwroot/default/lib/plugins/function.sitename.php';
if (!is_callable('smarty_function_global_content')) require_once '/home/wwwroot/default/lib/plugins/function.global_content.php';
if (!is_callable('smarty_function_cms_jquery')) require_once '/home/wwwroot/default/lib/plugins/function.cms_jquery.php';
?>
<body id='boxed' class='container page-wrapper page-<?php echo $_smarty_tpl->tpl_vars['page_alias']->value;?>
 page-<?php echo $_smarty_tpl->tpl_vars['content_id']->value;?>
'>
        <!-- #wrapper (wrapping content in a box) -->
        <div class='row' id='wrapper'>
            <!-- accessibility links, jump to nav or content -->
            <ul class="visuallyhidden">
                <li><?php echo smarty_function_anchor(array('anchor'=>'nav','title'=>'Skip to navigation','accesskey'=>'n','text'=>'Skip to navigation'),$_smarty_tpl);?>
</li>
                <li><?php echo smarty_function_anchor(array('anchor'=>'main','title'=>'Skip to content','accesskey'=>'s','text'=>'Skip to content'),$_smarty_tpl);?>
</li>
            </ul>
            <!-- accessibility //-->
            <!-- .top (top section of page containing logo, navigation search...) -->
            <header class='top inner-section'>
                <div class='row header'>
                    <!-- .logo (cmsms logo on the left side) -->
                    <div class='logo four-col'>
                        <a href='<?php echo smarty_function_root_url(array(),$_smarty_tpl);?>
' title='<?php echo smarty_function_sitename(array(),$_smarty_tpl);?>
'>
                            <img src='<?php echo $_smarty_tpl->tpl_vars['theme_path']->value;?>
/images/cmsmadesimple-logo.png' width='227' height='59' alt='<?php echo smarty_function_sitename(array(),$_smarty_tpl);?>
' />
                            <span class='palm'></span>
                        </a>
                    </div>
                    <!-- .logo //-->
                    <!-- .main-navigation (main navigation on the right side) -->
                    <nav class='main-navigation eight-col cf noprint' id='nav' role='navigation'>
                        <?php echo Navigator::function_plugin(array('loadprops'=>'0','template'=>'Simplex Main Navigation'),$_smarty_tpl);?>
 
                    </nav>
                    <!-- .main-navigation //-->
                </div>
                <!-- .header-bottom (bottom part of header containing catchphrase and search field) -->
                <div class='row header-bottom'>
                    <section class='phrase cf'>
                        <span class='seven-col phrase-text'>Power for professionals<br class='lt-768' /> Simplicity for End Users</span>
                        <?php echo preg_replace('!\s+!u', ' ',Search::function_plugin(array('formtemplate'=>'Simplex Search'),$_smarty_tpl));?>
 
                    </section>
                </div>
                <!-- .header-bottom //-->
                <!-- .banner (banner area for a slider or teaser image) -->
                <?php echo smarty_function_global_content(array('name'=>'Simplex Slideshow'),$_smarty_tpl);?>

                <!-- .banner //-->
            </header>
            <!-- .top //-->
            <!-- .content-wrapper (wrapping div for content area) -->
            <main role='main' class='content-wrapper inner-section'>
                <div class='row'>
                    <!-- .content-inner (display content first) -->
                    <div class='content-inner eight-col push-four'>
                        <!-- .content-top (breadcrumbs) -->
                        <div class='content-top cf' itemscope itemtype='http://data-vocabulary.org/Breadcrumb'>
                            <?php echo Navigator::function_plugin(array('action'=>'breadcrumbs'),$_smarty_tpl);?>
 
                            <span class='title-border' aria-hidden='true'></span>
                        </div>
                        <!-- .content-top //-->
                        <!-- .content (actual content with title and content tags) -->
                        <article class='content' id='main'>
                            <h1><?php echo $_smarty_tpl->tpl_vars['main_title']->value;?>
 </h1> 
                                <?php echo $_smarty_tpl->tpl_vars['main_content']->value;?>
 
                        </article>
                        <!-- .content //-->
                    </div>
                    <!-- .content-inner //-->
                    <!-- .sidebar (then show sidebar) -->
                    <aside class='sidebar four-col pull-eight'>
                        
                        <?php echo News::function_plugin(array('summarytemplate'=>'Simplex News Summary','number'=>'2','detailtemplate'=>'Simplex News Detail'),$_smarty_tpl);?>

                    </aside>
                    <!-- .sidebar //-->
                    <div class='cf eight-col push-four'>
                        <?php if (!empty($_smarty_tpl->tpl_vars['prev_page']->value)) {?><span class='previous'><?php echo $_smarty_tpl->tpl_vars['prev_page']->value;?>
</span><?php }?>
                        <?php if (!empty($_smarty_tpl->tpl_vars['next_page']->value)) {?><span class='next'><?php echo $_smarty_tpl->tpl_vars['next_page']->value;?>
</span><?php }?>
                    </div>
                </div>
            </main>
            <!-- .content-wrapper //-->
            <!-- .footer (footer area) -->
            <footer class='footer inner-section'>
                <span class='back-top'><a href='<?php echo smarty_function_anchor(array('anchor'=>'main','onlyhref'=>'1'),$_smarty_tpl);?>
' id='scroll-top'><i class='icon-arrow-up' aria-hidden='true'></i></a></span>
                <div class='row'>
                    <section class='eight-col push-four noprint'>
                        <nav class='footer-navigation row'>
                            <?php echo Navigator::function_plugin(array('template'=>'Simplex Footer Navigation','excludeprefix'=>'home','number_of_levels'=>'2','loadprops'=>'0'),$_smarty_tpl);?>

                        </nav>
                    </section> 
                    <section class='four-col pull-eight copyright'>
                        <?php echo preg_replace('!\s+!u', ' ',smarty_function_global_content(array('name'=>'Simplex Footer'),$_smarty_tpl));?>
 
                    </section>
                </div>
            </footer>
        <!-- #wrapper //--> 
        </div>
    <?php echo smarty_function_cms_jquery(array('exclude'=>'ui,nestedSortable,json,migrate','append'=>'uploads/simplex/js/jquery.sequence-min.js,uploads/simplex/js/functions.min.js'),$_smarty_tpl);?>

    </body>
</html><?php }
}
