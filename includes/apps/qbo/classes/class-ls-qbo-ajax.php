<?php if (!defined('ABSPATH')) exit('Access is Denied');

class LS_QBO_Ajax
{

    public function __construct()
    {
        add_action('wp_ajax_qbo_set_empty_sku_automatically', array($this, 'setEmptySkuAutomatically'));
        add_action('wp_ajax_qbo_append_product_id_to_duplicate_skus', array($this, 'appendProductIdToDuplicateSku'));
        add_action('wp_ajax_qbo_delete_products_permanently', array($this, 'deleteProductsPermanently'));
        add_action('wp_ajax_qbo_done_syncing_required', array($this, 'doneRequiredResync'));

        add_action('wp_ajax_qbo_get_qbo_duplicate_skus', array($this, 'get_quickbooks_duplicate_skus'));
        add_action('wp_ajax_qbo_product_sku_unique', array($this, 'make_qbo_product_sku_unique'));
        add_action('wp_ajax_qbo_get_syncing_options', array($this, 'get_syncing_options'));


        add_action('wp_ajax_qbo_get_woocommerce_tax_rates', array($this, 'get_woocommerce_tax_rates'));
        add_action('wp_ajax_qbo_save_qbo_tax_agencies_to_wpdb', array($this, 'saveTaxAgencies'));
        add_action('wp_ajax_qbo_save_woo_taxrates_to_qbo', array($this, 'saveWooTaxRatesToQbo'));
        
        add_action('wp_ajax_qbo_save_product_qbo_duplicates', array($this, 'saveDuplicateQboProducts'));


    }

    public function saveDuplicateQboProducts()
    {
        $laid = LS_QBO()->laid()->getCurrentLaid();

        $in_woo_duplicate_skus = LS_Woo_Product::get_woo_duplicate_sku();
        $in_woo_empty_product_skus = LS_Woo_Product::get_woo_empty_sku();


        if (!empty($laid)) {
            $in_qbo_duplicate_and_empty_skus = LS_QBO()->api()->product()->get_duplicate_products();
            LS_QBO()->options()->updateQuickBooksDuplicateProducts($in_qbo_duplicate_and_empty_skus);
        }


        wp_send_json(array(
            'qbo_product_duplicates' => isset($in_qbo_duplicate_and_empty_skus['products']) ? $in_qbo_duplicate_and_empty_skus['products'] : array(),
            'woo_empty_product_skus' => $in_woo_empty_product_skus,
            'woo_duplicate_skus' => $in_woo_duplicate_skus,
            'laid' => $laid,
        ));
    }

    public function saveWooTaxRatesToQbo()
    {

        $taxAgencies = LS_QBO()->options()->get_tax_agencies();
        $taxAgencyIdTobeUsed = isset($taxAgencies[0]['id']) ? $taxAgencies[0]['id'] : '1';

        $tax_rates_to_import = array();
        $tax_classes = LS_Woo_Tax::get_tax_classes();
        foreach ($tax_classes as $tax_key => $tax_class) {

            $class_tax_rates = LS_Woo_Tax::get_tax_rates($tax_key);
            foreach ($class_tax_rates as $tax_rate) {

                $tax_rates_to_import[] = array(
                    'TaxCode' => $tax_rate['tax_rate_name'],
                    'TaxRateDetails' => array(
                        'TaxRateName' => $tax_rate['tax_rate_name'],
                        'RateValue' => $tax_rate['tax_rate'],
                        'TaxAgencyId' => $taxAgencyIdTobeUsed,
                        'TaxApplicableOn' => "Sales"
                    )
                );

            }

        }

//        $saveWooTaxRatesToQbo = LS_QBO()->api()->post('tax', $tax_rates_to_import);
//        $tax_rates_to_import['import_response'] = $saveWooTaxRatesToQbo;

        wp_send_json($tax_rates_to_import);
    }

    public function saveTaxAgencies()
    {
        $qbo_api = LS_QBO()->api();
        LS_QBO()->options()->update_tax_agencies($qbo_api->get_tax_agencies());
        die();
    }

    public function get_woocommerce_tax_rates()
    {
        $woocommerce_tax_rates = array();
        $tax_classes = LS_Woo_Tax::get_tax_classes();
        foreach ($tax_classes as $tax_key => $tax_class) {
            $class_tax_rates = LS_Woo_Tax::get_tax_rates($tax_key);
        }

        wp_send_json($woocommerce_tax_rates);
    }

    public function get_syncing_options()
    {
        $duplicate_or_empty_skus = LS_Product_Helper::get_woocommerce_duplicate_or_empty_skus();
        $ps_form = LS_QBO_Product_Form::instance();
        $product_option = LS_QBO()->product_option();
        $options = $product_option->get_current_product_syncing_settings();
        $ps_form->set_users_options($options);

        wp_send_json(array(
            'product' => LS_QBO()->product_option()->get_current_product_syncing_settings(),
            'order' => LS_QBO()->order_option()->get_current_order_syncing_settings(),
            'settings_link' => LS_User_Helper::linksync_settings_button(),
            'duplicate_sku' => array(
                'has_duplicate_or_empty_sku' => !empty($duplicate_or_empty_skus) ? true : false,
                'duplicate_or_empty_skus' => $duplicate_or_empty_skus,
                'message' => '<h4 style="color: red;">' . LS_QBO_Helper::duplicate_sku_message() . '</h4>'
            )
        ));
    }

