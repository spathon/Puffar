<?php


/**
 * Meta box where to show the puff
 * 
 * 
 */

function spathon_register_post_type_puff_meta_boxes(){
	
	$options = get_option('puff_settings');
	
	// add a meta box to show post types and add the puff to selected "posts"
	$types = spathon_puff_post_types();
	foreach( $types as $type ){
		if(in_array($type, (array) $options['post_types']))
			add_meta_box( 'page_puffar', __('Puffar', 'ps_puffar_lang'), 'spathon_sort_puffar_on_page', $type, 'side', 'low' );
	}
}




/**
 * Create a list of checkable post types where to add the puff
 * 
 * 
 */
function spathon_sort_puffar_on_page(){
	global $post, $wpdb;
	
	// get all registerd areas
	$options = get_option('puff_settings');
	
	// store all puff ids to exclude on add new
	$exclude_puffar = array();
	
	$table_name = PUFFAR_TABLE_NAME;
	// get all puffar connected to this post
	#$puffar = $wpdb->get_results( $wpdb->prepare( "SELECT puff_id, puff_order, puff_where FROM $table_name WHERE post_id = %d", $post->ID ) );
	
	$post_table = $wpdb->prefix .'posts';
	
	$puffar = $wpdb->get_results( $wpdb->prepare( "
		SELECT * FROM $table_name 
		JOIN $post_table 
		ON $table_name.puff_id = $post_table.ID 
		WHERE $table_name.post_id = %d
		AND $post_table.post_type = 'puffar'", $post->ID ) );
	

	
	$puffar_in_order = array();
	$i = 1000;
	// order puffar by area
	foreach($puffar as $puff){
		// if the order isn't set
		$order = ($puff->puff_order && $puff->puff_order != 1000) ? $puff->puff_order : $i++;
		$puffar_in_order[$puff->puff_where][$order] = $puff;
	}
	
	?>
	<div id="puffar" data-id="<?php echo $post->ID; ?>">
		<?php
		#echo '<a class="thickbox" href="#TB_inline?height=155&width=300&inlineId=myOnPageContent&modal=true" id="addPuff">'. __('Add puff', 'ps_puffar_lang') .'<span>&raquo;</span></a>';
		?>
		<?php #foreach($puffar_in_order as $area => $puffar): ?>
		<?php foreach($options['area'] as $area):
			
			$puffID = array();
			?>
			
			<div class="ps-puff-area">
				<h4 class="ps-puff-area-title"><?php echo $area; ?></h4>
				
				<div class="ps-puff-sort-area">
					<?php
					if(isset($puffar_in_order[$area])){
						$puffar = $puffar_in_order[$area];
						ksort($puffar);
						
						// add puff to the current sidebar
						foreach($puffar as $key => $puff){
							spathon_post_puff_template($puff);
							$exclude_puffar[] = $puff->ID;
							
							// add puffar to the value
							$puffID[] = 'ps_puff_'. $puff->ID;
						}
					}
					?>
				</div>
				
				<div class="ps-post-puff ps-post-puff-new ps-add-puff-button">
					<h3 class="ps-post-puff-title">
						<?php _e('Add Puff', 'ps_puffar_lang'); ?>
					</h3>
				</div>
				
				
				<input type="hidden" name="ps_puff_area_<?php echo esc_attr(str_replace(' ', '_', $area)); ?>" class="ps-puff-input-order" value="<?php echo implode(',', $puffID); ?>" />
				
			</div>
			
		<?php endforeach; ?>

	
		
	</div>
	
	
	<!-- thickbox with puffar to add -->
	<div id="addPuffBox">
		<div id="addPuffBoxWrap">
			<?php /*
			<a href="#" id="create_a_new_puff_button"><?php _e('Create a new puff'); ?></a>
			<div class="ps-add-new-puff" id="create_a_new_puff">
				<h2><?php _e('Title', 'ps_puffar_lang'); ?></h2>
				<input type="text" name="ps_add_new_puff_title" id="ps_add_new_puff_title" />
				<textarea id="ps_add_new_puff_content" name="ps_add_new_puff_content" class="mceEditor"></textarea>
			</div>
			 */ ?>
			
			<?php
			$args = array(
				'post_type' => 'puffar',
				'numberposts' => -1,
				'exclude' => implode(',', $exclude_puffar)
			);
			$new_puffar = get_posts($args);
			
			foreach($new_puffar as $puff){
				spathon_post_puff_template($puff);
			}
			?>
		</div>
	</div>
	
	<input type="hidden" name="spathon_puff_post_meta_noncename" value="<?php echo wp_create_nonce(__FILE__); ?>" />
	<?php
}



/**
 * The template for puffar on the post/page edit page
 * 
 * @param post object
 * @return an template 
 */
function spathon_post_puff_template($puff){
	
	$meta = get_post_meta($puff->ID, '_puff_meta', true);
	
	?>
	<div class="ps-post-puff" id="ps_puff_<?php echo $puff->ID; ?>" data-id="<?php echo $puff->ID; ?>">
		<div class="ps-puff-handle-add ps-puff-add-remove" title="<?php _e('Add puff', 'ps_puffar_lang'); ?>">+</div>
		<div class="ps-puff-handle-remove ps-puff-add-remove" title="<?php _e('Remove puff', 'ps_puffar_lang'); ?>">-</div>
		<div class="ps-puff-handle" title="<?php _e('Click to show/hide content', 'ps_puffar_lang'); ?>"><br></div>
		
		<h3 class="ps-post-puff-title">
			<?php echo (!empty($puff->post_title)) ? $puff->post_title : $puff->ID; ?>
		</h3>
		<div class="ps-post-puff-content">
			<?php echo $puff->post_content; ?>
		</div>
	</div>
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
function spathon_save_puff_posts_meta($post_id){
	
	global $wpdb;
	
	// save the orginal id if is revision
	$org_id = $post_id;
	// authentication checks
	// make sure data came from our meta box
	if (!isset($_POST['spathon_puff_post_meta_noncename']) || !wp_verify_nonce($_POST['spathon_puff_post_meta_noncename'],__FILE__)) return $post_id;
	// check user permissions
	if (!current_user_can('edit_page', $post_id)) {
		return $post_id;
	}
	// set post ID if is a revision
	if (wp_is_post_revision($post_id)) {
	    $post_id = wp_is_post_revision($post_id);
	}
	
	$options = get_option('puff_settings');
	$areas = $options['area'];
	
	
	$table_name = PUFFAR_TABLE_NAME;
	
	$wpdb->query( $wpdb->prepare( "DELETE FROM $table_name WHERE post_id = %d", $post_id ) );
	
	$i = 0;
	foreach($areas as $area){
		
		$area = strip_tags($area);
		$puff_ids = explode(',', $_POST['ps_puff_area_'.str_replace(' ', '_', $area)]);
		
		#echo '<pre>'; print_r( $_POST['ps_puff_area_'.str_replace(' ', '_', $area)] ); echo '</pre>';
		
		if(!empty($puff_ids)){
			foreach($puff_ids as $pid){
				$pid = substr($pid, 8);
				$i++;
				$wpdb->insert( $table_name, array( 'puff_id' => $pid, 'post_id' => $post_id, 'puff_where' => $area, 'puff_order' => $i ), array( '%d', '%d', '%s', '%d' ) );
			}
		}
	}
	
	return $org_id;
}