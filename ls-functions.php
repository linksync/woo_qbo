<?php if ( ! defined( 'ABSPATH' ) ) exit('Access is Denied');

/**
 * Plugins Global functions file
 * Apps functions.php file will be included here
 */

/**
 * QBO functions.php
 */
include LS_INC_DIR.'apps/qbo/functions/functions.php';

/**
 * A wrapper function that will wrap print_r into pre tag
 * Useful in debuging purposes.
 * @param array or object $data
 */
function ls_print_r($data){
	echo '<pre>';
	print_r($data);
	echo '</pre>';
}
/**
 * A wrapper function that will wrap var_dump into pre tag
 * Useful in debuging purposes.
 * @param array or object $data
 */
function ls_var_dump($data){
	echo '<pre>';
	var_dump($data);
	echo '</pre>';
}


/**
 * Show Image help link
 */
function help_link($attribute){
	/**
	 * Check if href key has been added if not set to default link
	 */
	$href = isset($attribute['href'])? $attribute['href']: 'https://www.linksync.com/help/woocommerce';

	$src = '../wp-content/plugins/linksync/assets/images/linksync/help.png';
	echo '	<a style="color: transparent !important" target="_blank" href="', $href ,'">
				<img title="',$attribute['title'],'"
					 src="', $src ,'"
					 height="16" width="16">
			</a>';
}

