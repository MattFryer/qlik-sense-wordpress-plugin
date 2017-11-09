<?php
	/*
	Plugin Name: Qlik Sense
	Plugin URI: https://github.com/yianni-ververis/qlik-sense-wordpress-plugin
	Description: 
		A plugin to connect to Qlik Sense server and get the objects. 
		- Unzip the plugin into your plugins directory
		- Activate from the admin panel
		- Go to "Qlik Sense" settings and add the host, virtual proxy and the app id
		- then add the shortcode into your posts "[sense-object qvid="ZwjJQq" height="400" nointeraction="true"]"
		- YOu can also add the Clear Selections button [sense-object-clear-selections title="Clear Selections"]
	Version: 1.0.5
	Author: yianni.ververis@qlik.com
	License: MIT
	*/

    define( 'QLIK_SENSE_PLUGIN_VERSION', '1.0.5' );
    define( 'QLIK_SENSE_PLUGIN_MINIMUM_WP_VERSION', '4.0' );
    define( 'QLIK_SENSE_PLUGIN_PLUGIN_DIR', plugin_dir_url( __FILE__ ) );

	// Get the CSS and JS from Sense
    add_action( 'wp_enqueue_scripts', 'qlik_sense_enqueued_assets' );
    function qlik_sense_enqueued_assets() {
		wp_enqueue_style( 'qlik-sense-styles', 'https://'.esc_attr( get_option('qs_host') ) . esc_attr( get_option('qs_prefix') ) . 'resources/autogenerated/qlik-styles.css' );
		if( ! wp_script_is( 'qlik-sense-require', 'enqueued' ) ) {
			wp_enqueue_script( 'qlik-sense-require', 'https://' . esc_attr( get_option('qs_host') ) . esc_attr( get_option('qs_prefix') ) . 'resources/assets/external/requirejs/require.js', array(), '1.0.0' );
		}

		// Register the script
		wp_register_script( 'qlik-sense-js', QLIK_SENSE_PLUGIN_PLUGIN_DIR . 'index.js', array('jquery'), '1.0.0' );

		// Localize the script with new data
		$translation_array = array(	
			'qs_host'		=> esc_attr( get_option('qs_host') ),
			'qs_prefix'		=> esc_attr( get_option('qs_prefix') ),
			'qs_id'			=> esc_attr( get_option('qs_id') ),
		);
		wp_localize_script( 'qlik-sense-js', 'vars', $translation_array );

		// Enqueued script with localized data.
		wp_enqueue_script( 'qlik-sense-js' );
	}
	
	add_action('admin_menu', 'qlik_sense_plugin_menu');
	function qlik_sense_plugin_menu() {
		add_menu_page('Qlik Sense Plugin Settings', 'Qlik Sense', 'administrator', 'qlik_sense_plugin_settings', 'qlik_sense_plugin_settings_page', 'dashicons-admin-generic');
	}
	
	// Create the options to be saved in the Database
	add_action( 'admin_init', 'qlik_sense_plugin_settings' );	
	function qlik_sense_plugin_settings() {
		register_setting( 'qlik_sense-plugin-settings-group', 'qs_host' );
		register_setting( 'qlik_sense-plugin-settings-group', 'qs_prefix' );
		register_setting( 'qlik_sense-plugin-settings-group', 'qs_id' );
	}

	// Create the Admin Setting Page
	function qlik_sense_plugin_settings_page() {
?>
		<div class="wrap">
			<h2>Qlik Sense Plugin Settings</h2>
			<form method="post" action="options.php">
				<?php settings_fields( 'qlik_sense-plugin-settings-group' ); ?>
				<?php do_settings_sections( 'qlik_sense-plugin-settings-group' ); ?>
				<table class="form-table">
					<tr valign="top">
						<th scope="row">Host:</th>
						<td><input type="text" name="qs_host" size="50" value="<?php echo esc_attr( get_option('qs_host') ); ?>" /></td>
					</tr>					
					<tr valign="top">
						<th scope="row">Virtual Proxy (Prefix):</th>
						<td><input type="text" name="qs_prefix" size="5" value="<?php echo esc_attr( get_option('qs_prefix') ); ?>" /></td>
					</tr>					
					<tr valign="top">
						<th scope="row">App ID:</th>
						<td><input type="text" name="qs_id" size="50" value="<?php echo esc_attr( get_option('qs_id') ); ?>" /></td>
					</tr>				
					<tr valign="top">
						<th scope="row">&nbsp;</th>
						<td><?php submit_button(); ?></td>
					</tr>
				</table>
				
				<div style="border-top:1px solid #ccc;padding-top:35px;"><a href="https://www.qlik.com/us/"><img src="<?php echo QLIK_SENSE_PLUGIN_PLUGIN_DIR . "/QlikLogo-RGB.png"?>" width="200"></a></div>
			</form>
		</div>
<?php
	}

	// Create the Html Snippet for use inside the posts/pages
	// [sense-object qvid="ZwjJQq" height="400" nointeraction="true"]
	function qlik_sense_object_func( $atts ) {
		return "<div id=\"{$atts['qvid']}\" data-qvid=\"{$atts['qvid']}\" data-nointeraction=\"{$atts['nointeraction']}\" class=\"wp-qs\" style=\"height:{$atts['height']}px\"></div>";
	}
	add_shortcode( 'qlik-sense-object', 'qlik_sense_object_func' );
	
	// [sense-object-clear-selections title="Clear Selections"]
	function qlik_sense_object_clear_selections_func( $atts ) {
		return "<button id=\"qlik-sense-clear-selections\">{$atts['title']}</button>";
	}
	add_shortcode( 'qlik-sense-object-clear-selections', 'qlik_sense_object_clear_selections_func' );
?>