    public function get_quickbooks_duplicate_skus()
    {
        $page = isset($_POST['page']) ? $_POST['page'] : 1;
        $in_qbo_duplicate_and_empty_skus = LS_QBO()->api()->product()->get_duplicate_products($page);
        LS_QBO()->options()->updateQuickBooksDuplicateProducts($in_qbo_duplicate_and_empty_skus);
        wp_send_json($in_qbo_duplicate_and_empty_skus);
    }

    public function make_qbo_product_sku_unique()
    {
        $product = new LS_Simple_Product($_POST['product']);
        $product->set_id(get_qbo_id($product->get_id()));
        $sku = $product->get_sku();
        $tax_id = $product->get_tax_id();
        $product_type = $product->get_product_type();
        if (LS_QBO_ItemType::SERVICE == $product_type || LS_QBO_ItemType::NONINVENTORY == $product_type) {
            $product->remove_quantity();
        }

        $asset_account_id = $product->get_asset_account_id();
        $default_asset_account_id = LS_QBO()->product_option()->inventory_asset_account();

        $expense_account_id = $product->get_expense_account_id();
        $default_expense_account_id = LS_QBO()->product_option()->expense_account();

        $income_account_id = $product->get_income_account_id();
        $default_income_account_id = LS_QBO()->product_option()->income_account();

        $product->set_asset_account_id((empty($asset_account_id)) ? $default_asset_account_id : $asset_account_id);
        $product->set_expense_account_id((empty($expense_account_id)) ? $default_expense_account_id : $expense_account_id);
        $product->set_income_account_id((empty($income_account_id)) ? $default_income_account_id : $income_account_id);

        $product->set_sku($sku . '_' . time());
        $product->set_tax_id((empty($tax_id) ? null : $tax_id));
        $product->remove_deleted_at();
        $product->remove_update_at();
        $product->unset_data('category');
        $product->unset_data('subItem');
        $product->unset_data('parentId');
        $product->unset_data('level');

        error_log($product->get_product_json());
        $quickBooksProductUpdate = LS_QBO()->api()->product()->save_product($product->get_product_json());
        error_log(json_encode($quickBooksProductUpdate));

        $page = isset($_POST['page']) ? $_POST['page'] : 1;
        $product_count = isset($_POST['product_count']) ? $_POST['product_count'] : 0;
        $product_total_count = isset($_POST['product_total_count']) ? $_POST['product_total_count'] : 0;
        $product_count = ($product_count > $product_total_count) ? $product_total_count : $product_count;
        $progressValue = round(($product_count / $product_total_count) * 100);
        $msg = $product_count . " of " . $product_total_count . " Product(s)";

        $response = array(
            'msg' => $msg,
            'percentage' => $progressValue,
            'product_number' => $product_count,
            'product_total_count' => $product_total_count,
            'product_update_response' => $quickBooksProductUpdate
        );
        wp_send_json($response);
    }

    public function deleteProductsPermanently()
    {

        if (!empty($_POST['product_ids'])) {

            foreach ($_POST['product_ids'] as $product_id) {
                if (is_numeric($product_id)) {
                    wp_delete_post($product_id, true);
                }
            }
        }
        wp_send_json(array('message' => 'done'));
    }

    public function doneRequiredResync()
    {
        LS_QBO()->options()->done_required_sync();
        die();
    }

    public function setEmptySkuAutomatically()
    {
        if (!empty($_POST['product_ids'])) {
            foreach ($_POST['product_ids'] as $product_id) {
                if (is_numeric($product_id)) {
                    $product_meta = new LS_Product_Meta($product_id);
                    $sku = $product_meta->get_sku();
                    if (empty($sku)) {
                        $newSku = 'sku_' . $product_id;
                        $product_meta->update_sku($newSku);
                    }
                }
            }
        } else {

            $emptySkus = LS_Woo_Product::get_woo_empty_sku();
            foreach ($emptySkus as $product_ref) {
                $sku = $product_ref['meta_value'];
                if (empty($sku)) {
                    $product_meta = new LS_Product_Meta($product_ref['ID']);
                    $newSku = 'sku_' . $product_ref['ID'];
                    $product_meta->update_sku($newSku);
                }
            }

        }


        wp_send_json(array('message' => 'done'));
    }

    public function appendProductIdToDuplicateSku()
    {
        if (!empty($_POST['product_ids'])) {
            foreach ($_POST['product_ids'] as $product_id) {
                if (is_numeric($product_id)) {
                    $product_meta = new LS_Product_Meta($product_id);
                    $sku = $product_meta->get_sku();
                    $uniqueSku = 'sku_' . $product_id;
                    if ($sku != $uniqueSku && !empty($sku)) {
                        $newSku = $uniqueSku . $sku;
                        $product_meta->update_sku($newSku);
                    }
                }
            }
        } else {

            $duplicateSkus = LS_Woo_Product::get_woo_duplicate_sku();
            foreach ($duplicateSkus as $duplicateSku) {
                $sku = $duplicateSku['meta_value'];
                $productMeta = new LS_Product_Meta($duplicateSku['ID']);
                $uniqueSku = 'sku_' . $duplicateSku['ID'];
                if ($sku != $uniqueSku) {
                    $newSku = $uniqueSku . $sku;
                    $productMeta->update_sku($newSku);
                }

            }

        }


        wp_send_json(array('message' => 'done'));
    }
}