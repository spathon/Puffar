<?php
/*


get_option('puff_settings'); 

$options = get_option('puff_settings');
$options['post_types'];
$options['area'];

*/




/**
 * Register a new menu page
 *
 * Register the new submenu for puff settings under Puffar
 */
function spathon_puff_create_settings_page(){
	$settings = add_submenu_page(
		'edit.php?post_type=puffar', // parent slug
		__('Puff settings', 'ps_puffar_lang'), // page title
		__('Puff settings', 'ps_puffar_lang'), // menu title
		'manage_options', // capability
		'puff_settings', // menu_slug
		'spathon_the_puff_settings_page' // function
	);
	#echo $settings;
}




/**
 * The settings page
 */
function spathon_the_puff_settings_page(){
	require_once('settings_page.php');
}




/**
 * Register the puff settings
 */
function spathon_puff_register_settings() {
	register_setting('spathon_puff_settings', 'puff_settings', 'spathon_validate_puff_settings');
	add_settings_section(
		'puff_settings_id', // css ID
	 	__('General puff settings:', 'ps_puffar_lang'), // h3
	 	'spathon_puff_settings_text', // callback for the text
	 	'puff_settings'// the page where to show
	 	);
	add_settings_field(
		'puff_where_to_show', // CSS ID
		__('Select post types where you want to show Puffar', 'ps_puffar_lang'), // the text next to the field
		'spathon_puff_select_where_input', // callback for form fields
		'puff_settings', // the page where this field will be visible
		'puff_settings_id' // where on the settings page
	);
	add_settings_field(
		'puff_areas',
		__('Create widget areas where you want to show puffar', 'ps_puffar_lang'),
		'spathon_puff_areas',
		'puff_settings',
		'puff_settings_id'
	);
}


/**
 * The section text
 */
function spathon_puff_settings_text(){
	echo '';
}




// the input
function spathon_puff_select_where_input(){
	// get the settings
	$option = get_option('puff_settings');
	// filter out the post_types
	$post_types = $option['post_types'];
	// get all registered post types
	$existing_post_types = spathon_puff_post_types();
	
	#echo '<pre>'; print_r($option); echo '</pre>';
	
	// print checkboxes
	foreach($existing_post_types as $type): ?>
		
		<div>
		<label>
			<input type="checkbox" name="puff_settings[post_types][]" value="<?php echo $type; ?>" <?php
			if(in_array($type, (array) $post_types)) echo 'checked="checked"';
			?> />
			<span><?php echo $type; ?></span>
		</label>
		</div>
	<?php endforeach;
}




/**
 * Create widgets where puffar can be shown
 * 
 */
function spathon_puff_areas(){
	
	
	$options = get_option('puff_settings');
	// if the area option is set
	if(isset($options['area'])){
		$areas = (array) $options['area'];
	}else{
		$areas = array();
	}
	
	// add an empty in the begining for copying
	array_unshift($areas, '');
	// do the same at the end for an empty
	array_push($areas, '');
	
	$i = 0;
	foreach($areas as $area): ?>
	
		<div class="spathon-puff-area-row<?php if($i == 0) echo ' spathon-clone-area'; ?>">
			<label>
				<input type="text" name="puff_settings[area][]" value="<?php echo esc_attr($area); ?>" />
				<a href="#remove" class="spathon-remove-area">
					<img src="<?php echo SPATHON_PUFFAR_URL; ?>/images/remove.png" alt="remove" />
					<span><?php _e('Remove', 'ps_puffar_lang'); ?></span>
				</a>
			</label> 
		</div>
	
	<?php $i++; endforeach; ?>
	
	<a href="#add_field" class="" id="spathon_puffar_add_area"><?php _e('Add area', 'ps_puffar_lang'); ?></a>
	
	<?php
}




/*
 * Validate the puff settings
 */
function spathon_validate_puff_settings( $input ){
	
	// store all valid input
	$valid = array();
	
	#echo '<pre>'; print_r($input); echo '</pre>'; 
	
	/**
	 * Post types
	 */
	
	// save the post types
	$valid['post_types'] = array();
	// get all registered post types
	$existing_post_types = spathon_puff_post_types();
	
	// check if there is any post type selected
	if(isset($input['post_types'])){
		foreach( (array)$input['post_types'] as $type){
			// if the value is an existing post type save it
			if(in_array($type, $existing_post_types)){
				$valid['post_types'][] = $type;
			}
		}
	}
	
	/**
	 * Areas
	 */
	$valid['area'] = array();
	// validate aera names
	foreach($input['area'] as $area){
		$area = ps_puff_validate_area($area);
		// save the area if not empty
		if(!empty($area)) $valid['area'][] = $area;
	}
	
	
	#echo '<pre>'; print_r($valid); echo '</pre>';
	return $valid;
}



