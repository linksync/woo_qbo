<?php if ( ! defined( 'ABSPATH' ) ) exit('Access is Denied');

/*
	Plugin directories and Url
	Set Globals Linksync Constant
*/
define('LS_PLUGIN_DIR', plugin_dir_path(__FILE__ ));
define('LS_INC_DIR', LS_PLUGIN_DIR.'includes/');

define('LS_PLUGIN_URL',plugin_dir_url( __FILE__ ));
define('LS_ASSETS_URL',LS_PLUGIN_URL.'assets/');
define('LS_QBO_ASSETS_URL',LS_PLUGIN_URL.'assets/');