<?php if (!defined('ABSPATH')) exit('Access is Denied');
$connected_to = get_option('linksync_connectedto');

// Adding API Key by Pop UP into our wp database
if (isset($_POST['add_apiKey']) || isset($_POST['apikey_update'])) {
    global $wpdb;
    if (!empty($_POST['apikey'])) {
        $laid_key = trim($_POST['apikey']);
        $laidCheck = LS_QBO()->laid()->checkApiKey($laid_key);

        if(isset($laidCheck['errorCode'])){
            LSC_Log::add('checkAPI Key', 'fail', $laidCheck['error_message'], '-');

            $class1 = 'updated';
            $class2 = 'error';
            $response = 'Key was rejected. ' . $laidCheck['error_message'];

            if ('Connection to the update URL failed.' == $laidCheck['error_message']) {
                $response = 'Connection to the update URL failed. Please check our <a href="https://help.linksync.com/hc/en-us/articles/115000591510-Connection-to-the-update-URL-failed" target="_blank">FAQ</a> section to find possible solutions.';
            }
        } elseif(isset($_POST['apikey_update'])){

            $response = 'API Key has been updated successfully!';
            $class1 = 'error';
            $class2 = 'updated';
        }else {
            $response = 'API Key has been added successfully !';
            $class1 = 'error';
            $class2 = 'updated';
        }

    } else {
        LSC_Log::add('Manage API Keys', 'fail', 'API Key is empty!!', '-');
        $response = "API Key is Empty!!";
        $class1 = 'updated';
        $class2 = 'error';
    }

    ?>
    <script>

        jQuery('#response').removeClass("<?php echo $class1; ?>").addClass("<?php echo $class2; ?>").html("<?php echo $response; ?>").fadeIn().delay(3000).fadeOut(4000);

    </script>
    <?php
// End - Adding API Key by Pop UP
}
$product_syncing_form = LS_QBO_Product_Form::instance();
$product_syncing_form->accounts_error_message();
$product_syncing_form->require_syncing_error_message();



?>

<div id="tiptip_holder" class="tip_top">
    <div id="tiptip_arrow">
        <div id="tiptip_arrow_inner"></div>
    </div>
    <div id="tiptip_content">The linksync API Key is a unique key that's created when you link two apps via the linksync
        dashboard. You need a valid API Key for this linkysnc extension to work.
    </div>
</div><?php

/*
 * Reset Product and Order Syncing Setting
 */
if (isset($_POST['rest'])) {
    LSC_Log::add('Reset Option', 'success', "Reset Product and Order Syncing Setting", '-');
    $class1 = 'error';
    $class2 = 'updated';
    $response = 'Successfully! Reset Syncing Setting.';

    LS_QBO()->product_option()->reset_options();
    LS_QBO()->order_option()->reset_options();

    ?>
    <script>
        jQuery(document).ready(function ($) {
            $('#response').removeClass("<?php echo $class1; ?>").addClass("<?php echo $class2; ?>").html("<?php echo $response; ?>").fadeIn().delay(3000).fadeOut(4000);
        });
    </script><?php
}


LS_QBO()->view()->display_add_api_key_modal();
LS_QBO()->view()->display_update_api_key_modal();
?>
<div class="wrap">
    <div id="response"></div>
    <?php
    $checked = get_option('linksync_test') == 'on' ? 'checked' : '';
    ?>
    <div id="ls-qbo-api-key-configuration" class="ls-qbo-section">
        <br/>
        <?php LS_QBO_View_Config_Section::api_key_configuration(); ?>
    </div>

    <div id="ls-qbo-sync-now"
         class="ls-qbo-section">
        <?php LS_QBO_View_Config_Section::sync_now(); ?>
    </div>

    <div id="ls-qbo-status" class="ls-qbo-section">
        <?php LS_QBO_View_Config_Section::connection_status(); ?>
    </div>

</div>