<?php
/*
Plugin Name: Puffar
Plugin URI: http://labs.dinwebb.nu
Description: Create "Puffar" that you can put on different pages and places
Author: Spathon @ Dinwebb
Version: 0.2
Author URI: http://www.spathon.com/
License: GPLv2 or later

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/


/*
 * Definitions
 * 
 * Url to the plugin dir
 */
add_action('plugins_loaded', 'spathon_define_constants');
function spathon_define_constants(){
	global $wpdb;
	define('SPATHON_PUFFAR_DIR', plugin_dir_path(__FILE__)); //plugins_url('images/icon.png', __FILE__);
	define('SPATHON_PUFFAR_URL', plugins_url('', __FILE__));
	
	define('SPATHON_PUFF_VERSION', '0.7.1');
	define('PUFFAR_TABLE_NAME', $wpdb->prefix ."ps_puffar_relations");
}
$puff_conditional = array(
	'is_home' => __('Home', 'ps_puffar_lang'), // show on blog pages
	//'is_single' => __('Posts', 'ps_puffar_lang'),
	'is_page' => __('Pages', 'ps_puffar_lang'),
	'is_archive' => __('Archive', 'ps_puffar_lang'),
	'is_category' => __('Category', 'ps_puffar_lang'),
	'is_tag' => __('Tag', 'ps_puffar_lang'),
	'is_author' => __('Author', 'ps_puffar_lang'),
	'is_search' => __('Search', 'ps_puffar_lang'),
	'is_404' => __('404 Error', 'ps_puffar_lang')
);


/*
echo '<pre>'; print_r(get_option('widget_ps_puff')); echo '</pre>';
echo '<pre>'; print_r(get_option('sidebars_widgets')); echo '</pre>';
*/



/**
 * Activation
 * 
 *
 * Check if WordPress is at least 3.0 else Create some default settings if not done
 */
register_activation_hook( __FILE__, 'spathon_puffar_activation' );
function spathon_puffar_activation() {
	// check to see if WP is 3.0 >
	// if less deactivate puffar
	if(version_compare(get_bloginfo('version'), '3.0', '<')){
		deactivate_plugins(basename(__FILE__)); // deactivate don't seem to work :S
		// but this do
		die(__('You are using an old version of WordPress please upgrade!')); 
		
	// check if the settings already exist else create default
	}elseif(!get_option('puff_settings')){
		
		//spathon_puffar_install();
		
		// save the default options
		$puff_options = array(
			'post_types' => 'page',
			'area' => array('Primary area')
		);
		update_option('puff_settings', $puff_options);
	}
}

/**
 * Check if the plugin has been updated
 */
add_action('plugins_loaded', 'puff_check_version_update'); // on upgrade activation not called
function puff_check_version_update() {
	if (get_option('puff_plugin_version') != SPATHON_PUFF_VERSION) {
		puff_update_db_table();
	}
}
// update/create the table for connection between puffar and pages
function puff_update_db_table(){

	global $wpdb;
    if ( ! empty($wpdb->charset) )
        $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
    if ( ! empty($wpdb->collate) )
        $charset_collate .= " COLLATE $wpdb->collate";

	$sql = "CREATE TABLE " . PUFFAR_TABLE_NAME . " (
		id INT NOT NULL AUTO_INCREMENT,
		puff_id INT NOT NULL,
		post_id INT NOT NULL,
		puff_order INT NOT NULL,
		puff_where VARCHAR( 255 ) NOT NULL,
		PRIMARY KEY  (id)
	) ". $charset_collate .";";
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	$result = dbDelta($sql);
	
	add_option("puff_plugin_version", SPATHON_PUFF_VERSION);
}
	







// include all needed files
require_once('inc/functions.php'); // general functions
require_once('inc/register.php'); // register puffar/taxonomy
require_once('settings/settings.php'); // register and create settings

require_once('inc/puff-meta-boxes.php'); // meta boxes on edit/new puff (where, template)
require_once('inc/puff-meta-boxes-functions.php'); // functions for puff meta boxes
require_once('inc/post-type-meta-boxes.php'); // meta box on page/post and custom edit

require_once('inc/ajax.php'); // all ajax functions


require_once('puff-widget.php'); // and finaly the widget
require_once('single-puff-widget.php'); // and finaly the widget

/**
 * Loads the translations files
 * 
 *
 */
