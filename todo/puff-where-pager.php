<?php

// 
// inc/puff-meta-boxes.php

// check if there is any post types checked if not show a error message
		if(!isset($options['post_types']) || empty($options['post_types'])) : ?>
			
			<p>
				<strong>
					<?php _e('You have to check some post type where you want to show the puff.', 'ps_puffar_lang'); ?>
				</strong>
				<br />
				<?php printf(__('Go to the <a href="%s">settings page</a> to select post types'), admin_url('edit.php?post_type=puffar&page=puff_settings')); ?>
			</p>
			
		<?php else: 
		
			// loop through all checked post types
			foreach( (array)$options['post_types'] as $type):
				
				echo '<div><h2>'. $type .'</h2>';
				
				// get all puff_tax terms
				$terms = wp_get_object_terms( $post->ID, 'puff_tax', array('fields' => 'names') );
				
				
				// Set options to get the post types
				$args = array(
					'post_type' => $type,
					'number' => 1000,
					'numberposts' => 1000,
					'exclude' => $terms,
					'depth' => 0,
					'hierarchical' => true
				);
				
				
				$args2 = array(
					'include' => $terms
				);
				
				// Make sure the post type is hierarchical
				$hierarchical_post_types = get_post_types( array( 'hierarchical' => true ) );
				if ( in_array( $type, $hierarchical_post_types )){
					$out = get_pages($args);
				}else{
					$out = get_posts($args);
				}
				
				
				#echo "<pre>"; print_r($out); echo "</pre>";
				
				$walker = new Walker_Puffar_Page();
				$walker_args = array( 'link_before' => '', 'link_after' => '', 'terms' => $terms, 'show_parent' => false);
				
				
				// only print the exisisting posts if there are any
				if(!empty($terms)){
					$out2 = get_posts($args2);
					$walker_args['show_parent'] = true; // show parent 
					echo $walker->walk($out2, 0, $walker_args, 0);
				} 
				// echo some posts
				echo $walker->walk($out, 0, $walker_args, 0);
				
				
				
				/**
				 * Create a pager
				 */
				
				// get the number of published posts
				$count_posts = wp_count_posts($type); //$count_posts->publish
				
				if(!class_exists('Pager')) require_once('pager.class.php');
				$get_var = 'ps_pager_'.$type;
				#$pager = new Pager(10, (isset($_GET[$get_var]) && is_numeric($_GET[$get_var])) ? $_GET[$get_var] : 1, $count_posts->publish, $get_var);
				#$pager->generatePagination();
				
				
				echo '</div>';
				
			endforeach;
		
		endif;
		
		
		
		
		
		
		

		




/**
 * Create HTML list of pages.
 *
 * @package WordPress
 * @since 2.1.0
 * @uses Walker
 */
class Walker_Puffar_Page extends Walker {
	/**
	 * @see Walker::$tree_type
	 * @since 2.1.0
	 * @var string
	 */
	var $tree_type = 'page';

	/**
	 * @see Walker::$db_fields
	 * @since 2.1.0
	 * @todo Decouple this.
	 * @var array
	 */
	var $db_fields = array ('parent' => 'post_parent', 'id' => 'ID');

	/**
	 * @see Walker::start_lvl()
	 * @since 2.1.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int $depth Depth of page. Used for padding.
	 */
	function start_lvl(&$output, $depth) {
		$indent = str_repeat("\t", $depth);
		$output .= "\n$indent<ul class='children'>\n";
	}

	/**
	 * @see Walker::end_lvl()
	 * @since 2.1.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int $depth Depth of page. Used for padding.
	 */
	function end_lvl(&$output, $depth) {
		$indent = str_repeat("\t", $depth);
		$output .= "$indent</ul>\n";
	}

	/**
	 * @see Walker::start_el()
	 * @since 2.1.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $page Page data object.
	 * @param int $depth Depth of page. Used for padding.
	 * @param int $current_page Page ID.
	 * @param array $args
	 */
	function start_el(&$output, $page, $depth, $args, $current_page) {
		if ( $depth )
			$indent = str_repeat("\t", $depth);
		else
			$indent = '';
		
		extract($args, EXTR_SKIP);
		
		// The input
		$checked = (in_array($page->ID, $terms)) ? 'checked="checked"' : '';
		$input = '<input type="checkbox" name="puff_post_id[]" '. $checked .' value="'. $page->ID.'" />';
		
		$css_class = 'puff_post_type_posts';

		$output .= $indent . '<li class="' . $css_class . '"><label>'
				.$input
				. $link_before . apply_filters( 'the_title', $page->post_title, $page->ID ) . $link_after . '</label>';
		
		//if($show_parent && $page->post_parent != 0) $output .= ' ('. get_the_title($page->post_parent) .')';
	}

	/**
	 * @see Walker::end_el()
	 * @since 2.1.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $page Page data object. Not used.
	 * @param int $depth Depth of page. Not Used.
	 */
	function end_el(&$output, $page, $depth) {
		$output .= "</li>\n";
	}

}
		