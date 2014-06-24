<?php

/*
Plugin Name: Mapply
Plugin URI: http://wordpress.org/plugins/mapply/
Description: Dislay a map of your stores on your WordPress site!
Version: 1.2.1
Author: Mapply
Author URI: http://www.mapply.net
*/

// Originally developed by Dann Blair
// boldinnovationgroup.net
// https://github.com/BOLDInnovationGroup/Mapply-WordPress-Plugin

// Shortcodes
add_shortcode("mapply", "mapply_handler");

// Add actions
add_action('admin_menu', 'mapply_create_menu');
add_action( 'wp_mapply_api', 'get_mapply_api' );
add_action( 'wp_google_gapi', 'get_google_api' );
add_action( 'wp_set_google_gapi', 'save_google_api' );
add_action( 'wp_set_mapply_gapi', 'save_mapply_api' );

// Process the apps
add_action( 'admin_post_mapply_api_keys', 'process_mapply_keys' );

// Activiation Hook
register_activation_hook( __FILE__, 'mapply_install' );

// Install functions
define( 'MAPPLY_DB_VERSION', '1.0' );

// Create the table to hold the API keys
function mapply_install () {
   global $wpdb;

   $installed_ver = get_option( "mapply_db_version" );
   $table_name = get_table_name();

  if( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name || $installed_ver != MAPPLY_DB_VERSION ) {

    $sql = 'CREATE TABLE ' .$table_name. ' (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      mapply_api VARCHAR(255) DEFAULT "" NOT NULL,
      google_api VARCHAR(255) DEFAULT "" NOT NULL,
      mapply_link VARCHAR(255) DEFAULT "",
      display_refferal INT(1) DEFAULT "0" NOT NULL,
      UNIQUE KEY id (id)
    );';

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
    update_option( "mapply_db_version", MAPPLY_DB_VERSION );
    create_first_row();
  }
}

// Get the table prefix and return the name
function get_table_name(){
  global $wpdb;
  return $wpdb->prefix . "mapply";
}

// End of Install functions

// Uninstall
function mapply_uninstall() {
  global $wpdb;
  $table_name = get_table_name();
  $wpdb->query( "DROP TABLE IF EXISTS $table_name" );
}
register_uninstall_hook( __FILE__, 'mapply_uninstall' );
// End of Uninstall

// The function that actually handles replacing the short code
function mapply_handler($incomingfrompost) {

  $api = get_mapply_api();
  $gapi = get_google_api();
  $script_text = "";

  if ($api == "" || $gapi == ""){
    $script_text = "<p>You need to save your Mapply API key and Google API key in the settings page.";
  } else {
    $script_text = build_script_text();
  }

  $incomingfrompost=shortcode_atts(array(
    "headingstart" => $script_text
  ), $incomingfrompost);

  $demolph_output = script_output($incomingfrompost);
  return $demolph_output;
}

function build_script_text(){
  $api = get_mapply_api();
  $gapi = get_google_api();
  $mapply_link = get_mapply_refferal_url();
  $display_ref = get_display_ref();

  $script = '<script id="locator" type="text/javascript" src="//app.mapply.net/front-end/js/locator.js" data-api-key="';
  $script .= $api;
  $script .= '" data-path="//app.mapply.net/front-end/" data-maps-api-key="';
  $script .= $gapi;
  $script .= '" ></script>';

  // If the user has selected display ref link, add it.
  if ($display_ref == "1"){
    $script .= $mapply_link;
  }

  return $script;
}

// build the script to replace the short code
function script_output($incomingfromhandler) {
  $demolp_output = wp_specialchars_decode($incomingfromhandler["headingstart"]);
  $demolp_output .= wp_specialchars_decode($incomingfromhandler["liststart"]);

  for ($demolp_count = 1; $demolp_count <= $incomingfromhandler["categorylist"]; $demolp_count++) {
    $demolp_output .= wp_specialchars_decode($incomingfromhandler["itemstart"]);
    $demolp_output .= $demolp_count;
    $demolp_output .= " of ";
    $demolp_output .= wp_specialchars($incomingfromhandler["categorylist"]);
    $demolp_output .= wp_specialchars_decode($incomingfromhandler["itemend"]);
  }

  $demolp_output .= wp_specialchars_decode($incomingfromhandler["listend"]);
  $demolp_output .= wp_specialchars_decode($incomingfromhandler["headingend"]);

  return $demolp_output;
}

// Create the admin menu
function mapply_create_menu() {
  //create new top-level menu
  add_menu_page('Mapply Settings', 'Mapply', 'administrator', __FILE__, 'mapply_settings_page',plugins_url('/images/icon.png', __FILE__));
}

// Require the important files
require("includes/functions.php");
require("includes/admin_page.php");
