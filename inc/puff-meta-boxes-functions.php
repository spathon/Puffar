<?php




/**
 * Generates an list of posts that have been added
 *
 * @param array (post_type, puff_id)
 * @echo list of posts
 */
function puff_list_selected_posts($args){
	global $wpdb;
	// default args
	$defaults = array(
		'post_type' => 'page',
		'puff_id' => 0,
		'post_ids' => false,
		'puff_info' => false
	);

	$args = wp_parse_args( $args, $defaults );
	extract( $args, EXTR_SKIP );
	
	if(!$post_ids){
		$table_name = PUFFAR_TABLE_NAME;
		$puff_posts = $wpdb->get_results( $wpdb->prepare( "SELECT post_id, puff_where, puff_order FROM $table_name WHERE puff_id = %d", $puff_id ) );
		// save all puff ids in an array
		$post_ids = array();
		foreach($puff_posts as $p){
			$post_ids[] = $p->post_id;
			$puff_info[$p->post_id] = array($p->puff_where, $p->puff_order);
		}
	}
	$post_args = array(
		'include' => $post_ids, 
		'post_type' => $post_type, 
		'numberofposts' => -1
	);
	// if is pages
	$hierarchical_post_types = get_post_types( array( 'hierarchical' => true ) );
	if ( in_array( $post_type, $hierarchical_post_types )){
		$post_args['orderby'] = 'menu_order';
		$post_args['order'] = 'ASC';
	}
	
	$the_posts = get_posts( $post_args );
	
	foreach( $the_posts as $p ){
		puff_list_selected_posts_template($p, $puff_info);
	}
}
// list parents
function puff_get_parents_titles( $id, $text = '' ){
	$parent = get_post( $id );
	$text =  $parent->post_title .' &bull; '. $text;
	if($parent->post_parent != 0){ 
		return puff_get_parents_titles( $parent->post_parent, $text );
	}else{
		return $text;
	}
}
// A template for use in ajax
function puff_list_selected_posts_template($p, $puff_info = false){
	
	// get puff settings for the puff areas
	$options = get_option('puff_settings');
	?>
	
	<div class="spathon-where-single-post spathon-where-row clearfix">
		<label class="spathon-where-checkbox spathon-where-col spathon-where-col-1">
			<input type="hidden" name="puff_post_id[]" value="<?php echo $p->ID; ?>" />
			<span class="spathon-where-title">
				<?php
				if($p->post_parent != 0){
					echo '<span class="puff-page-parent">'. puff_get_parents_titles($p->post_parent) .'</span>';
				}
				?>
				<?php echo $p->post_title; ?>
			</span> 
		</label>
		<!-- Select where -->
		<label class="spathon-where-widgetarea spathon-where-col spathon-where-col-2">
			<?php
			// create a select list of puff areas if exist else an error message
			if(isset($options['area']) && !empty($options['area'])): ?>
				<select name="spathon_puff_where[<?php echo $p->ID; ?>]">
					<?php
					// list all widget areas
					foreach($options['area'] as $area): ?>
						<option<?php
						echo (isset($puff_info[$p->ID][0]) && $puff_info[$p->ID][0] == $area) ? ' selected="selected"' : '';
						?>><?php echo esc_attr($area); ?></option>
					<?php endforeach; ?>
				</select>
			<?php else: ?>
				<em><?php _e('No puff area has been set pleease go to the settings page and create one', 'ps_puffar_lang'); ?></em>
			<?php endif; ?>
		</label>
		
		<!-- Remove -->
		<div class="psPuff-remove-post" data-id="<?php echo $p->ID; ?>">
			<?php _e('Remove', 'ps_puffar_lang'); ?>
		</div>
		
		<!-- Save the order -->
		<input type="hidden" name="spathon_puff_order[<?php echo $p->ID; ?>]" value="<?php 
			echo (isset($puff_info[$p->ID][1])) ? $puff_info[$p->ID][1] : 1000;
			?>" />
		
	</div>
	<?php
}























function ps_puff_get_posts($args = null) {

	global $wpdb;
	
    $defaults = array(
    	"post_type" => "post",
		"parent" => "0",
		"post_status" => "publish",
		"numberposts" => "-1",
		"ignore_sticky_posts" => 1,
		"xsuppress_filters" => "0"
	);
    $post_args = wp_parse_args( $args, $defaults );
	
	// set order by menu order if hierarchial
	$hierarchical_post_types = get_post_types( array( 'hierarchical' => true ) );
	if ( in_array( $post_args['post_type'], $hierarchical_post_types )){
		$post_args['orderby'] = 'menu_order';
		$post_args['order'] = 'ASC';
	}
	
	// does not work with plugin role scoper. don't know why, but this should fix it
	remove_action("get_pages", array('ScoperHardway', 'flt_get_pages'), 1, 2);

	// does not work with plugin ALO EasyMail Newsletter
	remove_filter('get_pages','ALO_exclude_page');
	
	#do_action_ref_array('parse_query', array(&$this));
	#print_r($get_posts_args);
	$pages = get_posts($post_args);

	// filter out pages for wpml, by applying same filter as get_pages does
	// only run if wpml is available or always?
	//$pages = apply_filters('get_pages', $pages, $post_args);
	
	return $pages;
}

function ps_puff_echo_posts( $id = 0, $post_type = 'page', $depth = 0, $limit = 40, $page_nr = 0 ){
	
	$args = array(
		'post_type' => $post_type,
		'post_parent' => $id,
		'numberposts' => $limit,
		'offset' => ( $limit * $page_nr )
	);
	$hierarchical_post_types = get_post_types( array( 'hierarchical' => true ) );
	$is_hierarchical = false;
	if ( in_array( $args['post_type'], $hierarchical_post_types )){
		$is_hierarchical = true;
	}
	
	$get_posts = ps_puff_get_posts($args);
	
	if($get_posts):
		echo '<ul>';
		foreach( $get_posts as $p ): 
			echo '<li class="psPuff-posts-to-add">';
				echo '<div class="psPuff-add-post-to-puff" data-post_type="'. $p->post_type .'" data-id="'. $p->ID .'">';
					for($i = 0; $i < $depth; $i++ ) echo ' - ';
					echo $p->post_title;
				echo '</div>';
				if($is_hierarchical){
					ps_puff_echo_posts($p->ID, $p->post_type, ($depth + 1), -1, 0 );
				}
			echo '</li>';
			
		endforeach;
		echo '</ul>';
	endif;
}

function ps_puff_echo_posts_wrapper($post_type = 'page', $offset = 0, $limit = 20){
	
	echo '<div class="spathon-where-list-page" id="spathon_where_offset_'. $offset .'_type_'. $post_type .'">';
		ps_puff_echo_posts(0, $post_type, 0, $limit, $offset);
	echo '</div>';
}






add_action('wp_ajax_spathon_ajax_puff_where_pager2', 'spathon_ajax_puff_where_pager2'); // Load in new puffar
function spathon_ajax_puff_where_pager2(){
	ps_puff_echo_posts_wrapper($_POST['post_type'], $_POST['offset']);
	die();
}

add_action('wp_ajax_spathon_ajax_add_page_to_puff', 'spathon_ajax_add_page_to_puff'); // Load in new puffar
function spathon_ajax_add_page_to_puff(){
	$the_post = get_post($_POST['id']);
	puff_list_selected_posts_template($the_post);
	die();
}



