<?php
/*
Plugin Name: Feedback Side Tab
Plugin URI: http://www.grabimo.com
Description: A feedback tab on your web. Enable your customers to provide feedbacks in video, audio, photo. You approve and publish video to YouTube with 1-click. photo, and text formats.
Version: 1.4.1
Author: Grabimo
Author URI: http://www.grabimo.com
License: GPLv2 or later
*/

/*  Copyright 2014 Grabimo  (email: admin@grabimo.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
	
	The plugin reuses the simpe_side_tab wordpress plugin devloped by Scot Rumery.
*/

// Hook will fire upon activation - we are using it to set default option values
register_activation_hook( __FILE__, 'multimedia_feedback_tab_activate_plugin' );

// Add options and populate default values on first load
function multimedia_feedback_tab_activate_plugin() {
	$multimedia_feedback_tab_plugin_option_array = get_option('multimedia_feedback_tab_plugin_options');
	if ($multimedia_feedback_tab_plugin_option_array && !empty($multimedia_feedback_tab_plugin_option_array)) {
		// already exists the option
		if (!array_key_exists('show_title', $multimedia_feedback_tab_plugin_option_array)) {
			// but not show_title		
			$multimedia_feedback_tab_plugin_option_array[ 'show_title' ] = '1';
		} 
		
		if (!array_key_exists('tab_align', $multimedia_feedback_tab_plugin_option_array)) {
			// but not show_title
			$multimedia_feedback_tab_plugin_option_array[ 'tab_align' ] = 'right';
		}
		
		if (!array_key_exists('tab_offset', $multimedia_feedback_tab_plugin_option_array)) {
			// but not show_title
			$multimedia_feedback_tab_plugin_option_array[ 'tab_offset' ] = '50%';
		} else {
			$oldVar = $multimedia_feedback_tab_plugin_option_array[ 'tab_offset' ];
			if (is_numeric($oldVar)) {
				$multimedia_feedback_tab_plugin_option_array[ 'tab_offset' ] = $oldVar . '%';
			}
		}
		
		// udpate the option
		update_option( 'multimedia_feedback_tab_plugin_options', $multimedia_feedback_tab_plugin_option_array );
	} else {
		// don't exist or empty, populate plugin options array
		$multimedia_feedback_tab_plugin_option_array = array(
			'business_alias'   => 'example',
			'text_for_tab'     => 'Feedback',
			'font_family'      => 'Tahoma, sans-serif',
			'font_weight_bold' => '1',
			'text_shadow'      => '0',
			'text_color'       => '#FFFFFF',
			'tab_color'        => '#A0244E',
			'hover_color'      => '#A4A4A4',
			'tab_align'		   => 'right',
			'tab_offset'       => '50%',
			'corner_radius'    => '5',
			'show_title'       => '1'
		);
		
		// fill in the empty option or not existing
		update_option( 'multimedia_feedback_tab_plugin_options', $multimedia_feedback_tab_plugin_option_array );
	}
}

// --- for admin hooks only -----------------------------------------
// Fire off hooks depending on if the admin settings page is used or the public website
if (is_admin()){ // admin actions and filters
	// Hook for adding admin menu
	add_action('admin_menu', 'multimedia_feedback_tab_admin_menu');

	// Hook for registering plugin option settings
	add_action('admin_init', 'multimedia_feedback_tab_settings_api_init');
	
	// Hook to fire farbtastic includes for using built in WordPress color picker functionality
	add_action('admin_enqueue_scripts', 'multimedia_feedback_tab_farbtastic_script');

	// Display the 'Settings' link in the plugin row on the installed plugins list page
	add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'multimedia_feedback_tab_admin_plugin_actions', -10);
} else { // non-admin enqueues, actions, and filters
	// hook to get option values and dynamically render css to support the tab classes
	add_action( 'wp_head', 'multimedia_feedback_tab_custom_css_hook' );

	// hook to get option values and write the div for the tab to display
	add_action( 'wp_footer', 'multimedia_feedback_tab_body_tag_html' );
}

// action function to add a new submenu under Settings
function multimedia_feedback_tab_admin_menu() {
	// Add a new submenu under Settings
	add_options_page('Feedback Side Tab', '<img style="position:relative;top:4px" src="//www.grabimo.com/download/grabimo16x16.png"/>&nbsp;Feedback Tab', 'manage_options', 'multimedia_feedback_side_tab', 'multimedia_feedback_tab_options_page');
}

// Use Settings API to whitelist options
function multimedia_feedback_tab_settings_api_init() {
	register_setting('multimedia_feedback_tab_option_group', 'multimedia_feedback_tab_plugin_options');
}

