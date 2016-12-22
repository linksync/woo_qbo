<?php if ( ! defined( 'ABSPATH' ) ) exit('Access is Denied');

    require_once LS_PLUGIN_DIR.'/classes/Class.linksync.php';
    require_once LS_INC_DIR. 'view/ls-plugins-tab-menu.php'; # Handle Tabs
    require_once LS_INC_DIR. 'apps/vend/ls-vend-api-key.php';
    require_once LS_INC_DIR. 'apps/vend/ls-vend-log.php';
?>

<div class="wrap" id="ls-wrapper">
    <div id="response"></div>

    <?php
        global $wpdb;
        $linksync = new linksync();
        //Send log feature
        $testMode = get_option('linksync_test');
        $LAIDKey = linksync::get_current_laid();
        $apicall = new linksync_class($LAIDKey, $testMode);

		//Check if adding,update api key button was not set then check the current api key
		if ( !isset($_POST['add_apiKey']) && !isset($_POST['apikey_update']) ){
			LS_ApiController::check_api_key();
		}

		echo "<div id='ls-main-views-cont'>";
		linksync::load_views();
		echo "</div>";
    ?>

</div> 