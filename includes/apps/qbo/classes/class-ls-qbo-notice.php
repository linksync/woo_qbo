<?php if (!defined('ABSPATH')) exit('Access is Denied');

class LS_QBO_Notice
{

    public function display()
    {
        $this->display_run_wizard_notice();
        $this->linksync_update_plugin_notice();
        $this->show_on_product_edit_screen();
    }

    public function display_run_wizard_notice()
    {
        $currentLaid = LS_QBO()->laid()->getCurrentLaid('');

        if (empty($currentLaid)) {
            LS_Message_Builder::notice('Looks Like you have not successfully set up linksync plugin. ' . LS_User_Helper::wizard_button());
        }
    }

    public function show_on_product_edit_screen()
    {
        global $typenow;
        if ('product' == $typenow && isset($_GET['post'])) {
            $product_id = (int)$_GET['post'];
            $product = wc_get_product($product_id);
            $sku = $product->get_sku();
            $unique_sku = LS_Woo_Product::product_has_unique_sku($product_id, $sku);

            $errors = null;
            if (!$unique_sku) {
                $url = admin_url('edit.php?post_status=trash&post_type=product&s=' . $sku);
                $errors[] = 'Linksync detected that the SKU of this product existed in product trash. <a href="' . $url . '" target="_blank">Click here to view</a>';
            } else if ('' == $sku) {
                $errors[] = 'Linksync detected that the SKU of this product is empty.';
            }

            if (!empty($errors)) {
                $this->show_errors($errors);
            }
        }

    }

    /**
     * Add a notice to all admin that linksync and vend plugin has updates
     */
    public function linksync_update_plugin_notice()
    {
        $running_version = Linksync_QuickBooks::$version;

        $laid_key = LS_QBO()->laid()->getCurrentLaid();
        if (!empty($laid_key)) {

            $laid_info = LS_QBO()->laid()->getLaidInfo($laid_key);
            if (!empty($laid_info) && !isset($laid_info['errorCode'])) {

                if ($laid_info['connected_app'] == '13') {
                    $linksync_version = $laid_info['connected_app_version'];
                } elseif ($laid_info['app'] == '13') {
                    $linksync_version = $laid_info['app_version'];
                } else {
                    $linksync_version = NULL;
                }

                update_option('linksync_version', $linksync_version);
                $linksync_version = get_option('linksync_version');
                if (version_compare($linksync_version, $running_version, '>')) {
                    LS_Message_Builder::info('linksync for WooCommerce <b>' . $linksync_version . '</b> is available! Please <a target="_blank" href="https://www.linksync.com/help/releases/vend-woocommerce">Update now.</a>', true);
                }
                update_option('laid_message', isset($laidinfo['message']) ? $laidinfo['message'] : null);
            }

        }

    }

    public function show_errors($errors)
    {
        ?>
        <div id="ls_errors" class="error">
            <?php
            foreach ($errors as $error) {
                echo '<p>' . wp_kses_post($error) . '</p>';
            }
            ?>
        </div>
        <?php
    }
}