<?php
/**
* Plugin Name: Cleverwise FatSecret Basic
* Description: Easily display the FatSecret Platform JavaScript API integration system on your site.
* Version: 1.0
* Author: Jeremy O'Connell
* Author URI: http://www.cyberws.com/cleverwise-plugins/
* License: GPL2 .:. http://opensource.org/licenses/GPL-2.0
*/

////////////////////////////////////////////////////////////////////////////
//	Load Cleverwise Framework Library
////////////////////////////////////////////////////////////////////////////
include_once('cwfa.php');
$cwfa_fsb=new cwfa_fsb;

////////////////////////////////////////////////////////////////////////////
//	Wordpress database option
////////////////////////////////////////////////////////////////////////////
Global $wpdb,$fsb_wp_option_version_txt,$fsb_wp_option,$fsb_wp_option_version_num;

$fsb_wp_option_version_num='1.0';
$fsb_wp_option='fatsecret_basic';
$fsb_wp_option_version_txt=$fsb_wp_option.'_version';

////////////////////////////////////////////////////////////////////////////
//	If admin panel is showing and user can manage options load menu option
////////////////////////////////////////////////////////////////////////////
if (is_admin()) {
	//	Hook admin code
	include_once("fsba.php");

	//	Activation code
	register_activation_hook( __FILE__, 'cw_fatsecret_basic_activate');

	//	Check installed version and if mismatch upgrade
	Global $wpdb;
	$fsb_wp_option_db_version=get_option($fsb_wp_option_version_txt);
	if ($fsb_wp_option_db_version < $fsb_wp_option_version_num) {
		update_option($fsb_wp_option_version_txt,$fsb_wp_option_version_num);
	}
}

////////////////////////////////////////////////////////////////////////////
//	Register shortcut to display visitor side
////////////////////////////////////////////////////////////////////////////
add_shortcode('cw_fatsecret_basic', 'cw_fatsecret_basic_vside');

////////////////////////////////////////////////////////////////////////////
//	Check to see what to do
////////////////////////////////////////////////////////////////////////////
if (isset($_REQUEST['cw_action'])) {
	$cw_action=$_REQUEST['cw_action'];
}

////////////////////////////////////////////////////////////////////////////
//	Visitor Display
////////////////////////////////////////////////////////////////////////////
function cw_fatsecret_basic_vside() {
Global $fsb_wp_option;

	////////////////////////////////////////////////////////////////////////////
	//	Load options for plugin
	////////////////////////////////////////////////////////////////////////////
	$fsb_wp_option_array=get_option($fsb_wp_option);
	$fsb_wp_option_array=unserialize($fsb_wp_option_array);
	$settings_api_key=$fsb_wp_option_array['settings_api_key'];
	$settings_theme=$fsb_wp_option_array['settings_theme'];
	$settings_nav_options=unserialize($fsb_wp_option_array['settings_nav_options']);

	$cw_fatsecret_basic_js='';
	$cw_fatsecret_basic_page='';
	
	//	Build FatSecret URI
	$cw_fatsecret_basic_url='http://platform.fatsecret.com/js?key='.$settings_api_key.'&theme='.$settings_theme;
	if (in_array('none',$settings_nav_options)) {
		$cw_fatsecret_basic_url .='&auto_nav=false';
	} else {
		$cw_fatsecret_basic_js='fatsecret.variables.navOptions=';
		$i='0';
		foreach ($settings_nav_options as $setting_nav_option) {
			if ($i > '0') {
				$cw_fatsecret_basic_js .='|';
			}
			$cw_fatsecret_basic_js .='fatsecret.navFeatures.'.$setting_nav_option;
			$i++;
		}
		$cw_fatsecret_basic_js .=';'."\n";
	}
	
	//	Build Javascript
$cw_fatsecret_basic_js .=<<<EOM
fatsecret.setContainer("cleverwise_fatsecret");
fatsecret.setCanvas("home");
EOM;
	
	//	If key settings are missing display error message
	if (!$settings_api_key or !$settings_theme or !$settings_nav_options) {
		$cw_fatsecret_basic_page='Please setup the Cleverwise FatSecret Basic plugin settings!  This message will then disappear.  Thank you.';
	//	Load FatSecret screen
	} else {
$cw_fatsecret_basic_page .=<<<EOM
<script src="$cw_fatsecret_basic_url"></script>
<div id="cleverwise_fatsecret" class="fatsecret_container"></div>
<script>
$cw_fatsecret_basic_js
</script>
EOM;
	}
	
	//	Display to browser/site
	return($cw_fatsecret_basic_page);
}

