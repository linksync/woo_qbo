<?php if (!defined('ABSPATH')) exit('Access is Denied');

class LS_QBO_Sync
{


    public function __construct()
    {
        add_action('wp_ajax_qbo_get_products', array('LS_QBO_Sync', 'qbo_get_products_callback'));
        add_action('wp_ajax_import_to_woo', array('LS_QBO_Sync', 'import_qbo_product_to_woo'));

        add_action('wp_ajax_woo_get_products', array('LS_QBO_Sync', 'woo_get_products_callback'));
        add_action('wp_ajax_import_to_qbo', array('LS_QBO_Sync', 'import_woo_product_to_qbo'));

        add_action('wp_ajax_since_last_sync', array('LS_QBO_Sync', 'qbo_get_products_since_last_update'));

        $product_options = LS_QBO()->product_option();
        $current_sync_option = $product_options->get_current_product_syncing_settings();

        if ('disabled' != $current_sync_option['sync_type']) {

            $delete = $current_sync_option['delete'];
            if ('on' == $delete) {
                /**
                 *Delete product in quickbooks if delete option is "on" and
                 *user is deleting woocommerce product permanently
                 */
                add_action('before_delete_post', array('LS_QBO_Sync', 'delete_product'));
            }

            //Save product to quickbooks
            self::add_action_save_post();
        }


        $order_options = LS_QBO()->order_option();
        $current_order_options = $order_options->get_current_order_syncing_settings();

        if ('disabled' != $current_order_options['sync_type']) {
            $order_action_hooks = ls_woo_order_hook_names();
            //Add the action when the user completes the payment and when the order was created via admin page
            //$order_action_hooks[] = 'woocommerce_thankyou';
            $order_action_hooks[] = 'woocommerce_process_shop_order_meta';
            foreach ($order_action_hooks as $order_action_hook) {
                add_action($order_action_hook, array('LS_QBO_Sync', 'import_single_order_to_qbo'), 1);
            }
        }

        $wh_code = get_option('webhook_url_code');
        add_action('wp_ajax_' . $wh_code, array('LS_QBO_Sync', 'sync_triggered_by_lws'));
        add_action('wp_ajax_nopriv_' . $wh_code, array('LS_QBO_Sync', 'sync_triggered_by_lws'));

        add_action('wp_ajax_ls_product_meta', array('LS_QBO_Sync', 'product_meta'));
        add_action('wp_ajax_nopriv_ls_product_meta', array('LS_QBO_Sync', 'product_meta'));


        add_action('wp_ajax_ls_product_options', array('LS_QBO_Sync', 'product_options'));
        add_action('wp_ajax_nopriv_ls_product_options', array('LS_QBO_Sync', 'product_options'));

        add_action('wp_ajax_ls_order_options', array('LS_QBO_Sync', 'order_options'));
        add_action('wp_ajax_nopriv_ls_order_options', array('LS_QBO_Sync', 'order_options'));

        add_action('wp_ajax_ls_taxrate_taxcode', array('LS_QBO_Sync', 'taxCodeAndRateReferences'));
        add_action('wp_ajax_nopriv_ls_taxrate_taxcode', array('LS_QBO_Sync', 'taxCodeAndRateReferences'));

        add_action('wp_ajax_qbo_accounts_webhook', array('LS_QBO_Sync', 'updateQuickBooksAccounts'));
        add_action('wp_ajax_nopriv_qbo_accounts_webhook', array('LS_QBO_Sync', 'updateQuickBooksAccounts'));

        add_action('wp_ajax_ls_dev_logs', array('LS_QBO_Sync', 'showDevLogs'));
        add_action('wp_ajax_nopriv_ls_dev_logs', array('LS_QBO_Sync', 'showDevLogs'));

        add_action('wp_ajax_ls_lws_api_update', array('LS_QBO_Sync', 'lwsApiHasUpdates'));
        add_action('wp_ajax_nopriv_ls_lws_api_update', array('LS_QBO_Sync', 'lwsApiHasUpdates'));

    }

    /**
     * Prepared method in case there are LWS Api updates that the plugin needs to do or change something
     */
    public static function lwsApiHasUpdates()
    {
        set_time_limit(0);
        $taxDataToBeUsed = LS_Woo_Tax::getQuickBookTaxDataToBeUsed();
        LS_QBO()->options()->updateQuickBooksTaxClasses($taxDataToBeUsed);

    }

    public static function showDevLogs()
    {
        echo LSC_Log::printallLogs();
        die();
    }

    public static function updateQuickBooksAccounts()
    {
        if(isset($_REQUEST['update']) && true == $_REQUEST['update']){
            set_time_limit(0);
            $qbo_api = LS_QBO()->api();

            $accounts = $qbo_api->get_accounts();
            $account = new LS_QBO_Account();
            if(!empty($accounts['accounts'])){
                $account->batchInsertUpdate($accounts['accounts']);
            }

            wp_send_json($account->getAll());
        }

        wp_send_json(array('error' => 'QuickBooks Account update to Woocommerce was not triggered'));
    }

    public static function taxCodeAndRateReferences()
    {
        wp_send_json(LS_QBO()->options()->getTaxRateAndCodeOjects());
        die();
    }
    public static function product_options()
    {
        wp_send_json(LS_QBO()->product_option()->get_current_product_syncing_settings());
        die();
    }

    public static function order_options()
    {
        wp_send_json(LS_QBO()->order_option()->get_current_order_syncing_settings());
        die();
    }

    public static function product_meta()
    {
        if (isset($_REQUEST['product_id'])) {
            $product_id = $_REQUEST['product_id'];
            echo json_encode(get_post_meta($product_id));
        }
        die();
    }

