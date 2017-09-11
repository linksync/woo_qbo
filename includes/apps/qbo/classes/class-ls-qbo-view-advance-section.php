<?php if (!defined('ABSPATH')) exit('Access is Denied');

class LS_QBO_View_Advance_Section
{
    public static function update_section()
    {

        $webhook = get_option('linksync_addedfile');
        if (is_qbo()) {
            $status = LS_QBO()->options()->connection_status();
            $product_option = LS_QBO()->product_option();
            $ps_form = LS_QBO_Product_Form::instance();

            $options = $product_option->get_current_product_syncing_settings();
            $options['pop_up_style'] = 'none';
            $ps_form->set_users_options($options);
            $ps_form->confirm_sync();

            $qbo_sync_type = $product_option->sync_type();
            ?>
            <table class="wp-list-table widefat fixed">
                <thead>
                <tr>
                    <td><strong>Update</strong></td>
                </tr>
                </thead>

                <tbody>
                <tr>
                    <td>
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

                        $url = LS_QBO()->get_update_url();
                        ?>
                        <b>Update URL : </b><a class="<?php echo ('disabled' != $qbo_sync_type && $show_manual_sync == true) ? 'manual_sync': ''; ?>" href="javascript:void(0)"><?php echo $url; ?></a>
                        <br>
                        <br>In case of integration halt, use this button to manually update and resync data from QuickBooks Online to WooCommerce since last sync.
                        <p><input type="button" <?php echo ('disabled' != $qbo_sync_type && $show_manual_sync == true) ? '': 'disabled'; ?> class="<?php echo ('disabled' != $qbo_sync_type && $show_manual_sync == true) ? 'manual_sync': ''; ?> button button-primary" value="Resync from last update"></p>
                        <?php


                        ?>
                    </td>
                </tr>
                </tbody>
            </table>
            <?php
        }
    }
}