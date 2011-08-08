<?php


/**
 * 
 */
function spathon_display_where_posts($args){
	global $post, $wpdb;
	$tmp = $post;
	
	
	// default args
	$defaults = array(
		'post_args' => array(),
		'posts_per_page' => 15,
		'post_type' => 'page',
		'offset' => 0
	);

	$args = wp_parse_args( $args, $defaults );
	extract( $args, EXTR_SKIP );

	// get puff settings for the puff areas
	$options = get_option('puff_settings');
	
	
	
	$table_name = PUFFAR_TABLE_NAME;
	//$myrows = $wpdb->get_results( "SELECT id, name FROM mytable" );
	$posts = $wpdb->get_results( $wpdb->prepare( "SELECT post_id FROM $table_name WHERE puff_id = %d", $post->ID ) );
	
	$terms = array();
	foreach($posts as $p){
		$terms[] = $p->post_id;
	}
	
	
	// if terms is set get all the post 
	if(!empty($terms)){
		$checked_posts = get_posts(array('include' => $terms, 'post_type' => $post_type));
		$checked_count = count($checked_posts);
	}else{
		$checked_count = 0;
	}
	
	
	// set default post args
	$default_post_args = array(
		'post_type' => $post_type,
		'numberposts' => ($posts_per_page - $checked_count),
		'exclude' => $terms,
		'orderby' => 'date',
		'order' => 'DESC',
		'offset' => ($posts_per_page * $offset)
	);
	$post_args = wp_parse_args( $post_args, $default_post_args );
	
	
	// set order by menu order if hierarchial
	$hierarchical_post_types = get_post_types( array( 'hierarchical' => true ) );
	if ( in_array( $post_type, $hierarchical_post_types )){
		$post_args['orderby'] = 'menu_order';
		$post_args['order'] = 'ASC';
	}
	
	
	
	
	$posts = get_posts($post_args);
	
	// merge the two arrays
	if($checked_count > 0)	$posts = array_merge($checked_posts, $posts);
	
	$i = 0;
	echo '<div class="spathon-where-list-page" id="spathon_where_offset_'. $offset .'_type_'. $post_type .'" data-offset="'. $offset .'">';
		foreach($posts as $post): setup_postdata($post); 
		
			// if the post is in the array $terms
			$checked = (in_array($post->ID, $terms)) ? 'checked="checked"' : '';
			?>
			
			<div class="spathon-where-single-post spathon-where-row clearfix">
				<label class="spathon-where-checkbox spathon-where-col spathon-where-col-1">
					<input type="checkbox" name="puff_post_id[]" <?php echo $checked; ?> value="<?php echo $post->ID; ?>" />
					<span class="spathon-where-title"><?php the_title(); ?></span> 
				</label>
				<label class="spathon-where-widgetarea spathon-where-col spathon-where-col-2">
					<?php
					// create a select list of puff areas if exist else an error message
					if(isset($options['area']) && !empty($options['area'])): ?>
						<select name="spathon_puff_where[<?php echo $post->ID; ?>]">
							<?php
							// list all widget areas
							foreach($options['area'] as $area): ?>
								<option><?php echo esc_attr($area); ?></option>
							<?php endforeach; ?>
						</select>
					<?php else: ?>
						<em><?php _e('No puff area has been set pleease go to the settings page and create one', 'ps_puffar_lang'); ?></em>
					<?php endif; ?>
				</label>
			</div>
			
		
		<?php $i++; endforeach; 
	echo '</div>';
	
	// reset the post var
	$post = $tmp;
	
}