// Build array of links for rendering in installed plugins list
function multimedia_feedback_tab_admin_plugin_actions($links) {
	$settings_link = '<img style="position:relative;top:4px" src="//www.grabimo.com/download/grabimo16x16.png"/>&nbsp;<a href="'. get_admin_url(null, 'options-general.php?page=multimedia_feedback_side_tab') .'">Settings</a>';
	array_unshift($links, $settings_link);
	
	return $links;
}

// Hook to fire farbtastic includes for using built in WordPress color picker functionality
function multimedia_feedback_tab_farbtastic_script($hook) {
	// only enqueue farbtastic on the plugin settings page
	if( $hook == 'settings_page_multimedia_feedback_side_tab' ) {
		// load the style and script for farbtastic
		wp_enqueue_style('farbtastic');
		wp_enqueue_script('farbtastic');
	} 
}

// --- add javascrit and CSS file on webpage head ------------
function multimedia_feedback_tab_css_js_files() {
	// add Javscript
	wp_enqueue_script( 'multimedia-feedback-js-file', '//www.grabimo.com/download/mf.js' );	
	
	// CSS file
	wp_enqueue_style( 'multimedia-feedback-css-file', '//www.grabimo.com/download/mf.css' );
}
add_action( 'wp_enqueue_scripts', 'multimedia_feedback_tab_css_js_files' );

// action function to get option values and write the div for tab to display
function multimedia_feedback_tab_body_tag_html() {
	// get plugin option array and store in a variable
	$multimedia_feedback_tab_plugin_option_array = get_option('multimedia_feedback_tab_plugin_options');

	// fetch individual values from the plugin option variable array
	$multimedia_feedback_tab_text_for_tab = $multimedia_feedback_tab_plugin_option_array['text_for_tab'];
	$multimedia_feedback_tab_align = $multimedia_feedback_tab_plugin_option_array['tab_align'];
	$multimedia_feedback_tab_business_alias = sanitize_text_field($multimedia_feedback_tab_plugin_option_array['business_alias']);	
	$multimedia_feedback_tab_font_family = $multimedia_feedback_tab_plugin_option_array[ 'font_family' ];
	$multimedia_feedback_tab_show_title	= $multimedia_feedback_tab_plugin_option_array[ 'show_title' ];

	// set side of page for tab
	$multimedia_feedback_tab_align_location = 'multimedia_feedback_tab_' . $multimedia_feedback_tab_align;
	
	//Write HTML to render tab
	$font = str_replace("'","\"", $multimedia_feedback_tab_font_family);
	$font = json_encode($font);
	if(preg_match('/(?i)msie [7-8]/',$_SERVER['HTTP_USER_AGENT'])) {
	    // if IE 7 or 8, 
		echo '<a onclick=\'grab_multimedia_feedback.startFlow("' . $multimedia_feedback_tab_business_alias . '",' . $font . ',' . $multimedia_feedback_tab_show_title . ')\'><div id="multimedia_feedback_tab" class="less-ie-9 ' . $multimedia_feedback_tab_align_location . '">' . esc_html( $multimedia_feedback_tab_text_for_tab ) . '</div></a>';
	} else {
	    // if HTML 5 supported
	    echo '<a onclick=\'grab_multimedia_feedback.startFlow("' . $multimedia_feedback_tab_business_alias . '",' . $font .  ',' . $multimedia_feedback_tab_show_title . ')\' id="multimedia_feedback_tab" class="' . $multimedia_feedback_tab_align_location . '">' . esc_html( $multimedia_feedback_tab_text_for_tab ) . '</a>';
	}
}

