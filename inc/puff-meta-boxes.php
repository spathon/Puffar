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
	global $post, $wpdb;
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
			
		<?php 
		// loop through all checked post types
		else: 
			$table_name = PUFFAR_TABLE_NAME;
			//$myrows = $wpdb->get_results( "SELECT id, name FROM mytable" );
			$puff_posts = $wpdb->get_results( $wpdb->prepare( "SELECT post_id, puff_where, puff_order FROM $table_name WHERE puff_id = %d", $post->ID ) );
			// save all puff ids in an array
			$post_ids = array();
			foreach($puff_posts as $p){
				$post_ids[] = $p->post_id;
				$puff_info[$p->post_id] = array($p->puff_where, $p->puff_order);
			}
			echo '<div class="hidden" id="psPuff_all_post_ids">'. join(',', $post_ids) .'</div>';
			foreach( (array)$options['post_types'] as $post_type): ?>
				<div class="spathon-where-post-type clearfix">
					<div class="spathon-where-header spathon-where-row clearfix">
						<div class="spathon-where-col spathon-where-col-1">
							<!--input type="checkbox" class="spathon-check-all" data-post-type="<?php echo $post_type; ?>" /-->
							<strong><?php echo $post_type; ?></strong>
						</div>
						<div class="spathon-where-col spathon-where-col-2">
							<strong><?php _e('Widget area', 'ps_puffar_lang'); ?></strong>
						</div>
					</div>
					<div id="spathon_where_wrapper_<?php echo $post_type; ?>" class="spathon-where-list">
						<?php
						$args = array(
							'puff_id' => $post->ID,
							'post_type' => $post_type,
							'post_ids' => $post_ids,
							'puff_info' => $puff_info
						);
						puff_list_selected_posts($args);
						?>
					</div>
					<div class="psPuff-pagination">
						<p>
							<a href="#TB_inline?height=500&width=640&inlineId=psPuff_where_select_<?php echo $post_type; 
								?>" data-post_type="<?php echo $post_type; ?>" class="puff-add-more-posts thickbox">
								<?php _e('Add'); echo ' '. $post_type; ?> &raquo;</a>
						</p>
						<div class="hidden">
						<div id="psPuff_where_select_<?php echo $post_type; ?>">
							<h3>Click to add</h3>
							<div class="puff-posts-list-wrapper puff-posts-list-<?php echo $post_type; ?>">
								<div id="puff_list_posts_wrapper_<?php echo $post_type; ?>" class="puff-posts-list"> 
									<?php ps_puff_echo_posts_wrapper( $post_type, 0, 20, 0 ); ?>
								</div>
								<?php
								// PAGER
								$count = $wpdb->get_results("SELECT 
									COUNT(*) as count
									FROM $wpdb->posts 
									WHERE post_type = '$post_type' 
									AND post_parent = '0'
									AND post_status = 'publish'");
								if(isset($count[0]->count)) $count = $count[0]->count;
								
								$posts_per_page = 20;
								
								if($count > $posts_per_page){
									if(!class_exists('Pager')) require_once('pager.class.php');
									$get_var = 'ps_pager_'.$post_type;
									$pager = new Pager($posts_per_page, (isset($_GET[$get_var]) && is_numeric($_GET[$get_var])) ? $_GET[$get_var] : 1, $count, $post_type);
									$pager->generatePagination();
								}
								?>
							</div>
						</div></div><!-- hidden -->
					</div>
				</div>
		<?php endforeach;endif; ?>
	</div>
	<input type="hidden" name="spathon_puff_meta_noncename" value="<?php echo wp_create_nonce(__FILE__); ?>" />
	<?php
}









/**
 * Create a box with puff attributes
 * 
 * Template for custom layout on the puff
 * - Link 
 * - Description
 * - Template
 * - Class
 */
function spathon_puff_attributes_func(){
	global $post;
	// get puff meta
	$puff_meta = get_post_meta($post->ID, '_puff_meta', true);
	$puff_link = get_post_meta($post->ID, '_puff_link', true);
	
	/**
	 * Puff link
	 */
	?>
	<p class="ps-puff-link-box">
		<label for="ps_puff_link"><strong><?php _e('Link', 'ps_puffar_lang'); ?></strong></label>
		<input type="text" class="widefat" name="ps_puff_link" id="ps_puff_link" value="<?php echo $puff_link; ?>" />
	</p>
	
	<?php
	/**
	 * Description
	 */
	?>
	<p class="ps-puff-description-box">
		
		<label for="ps_puff_desctiption"><strong><?php _e('Puff description', 'ps_puffar_lang'); ?></strong></label>
		<textarea name="_puff_meta[description]" class="widefat" id="ps_puff_desctiption"><?php echo (isset($puff_meta['description'])) ? $puff_meta['description'] : ''; ?></textarea>
		<br />
		<span class="description">
			<?php _e('Usefull when many puffar has the same title', 'ps_puffar_lang'); ?>
		</span>
	</p>
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
	// Creates a select list of the puff templates
	if(count($templates) > 0):
		$selected_template = (isset($puff_meta['template']) && !empty($puff_meta['template'])) ? $puff_meta['template'] : '';
		?>
		<p class="puff-template">
			<label for="puff_template"><strong><?php _e('Puff template', 'ps_puffar_lang'); ?></strong></label>
			<div>
			<select name="_puff_meta[template]" id="puff_template">
				<option value=""><?php _e('Default', 'ps_puffar_lang'); ?></option>
				<?php foreach($templates as $t_name => $t_file): ?>
					<option <?php selected($selected_template, $t_file); ?> value="<?php echo $t_file; ?>"><?php echo $t_name; ?></option>
				<?php endforeach; ?>
			</select>
			</div>
		</p>
	<?php endif; 
	
	/**
	 * Puff Class
	 */
	?>
	<p class="ps-puff-class-box">
		<label for="ps_puff_class"><strong><?php _e('Puff CSS class', 'ps_puffar_lang'); ?></strong></label>
		<input type="text" name="_puff_meta[class]" class="widefat" id="ps_puff_class" value="<?php echo (isset($puff_meta['class'])) ? $puff_meta['class'] : ''; ?>" />
		<br />
		<span class="description">
			<?php _e('Add a custom class for this puffs container', 'ps_puffar_lang'); ?>
		</span>
	</p>

	
	<?php
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
	 * Save the post type ids
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
	
	// delete the rest
	$wpdb->query( $wpdb->prepare( "DELETE FROM $table_name WHERE puff_id = %d", $puff_id ) );
	
	
	foreach($ids as $id){
		
		// insert puff_id, post_id and where
		$where = strip_tags($_POST['spathon_puff_where'][$id]);
		$order = (int) $_POST['spathon_puff_order'][$id];
		#echo '<pre>'; print_r($_POST); echo '</pre>'; 
		
		$wpdb->insert( $table_name, array( 'puff_id' => $puff_id, 'post_id' => $id, 'puff_where' => $where, 'puff_order' => $order ), array( '%s', '%s', '%s', '%d' ) );
	}
	
	#echo '<pre>'; print_r($existing_posts); echo '</pre>'; die();
	
	
	/**
	 * Save the puff link
	 * 
	 * Delete the meta if empty else save the link
	 */
	$puff_link = trim($_POST['ps_puff_link']);
	if (empty($puff_link)) delete_post_meta($puff_id,'_puff_link');
	else update_post_meta($puff_id,'_puff_link',$puff_link);
	 

	
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



