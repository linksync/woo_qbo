<?php

class LS_Description_Handler
{

    public function __construct()
    {
        add_action('wp_ajax_qbo_description_toggle', array($this, 'showHideDescriptionLengthTable'));
        add_action('before_product_syncing_options', array($this, 'showHtmlErrors'));
    }


    public function showHideDescriptionLengthTable()
    {
        $return = 'true';
        if (isset($_POST['show_description']) && 'false' == $_POST['show_description']) {
            $return = 'false';
        }
        LS_Description_Handler::is_tooggle_down($return);

        wp_send_json($return);
    }

    public static function is_tooggle_down($trueOrfalse = null)
    {
        $meta_key = '_ls_description_4000_tooggled';
        if (null !== $trueOrfalse) {
            update_option($meta_key, $trueOrfalse);
        }
        return get_option($meta_key, 'true');
    }


    public function showHtmlErrors()
    {
        $productsDescriptionsOver4000 = LS_Woo_Product::getProductsOver4000LengthDescription();
        $productOptins = LS_QBO()->product_option()->get_current_product_syncing_settings();
        $productSyncingDescriptionOption = $productOptins['description'];
        $productSyncingSyncType = $productOptins['sync_type'];
        $adminUrl = admin_url();

        if (
            !empty($productsDescriptionsOver4000) &&
            'on' == $productSyncingDescriptionOption &&
            'two_way' == $productSyncingSyncType
        ) {
            $descriptionError = 'Your product description exceeds QuickBooks 4000 maximum character limit. If you are updating from WooCommerce, your product description will be trimmed down in Quickbooks. If you are updating from Quickbooks, it will not reflect in WooCommerce.';
            LS_Message_Builder::notice($descriptionError);
            $is_toogled = LS_Description_Handler::is_tooggle_down();

            ?>

            <table class="widefat">
                <thead>
                <tr>
                    <th colspan="4" id="toogle_description_error">
                        <label style="width: 100%;">
                            <input style="visibility: hidden; position: absolute;" type="checkbox"
                                   name="checkbox_toogle_description"
                                   value="true" <?php echo ('true' == $is_toogled) ? 'checked' : ''; ?>>
                            <strong>Product with more than 4000 description length</strong>
                            <span id="ls-toggle-indicator"
                                  class="<?php echo ('true' == $is_toogled) ? 'toggle-indicator-up' : 'toggle-indicator-down'; ?> "
                                  style="float: right;"></span>
                        </label>

                    </th>
                </tr>
                </thead>
                <tbody id="tbl_description_error_body"
                       style="<?php echo ('true' == $is_toogled) ? 'display:none;' : ''; ?>">
                <tr>
                    <td><b class="font-size-14">Product name</b></td>
                    <td><b class="font-size-14">SKU</b></td>
                    <td><b class="font-size-14">Character Count</b></td>
                    <td></td>
                </tr>
                <?php
                $counter = 1;
                foreach ($productsDescriptionsOver4000 as $product) {
                    $alternate = ls_is_odd($counter) ? 'alternate' : '';
                    $productId = $product['ID'];
                    $product_meta = new LS_Product_Meta($productId);
                    $sku = $product_meta->get_sku();
                    if ($product['post_type'] == 'product_variation') {
                        $link = $adminUrl . 'post.php?action=edit&post=' . $product['post_parent'];
                        $productDescription = $product_meta->get_variation_description();
                    } else {
                        $link = $adminUrl . 'post.php?action=edit&post=' . $productId;
                        $productDescription = $product['post_content'];
                    }
                    $charCount = strlen(htmlentities($productDescription, ENT_QUOTES));

                    ?>
                    <tr class="<?php echo $alternate; ?>">
                        <td>
                            <a target="_blank" href="<?php echo $link; ?>">
                                <?php echo !empty($product['post_title']) ? $product['post_title'] : ''; ?>
                            </a>
                        </td>
                        <td><?php echo $sku; ?></td>
                        <td><?php echo $charCount; ?></td>
                        <td><a target="_blank" href="<?php echo $link; ?>">Click Here To edit!</a></td>
                    </tr>
                    <?php
                    $counter++;
                }

                ?>
                </tbody>
            </table>
            <?php


        }
    }
}