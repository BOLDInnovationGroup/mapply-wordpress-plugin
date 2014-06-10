<?php

/*
Plugin Name: Mapply
Plugin URI: https://github.com/BOLDInnovationGroup/Mapply-WordPress-Plugin
Description: Dislay a map of your stores on your WordPress site!
Version: 1.0
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
global $mapply_db_version;
$mapply_db_version = "1.0";

// Create the table to hold the API keys
function mapply_install () {
   global $wpdb;

   $installed_ver = get_option( "mapply_db_version" );
   $table_name = get_table_name();

  if( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name || $installed_ver != $mapply_db_version ) {

    $sql = 'CREATE TABLE ' .$table_name. ' (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      mapply_api VARCHAR(255) DEFAULT "" NOT NULL,
      google_api VARCHAR(255) DEFAULT "" NOT NULL,
      mapply_link VARCHAR(255) DEFAULT "",
      UNIQUE KEY id (id)
    );';

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
    update_option( "mapply_db_version", $mapply_db_version );
    create_first_row();
  }
}

// Get the table prefix and return the name
function get_table_name(){
  global $wpdb;
  return $wpdb->prefix . "mapply";
}

// End of Install functions
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

  $script = '<script id="locator" type="text/javascript" src="//app.mapply.net/front-end/js/locator.js" data-api-key="store_locator.';
  $script .= $api;
  $script .= '" data-path="//app.mapply.net/front-end/" data-maps-api-key="';
  $script .= $gapi;
  $script .= '" ></script>';
  $script .= $mapply_link;

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

// Create the row to store the keys
function create_first_row(){
  global $wpdb;
  $table_name = get_table_name();
  $wpdb->insert( $table_name, array('mapply_api' => '', 'google_api' => ''), array());
}

// Save the mapply API key
function save_mapply_api($api){
  global $wpdb;

  $table_id = 1;
  $table_name = get_table_name();
  $wpdb->query($wpdb->prepare("UPDATE ".$table_name." SET mapply_api='$api' WHERE id = %d", $table_id));
}

// Save the refferal link to mapply
function save_mapply_link($link){
  global $wpdb;

  $table_id = 1;
  $table_name = get_table_name();
  $wpdb->query($wpdb->prepare("UPDATE ".$table_name." SET mapply_link='$link' WHERE id = %d", $table_id));
}

// Save the Google API key
function save_google_api($gapi){
  global $wpdb;

  $table_id = 1;
  $table_name = get_table_name();
  $wpdb->query($wpdb->prepare("UPDATE ".$table_name." SET google_api='$gapi' WHERE id = %d", $table_id));
}

// Get the mapply api from the db
function get_mapply_api(){
  global $wpdb;

  $table_id = 1;
  $table_name = get_table_name();
  $api = $wpdb->get_row( $wpdb->prepare( "SELECT mapply_api FROM " .$table_name. " WHERE ID = %d", $table_id));
  return $api->mapply_api;
}

// Get the google API from the db
function get_google_api(){
  global $wpdb;

  $table_id = 1;
  $table_name = get_table_name();
  $gapi = $wpdb->get_row( $wpdb->prepare( "SELECT google_api FROM " .$table_name. " WHERE ID = %d", $table_id));
  return $gapi->google_api;
}

// Get the refferal link from the database
function get_mapply_refferal_url(){
  global $wpdb;

  $table_id = 1;
  $table_name = get_table_name();
  $href = $wpdb->get_row( $wpdb->prepare( "SELECT mapply_link FROM " .$table_name. " WHERE ID = %d", $table_id));
  return $href->mapply_link;
}

// Process the form data
function process_mapply_keys(){
  if ($_POST){

    // Check for the google api key
    if (isset($_POST['google_api_key'])){
      save_google_api(sanitize_text_field($_POST['google_api_key']));
    }

    // Check for the apply api key
    if (isset($_POST['mapply_api_key'])){
      save_mapply_api(sanitize_text_field($_POST['mapply_api_key']));
    }

    // Check if the mapply link was posted
    if (isset($_POST['mapply_link'])){
      save_mapply_link($_POST['mapply_link']);
    }

    // redirect
    wp_redirect(  admin_url( 'admin.php?page=mapply.php_/mapply.php' ) );
    exit;
  }
}



function mapply_create_menu() {

  //create new top-level menu
  add_menu_page('Mapply Settings', 'Mapply', 'administrator', __FILE__, 'mapply_settings_page',plugins_url('/images/icon.png', __FILE__));

  //call register settings function
  add_action( 'admin_init', 'register_mysettings' );

}

function register_mysettings() {
  //register our settings
  register_setting( 'mapply-settings-group', 'mapply_api_key' );
  register_setting( 'mapply-settings-group', 'some_other_option' );

}

// Build the settings page
function mapply_settings_page() {
  $default_link = "<a href='http://mapply.net'>Mapply by Mapply!</a>";

  $image = WP_PLUGIN_URL . '/mapply/images/logo.png';
  $styles = WP_PLUGIN_URL . '/mapply/css/mapply_styles.css';

  $api = get_mapply_api();
  $gapi = get_google_api();

?>

<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/0.16.1/css/semantic.css"/>
<link rel="stylesheet" type="text/css" href="<?php echo $styles ?>"/>

<div class="navbar">
    <a class="logo" href="http://mapply.net" target="_blank"><img src="https://app.mapply.net/assets/imgs/logo.png" width="150"></a>
</div>

<div class="wrap ui segment purple">

<img src="<?php echo $image ?>" width="150">
<hr>
<div class="instructions">
  <p><b>Step 1</b> - First we'll need to have a Mapply account. If you don't have one already, you can sign up for a <a href="http://www.mapply.net">free 30 day trial here</a>! :-)</p>
  <p><b>Step 2</b> - Once you're signed up and inside your Mapply account, navigate to the <a href="#">API setup page</a> to grap your Mapply and Google Map API keys to populate the fields below.</p>
  <p><b>Step 3</b> - Once you have all of your <a href="http://www.app.mapply.net">stores setup</a> in your Mapply account, you can insert your map on any page by using the <b>[mapply]</b> shortcode.</p>
</div>

<form method="post" action="admin-post.php">

    <?php settings_fields( 'baw-settings-group' ); ?>
    <?php do_settings_sections( 'baw-settings-group' ); ?>

    <input type="hidden" name="action" value="mapply_api_keys" />
    <input id="mapply_link_box" style="display:none" type="text" name="mapply_link" value="<?php echo $default_link ?>" />
    <table class="form-table">
        <tr valign="top">
        <th scope="row">Mapply API key</th>
        <td><input id="mapply_api_box" type="text" name="mapply_api_key" class="ui input" value="<?php echo $api ?>" /></td>
        </tr>



        <tr valign="top">

        <th scope="row">Google API key</th>

        <td><input type="text" name="google_api_key" class="ui input" value="<?php echo $gapi ?>" /></td>

        </tr>

    </table>

    <?php submit_button(); ?>

</form>
</div>

<?php } ?>
