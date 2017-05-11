<h1>Let's Sync your products</h1>
<hr>
<?php
    $product_option = LS_QBO()->product_option();
    $ps_form = LS_QBO_Product_Form::instance();

    $options = $product_option->get_current_product_syncing_settings();
    $options['pop_up_style'] = 'none';
    $ps_form->set_users_options($options);
    $ps_form->confirm_sync();
    $woocommerce_product_ids = LS_Woo_Product::get_product_ids();
?>
<div class="wizard-form" id="ls-wrapper">



    <br/><br/>
    <table class="widefat">

        <tbody>
        <tr>
            <td class="sync-message">
                <p>
                    Selecting the <?php echo LS_QBO_Constant::SYNC_ALL_PRODUCTS_FROM_QBO; ?> button resets linksync to update all WooCommerce products with data from QuickBooks, based on your existing Product Sync Settings.
                </p>
            </td>
            <?php
            if('two_way' == $options['sync_type'] && !empty($woocommerce_product_ids)){
                ?>
                <td class="sync-message">
                    <p>
                        Selecting this option will sync your entire WooCommerce product catalogue to QuickBooks, based on your existing Product Sync Settings. It takes 3-5 seconds to sync each product, depending on the performance of your server, and your geographic location.
                    </p>
                </td>
                <?php
            }
            ?>
        </tr>
        <tr>
            <td class="sync-qbo-button-cont">
                <p class="form-holder">
                    <input id="btn_sync_products_from_qbo" type="submit" name="submit" class="sync-qbo-button"
                           value="<?php echo LS_QBO_Constant::SYNC_ALL_PRODUCTS_FROM_QBO; ?>"/>
                </p>
            </td>
            <?php
                if('two_way' == $options['sync_type'] && !empty($woocommerce_product_ids)){
                    ?>
                    <td class="sync-qbo-button-cont">
                        <p class="form-holder">
                            <input id="btn_sync_products_to_qbo" type="submit" name="submit" class="sync-qbo-button"
                                   value="<?php echo LS_QBO_Constant::SYNC_ALL_PRODUCTS_TO_QBO; ?>"/>
                        </p>
                    </td>
                    <?php
                }
            ?>

        </tr>

        </tbody>
    </table>
    <div class="clearfix"></div>
</div>
<script>
    (function( $ ){



    }( jQuery ));
</script>