// --- for admin page setting html UI ----------------------------------
// Display and fill the form fields for the plugin admin page
function multimedia_feedback_tab_options_page() {
?>
	<div class="wrap">
	<?php screen_icon( 'plugins' ); ?>
	<form method="post" action="options.php">
<?php
	settings_fields( 'multimedia_feedback_tab_option_group' );
	do_settings_sections( 'multimedia_feedback_side_tab' );

	// get plugin option array and store in a variable
	$multimedia_feedback_tab_plugin_option_array	= get_option( 'multimedia_feedback_tab_plugin_options' );

	// fetch individual values from the plugin option variable array
	$multimedia_feedback_tab_business_alias 		= $multimedia_feedback_tab_plugin_option_array[ 'business_alias' ];		
	$multimedia_feedback_tab_text_for_tab			= $multimedia_feedback_tab_plugin_option_array[ 'text_for_tab' ];
	$multimedia_feedback_tab_font_family			= $multimedia_feedback_tab_plugin_option_array[ 'font_family' ];
	$multimedia_feedback_tab_font_weight_bold		= $multimedia_feedback_tab_plugin_option_array[ 'font_weight_bold' ];
	$multimedia_feedback_tab_text_shadow			= $multimedia_feedback_tab_plugin_option_array[ 'text_shadow' ];
	$multimedia_feedback_tab_text_color				= $multimedia_feedback_tab_plugin_option_array[ 'text_color' ];
	$multimedia_feedback_tab_tab_color				= $multimedia_feedback_tab_plugin_option_array[ 'tab_color' ];
	$multimedia_feedback_tab_hover_color			= $multimedia_feedback_tab_plugin_option_array[ 'hover_color' ];
	$multimedia_feedback_tab_corner_radius			= $multimedia_feedback_tab_plugin_option_array[ 'corner_radius' ]; 
	$multimedia_feedback_tab_align					= $multimedia_feedback_tab_plugin_option_array[ 'tab_align' ];
	$multimedia_feedback_tab_offset					= $multimedia_feedback_tab_plugin_option_array[ 'tab_offset' ];
	$multimedia_feedback_tab_show_title				= $multimedia_feedback_tab_plugin_option_array[ 'show_title' ];		
	
?>
	<script type="text/javascript">
		jQuery(document).ready(function() {
			jQuery('#colorpicker1').hide();
			jQuery('#colorpicker1').farbtastic("#color1");
			jQuery("#color1").click(function(){jQuery('#colorpicker1').slideToggle()});
		});

		jQuery(document).ready(function() {
			jQuery('#colorpicker2').hide();
			jQuery('#colorpicker2').farbtastic("#color2");
			jQuery("#color2").click(function(){jQuery('#colorpicker2').slideToggle()});
		});

		jQuery(document).ready(function() {
			jQuery('#colorpicker3').hide();
			jQuery('#colorpicker3').farbtastic("#color3");
			jQuery("#color3").click(function(){jQuery('#colorpicker3').slideToggle()});
		});
	</script>

	<table class="widefat">
		<tr valign="top">
		<td style="width:250px"><label for="multimedia_feedback_tab_business_alias">Set business alias</label></td>
		<td><input maxlength="30" size="25" type="text" name="multimedia_feedback_tab_plugin_options[business_alias]" value="<?php echo esc_html( $multimedia_feedback_tab_business_alias ); ?>" />
			<p class="description">To create an Alias for your website, sign up at <a href="https://www.grabimo.com">https://www.grabimo.com</a></p></td>
		</tr>
		</table>
	<br/>	
	
	<table class="widefat">	
		<tr valign="top">
		<td style="width:250px"><label for="multimedia_feedback_tab_left_right">Tab Aligning</label></td>
		<td>
			<input name="multimedia_feedback_tab_plugin_options[tab_align]" type="radio" value="left" <?php checked( 'left', $multimedia_feedback_tab_align ); ?> /> Left&nbsp; &nbsp; 
			<input name="multimedia_feedback_tab_plugin_options[tab_align]" type="radio" value="right" <?php checked( 'right', $multimedia_feedback_tab_align ); ?> /> Right&nbsp; &nbsp;
			<input name="multimedia_feedback_tab_plugin_options[tab_align]" type="radio" value="top" <?php checked( 'top', $multimedia_feedback_tab_align ); ?> /> Top&nbsp; &nbsp; 
			<input name="multimedia_feedback_tab_plugin_options[tab_align]" type="radio" value="bottom" <?php checked( 'bottom', $multimedia_feedback_tab_align ); ?> /> Bottom
			</td>
		</tr>

		<tr valign="top">
		<td><label for="multimedia_feedback_tab_pixels_from_top">Offset to bottom or right</label></td>
		<td><input maxlength="10" size="10" type="text" placeholder="0-100%" name="multimedia_feedback_tab_plugin_options[tab_offset]" value="<?php echo sanitize_text_field( $multimedia_feedback_tab_offset ); ?>" />
			<p class="description">The offset in percentage measures from the right side when your tab aligns the top or bottom side of the web browser. Otherwise, it measures from the bottom side.</td>
		</tr>		

		<tr valign="top">
		<td><label for="multimedia_feedback_tab_corner_radius">Corner radius (px)</label></td>
		<td><input maxlength="4" size="4" type="text" name="multimedia_feedback_tab_plugin_options[corner_radius]" value="<?php echo sanitize_text_field( $multimedia_feedback_tab_corner_radius ); ?>" /></td>
		</tr>	
		
		<tr valign="top">
		<td><label for="multimedia_feedback_tab_text_shadow">Drop shadow on hover</label></td>
		<input name="multimedia_feedback_tab_plugin_options[text_shadow]" type="hidden" value="0" />
		<td><input name="multimedia_feedback_tab_plugin_options[text_shadow]" type="checkbox" value="1" <?php checked( '1', $multimedia_feedback_tab_text_shadow ); ?> /></td>
		</tr>		
	</table>
	<br/>	
		
	<table class="widefat">		
		<tr valign="top">
		<td style="width:250px"><label for="multimedia_feedback_tab_text_for_tab">Text on tab</label></td>
		<td><input maxlength="30" size="25" type="text" name="multimedia_feedback_tab_plugin_options[text_for_tab]" value="<?php echo esc_html( $multimedia_feedback_tab_text_for_tab ); ?>" /></td>
		</tr>

		<tr valign="top">
		<td><label for="multimedia_feedback_tab_tab_font">Text font family</label></td>
		<td><input maxlength="30" size="25" type="text" name="multimedia_feedback_tab_plugin_options[font_family]" value="<?php echo esc_html( $multimedia_feedback_tab_font_family ); ?>" /></td>
		</tr>

		<tr valign="top">
		<td><label for="multimedia_feedback_tab_font_weight_bold">Text bold weight</label></td>
		<input name="multimedia_feedback_tab_plugin_options[font_weight_bold]" type="hidden" value="0" />
		<td><input name="multimedia_feedback_tab_plugin_options[font_weight_bold]" type="checkbox" value="1" <?php checked( '1', $multimedia_feedback_tab_font_weight_bold ); ?> /></td>
		</tr>
		
		<tr valign="top">
		<td><label for="multimedia_feedback_tab_show_title">Show title</label></td>
		<input name="multimedia_feedback_tab_plugin_options[show_title]" type="hidden" value="0" />
		<td><input name="multimedia_feedback_tab_plugin_options[show_title]" type="checkbox" value="1" <?php checked( '1', $multimedia_feedback_tab_show_title ); ?> /></td>
		</tr>					
	</table>
	<br/>

	<table class="widefat">
		<!--caption>Click on each field to display the color picker. Click again to close it.</caption-->
		<tr valign="top">
			<td style="width:250px"><label for="multimedia_feedback_tab_text_color">Text color</label></td>
			<td style="width:100px"><input type="text" maxlength="7" size="6" value="<?php echo esc_attr( $multimedia_feedback_tab_text_color ); ?>" name="multimedia_feedback_tab_plugin_options[text_color]" id="color1" /></td>
			<td><div id="colorpicker1"></div></td>
		</tr>

		<tr valign="top">
			<td><label for="multimedia_feedback_tab_tab_color">Tab color</label></td>
			<td><input type="text" maxlength="7" size="6" value="<?php echo esc_attr( $multimedia_feedback_tab_tab_color ); ?>" name="multimedia_feedback_tab_plugin_options[tab_color]" id="color2" /></td>
			<td><div id="colorpicker2"></div></td>
		</tr>

		<tr valign="top">
			<td><label for="multimedia_feedback_tab_hover_color">Tab hover color</label></td>
			<td><input type="text" maxlength="7" size="6" value="<?php echo esc_attr( $multimedia_feedback_tab_hover_color ); ?>" name="multimedia_feedback_tab_plugin_options[hover_color]" id="color3" /></td>
			<td><div id="colorpicker3"></div></td>
		</tr>
	</table>

	<p class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" /></p>
	
	</form>
	</div>
<?php }

