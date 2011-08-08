<?php
/*
 * Uninstall 
 * 
 * Removes all options but NOT the post type puffar
 */

// if uninstalled not called from wordpress exit
if(!defined('WP_UNINSTALL_PLUGIN')) 
	exit();



echo '<pre>'; print_r($wpdb); echo '</pre>';
die();
global $wpdb;
  
$thetable = $wpdb->prefix."your_table_name";
//Delete any options that's stored also?
//delete_option('wp_yourplugin_version');
$wpdb->query("DROP TABLE IF EXISTS $thetable");

// Delete all options from the options table
delete_option('puff_options');