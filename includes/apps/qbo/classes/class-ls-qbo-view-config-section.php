<?php if (!defined('ABSPATH')) exit('Access is Denied');

class LS_QBO_View_Config_Section
{
    public static function api_key_configuration()
    {
        ?>
        <table class="wp-list-table widefat fixed">
            <thead>
            <tr>
                <td><strong>API Key configuration</strong></td>
            </tr>
            </thead>

            <tbody>
            <tr>
                <td>
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
                                    <a href="#" data-reveal-id="myModal" data-animation="fade"
                                       class="button button-primary">Add
                                        Api Key</a><?php
                                } else {
                                    ?>
                                    <a href="#" data-reveal-id="modal_update_api" class="button button-primary">Edit Api
                                        Key</a>
                                    <?php
                                } ?>
                            </td>
                        </tr>

                    </table>

                </td>
            </tr>
            </tbody>
        </table>
        <?php
    }

    public static function sync_now()
    {
        $options = LS_QBO()->options();
        $connection_status = $options->connection_status();
        if ('Active' == $connection_status) {
            $product_option = LS_QBO()->product_option();
            $sync_type = $product_option->sync_type();
            $woocommerce_product_ids = LS_Woo_Product::get_product_ids();

            $ps_form = LS_QBO_Product_Form::instance();

            $options = $product_option->get_current_product_syncing_settings();
            $options['pop_up_style'] = 'block';
            $ps_form->set_users_options($options);

            $duplicate_product_by_sku = array();

            $duplicate_or_empty_skus = LS_Product_Helper::get_woocommerce_duplicate_or_empty_skus();
            if(!empty($duplicate_or_empty_skus)){
                $duplicate_product_by_sku = $duplicate_or_empty_skus;
                LS_QBO()->show_woo_duplicate_products();
            }

            $qbo_empty_or_duplicate_skus = LS_QBO()->options()->getQuickBooksDuplicateProducts();
            if(!empty($qbo_empty_or_duplicate_skus['products'])){
                $duplicate_product_by_sku = $qbo_empty_or_duplicate_skus['products'];
                LS_QBO()->show_qbo_duplicate_products();
            }
            $ps_form->confirm_sync($duplicate_product_by_sku);


            ?>
            <table class="wp-list-table widefat fixed">
                <thead>
                <tr>
                    <td <?php echo ('two_way' == $sync_type) ? 'colspan="2"' : ''; ?>>
                        <?php echo ('disabled' != $sync_type) ? '<strong>Sync Now!</strong>' : ''; ?>
                        <?php echo ('disabled' == $sync_type) ? 'Product syncing type disabled! <a href="' . LS_QBO_Menu::tab_admin_menu_url('product_config') . '">Configure it now!</a>' : ''; ?>
                    </td>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <?php
                    if ('two_way' == $sync_type) {
                        ?>
                        <td class="sync-message">
                            <p>
                                Selecting the <?php echo LS_QBO_Constant::SYNC_ALL_PRODUCTS_FROM_QBO; ?> button resets
                                linksync to update all WooCommerce products with data from QuickBooks, based on your
                                existing Product Sync Settings.
                            </p>
                        </td>
                        <?php
                        if (!empty($woocommerce_product_ids)) {
                            ?>
                            <td class="sync-message">
                                <p>
                                    Selecting this option will sync your entire WooCommerce product catalogue to QuickBooks,
                                    based on your existing Product Sync Settings. It takes 3-5 seconds to sync each product,
                                    depending on the performance of your server, and your geographic location.
                                </p>
                            </td>
                            <?php
                        }
                    }

                    if ('qbo_to_woo' == $sync_type) {
                        ?>
                        <td class="sync-message">
                            <p>
                                Selecting this option will sync your entire WooCommerce product catalogue to QuickBooks,
                                based on your existing Product Sync Settings. It takes 3-5 seconds to sync each product,
                                depending on the performance of your server, and your geographic location.
                            </p>
                        </td>
                        <?php
                    }
                    ?>
                </tr>
                <tr>
                    <?php
                    if ('two_way' == $sync_type) {
                        ?>
                        <td class="sync-qbo-button-cont">
                            <p class="form-holder">
                                <input id="btn_sync_products_from_qbo" type="submit" name="submit"
                                       class="button button-primary sync-qbo-button"
                                       value="<?php echo LS_QBO_Constant::SYNC_ALL_PRODUCTS_FROM_QBO; ?>"/>
                            </p>
                        </td>

                        <?php
                        if (!empty($woocommerce_product_ids)) {
                            ?>
                            <td class="sync-qbo-button-cont">
                                <p class="form-holder">
                                    <input id="btn_sync_products_to_qbo" type="submit" name="submit"
                                           class="button button-primary sync-qbo-button"
                                           value="<?php echo LS_QBO_Constant::SYNC_ALL_PRODUCTS_TO_QBO; ?>"/>
                                </p>
                            </td>
                            <?php
                        }

                    }
                    ?>

                    <?php
                    if ('qbo_to_woo' == $sync_type) {
                        ?>
                        <td class="sync-qbo-button-cont">
                            <p class="form-holder">
                                <input id="btn_sync_products_from_qbo" type="submit" name="submit"
                                       class="button button-primary sync-qbo-button"
                                       value="<?php echo LS_QBO_Constant::SYNC_ALL_PRODUCTS_FROM_QBO; ?>"/>
                            </p>
                        </td>
                        <?php
                    }
                    ?>

                </tr>
                </tbody>
            </table>
            <?php
        }

    }

    public static function connection_status()
    {

        $status = get_option('linksync_status');
        if (isset($status) && $status == 'Active' || $status == 'Inactive') {
            $last_time_tested = get_option('linksync_last_test_time');
            $connected_to = get_option('linksync_connectedto');
            ?>
            <style>
                .ls-qbo-connection-status > table tr td label,
                #ls-qbo-status > table tr td label {
                    width: 100px !important;
                }
            </style>
            <table class="wp-list-table widefat fixed ls-qbo-connection-status">
                <thead>
                <tr>
                    <td>
                        <strong <?php echo ('Inactive' == $status) ? 'style="color: red;"' : ''; ?>>
                            Connection Status
                        </strong>
                    </td>
                </tr>
                </thead>

                <tbody>
                <tr>
                    <td><label>Account Status</label> :
                        <strong><?php echo($last_time_tested != '' ? get_option('linksync_status') : 'Failed / Not tested'); ?></strong>
                    </td>
                </tr>

                <tr>
                    <td><label>linksync API Url</label> :
                        <strong><?php echo($last_time_tested != '' ? '<a target="_blank" href="http://developer.linksync.com/">' . get_option('linksync_connected_url') . '</a>' : 'Failed / Not tested') ?></strong>
                    </td>
                </tr>

                <?php
                if (!empty($connected_to)) {
                    ?>
                    <tr>
                        <td><label>Connected To</label> :
                            <strong><?php echo($last_time_tested != '' ? $connected_to : 'Failed / Not tested') ?></strong>
                        </td>
                    </tr>
                    <?php
                }
                ?>
                <tr>
                    <td><label>Last Message</label> :
                        <strong><?php echo($last_time_tested != '' ? get_option('linksync_frequency') : 'Failed / Not tested') ?></strong>
                    </td>
                </tr>

                <tr>
                    <td><label>Last time tested</label> :
                        <strong><?php echo($last_time_tested != '' ? $last_time_tested : 'Failed / Not tested') ?></strong>
                    </td>
                </tr>
                </tbody>
            </table>
            <?php
        }
    }

}