// This function runs all the css and dynamic css elements for displaying the tab
function multimedia_feedback_tab_custom_css_hook() {
	// get plugin option array and store in a variable
	$multimedia_feedback_tab_plugin_option_array	= get_option( 'multimedia_feedback_tab_plugin_options' );

	// fetch individual values from the plugin option variable array
	$multimedia_feedback_tab_font_family			= $multimedia_feedback_tab_plugin_option_array[ 'font_family' ];
	$multimedia_feedback_tab_font_weight_bold		= $multimedia_feedback_tab_plugin_option_array[ 'font_weight_bold' ];
	$multimedia_feedback_tab_text_shadow			= $multimedia_feedback_tab_plugin_option_array[ 'text_shadow' ];
	$multimedia_feedback_tab_offset					= $multimedia_feedback_tab_plugin_option_array[ 'tab_offset' ];
	$multimedia_feedback_tab_text_color				= $multimedia_feedback_tab_plugin_option_array[ 'text_color' ];
	$multimedia_feedback_tab_tab_color				= $multimedia_feedback_tab_plugin_option_array[ 'tab_color' ];
	$multimedia_feedback_tab_hover_color			= $multimedia_feedback_tab_plugin_option_array[ 'hover_color' ];
	$multimedia_feedback_tab_corner_radius			= $multimedia_feedback_tab_plugin_option_array[ 'corner_radius' ];
	$multimedia_feedback_tab_show_title				= $multimedia_feedback_tab_plugin_option_array[ 'show_title' ];	
?>

<style type='text/css'>
#multimedia_feedback_tab {
	font-family:<?php echo $multimedia_feedback_tab_font_family; ?>;
	background-color:<?php echo $multimedia_feedback_tab_tab_color; ?>;
	color:<?php echo $multimedia_feedback_tab_text_color; ?>;
	border-style:solid;
	border-width:0px;
	text-decoration: none;
	
	position:fixed;
	margin:0;
	padding:6px 13px 8px 13px;
	text-decoration:none;
	text-align:center;
	font-size:15px;
	<?php
	if ( $multimedia_feedback_tab_font_weight_bold =='1' ) :
	  echo 'font-weight:bold;' . "\n";
	else :
	  echo 'font-weight:normal;' . "\n";
	endif;
	?>
	border-style:solid;
	display:block;
	z-index:30000;
	
}