    public static function sync_triggered_by_lws()
    {
        LS_QBO_Sync::lwsApiHasUpdates();

        $last_sync = LS_QBO()->options()->last_product_update();
        LSC_Log::add_dev_success('LS_QBO_Sync::sync_triggered_by_lws', 'Linksync triggered a sync.<br/> Last sync :' . $last_sync . '<br/> Current Server Time: ' . current_time('mysql'));

        if (empty($last_sync)) {
            set_time_limit(0);
            LS_QBO_Sync::product_to_woo();
        } else {
            set_time_limit(0);
            LS_QBO_Sync::product_to_woo_since_last_update();
        }

        die();
    }


    /**
     * @param $product_id
     * @param $post
     * @param $update true on update false on save
     */
    public static function save_product($product_id, $post, $update)
    {
        // Dont' send product for revisions or autosaves and auto-draft post_status
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return $product_id;
        }

        // Don't save revisions and autosaves
        if (wp_is_post_revision($product_id) || wp_is_post_autosave($product_id)) {
            return $product_id;
        }

        // Check post type is product
        if ('product' != $post->post_type || 'auto-draft' == $post->post_status) {
            return $product_id;
        }

        //Do not send http post to linksync server if user is trashing product
        if ('trash' == $post->post_status) {
            return $product_id;
        }

        $product = wc_get_product($product_id);
        $has_children = $product->has_child();

