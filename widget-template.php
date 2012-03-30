<?php
/**
 * Creating and echoing the puff's
 * - Adds classes
 * - Custom id
 * - Use the right template
 */
$puff_meta = get_post_meta($post->ID, '_puff_meta', true);
$puff_link = get_post_meta($post->ID, '_puff_link', true);
$classes = 'puff-nr-'. $i;

// add user defined class
if( isset($puff_meta['class']) ){
	$classes .= ' '. preg_replace('/[^A-Za-z0-9_-]/', '', $puff_meta['class']);
}

// the puff template
$template = (isset($puff_meta['template'])) ? $puff_meta['template'] : false; 
// add a class with the template
if($template){
	$template_class = preg_replace('/[^0-9a-zA-Z_-]/', '-', $template);
	$classes .= ' ps-'. $template_class;
}

// prevent same id
$before_puff_widget = preg_replace('/id=\".+?\"/i', 'id="ps_puff_'. $post->ID .'"', $before_widget);
echo preg_replace('/class=\"/i', 'class="'. $classes .' ', $before_puff_widget, 1);

/**
 * Use the right layout on puff
 *
 * - Use a custom template if isset and exist
 * - Use puff.php if exsist in the theme
 * - Last use the default layout
 */
// use a template from the user theme if exist
if($template && file_exists(TEMPLATEPATH.'/'.$template)){
	include(TEMPLATEPATH.'/'.$template);
// puff template
}elseif(file_exists(TEMPLATEPATH.'/puff.php')){
	include(TEMPLATEPATH.'/puff.php');
// default template
}else{

	if(has_post_thumbnail()){ the_post_thumbnail(); }

	echo $before_title;
		if(!empty($puff_link)) echo '<a href="'. $puff_link .'" title="'. esc_attr(get_the_title($post->ID)) .'">';
			the_title();
		if(!empty($puff_link)) echo '</a>';
	echo $after_title;

	echo '<div class="puff-content">';
		the_content();
	echo '</div>';

	if(is_user_logged_in() && current_user_can('edit_post')){
		edit_post_link( __( 'Edit', 'ps_puffar_lang' ), '<span class="edit-puff-link">', '</span>', $post->ID );
	}

}// end puff mall

echo $after_widget;