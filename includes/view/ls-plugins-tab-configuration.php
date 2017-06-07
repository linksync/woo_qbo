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


?>
<div id="myModal" class="reveal-modal">
    <form method="POST" name="f1" action="">
        <center><span>Enter the API Key</span></center>
        <hr>
        <br/>

        <center>
            <div>
                <b style="color: #0074a2;">API Key*:</b>
                <a href="https://www.linksync.com/help/woocommerce"
                   style="text-decoration: none"
                   target="_blank"
                   title=' Unsure about how to generate an API Key? Click the icon for a specific guidelines to get you up and running with linksync Vend & WooCommerce.'>
                    <img class="help_tip" src="../wp-content/plugins/linksync/assets/images/linksync/help.png"
                         height="16" width="16">
                </a>
                <input type="text" size="30" name="apikey" value="">
                <input type="submit" value="Save" onclick="return checkEmptyLaidKey()" class="button color-green"
                       name="add_apiKey">
            </div>
        </center>
        <span class="ui-icon ui-icon-close close-reveal-modal"></span>
    </form>
</div>

<div id="modal_update_api" class="reveal-modal">
    <form method="POST" name="f1" action="">
        <center><span>Update API Key</span></center>
        <hr>
        <br>
        <center>
            <div>
                <b style="color: #0074a2;">API Key*:</b>
                <a href="https://www.linksync.com/help/woocommerce"
                   style="text-decoration: none"
                   target="_blank"
                   title=' Unsure about how to generate an API Key? Click the icon for a specific guidelines to get you up and running with linksync Vend & WooCommerce.'>
                    <img class="help_tip" src="../wp-content/plugins/linksync/assets/images/linksync/help.png"
                         height="16" width="16">
                </a>
                <input type="text" size="30" name="apikey"
                       value="<?php echo LS_QBO()->laid()->getCurrentLaid('No Api Key'); ?>">
                <input type="submit" value="Update" class='button color-green' name="apikey_update">
            </div>
        </center>
        <span class="ui-icon ui-icon-close close-reveal-modal"></span>
    </form>
</div>

<div class="wrap">
    <div id="response"></div>
    <?php
    $checked = get_option('linksync_test') == 'on' ? 'checked' : '';
    ?>
    <fieldset>
        <legend>API Key configuration</legend>
        <div>
            <form method='POST'>
                <input type='submit' style="float: right;margin-top: 10px;" class="button button-primary"
                       title=' Use this button to reset Product and Order Syncing Setting.' name='rest'
                       value='Reset Syncing Setting'>
            </form>
        </div>


            <table cellpadding="8">
                <tr>
                    <td><b style='font-size: 14px;'>API Key*:</b></td>
                    <td>
                        <?php

                        $laid = LS_QBO()->laid()->getCurrentLaid('No Api Key');
                        echo '<b>', $laid, '</b>';
                        ?>
                        <a href="https://www.linksync.com/help/woocommerce"
                           style="text-decoration: none !important;">
                            <img title="The linksync API Key is a unique key that's created when you link two apps via the linksync dashboard. You need a valid API Key for this linkysnc extension to work."
                                 style="margin-bottom: -4px;"
                                 src="../wp-content/plugins/linksync/assets/images/linksync/help.png"
                                 height="16" width="16"/>
                        </a>
                    </td>
                    <td>
                        &nbsp;&nbsp;&nbsp;&nbsp;
                        <?php
                        $count_ls_laidkeys = LS_QBO()->laid()->getCurrentLaid();

                        if (empty($count_ls_laidkeys)) { ?>
                            <a href="#" data-reveal-id="myModal" data-animation="fade" class="button button-primary">Add
                                Api Key</a><?php
                        } else {
                            ?>
                            <a href="#" data-reveal-id="modal_update_api" class="button button-primary">Edit Api Key</a>
                            <?php
                        } ?>
                    </td>
                </tr>

            </table>


    </fieldset>
    <?php
    $webhook = get_option('linksync_addedfile');
    if (is_qbo()) {
        $product_option = LS_QBO()->product_option();
        $ps_form = LS_QBO_Product_Form::instance();

        $options = $product_option->get_current_product_syncing_settings();
        $options['pop_up_style'] = 'none';
        $ps_form->set_users_options($options);
        $ps_form->confirm_sync();

        $qbo_sync_type = $product_option->sync_type();
        ?>
        <fieldset style="<?php echo ('disabled' != $qbo_sync_type) ? '' : 'display:none'; ?>">
            <legend>Update</legend>
            <?php
            $show_manual_sync = true;

            if ('disabled' != $options['sync_type']) {

                $match_with = $product_option->match_product_with();
                if ('sku' == $match_with) {

                    $duplicateProductSkus = LS_Woo_Product::get_woo_duplicate_sku();
                    $emptyProductSkus = LS_Woo_Product::get_woo_empty_sku();
                    $duplicate_products = array_merge($duplicateProductSkus, $emptyProductSkus);

                    if (count($duplicate_products) > 0) {
                        LS_QBO()->show_woo_duplicate_products($duplicate_products, $emptyProductSkus, $duplicateProductSkus);
                        $show_manual_sync = false;
                    }
                }

            }


            if ('disabled' != $qbo_sync_type && $show_manual_sync == true) {
                $url = LS_QBO()->get_update_url();
                ?>
                <b>Update URL : </b><a class="manual_sync" href="javascript:void(0)"><?php echo $url; ?></a>
                <br>
                <br>Use the Trigger button to open the Update URL in a new window. linksync for WooCommerce is engineered to automatically have changes synced immediately for both products and orders, but you can use this option to manually trigger a sync.
                <p><input type="button" class="manual_sync button button-primary" value="Trigger"></p>
                <?php
            }


            ?>
        </fieldset>
        <?php
    }

    $status = get_option('linksync_status');
    if (isset($status) && $status == 'Active' || $status == 'Inactive') {
        ?>
        <fieldset>
            <legend>Linksync Status</legend>
            <form method="post">
                <p>Account Status :
                    <b><?php echo(get_option('linksync_last_test_time') != '' ? get_option('linksync_status') : 'Failed / Not tested'); ?></b>
                </p>
                <p>Connected URL :
                    <b><?php echo(get_option('linksync_last_test_time') != '' ? get_option('linksync_connected_url') : 'Failed / Not tested') ?></b>
                </p>
                <p>Last Message:
                    <b><?php echo(get_option('linksync_last_test_time') != '' ? get_option('linksync_frequency') : 'Failed / Not tested') ?></b>
                </p>
                <p>Connected:
                    <b><?php echo(get_option('linksync_last_test_time') != '' ? get_option('linksync_connectedto') : 'Failed / Not tested') ?></b>
                </p>
                <p>Connected To:
                    <b><?php echo(get_option('linksync_last_test_time') != '' ? get_option('linksync_connectionwith') : 'Failed / Not tested') ?></b>
                </p>
                <p>Last time tested:
                    <b><?php echo(get_option('linksync_last_test_time') != '' ? get_option('linksync_last_test_time') : 'Failed / Not tested') ?></b>
                </p>

            </form>
        </fieldset>
    <?php } ?>
</div>