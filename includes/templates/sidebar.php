<?php
/**
 * The Docs Sidebar for Single Docs
 * @package	Organized Docs
 * @since 1.2.3
 */
?>
<div id="content-sidebar" class="content-sidebar widget-area" role="complementary"><ul>
<?php if( ! dynamic_sidebar( 'isa_organized_docs' ) ) {
		the_widget('DocsSectionContents', array('title' => 'Table of Contents'), array(
			'before_widget' => '<li id="docs_section_contents-1" class="widget widget_docs_section_contents">',
	        'after_widget' => '</li>',
	        'before_title' => '<h3 class="widget-title">',
	        'after_title' => '</h3>'
		));
} ?>
</ul></div>