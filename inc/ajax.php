<?php

/**
 * Return list of post types after clicking pager
 * 
 * 
 * @param int offset
 * @param string post_type
 * @return html string with list of pages
 */
function spathon_ajax_puff_where_pager(){
	
	$offset = absint($_REQUEST['offset']);
	$post_type = esc_attr($_REQUEST['post_type']);
	
	$args = array(
		'post_type' => $post_type,
		'offset' => $offset
	);
	
	// display a list of all
	spathon_display_where_posts($args);
	
	die();
}






/**
 * Save the order of puffar
 * 
 * 
 * @param int id
 * @param array order
 * @return string success/error message
 */
function ps_save_puff_order_ajax(){
	
	global $wpdb;
	
	$id = $_POST['id'];
	
	// check if user has the rights
	if (isset($_POST['id']) && !current_user_can('edit_page', $id)) {
		die('not allowed');
	}
	// set post ID if is a revision
	if (wp_is_post_revision($id)) {
	    $id = wp_is_post_revision($id);
	}
	
	
	/**
	 * Put the puffar and area in an array 
	 */
	$order_out = array();
	$order = $_POST['order'];
	if(is_array($order)){
		// put the order in an array $order_out[area name] => array(puff, puff, osv)
		foreach($order as $o){
			$order_out[$o[0]] = $o[1];
		}
	}else{
		die('no array');
	}
	
	
	$table_name = PUFFAR_TABLE_NAME;
	
	// get all posts allready connected
	/*
	$posts = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE post_id = %d", $id ) );
	
	$existing_posts = array();
	foreach($posts as $p){
		$existing_posts[$p->puff_id] = $p->puff_id;
	}*/
	
	$wpdb->query( $wpdb->prepare( "DELETE FROM $table_name WHERE post_id = %d", $id ) );
	
	#echo '<pre>'; print_r($existing_posts); echo '</pre>';	die();
	
	$i = 0;
	foreach($order_out as $area => $puff_ids){
		
		
		foreach($puff_ids as $pid){
			$i++;
			$pid = substr($pid, 8);
			
			// insert puff_id, post_id and where
			$where = strip_tags($area);
			echo $wpdb->insert( $table_name, array( 'puff_id' => $pid, 'post_id' => $id, 'puff_where' => $where, 'puff_order' => $i ), array( '%d', '%d', '%s', '%d' ) );
		}
	}
	
	#echo '<pre>'; print_r($existing_posts); echo '</pre>'; die();
	/*
	// delete the rest
	if(!empty($existing_posts)){
		$post_ids = implode("', '", $existing_posts);
		$wpdb->query( $wpdb->prepare( "DELETE FROM $table_name WHERE post_id = %d AND puff_id IN ('$post_ids')", $id ) );
	}*/
	
	die();
}







function ps_load_new_puffar_to_include(){
	echo "<pre>"; print_r($_GET); echo "</pre>";
}


