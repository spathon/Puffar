<?php


function spathon_register_post_type_puffar(){
	
	$args = array(
		'labels' => array(
			'name' => __('Puffar', 'ps_puffar_lang'), //general name for the post type, usually plural. The same as, and overridden by $post_type_object->label
			'singular_name' => __('Puff', 'ps_puffar_lang'), // - name for one object of this post type. Defaults to value of name
			'add_new' => __('Add puff', 'ps_puffar_lang'), //- the add new text. The default is Add New for both hierarchical and non-hierarchical types. When internationalizing this string, please use a gettext context matching your post type. Example: _x('Add New', 'product');
			'add_new_item' => __('Add puff', 'ps_puffar_lang'), //- the add new item text. Default is Add New Post/Add New Page
			'edit_item' => __('Edit puff', 'ps_puffar_lang'), //- the edit item text. Default is Edit Post/Edit Page
			'new_item' => __('New puff', 'ps_puffar_lang'), //- the new item text. Default is New Post/New Page
			'view_item' => __('View puff', 'ps_puffar_lang'), //- the view item text. Default is View Post/View Page
			'search_items' => __('Search puff', 'ps_puffar_lang'), //- the search items text. Default is Search Posts/Search Pages
			'not_found' => __('No puff found', 'ps_puffar_lang'), //- the not found text. Default is No posts found/No pages found
			'not_found_in_trash' => __('No puff found in the trash.', 'ps_puffar_lang'), //- the not found in trash text. Default is No posts found in Trash/No pages found in Trash
			'parent_item_colon' => __('Puff parent', 'ps_puffar_lang') //- the parent text. This string isn't used on non-hierarchical types. In hierarchical ones the default is Parent Page:
		), // (string) (optional) A plural descriptive name for the post type marked for translation.
		'description' => __('Puffar to insert in widget areas', 'ps_puffar_lang'), //(string) (optional) A short descriptive summary of what the post type is. Defaults to blank.
		'public' => false, // (boolean) (optional) Whether posts of this type should be shown in the admin UI.
		'exclude_from_search' => true, //(boolean) (importance) Whether to exclude posts with this post type from search results.
		'publicly_queryable' => true,//(boolean) (optional) Whether post_type queries can be performed from the front page.
		'show_ui' => true, // (boolean) (optional) Whether to generate a default UI for managing this post type.
		'capability_type' => 'page',//(string) (optional) The post type to use for checking read, edit, and delete capabilities.
		'hierarchical' => false, // (boolean) (optional) Whether the post type is hierarchical.
		'show_in_nav_menus' => false,
		'rewrite' => false,
		'supports' => array(
			'title',
			'editor',
			#'author',
			'thumbnail',
			'excerpt',
			#'custom-fields',
			#'trackbacks',
			#'comments',
			#'revisions'//,
			#'page-attributes' //(parent, template, and menu order) 
		)
	);
	register_post_type('puffar', $args);
}






function ps_puff_edit_columns($columns){
	
	$columns = array(
		"cb" => $columns["cb"],
		"title" => $columns["title"],
		"ps_puff_description" => __('Description', 'ps_puffar_lang'),
		"ps_puff_pages" => __('Is show where?', 'ps_puffar_lang'),
		"date" => $columns["date"]
	);
	
	return $columns;
}


function ps_puff_custom_columns($column){
		global $post, $wpdb;
		$meta = get_post_meta($post->ID, '_puff_meta', true);
		$title = array();
		switch ($column) {
			case "ps_puff_description":
				echo isset($meta['description']) ? $meta['description'] : '';
			break;

			case "ps_puff_pages":
				
				$table_name = PUFFAR_TABLE_NAME;
				// get all pages connected to this puff
				$pages = $wpdb->get_results( $wpdb->prepare( "SELECT post_id FROM $table_name WHERE puff_id = %d", $post->ID ) );
				
				
				if(!empty($pages)){
					$num = count($pages);
					$i = 1;
					$ant = 4; // number of pages to show
					foreach($pages as $p){
						echo get_the_title($p->post_id);
						if($i < $num) echo ', ';
						if($i == $ant && $ant < $num){ 
							echo ' <a href="#" class="puff-more-pages">'. __('See all &raquo;', 'ps_puffar_lang') .'</a><br /><span class="puff-all-pages">';
						}
						if($i >= $num && $i > $ant) echo '</span>';
						$i++;
					}
				}

			break;

		}// end switch
	}

