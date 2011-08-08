<?php


/**
 * Meta box where to show the puff
 * 
 * 
 */

function spathon_register_puff_meta_boxes(){
	
	// add a meta box to show post types and add the puff to selected "posts"
	add_meta_box( 'spathon_puff_where', __('Where will the puff be shown?', 'ps_puffar_lang'), 'spathon_puff_where_function', 'puffar', 'normal', 'high' );
	
	// add puff attributes ex. link
	add_meta_box( 'spathon_puff_attributes', __('Puff attribut', 'ps_puffar_lang'), 'spathon_puff_attributes_func', 'puffar', 'side', 'low' );
}










/**
 * Create a list of checkable post types where to add the puff
 * 
 * 
 */
function spathon_puff_where_function(){
	global $post;
	?>
	
	<div class="spathon-list-of-post-types">
		
		<?php 
		
		$options = get_option('puff_settings');
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
			foreach( (array)$options['post_types'] as $post_type):
				
				
				?>
				<div class="spathon-where-post-type clearfix">
					<div class="spathon-where-header spathon-where-row clearfix">
						<div class="spathon-where-col spathon-where-col-1">
							<input type="checkbox" class="spathon-check-all" data-post-type="<?php echo $post_type; ?>" />
							<strong><?php echo $post_type; ?></strong>
						</div>
						<div class="spathon-where-col spathon-where-col-2">
							<strong><?php _e('Widget area', 'ps_puffar_lang'); ?></strong>
						</div>
					</div>
					<div id="spathon_where_wrapper_<?php echo $post_type; ?>" class="spathon-where-list"">
					<?php
					$posts_per_page = 15;
					
					$args = array(
						'post_type' => $post_type,
						'posts_per_page' => $posts_per_page
					);
					
					// display a list of all
					spathon_display_where_posts($args);
					
					
					// end the list
					echo '</div>';
					
					/**
					 * Create a pager
					 */
					// get the number of published posts
					$count_posts = wp_count_posts($post_type); //$count_posts->publish
					
					if(!class_exists('Pager')) require_once('pager.class.php');
					$get_var = 'ps_pager_'.$post_type;
					$pager = new Pager($posts_per_page, (isset($_GET[$get_var]) && is_numeric($_GET[$get_var])) ? $_GET[$get_var] : 1, $count_posts->publish, $post_type);
					$pager->generatePagination();
					
				echo '</div>';
				
			endforeach;
		
		endif;
		?>
		
		
		
		<?php /*
		<!-- Conditional tags -->
		<div class="spathon-where-conditional">
			
			<div><strong><?php _e('Conditional tags', 'ps_puffar_lang'); ?></strong></div>
			
			<?php
			$conditional = array(
				'is_home', // show on blog pages
				'is_archive',
				'is_category',
				'is_tag',
				'is_author',
				'is_search',
				'is_404'
			);
			
			foreach($conditional as $tag): 
				 $checked = (in_array($tag, $terms)) ? 'checked="checked"' : '';
				?>
				<div class="spathon-where-single-post spathon-where-row clearfix">
					<label class="spathon-where-checkbox spathon-where-col spathon-where-col-1">
						<input type="checkbox" name="puff_post_id[]" <?php echo $checked; ?> value="<?php echo $tag; ?>" />
						<span class="spathon-where-title"><?php echo $tag; ?></span> 
					</label>
					<label class="spathon-where-widgetarea spathon-where-col spathon-where-col-2">
						<select name="spathon_puff_where[]">
							<option value="false"><?php _e('Where on the page?', 'ps_puffar_lang'); ?></option>
							<option>Sidebar 1</option>
							<option>Sidebar 2</option>
						</select>
					</label>
				</div>
			<?php endforeach; ?>
			
		</div>
		*/ ?>
		
		
	</div>
	
	<input type="hidden" name="spathon_puff_meta_noncename" value="<?php echo wp_create_nonce(__FILE__); ?>" />
	<?php
	
}





/**
 * Create a box with puff attributes
 * 
 * Template for custom layout on the puff
 */