        if (true == $product->is_type('variable')) {
            $product_meta = new LS_Product_Meta($product_id);
            $sku = $product->get_sku();
            if (empty($sku)) {
                $sku = 'sku_' . $product_id;
                $product_meta->update_sku($sku);
            }

            //Update product tax class to zero-rate if tax status is none
            if (!empty($_POST['_tax_status']) && 'none' == $_POST['_tax_status']) {
                $product_meta->update_tax_class('zero-rate');
            }

            if (true == $has_children) {
                $variation_ids = $product->get_children();
                if (!empty($variation_ids)) {
                    foreach ($variation_ids as $variation_id) {
                        LS_QBO_Sync::import_single_product_to_qbo($variation_id);
                    }
                }
            }
        } elseif (true == $product->is_type('simple')) {
            LS_QBO_Sync::import_single_product_to_qbo($product_id);
        }


    }

    /**
     * @param $product_id
     * @return string
     */
    public static function import_single_product_to_qbo($product_id)
    {

        set_time_limit(0);
        $product = wc_get_product($product_id);
        $productHelper = new LS_Product_Helper($product);
        $product_meta = new LS_Product_Meta($product_id);
        $product_child = LS_Woo_Product::has_child($product_id);

        if (!empty($product_child)) {
            return 'parent_product_will_not_be_sync';
        }

        //Update product tax class to zero-rate if tax status is none
        if (!empty($_POST['_tax_status']) && 'none' == $_POST['_tax_status']) {
            $product_meta->update_tax_class('zero-rate');
        }

        $quickbooks_option = LS_QBO()->options();
        $product_options = LS_QBO()->product_option();
        $current_sync_option = $product_options->get_current_product_syncing_settings();
        $isQuickBooksUsAccount = LS_QBO()->isUsAccount();

        if ('two_way' == $current_sync_option['sync_type'] || 'qbo_to_woo' == $current_sync_option['sync_type']) {
            $product_type = $productHelper->getType();
            $post_parent = $productHelper->getPostParentId();

            //Check if the post type is product
            if (LS_QBO_Product_Helper::isSyncAbleToQuickBooks($product_type)) {
                $json_product = new LS_Json_Product_Factory($product);

                $qbo_product_id = $product_meta->get_product_id();
                if (!empty($qbo_product_id)) {
                    $json_product->set_id(get_qbo_id($qbo_product_id));
                }

                $productName = $productHelper->getPostTitle();
                $productNameLength = strlen($productName);
                $truncated100CharProductName = LS_QBO_Product_Helper::prepareProductNameForQuickBooks($productName);
                $json_product->set_name($truncated100CharProductName);
                /**
                 * Override product name/title if it has more than 100 character because
                 * QuickBooks has 100 limit documented here https://developer.intuit.com/docs/api/accounting/item
                 */
                if ($productNameLength > 100) {
                    $productPost = array();
                    $productPost['ID'] = $product_id;
                    $productPost['post_title'] = $productName;

                    self::remove_action_save_post();
                    wp_update_post($productPost);
                    self::add_action_save_post();
                }

                if ('on' == $product_options->description()) {
                    $productDescription = $productHelper->getPostContent();
                    if ($productHelper->isVariation()) {
                        $productDescription = $product_meta->get_variation_description();
                    }
                    $productDescription = htmlentities($productDescription, ENT_QUOTES);
                    $productDescriptionCount = strlen($productDescription);
                    if ($productDescriptionCount > 4000) {
                        //QuickBooks limit is 4000 character so will save woocommerce product content to a temporary location
                        $product_meta->update_woo_product_description($productDescription);

                        $productDescription = mb_substr($productDescription, 0, 4000);
                    } elseif ($productDescriptionCount <= 4000) {
                        //Empty this temporary holder for product description
                        $product_meta->update_woo_product_description('');
                    }

                    $json_product->set_description($productDescription);
                }

                if ('on' == $product_options->price()) {

                    //Check whether the product is on sale or has sale price
                    $on_sale = $product->is_on_sale();
                    if (!empty($on_sale)) {

                        $json_product->set_list_price($product->get_sale_price());
                        $json_product->set_sell_price($product->get_sale_price());

                    } else {

                        $json_product->set_list_price($product->get_regular_price());
                        $json_product->set_sell_price($product->get_regular_price());

                    }
                }

                /**
                 * Set cost_price base on what has been saved before that comes from QuickBooks
                 */
                $qboCostPrice = $product_meta->get_cost_price();
                $json_product->set_cost_price($qboCostPrice);

                $productQuantityOption = $product_options->quantity();
                $isProductVirtual = $product->is_virtual();
                $isQuickBooksPlus = LS_QBO()->is_qbo_plus();
                $qboProductId = $product_meta->get_product_id();
                $isNewProduct = empty($qboProductId) ? true : false;

                $json_product->set_product_type(LS_QBO_ItemType::NONINVENTORY);
                if (true == $isNewProduct && $isProductVirtual) {
                    $json_product->set_product_type(LS_QBO_ItemType::SERVICE);
                    $json_product->remove_quantity();
                }


                if ('on' == $productQuantityOption) {
                    $stock_quantity = $product->get_stock_quantity();

                    $quantity = !empty($stock_quantity) ? $stock_quantity : 0;
                    $json_product->set_quantity($quantity);
                    $json_product->set_product_type(LS_QBO_ItemType::INVENTORY);

                    if ($isNewProduct && $isQuickBooksPlus && $isProductVirtual) {
                        $json_product->set_product_type(LS_QBO_ItemType::SERVICE);
                        $json_product->remove_quantity();
                    }

                }
                /**
                 * Do not alter product_type in sending product json to LWS then to QuickBooks
                 */
                $qboProductType = $product_meta->get_product_type();
                if(!empty($qboProductType)){
                    $json_product->set_product_type($qboProductType);
                    if (LS_QBO_ItemType::SERVICE == $qboProductType || LS_QBO_ItemType::NONINVENTORY == $qboProductType) {
                        $json_product->remove_quantity();
                    }
                }


                $sku = $product->get_sku();
                if (empty($sku)) {
                    $sku = 'sku_' . $product_id;
                    $product_meta->update_sku($sku);
                }
                $json_product->set_sku($sku);

                $skuLength = strlen($sku);
                /**
                 * Override product name/title if it has more that 100 character because
                 * QuickBooks has 100 limit documented here https://developer.intuit.com/docs/api/accounting/item
                 */
                if($skuLength > 100){
                    $truncated100CharSku = htmlentities($sku, ENT_QUOTES);
                    $truncated100CharSku = mb_substr($truncated100CharSku, 0, 100);
                    $json_product->set_sku($truncated100CharSku);
                    $product_meta->update_sku($truncated100CharSku);
                }


                $json_product->set_active(1);
                $productIncomeAccountId = $product_meta->get_income_account_id();
                $productExpenseAccountId = $product_meta->get_expense_account_id();
                $productAssetAccountId = $product_meta->get_asset_account_id();

                $income_account_id = (!empty($productIncomeAccountId)) ? $productIncomeAccountId : $product_options->income_account();
                $expense_account_id = (!empty($productExpenseAccountId)) ? $productExpenseAccountId : $product_options->expense_account();
                $asset_account_id = (!empty($productAssetAccountId)) ? $productAssetAccountId : $product_options->inventory_asset_account();

                $json_product->set_income_account_id($income_account_id);
                $json_product->set_expense_account_id($expense_account_id);
                $json_product->set_asset_account_id($asset_account_id);

                $wooPurchaseNote = $product_meta->get_purchase_note();
                $json_product->set_purchasing_information(null);
                if(!empty($wooPurchaseNote)){
                    $json_product->set_purchasing_information($wooPurchaseNote);
                }

                $wooTaxClass = ('' == $product_meta->get_tax_class()) ? 'standard' : $product_meta->get_tax_class();
                $qboTaxClassInfo = LS_Woo_Tax::getQuickBooksTaxInfoByWooTaxKey($wooTaxClass);

                $qbo_includes_tax = ('false' === $product_meta->get_qbo_includes_tax()) ? false : true;
                $json_product->set_includes_tax($qbo_includes_tax);

                $tax_id = ('' != $product_meta->get_tax_id()) ? $product_meta->get_tax_id() : null;

                if (!empty($qboTaxClassInfo['id']) && empty($tax_id)) {
                    $tax_id = $qboTaxClassInfo['id'];
                }

                $tax_name = ('' != $product_meta->get_tax_name()) ? $product_meta->get_tax_name() : null;
                if (!empty($qboTaxClassInfo['name']) && empty($tax_name)) {
                    $tax_name = $qboTaxClassInfo['name'];
                }
                $tax_value = ('' != $product_meta->get_tax_value()) ? $product_meta->get_tax_value() : null;
                if (!empty($qboTaxClassInfo['rateValue']) && empty($tax_value)) {
                    $tax_value = $qboTaxClassInfo['rateValue'];
                }

                $tax_rate = ('' != $product_meta->get_tax_rate()) ? $product_meta->get_tax_rate() : null;
                if (!empty($qboTaxClassInfo['rateValue']) && empty($tax_rate)) {
                    $tax_rate = $qboTaxClassInfo['rateValue'];
                }


                $temp_tax_id = $quickbooks_option->getTaxCodeIdByTaxRateId($tax_id);
                if (!empty($temp_tax_id)) {
                    $tax_id = $temp_tax_id;
                }


                $taxable = ('none' == $product_meta->get_tax_status()) ? 'false' : 'true';
                if ($productHelper->isVariation() && !empty($post_parent)) {
                    $parent_product_meta = new LS_Product_Meta($post_parent);
                    $taxable = ('none' == $parent_product_meta->get_tax_status()) ? 'false' : 'true';
                }

                if ($isQuickBooksUsAccount) {
                    $json_product->set_taxable($taxable);
                } elseif (!$isQuickBooksUsAccount && 'false' == $taxable) {
                    $tax_id = null;
                    $tax_name = null;
                    $tax_value = null;
                    $tax_rate = null;
                }

                $json_product->set_tax_id($tax_id);
                $json_product->set_tax_name($tax_name);
                $json_product->set_tax_value($tax_value);
                $json_product->set_tax_rate($tax_rate);

                $j_product = $json_product->get_json_product();
                $result = LS_QBO()->api()->product()->save_product($j_product);

                if (!empty($result['id'])) {
                    $product_meta->update_product_id(get_qbo_id($result['id']));

                    $product = new LS_Simple_Product($result);
                    $product_meta->update_tax_id($product->get_tax_id());
                    $product_meta->update_tax_name($product->get_tax_name());
                    $product_meta->update_tax_rate($product->get_tax_rate());
                    $product_meta->update_tax_value($product->get_tax_value());
                    $product_meta->update_taxable($product->get_taxable());

                    $qbo_tax_includes_tax = (false === $product->does_includes_tax()) ? 'false' : 'true';
                    $product_meta->update_qbo_includes_tax($qbo_tax_includes_tax);

                    $product_meta->update_income_account_id($product->get_income_account_id());
                    $product_meta->update_expense_account_id($product->get_expense_account_id());
                    $product_meta->update_asset_account_id($product->get_asset_account_id());
                    $product_meta->update_product_type($product->get_product_type());
                    $product_meta->update_cost_price($product->get_cost_price());

                    if(isset($productDescriptionCount) && $productDescriptionCount > 4000){
                        $result = array(
                            'errorCode' => 2050,
                            'type' => '',
                            'userMessage' => 'Product description was synced to QuickBooks but trimmed down from first to 4000 characters',
                            'technicalMessage' => 'Product description was synced to QuickBooks but trimmed down from first to 4000 characters'
                        );
                        update_post_meta($product_id, '_ls_json_product_error', $result);
                    } else {
                        delete_post_meta($product_id, '_ls_json_product_error');
                    }

                    LSC_Log::add_dev_success('LS_QBO_Sync::import_single_product_to_qbo', 'Product was imported to QuickBooks <br/> Product json being sent <br/>' . $j_product . '<br/> Response: <br/>' . json_encode($result));

                } else {
                    if(!empty($result['userMessage']) &&  'Duplicate Name Exists Error' == $result['userMessage']){
                        $productPost = array();
                        $productPost['ID'] = $product_id;
                        $productPost['post_status'] = 'private';

                        self::remove_action_save_post();
                        wp_update_post($productPost);
                        self::add_action_save_post();
                    }
                    update_post_meta($product_id, '_ls_json_product_error', $result);
                    LSC_Log::add_dev_failed('LS_QBO_Sync::import_single_product_to_qbo', 'Product ID: ' . $product_id . '<br/><br/>Json product being sent: ' . $j_product . '<br/><br/> Response: ' . json_encode($result));
                }
            }
        }

    }


    /**
     * Importing qbo orders to woocommerce
     */
    public static function order_to_woo()
    {

    }

    /**
     * Importing orders from qbo to woocommerce per one order
     */
    public static function import_single_order_to_woo()
    {

    }

    /**
     * Importing order from woocommerce to qbo per one order
     */
    public static function import_single_order_to_qbo($order_id)
    {
        set_time_limit(0);
        $order_option = LS_QBO()->order_option();
        $product_syncing_option = LS_QBO()->product_option();
        $isQuickBooksUsAccount = LS_QBO()->isUsAccount();

        $order = wc_get_order($order_id);
        $orderHelper = new LS_Order_Helper($order);

        $selected_order_status = ls_selected_order_status_to_trigger_sync();

        $order_status = $order->get_status();
        if (!in_array($order_status, $selected_order_status)) {
            //Do not continue importing if it status was not selected
            return;
        }

        $json_order = new LS_Order_Json_Factory();
        $included_tax = LS_Woo_Tax::is_included();
        $globalTaxCalculation = 'TaxExcluded';
        if (true === $included_tax) {
            $globalTaxCalculation = 'TaxInclusive';
        } else if (null === $included_tax) {
            $globalTaxCalculation = 'NotApplicable';
        }

        $items = $order->get_items();
        $user = $order->get_user();
        $order_tax = $order->get_taxes();
        $shipping_method = $order->get_shipping_method();
        $qbo_tax = '';
        $tax_mapping = $order_option->tax_mapping();
        $total_discount = 0;

        if (!empty($items)) {

            foreach ($items as $item) {
                if (!empty($item['variation_id'])) {
                    $product_id = $item['variation_id'];
                    $parentId = $item['product_id'];
                    $parentVariant = wc_get_product($parentId);
                } else {
                    $product_id = $item['product_id'];
                }
                $pro_object = wc_get_product($product_id);
                $product_meta = new LS_Product_Meta($product_id);
                $orderLineItem = new LS_Woo_Order_Line_Item($item);
                $price = $pro_object->get_price();
                $lineSubTotal = $orderLineItem->get_subtotal();
                $lineQuantity = $orderLineItem->get_quantity();
                if(!empty($lineSubTotal)){
                    $price = (float)($lineSubTotal / $lineQuantity);
                }
                $discount = $orderLineItem->get_discount_amount();

                $qbo_tax = LS_Woo_Tax::get_mapped_quickbooks_tax_for_product(
                    $tax_mapping,
                    $order_tax,
                    $orderLineItem->get_tax_class()
                );

                $taxName = ('' == $product_meta->get_tax_name()) ? null : $product_meta->get_tax_name();
                $taxId = ('' == $product_meta->get_tax_id()) ? null : $product_meta->get_tax_id();
                $taxRate = ('' == $product_meta->get_tax_rate()) ? null : $product_meta->get_tax_rate();

                if ('' != $qbo_tax) {
                    $taxName = (isset($qbo_tax[1])) ? $qbo_tax[1] : $taxName;
                    $taxId = (isset($qbo_tax[0])) ? $qbo_tax[0] : $taxId;
                    $taxRate = (isset($qbo_tax[3])) ? $qbo_tax[3] : $taxRate;
                }

                $productTaxStatus = $product_meta->get_tax_status();
                if (isset($parentVariant)) {
                    $productTaxStatus = $parentVariant->get_tax_status();
                }


                if(empty($taxId)){
                    $wooTaxClass = ('' == $product_meta->get_tax_class()) ? 'standard' : $product_meta->get_tax_class();
                    $qboTaxClassInfo = LS_Woo_Tax::getQuickBooksTaxInfoByWooTaxKey($wooTaxClass);
                    if (!empty($qboTaxClassInfo['id'])) {
                        $taxId = $qboTaxClassInfo['id'];
                        $taxName = $qboTaxClassInfo['name'];
                        $taxRate = isset($qboTaxClassInfo['rateValue']) ? $qboTaxClassInfo['rateValue'] : null;
                    }
                }


                $qboTaxable = ('none' == $product_meta->get_tax_status()) ? 'false': 'true';
                if($isQuickBooksUsAccount){
                    $metGetTaxable = $product_meta->get_taxable();
                    $qboTaxable = ('' == $metGetTaxable) ? $qboTaxable : $metGetTaxable;
                    if('false' == $qboTaxable){
                        $productTaxStatus =  'none';
                        $taxId =  '';
                    }
                }
                $orderLineTax = $orderLineItem->get_line_tax();
                if (empty($orderLineTax) || ('none' == $productTaxStatus && empty($taxId))) {
                    $taxName = null;
                    $taxId = null;
                    $taxRate = null;
                    $taxValue = null;
                }


                //Woocommerce line tax
                $taxValue = $orderLineTax;

                //If there is no tax setup on this line item then tax details to null
                if(empty($orderLineTax)){
                    $taxName = null;
                    $taxId = null;
                    $taxRate = null;
                    $taxValue = null;
                }

                $products[] = array(
                    'id' => get_qbo_id($product_meta->get_product_id()),
                    'sku' => $pro_object->get_sku(),
                    'title' => LS_QBO_Product_Helper::prepareProductNameForQuickBooks($pro_object->get_title()),
                    'price' => $price,
                    'quantity' => $item['qty'],
                    'discountAmount' => $discount,
                    'taxable' => $qboTaxable,
                    'taxName' => $taxName,
                    'taxId' => $taxId,
                    'taxRate' => $taxRate,
                    'taxValue' => $taxValue,
                    'discountTitle' => '',
                );
                //Calculate total discount
                $total_discount += $discount;
            }

        }

        //set the total order discount to send
        if (!empty($total_discount)) {
            $products[] = array(
                'title' => 'discount',
                'price' => $total_discount,
                'sku' => "discount"
            );
        }

        $site_admin_email = LS_QBO()->options()->get_current_admin_email();
        /**
         * Sets the default primary email in case if empty
         */
        $primary_email = !empty($site_admin_email) ? $site_admin_email : 'woocommerce_sale@linksync.com';
        $export_types = $order_option->get_all_export_type();
        if ($export_types[0] == $order_option->customer_export()) {

            $phone = !empty($_POST['_billing_phone']) ? $_POST['_billing_phone'] : $orderHelper->getBillingPhone();
            // Formatted Addresses
            $filtered_billing_address = apply_filters('woocommerce_order_formatted_billing_address', array(
                'firstName' => !empty($_POST['_billing_first_name']) ? $_POST['_billing_first_name'] : $orderHelper->getBillingFirsName(),
                'lastName' => !empty($_POST['_billing_last_name']) ? $_POST['_billing_last_name'] : $orderHelper->getBillingLastName(),
                'phone' => $phone,
                'street1' => !empty($_POST['_billing_address_1']) ? $_POST['_billing_address_1'] : $orderHelper->getBillingAddressOne(),
                'street2' => !empty($_POST['_billing_address_2']) ? $_POST['_billing_address_2'] : $orderHelper->getBillingAddressTwo(),
                'city' => !empty($_POST['_billing_city']) ? $_POST['_billing_city'] : $orderHelper->getBillingCity(),
                'state' => !empty($_POST['_billing_state']) ? $_POST['_billing_state'] : $orderHelper->getBillingState(),
                'postalCode' => !empty($_POST['_billing_postcode']) ? $_POST['_billing_postcode'] : $orderHelper->getBillingPostcode(),
                'country' => !empty($_POST['_billing_country']) ? $_POST['_billing_country'] : $orderHelper->getBillingCountry(),
                'company' => !empty($_POST['_billing_company']) ? $_POST['_billing_company'] : $orderHelper->getBillingCompany(),
                'email_address' => !empty($_POST['_billing_email']) ? $_POST['_billing_email'] : $orderHelper->getBillingEmail()
            ), $order);


            $billing_address = array(
                'firstName' => !empty($filtered_billing_address['firstName']) ? $filtered_billing_address['firstName'] : 'WooCommerce',
                'lastName' => !empty($filtered_billing_address['lastName']) ? $filtered_billing_address['lastName'] : 'Sale',
                'phone' => $filtered_billing_address['phone'],
                'street1' => $filtered_billing_address['street1'],
                'street2' => $filtered_billing_address['street2'],
                'city' => $filtered_billing_address['city'],
                'state' => $filtered_billing_address['state'],
                'postalCode' => $filtered_billing_address['postalCode'],
                'country' => $filtered_billing_address['country'],
                'company' => $filtered_billing_address['company'],
                'email_address' => !empty($filtered_billing_address['email_address']) ? $filtered_billing_address['email_address'] : $primary_email
            );

            $filtered_shipping_address = apply_filters('woocommerce_order_formatted_shipping_address', array(
                'firstName' => !empty($_POST['_shipping_first_name']) ? $_POST['_shipping_first_name'] : $orderHelper->getShippingFirstName(),
                'lastName' => !empty($_POST['_shipping_last_name']) ? $_POST['_shipping_last_name'] : $orderHelper->getShippingLastName(),
                'phone' => $phone,
                'street1' => !empty($_POST['_shipping_address_1']) ? $_POST['_shipping_address_1'] : $orderHelper->getShippingAddressOne(),
                'street2' => !empty($_POST['_shipping_address_2']) ? $_POST['_shipping_address_2'] : $orderHelper->getShippingAddressTwo(),
                'city' => !empty($_POST['_shipping_city']) ? $_POST['_shipping_city'] : $orderHelper->getShippingCity(),
                'state' => !empty($_POST['_shipping_state']) ? $_POST['_shipping_state'] : $orderHelper->getShippingState(),
                'postalCode' => !empty($_POST['_shipping_postcode']) ? $_POST['_shipping_postcode'] : $orderHelper->getShippingPostCode(),
                'country' => !empty($_POST['_shipping_country']) ? $_POST['_shipping_country'] : $orderHelper->getShippingCountry(),
                'company' => !empty($_POST['_shipping_company']) ? $_POST['_shipping_company'] : $orderHelper->getShippingCompany(),
            ), $order);

            $delivery_address = array(
                'firstName' => $filtered_shipping_address['firstName'],
                'lastName' => $filtered_shipping_address['lastName'],
                'phone' => $filtered_shipping_address['phone'],
                'street1' => $filtered_shipping_address['street1'],
                'street2' => $filtered_shipping_address['street2'],
                'city' => $filtered_shipping_address['city'],
                'state' => $filtered_shipping_address['state'],
                'postalCode' => $filtered_shipping_address['postalCode'],
                'country' => $filtered_shipping_address['country'],
                'company' => $filtered_shipping_address['company']
            );

            $primary_email = !empty($billing_address['email_address']) ? $billing_address['email_address'] : $primary_email;
        } else if ($export_types[1] == $order_option->customer_export()) {

            $billing_address = array(
                'firstName' => 'WooCommerce',
                'lastName' => 'Sale',
                'phone' => null,
                'street1' => null,
                'street2' => null,
                'city' => null,
                'state' => null,
                'postalCode' => null,
                'country' => null,
                'company' => null,
                'email_address' => $primary_email
            );

        }

        //UTC Time
        date_default_timezone_set("UTC");
        $order_created = date("Y-m-d H:i:s", time());

        $order_no = $order->get_order_number();
        if (strpos($order_no, '#') !== false) {
            $order_no = str_replace('#', '', $order_no);
        }

        $source = 'WooCommerce';
        $comments = $source . ' Order: ' . $order_no;
        $customer_notes = $orderHelper->getCustomerNotes(); 
        $comments = $customer_notes."\n\n".$comments;

        if (!empty($shipping_method)) {

            $qbo_tax = LS_Woo_Tax::get_mapped_quickbooks_tax_for_shipping($tax_mapping, $order_tax);
            $shipping_cost = $orderHelper->getShippingTotal();
            $shipping_tax = $order->get_shipping_tax();
            if(empty($shipping_tax) && isset($qbo_tax[0]) && 'no_tax' ==$qbo_tax[0]){
                $qbo_tax[0] = null;
                $qbo_tax[1] = null;
                $qbo_tax[3] = null;
            }
            $shippingQboTaxId = ('' == $qbo_tax) ? null : (isset($qbo_tax[0])) ? $qbo_tax[0] : null;

            $shippingProductLineItem = array(
                "price" => isset($shipping_cost) ? $shipping_cost : null,
                "quantity" => 1,
                "sku" => "shipping",
                'taxName' => ('' == $qbo_tax) ? null : (isset($qbo_tax[1])) ? $qbo_tax[1] : null,
                'taxId' => $shippingQboTaxId,
                'taxRate' => ('' == $qbo_tax) ? null : (isset($qbo_tax[3])) ? $qbo_tax[3] : null,
                'taxValue' => $shipping_tax
            );

            if ($isQuickBooksUsAccount && !empty($shipping_tax)) {
                $shippingProductLineItem = LS_Woo_Product::createQboShippingTaxProduct($shippingProductLineItem);
            }

            if (!empty($shippingProductLineItem)) {
                $products[] = $shippingProductLineItem;
            }
        }

        $products = !empty($products) ? $products : null;
        $billing_address = !empty($billing_address) ? $billing_address : null;
        $delivery_address = !empty($delivery_address) ? $delivery_address : null;

        $order_type = 'Invoice';
        $receipt_types = $order_option->get_all_receipt_type();
        $order_total = $order->get_total();

        if ($order_option->receipt_type() == $receipt_types[0]) {
            $order_type = 'SalesReceipt';

            $payment_method = $order_option->payment_method();
            $order_transaction_id = ('' == $order->get_transaction_id()) ? null : $order->get_transaction_id();
            $order_payment_method = $orderHelper->getPaymentMethod();
            $qbo_payment_method_id = null;
            if (!empty($order_payment_method)) {
                $payment = array();

                if ('mapped_payment_method' == $payment_method) {
                    //If 'Map payment methods' is selected

                    $selected_payment_method = $order_option->selected_mapped_payment_method();
                    if (isset($selected_payment_method[$order_payment_method])) {
                        $qbo_payment_method = explode("|", $selected_payment_method[$order_payment_method]);
                        $qbo_payment_method_id = isset($qbo_payment_method[0]) ? $qbo_payment_method[0] : null;
                        $payment = array(
                            'retailer_payment_type_id' => $qbo_payment_method_id,
                            'amount' => isset($order_total) ? $order_total : 0,
                            'method' => isset($qbo_payment_method[1]) ? $qbo_payment_method[1] : null,
                            'transactionNumber' => $order_transaction_id
                        );
                    }
                } elseif ('woo_order_payment_method' == $payment_method) {
                    // If 'Send WooCommerce order payment method to QBO' is selected

                    $payment = array(
                        'retailer_payment_type_id' => null,
                        'amount' => isset($order_total) ? $order_total : 0,
                        'method' => isset($order_payment_method) ? $order_payment_method : null,
                        'transactionNumber' => $order_transaction_id
                    );
                }

                $payment['deposit_accountref'] = $order_option->deposit_account();
                $json_order->set_payment($payment);
            }

            //Set payment_type_id
            $json_order->set_payment_type_id($qbo_payment_method_id);
        }


        $json_order->set_uid(null);
        $json_order->set_orderId($order_no);
        $json_order->set_idSource($orderHelper->getId());
        $json_order->set_orderType($order_type);
        $json_order->set_created($order_created);
        $json_order->set_source($source);
        $json_order->set_primary_email($primary_email);
        $json_order->set_total($order->get_total());
        $json_order->set_taxes_included($included_tax);
        $json_order->set_global_tax_calculation($globalTaxCalculation);
        $json_order->set_comments($comments);
        $json_order->set_register_id(null);
        $json_order->set_user_name(null);

        $json_order->set_products($products);
        $json_order->set_billingAddress($billing_address);
        $json_order->set_deliveryAddress($delivery_address);

        if('on' == $order_option->location_status()){
            $json_order->set_location_id($order_option->selected_location());
        }

        if('on' == $order_option->class_status()){
            $json_order->set_class_id($order_option->selected_order_class());
        }

        $order_json_data = $json_order->get_json_orders();
        $post_order = LS_QBO()->api()->order()->save_orders($order_json_data);

        if (!empty($post_order['id'])) {
            $note = sprintf(__('Order exported to QBO: %s', 'woocommerce'), $order_no);
            $order->add_order_note($note);
            LS_QBO_Order_Helper::deleteOrderSyncingError($order_id);
            LSC_Log::add_dev_success('LS_QBO_Sync::import_single_order_to_qbo', 'Woo Order ID: ' . $order_id . '<br/><br/>Json order being sent: ' . $order_json_data . '<br/><br/> Response: ' . json_encode($post_order));
        } else {
            LS_QBO_Order_Helper::updateOrderSyncingError($order_id, $post_order);
            LSC_Log::add_dev_failed('LS_QBO_Sync::import_single_order_to_qbo', 'Woo Order ID: ' . $order_id . '<br/><br/>Json order being sent: ' . $order_json_data . '<br/><br/> Response: ' . json_encode($post_order));
        }

    }


    /**
     * Importing all products to Woocommerce from page one to the last page.
     * @param $page page number of the products
     */
    public static function product_to_woo($page = 1)
    {
        $products = LS_QBO()->api()->product()->get_product_by_page($page);

        if (!empty($products['products'])) {
            foreach ($products['products'] as $product) {

                $product = new LS_Simple_Product($product);
                self::import_single_product_to_woo($product);

            }
        }

        if ($products['pagination']['page'] <= $products['pagination']['pages']) {

            $page = $products['pagination']['page'] + 1;
            if ($page <= $products['pagination']['pages']) {
                self::product_to_woo($page);
            }
        }
        return $products;
    }

    public static function remove_action_save_post()
    {
        $product_options = LS_QBO()->product_option();
        $current_sync_option = $product_options->get_current_product_syncing_settings();

        if ('two_way' == $current_sync_option['sync_type']) {
            remove_action('save_post', array('LS_QBO_Sync', 'save_product'), 999);
            remove_action('woocommerce_save_product_variation', array('LS_QBO_Sync', 'import_single_product_to_qbo'), 999);
        }
    }

    public static function add_action_save_post()
    {
        $product_options = LS_QBO()->product_option();
        $current_sync_option = $product_options->get_current_product_syncing_settings();

        if ('two_way' == $current_sync_option['sync_type']) {
            add_action('save_post', array('LS_QBO_Sync', 'save_product'), 999, 3);
            add_action('woocommerce_save_product_variation', array('LS_QBO_Sync', 'import_single_product_to_qbo'), 999, 1);
        }
    }

    /**
     * Importing products from qbo to Woocommerce since last update from page one to the last page
     * @param $page
     * @return array|null
     */
    public static function product_to_woo_since_last_update($page = 1)
    {
        $last_sync = LS_QBO()->options()->last_product_update();
        $params = array(
            'page' => $page,
            'since' => $last_sync
        );
        $products = LS_QBO()->api()->product()->get_product($params);
        LSC_Log::add_dev_success(
            'LS_QBO_Sync::product_to_woo_since_last_update',
            'Parameters being used: ' . json_encode($params) . '<br/>Product Get Response: <br/>' . json_encode($products)
        );
        if (!empty($products['products'])) {

            foreach ($products['products'] as $product) {

                $product = new LS_Simple_Product($product);
                self::import_single_product_to_woo($product);

            }
        }

        if ($products['pagination']['page'] <= $products['pagination']['pages']) {

            $page = $products['pagination']['page'] + 1;
            if ($page <= $products['pagination']['pages']) {
                self::product_to_woo_since_last_update($page);
            }
        }
        return $products;
    }

    /**
     * syncing part will happen for each single product
     *
     * Woocommerce WC_Meta_Box_Product_Data::save(posid,post) handles product meta saving
     */
    public static function import_single_product_to_woo($product)
    {


        //Make sure its the instance of LS_Simple_Product class
        if (!$product instanceof LS_Simple_Product) {
            $product = new LS_Simple_Product($product);
        }

        $product_options = LS_QBO()->product_option();
        $current_sync_option = $product_options->get_current_product_syncing_settings();

        if ('disabled' == $current_sync_option['sync_type']) {
            //return if sync type is disabled
            return;
        }
        if('shipping_with_tax' == $product->get_sku()){
            //Save or update the details to qbo shipping product to the wordpress option to use it later
            LS_QBO()->options()->updateShippingProductWithTax(json_encode($product->product));

            //Do not create this product in woocommerce if the sku is shipping_with_tax
            return;
        }

        if ('shipping' == $product->get_sku()) {
            //Do not create this product in woocommerce if the sku is shipping
            return;
        }

        $match_with = $current_sync_option['match_product_with'];

        $product_id = null;
        if ('name' == $match_with) {
            $product_name = $product->get_name();
            if ('on' == $current_sync_option['delete']) {
                $product_name = trim($product_name);
                $str_delete = substr($product_name, -9);
                if ('(deleted)' === $str_delete) {
                    $product_name = rtrim($product_name, '(deleted)');
                }
            }
            $product_name = remove_escaping_str($product_name);
            $product_id = LS_Woo_Product::get_product_id_by_name($product_name);

        } else if ('sku' == $match_with) {
            $product_id = LS_Woo_Product::get_product_id_by_sku($product->get_sku());
        }

        //Last Check if the product exist in woocommerce to attempt query product id using quickbooks id
        if (empty($product_id)) {
            $qboId = get_qbo_id($product->get_id());
            $product_id = LS_Woo_Product::get_product_id_by_quickbooks_id($qboId);
        }

        $deleted = false;
        $product_deleted = $product->get_deleted_at();
        $product_active = $product->is_active();
        if (0 == $product_active) {

            if ('on' == $current_sync_option['delete'] && !empty($product_id)) {
                $deleted = (false != wp_delete_post($product_id, true)) ? true : false;
            }
            $deleted = true;

        }

        // If it has been deleted in qbo there is no point in creating or updating it to woocommerce
        if (false == $deleted) {
            remove_all_actions('save_post');
            remove_action('pre_post_update', 'wp_save_post_revision');
            //$product_id will not be empty if the product exists
            if (!empty($product_id)) {

                //Get the product meta object for product
                $product_meta = new LS_Product_Meta($product_id);

                LS_Woo_Product::update_woo_product_using_qbo_product(
                    $current_sync_option,
                    $product,
                    $product_meta
                );

                //Enable back the revision for other plugin to still use it
                add_action('pre_post_update', 'wp_save_post_revision');
                LS_QBO_Sync::add_action_save_post();


            } else if (empty($product_id)) {

                //product was not found therefore check if create new was on and create the product
                if ('on' == $current_sync_option['create_new']) {
                    $product_description = $product->get_description();
                    //Create the product array
                    $product_args['post_title'] = $product->get_name();
                    $product_args['post_content'] = empty($product_description) ? '&nbsp' : html_entity_decode($product_description);

                    $product_id = LS_Woo_Product::create($product_args, true);
                }

                //Product was created
                if (!empty($product_id)) {

                    //Get the product meta object for product
                    $product_meta = new LS_Product_Meta($product_id);
                    LS_Woo_Product::update_woo_product_using_qbo_product(
                        $current_sync_option,
                        $product,
                        $product_meta,
                        true
                    );

                    //Enable back the revision for other plugin to still use it
                    add_action('pre_post_update', 'wp_save_post_revision');
                    LS_QBO_Sync::add_action_save_post();

                }
            }
        }

        //set last sync to the current UTC time
        LS_QBO()->options()->last_product_update($product->get_update_at());
    }

    /**
     * Delete product in the api database
     * @param $product_id
     */
    public static function delete_product($product_id)
    {
        $product = wc_get_product($product_id);
        $productHelper = new LS_Product_Helper($product);
        $product_type = $productHelper->getType();

        if (LS_QBO_Product_Helper::isSyncAbleToQuickBooks($product_type)) {
            $sku = $productHelper->getSku();
            if (!empty($sku)) {
                $delete = LS_QBO()->api()->product()->delete_product($sku);
                if (!empty($delete)) {
                    LSC_Log::add_dev_success('LS_QBO_Sync::delete_product', 'Woo Product id: ' . $product_id . ' <br/><br/>Response from server: ' . json_encode($delete));
                }
            }
        }

    }

    /**
     * Returns all the woocommerce product ids
     */
    public static function woo_get_products_callback()
    {
        wp_send_json(LS_Woo_Product::get_product_ids());
    }

    /**
     * For getting product using AJAX
     * Get the product by page
     * @param page $_POST ['page]
     * @param action $_POST ['qbo_get_products']
     */
    public static function qbo_get_products_callback()
    {

        $page = isset($_POST['page']) ? $_POST['page'] : 1;
        $products = LS_QBO()->api()->product()->get_product_by_page($page);
        wp_send_json($products);

    }

    public static function qbo_get_products_since_last_update()
    {

        $page = isset($_POST['page']) ? $_POST['page'] : 1;
        $last_sync = LS_QBO()->options()->last_product_update();
        $products = LS_QBO()->api()->product()->get_product(array(
            'page' => $page,
            'since' => $last_sync
        ));

        wp_send_json($products);
    }

    /**
     * Importing products using AJAX
     * @param json $_POST ['product']
     * @param action $_POST ['import_to_woo']
     */
    public static function import_qbo_product_to_woo()
    {

        if (!empty($_POST['product'])) {
            $deleted_product = (isset($_POST['deleted_product']) && is_numeric($_POST['deleted_product'])) ? $_POST['deleted_product']: 0;
            $total_product = (isset($_POST['product_total_count']) && is_numeric($_POST['product_total_count'])) ? $_POST['product_total_count']: 0;

            $product_total_count = (int)$total_product - (int)$deleted_product;

            $product = new LS_Simple_Product($_POST['product']);
            self::import_single_product_to_woo($product);

            $product_number = $_POST['product_number'];
            $product_number = ($product_number > $product_total_count) ? $product_total_count : $product_number;
            $msg = $product_number . " of " . $product_total_count . " Product(s)";

            $progressValue = round(($product_number / $product_total_count) * 100);

            $response = array(
                'msg' => $msg,
                'percentage' => $progressValue
            );
            wp_send_json($response);
        }
    }

    public static function import_woo_product_to_qbo()
    {

        if (!empty($_POST['p_id'])) {
            LS_QBO_Sync::import_single_product_to_qbo($_POST['p_id']);
            $product_number = isset($_POST['product_number']) ? $_POST['product_number'] : 0;
            $product_total_count = isset($_POST['total_count']) ? $_POST['total_count'] : 0;
            $product_number = ($product_number > $product_total_count) ? $product_total_count : $product_number;
            $progressValue = round(($product_number / $product_total_count) * 100);
            $msg = $product_number . " of " . $product_total_count . " Product(s)";

            $response = array(
                'msg' => $msg,
                'percentage' => $progressValue
            );
            wp_send_json($response);
        }
    }

}

new LS_QBO_Sync();