add_action('plugins_loaded', 'spathon_load_translation');
function spathon_load_translation(){
	load_plugin_textdomain('ps_puffar_lang', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/');
}



/*
 * Create a class for the creation of puffar
 * 
 */
class SpathonsPuffar {
	
	/*
	 * Init the plugin
	 * 
	 * Registrating post-types and taxonomies
	 */
	function __construct(){
		
		// register the the post type for puffar
		add_action('init', 'spathon_register_post_type_puffar');
		// Edit columns
		add_action("manage_posts_custom_column",  "ps_puff_custom_columns");
		add_filter("manage_edit-puffar_columns", "ps_puff_edit_columns");

		
		/**
		 * register settings 
		 *
		 * The settings is found in settings/settings.php
		 */
		add_action('admin_menu', 'spathon_puff_create_settings_page');
		add_action('admin_init', 'spathon_puff_register_settings'); // register the settings
		
		
		/**
		 * Puff meta boxes
		 */
		// Create meta boxes for puffar and pages
		add_action('add_meta_boxes', 'spathon_register_puff_meta_boxes');
		// save the meta box fields
		add_action('save_post', 'spathon_save_puff_meta');
		// save meta on post/page/custom
		add_action('save_post', 'spathon_save_puff_posts_meta');
		
		/**
		 * Post types meta box
		 */
		add_action('add_meta_boxes', 'spathon_register_post_type_puff_meta_boxes');

		
		
		/**
		 * Load javascript on the right places
		 */
		// load javascript on edit and add new
		add_action( 'admin_print_scripts-edit.php', array(&$this, 'load_js') ); // print javascript on edit pages
		add_action( 'admin_print_scripts-post.php', array(&$this, 'load_js') ); // print javascript on edit pages
		add_action( 'admin_print_scripts-post-new.php', array(&$this, 'load_js') ); // same as above but on new
		add_action( 'admin_print_scripts-puffar_page_puff_settings', array(&$this, 'load_js') ); // load on the settings page
		add_action( 'admin_print_scripts-widgets.php', array(&$this, 'load_js') ); // load on the widgets page (to add custom area)
		
		
		/**
		 * Load css on the right places
		 */
		add_action('admin_head', array(&$this, 'admin_head_css')); // css displayed on all admin pages
		add_action( 'admin_print_styles-post.php', array(&$this, 'load_css') ); // print css on edit page
		add_action( 'admin_print_styles-post-new.php', array(&$this, 'load_css') ); // same as above but on new
		add_action( 'admin_print_styles-puffar_page_puff_settings', array(&$this, 'load_css') ); // load on the settings page
		add_action( 'admin_print_styles-edit.php', array(&$this, 'load_css') ); // print css on edit page
		add_action( 'admin_print_styles-widgets.php', array(&$this, 'load_css') ); // print css on edit page
		
		
		/**
		 * Ajax functions
		 */
		add_action('wp_ajax_spathon_ajax_puff_where_pager', 'spathon_ajax_puff_where_pager'); // load more pages (select page on puff edit)
		add_action('wp_ajax_ps_save_puff_order_ajax', 'ps_save_puff_order_ajax'); // save the order of puffar
		add_action('wp_ajax_ps_load_new_puffar_to_include', 'ps_load_new_puffar_to_include'); // Load in new puffar
		
			
	}
	
	
	
	/**
	 * Insert all javascripts
	 */
	function load_js() {
	    #global $post_type, $pagenow;
	    #if( 
	    #	'puffar' == $post_type || 
	    #	( isset($_GET['page']) && 'puff_settings' == $_GET['page'] ) ||
		#	'widgets.php' == $pagenow
		#	){
	        	wp_enqueue_script( 'spathon-puffar-edit-admin', plugins_url('/js/puff-edit.js', __FILE__), array('jquery', 'thickbox'), '1.0', true);
		#}
	}



	/**
	 * CSS on all admin pages
	 */
	function admin_head_css(){
		?>
		<style>
			#icon-edit.icon32-posts-puffar { background:transparent url(<?php echo plugins_url('/images/puff.png', __FILE__); ?>) no-repeat; }
			
			#adminmenu #menu-posts-puffar div.wp-menu-image{background:transparent url(<?php echo plugins_url('images/puff-icon-small.png', __FILE__); ?>) no-repeat scroll 5px -34px;}
			#adminmenu #menu-posts-puffar:hover div.wp-menu-image,
			#adminmenu #menu-posts-puffar.wp-has-current-submenu div.wp-menu-image {background:transparent url(<?php echo plugins_url('/images/puff-icon-small.png', __FILE__); ?>) no-repeat 5px 6px;}		
	    </style>
		<?php
	}
	
	
	/**
	 * CSS
	 */
	function load_css(){
		wp_enqueue_style('spathon-puff-admin-styles', plugins_url('/css/puff-admin-styles.css', __FILE__));
	}
	
}

$spathons_puffar = new SpathonsPuffar();