#multimedia_feedback_tab:hover {
	background-color: <?php echo $multimedia_feedback_tab_hover_color; ?>;
	<?php
	if ( $multimedia_feedback_tab_text_shadow =='1' ) {
	  echo '	-moz-box-shadow:    -3px 3px 5px 2px #ccc;' . "\n";
	  echo '	-webkit-box-shadow: -3px 3px 5px 2px #ccc;' . "\n";
	  echo '	box-shadow:         -3px 3px 5px 2px #ccc;' . "\n";
	}
?>
}

.multimedia_feedback_tab_left {
	left:-1px;
	bottom: <?php echo $multimedia_feedback_tab_offset; ?>;
	cursor: pointer;
	-webkit-transform-origin:0 0;
	-moz-transform-origin:0 0;
	-o-transform-origin:0 0;
	-ms-transform-origin:0 0;
	-webkit-transform:rotate(270deg);
	-moz-transform:rotate(270deg);
	-ms-transform:rotate(270deg);
	-o-transform:rotate(270deg);
	transform:rotate(270deg);
}

.multimedia_feedback_tab_right {
	right:-1px;
	bottom: <?php echo $multimedia_feedback_tab_offset; ?>;
	cursor: pointer;
	-webkit-transform-origin:100% 100%;
	-moz-transform-origin:100% 100%;
	-o-transform-origin:100% 100%;
	-ms-transform-origin:100% 100%;
	-webkit-transform:rotate(-90deg);
	-moz-transform:rotate(-90deg);
	-ms-transform:rotate(-90deg);
	-o-transform:rotate(-90deg);
	transform:rotate(-90deg);
}

.multimedia_feedback_tab_bottom {
	bottom:-1px;
	right: <?php echo $multimedia_feedback_tab_offset; ?>;
	cursor: pointer;
}

.multimedia_feedback_tab_top {
	top:-1px;
	right: <?php echo $multimedia_feedback_tab_offset; ?>;
	cursor: pointer;
}

.multimedia_feedback_tab_right.less-ie-9 {
	filter: progid:DXImageTransform.Microsoft.BasicImage(rotation=1);
}

.multimedia_feedback_tab_left.less-ie-9 {
	filter: progid:DXImageTransform.Microsoft.BasicImage(rotation=3);
}

.multimedia_feedback_tab_left, .multimedia_feedback_tab_top {
	-moz-border-radius-bottomright: <?php echo $multimedia_feedback_tab_corner_radius; ?>px;
	border-bottom-right-radius: <?php echo $multimedia_feedback_tab_corner_radius; ?>px;
	-moz-border-radius-bottomleft: <?php echo $multimedia_feedback_tab_corner_radius; ?>px;
	border-bottom-left-radius:<?php echo $multimedia_feedback_tab_corner_radius; ?>px;
}

.multimedia_feedback_tab_right, .multimedia_feedback_tab_bottom {
	-moz-border-radius-topright: <?php echo $multimedia_feedback_tab_corner_radius; ?>px;
	border-top-right-radius: <?php echo $multimedia_feedback_tab_corner_radius; ?>px;
	-moz-border-radius-topleft: <?php echo $multimedia_feedback_tab_corner_radius; ?>px;
	border-top-left-radius:<?php echo $multimedia_feedback_tab_corner_radius; ?>px;	
}

</style>

<?php
}