function spathon_puff_attributes_func(){
	global $post;
	// get puff meta
	$puff_meta = get_post_meta($post->ID, '_puff_meta', true);
	
	
	
	
	?>
	<div class="">
		
		<label for="ps_puff_desctiption"><?php _e('Puff description', 'ps_puff_lang'); ?> </label>
		<textarea name="_puff_meta[description]" class="widefat" id="ps_puff_desctiption"><?php echo (isset($puff_meta['description'])) ? $puff_meta['description'] : ''; ?></textarea>
		<br />
		<span class="description">
			<?php _e('Usefull when many puffar has the same title', 'ps_puffar_lang'); ?>
		</span>
	</div>
	<?php
	
	
	
	
	/*
	 * Go through all template files for files that start with puff-
	 * then check if they have a template name
	 */
	$root = TEMPLATEPATH;
	$dir = dir($root) or die("Couldn't open: {$root}");
	$templates = array();

	while (($file = $dir->read()) !== false){
		if(substr($file, 0, 5) == 'puff-'){
			
			$template_data = implode( '', file( $root.'/'.$file ));
			$name = '';
			if ( preg_match( '|Puff template:(.*)$|mi', $template_data, $name ) )
				$name = _cleanup_header_comment($name[1]);
			
			if ( !empty( $name ) ) {
				$templates[trim( $name )] = $file;
			}
		}
	}
	
	
	/*
	 * Creates a select list of the puff templates
	 */
	if(count($templates) > 0):?>
		<p class="puff-template">
			<label for="puff_template"><strong><?php _e('Puff template', 'ps_puffar_lang'); ?></strong></label>
			<div>
			<select name="_puff_meta[template]" id="puff_template">
				<option value=""><?php _e('Default', 'ps_puffar_lang'); ?></option>
				<?php foreach($templates as $t_name => $t_file): ?>
					<option <?php selected($puff_meta['template'], $t_file); ?> value="<?php echo $t_file; ?>"><?php echo $t_name; ?></option>
				<?php endforeach; ?>
			</select>
			</div>
		</p>
	<?php endif;
	
}



















/**
 * Save puff meta
 * 
 * 
 * Save all the settings from the custom meta boxes
 * 
 * @param int post id
 * @return int post id
 */
function spathon_save_puff_meta($puff_id){
	
	global $wpdb;
	
	// save the orginal id if is revision
	$org_id = $puff_id;
	// authentication checks
	// make sure data came from our meta box
	if (!isset($_POST['spathon_puff_meta_noncename']) || !wp_verify_nonce($_POST['spathon_puff_meta_noncename'],__FILE__)) return $puff_id;
	// check user permissions
	if ($_POST['post_type'] != 'puffar' && !current_user_can('edit_page', $puff_id)) {
		return $puff_id;
	}
	// set post ID if is a revision
	if (wp_is_post_revision($puff_id)) {
	    $puff_id = wp_is_post_revision($puff_id);
	}


	
	
	/**
	 * Save the post type ids as taxonomy
	 */
	// if there isn't any posts checked remove all terms
	if(!isset($_POST['puff_post_id']) || !is_array($_POST['puff_post_id'])){
		$ids = array();
	}else{
		$ids = $_POST['puff_post_id'];
	}
	
	
	$table_name = PUFFAR_TABLE_NAME;
	
	// get all posts allready connected
	$posts = $wpdb->get_results( $wpdb->prepare( "SELECT post_id FROM $table_name WHERE puff_id = %d", $puff_id ) );
	
	$existing_posts = array();
	foreach($posts as $p){
		$existing_posts[$p->post_id] = $p->post_id;
	}
	
	foreach($ids as $id){
		
		// insert if not exist
		if(!in_array($id, $existing_posts)){
			
			// insert puff_id, post_id and where
			$where = strip_tags($_POST['spathon_puff_where'][$id]);
			$wpdb->insert( $table_name, array( 'puff_id' => $puff_id, 'post_id' => $id, 'puff_where' => $where ), array( '%s', '%s', '%s' ) );
		}else{
			unset($existing_posts[$id]);
		}
	}
	
	#echo '<pre>'; print_r($existing_posts); echo '</pre>'; die();
	
	// delete the rest
	if(!empty($existing_posts)){
		$post_ids = implode("', '", $existing_posts);
		$wpdb->query( $wpdb->prepare( "DELETE FROM $table_name WHERE puff_id = %d AND post_id IN ('$post_ids')", $puff_id ) );
	}
	


	
	/**
	 * Puff attributes
	 */
	if(isset($_POST['_puff_meta'])){
		$new_data = $_POST['_puff_meta'];
		
		// uses & refer to change the variable and clean empty
		puff_meta_clean($new_data);
		
		// save the puff attributes
		if (is_null($new_data)) delete_post_meta($puff_id,'_puff_meta');
		else update_post_meta($puff_id,'_puff_meta',$new_data);
	}
	
	return $org_id;
}



