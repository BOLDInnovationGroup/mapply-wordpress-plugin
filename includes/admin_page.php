<?php

// Build the settings page
function mapply_settings_page() {
  $default_link = "<a href='http://mapply.net'>Mapply by Mapply!</a>";

  $image    = WP_PLUGIN_URL . '/mapply/images/logo.png';
  $image2   = WP_PLUGIN_URL . '/mapply/images/logo2.png';
  $nav_bg   = WP_PLUGIN_URL . '/mapply/images/banner-bg.jpg';
  $styles   = WP_PLUGIN_URL . '/mapply/css/mapply_styles.css';
  $semantic = WP_PLUGIN_URL . '/mapply/css/semantic.css';

  $api         = get_mapply_api();
  $gapi        = get_google_api();
  $display_ref = get_display_ref();

?>

<link rel="stylesheet" type="text/css" href="<?php echo $semantic ?>"/>
<link rel="stylesheet" type="text/css" href="<?php echo $styles ?>"/>

<div class="navbar" style="background: url('<?php echo $nav_bg ?>') center center #757994 no-repeat;">
    <a class="logo" href="http://mapply.net" target="_blank"><img src="<?php echo $image2 ?>" width="150"></a>
</div>

<div class="wrap ui segment purple">

<img src="<?php echo $image ?>" width="150">
<hr>
<div class="instructions">
  <p><b>Step 1</b> - First we'll need to have a Mapply account. If you don't have one already, you can sign up for a <a href="http://mapply.net">free 30 day trial here</a>! :-)</p>
  <p><b>Step 2</b> - Once you're signed up and inside your Mapply account, navigate to the <a href="https://app.mapply.net/settings.php">API setup page</a> to grab your Mapply and Google Map API keys to populate the fields below.</p>
  <p><b>Step 3</b> - Once you have all of your <a href="https://app.mapply.net/admin.php">stores setup</a> in your Mapply account, you can insert your map on any page by using the <b>[mapply]</b> shortcode.</p>
</div>

<?php if ( isset( $_GET[ 'settings-saved' ] ) ): ?>
<div class="updated"><p>Settings updated successfully.</p></div>
<?php endif ?>

<form method="post" action="admin-post.php">

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

        <tr valign="top">
          <th scope="row">Display "Mapply by Mapply" refferal link :-)</th>
          <td><input type="checkbox" name="display_ref" <?php if($display_ref == "1"){ echo "checked"; }; ?> value="1"></td>
        </tr>

    </table>

    <?php submit_button(); ?>

</form>
</div>

<?php }
