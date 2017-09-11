<?php if (!defined('ABSPATH')) exit('Access is Denied');


if (!class_exists('LS_QBO')) {

    /**
     * Class LS_QBO This class serves as the entry point for linksync for QuickBooks Online
     */
    class LS_QBO
    {

        /**
         * @var LS_QBO instance
         */
        protected static $_instance = null;

        public static $api = null;
        private static $laid = null;
        public static $slug = 'linksync-qbo';

        /**
         * LS_QBO constructor.
         */
        public function __construct()
        {
            $this->includes();
            do_action('ls_qbo_loaded');
        }

        /**
         * This method is intended to run linksync QuickBooks Online plugin after including files
         * to avoid code execution on including php files
         */
        public function run()
        {

            LS_QBO_Hook::init();
            $this->load_ajax_hooks();

            new LS_Description_Handler();
            new LS_Support_Helper();
            new LS_QBO_Sync();
            new LS_Notice();
            new LS_QBO_Ajax();
            new LS_QBO_Menu();

        }

        public function load_ajax_hooks()
        {
            add_action('wp_ajax_save_needed_ps_data_from_qbo', array($this, 'save_needed_ps_data_from_qbo'));
            add_action('wp_ajax_show_ps_view', array(LS_QBO_Product_Form::instance(), 'product_syncing_settings'));

            add_action('wp_ajax_save_needed_os_data_from_qbo', array($this, 'save_needed_os_data_from_qbo'));
            add_action('wp_ajax_show_or_view', array(LS_QBO_Order_Form::instance(), 'order_syncing_settings'));

            add_action('wp_ajax_get_laid_info', array($this, 'get_qbo_laid_info'));
        }

        public function isLaidVersion11()
        {
            $currentLaidInfo = LS_QBO()->get_laid_info();

            if (!empty($currentLaidInfo['app_version']) && '1.1' == $currentLaidInfo['app_version']) {
                return true;
            }

            return false;

        }

        /**
         * get the current laid info from LWS api
         */
        public function get_qbo_laid_info()
        {
            $qbo_api = LS_QBO()->api();
            update_option('ls_laid_info', $qbo_api->get_laid_info());
            wp_send_json(get_option('ls_laid_info'));
        }

        /**
         * Returns Current Laid information
         * @return mixed|void
         */
        public function get_laid_info()
        {
            return get_option('ls_laid_info');
        }

        /**
         * returns string UTC time of the last syncing
         * @return string
         */
        public function get_last_sync()
        {
            return get_option('ls_last_sync');
        }

        /**
         * returns string UTC time of the last syncing
         * @return string
         */
        public function update_last_sync()
        {
            $laid_info = LS_QBO()->get_laid_info();
            update_option('ls_last_sync', $laid_info['time']);
            return get_last_sync();
        }

        /**
         * @param $account_name
         * @return array|null
         */
        public function get_expense_account_by($key, $value)
        {
            if (!empty($account_name)) {
                $expense_accounts = LS_QBO()->options()->getExpenseAccounts();
                if (!empty($expense_accounts)) {
                    return LS_QBO()->search_accounts($key, $value, $expense_accounts);
                }
            }

            return null;
        }

        /**
         * @param $account_name
         * @return array|null
         */
        public function get_inventory_asset_account_by($key, $value)
        {
            if (!empty($account_name)) {
                $inventory_asset_accounts = LS_QBO()->options()->getAssetAccounts();
                if (!empty($inventory_asset_accounts)) {
                    return LS_QBO()->search_accounts($key, $value, $inventory_asset_accounts);
                }
            }

            return null;
        }

        /**
         * @param $account_name
         * @return array|null
         */
        public function get_income_account_by($key, $value)
        {
            if (!empty($account_name)) {
                $income_accounts = LS_QBO()->options()->getIncomeAccounts();
                if (!empty($income_accounts)) {
                    return LS_QBO()->search_accounts($key, $value, $income_accounts);
                }
            }
            return null;
        }

        /**
         * Search Accounts array
         * @param $key
         * @param $value
         * @param $array
         * @return null|array
         */
        public function search_accounts($key, $value, $array)
        {
            $found = null;
            if (is_array($array)) {
                foreach ($array as $account) {
                    if ($account[$key] == $value) {
                        $found = $account;
                        break;
                    }
                }
            }
            return $found;
        }

        public function saveUserSettingsToLws()
        {
            $product_options = LS_QBO()->product_option();
            $order_options = LS_QBO()->order_option();

            $qboApi = LS_QBO()->api();
            $productOptions = $product_options->get_current_product_syncing_settings();
            $orderOptions = $order_options->get_current_order_syncing_settings();


            $userSettings['product_settings'] = $productOptions;
            $userSettings['order_settings'] = $orderOptions;
            $userSettings = json_encode($userSettings);
            $qboApi->save_users_settings($userSettings);
        }

        public function save_needed_ps_data_from_qbo()
        {
            $qbo_api = LS_QBO()->api();
            $product_options = LS_QBO()->product_option();

            set_time_limit(0);
            $qbo_info = $qbo_api->get_qbo_info();

            LS_QBO()->options()->updateQuickBooksInfo($qbo_info);
            LS_QBO()->options()->updateAssetAccounts($qbo_api->get_assets_accounts());
            LS_QBO()->options()->updateExpeseAccounts($qbo_api->get_expense_accounts());
            LS_QBO()->options()->updateIncomeAccounts($qbo_api->get_income_accounts());
            LS_QBO()->options()->update_tax_agencies($qbo_api->get_tax_agencies());
            $qbo_api->get_all_tax_rate(); // send request to qbo/tax api to create zero tax rate
            $taxDataToBeUsed = LS_Woo_Tax::getQuickBookTaxDataToBeUsed();
            LS_QBO()->options()->updateQuickBooksTaxClasses($taxDataToBeUsed);
            LS_QBO()->options()->updateQuickBooksDuplicateProducts($qbo_api->product()->get_duplicate_products());

            LS_QBO()->set_quantity_option_base_on_qboinfo($qbo_info);


            die();
        }

        public function save_needed_os_data_from_qbo()
        {
            $qbo_api = LS_QBO()->api();

            set_time_limit(0);
            $qbo_info = $qbo_api->get_qbo_info();
            $qbo_classes = $qbo_api->get_all_active_clases();
            $deposit_accounts = $qbo_api->getDepositAccounts();

            LS_QBO()->options()->update_deposit_accounts($deposit_accounts);
            LS_QBO()->options()->updateQuickBooksLocationList($qbo_api->get_all_active_location());
            LS_QBO()->options()->updateQuickBooksClasses($qbo_classes);
            LS_QBO()->options()->update_tax_agencies($qbo_api->get_tax_agencies());

            $qbo_api->get_all_tax_rate(); // send request to qbo/tax api to create zero tax rate
            $taxDataToBeUsed = LS_Woo_Tax::getQuickBookTaxDataToBeUsed();
            LS_QBO()->options()->updateQuickBooksTaxClasses($taxDataToBeUsed);
            LS_QBO()->options()->updateQuickBooksPaymentMethods($qbo_api->get_all_payment_methods());
            LS_QBO()->options()->updateQuickBooksInfo($qbo_info);
            LS_QBO()->set_quantity_option_base_on_qboinfo($qbo_info);


            die();
        }

        /**
         * Will return QBO subscription
         * @return null|string
         */
        public function get_subscription()
        {
            $qbo_info = LS_QBO()->options()->getQuickBooksInfo();
            if (!empty($qbo_info) && isset($qbo_info['version'])) {
                return $qbo_info['version'];
            }
            return null;
        }

        /**
         * Whether the subscription related to the laid key is 'QuickBooks Online Plus'
         * @return bool
         */
        public function is_qbo_plus()
        {
            $bool = false;

            $subscription = LS_QBO()->get_subscription();
            if (!empty($subscription)) {
                if ('QuickBooks Online Plus' == $subscription) {
                    $bool = true;
                }
            }
            return $bool;
        }

        public function get_update_url()
        {
            $url = admin_url('admin-ajax.php?action=' . get_option('webhook_url_code'));
            return $url;
        }

        /**
         * @param $qbo_info
         */
        public function set_quantity_option_base_on_qboinfo($qbo_info = null)
        {

            if ($qbo_info == null) {
                $qbo_info = LS_QBO()->options()->getQuickBooksInfo();
            }

            if (isset($qbo_info['version'])) {
                if ('QuickBooks Online Plus' != $qbo_info['version']) {

                    if (isset($qbo_info['trackInventory']) && $qbo_info['trackInventory'] == false) {
                        //Make sure to off this option if qbo version is not Quickbooks Onlie Plus (Docs 11.2.6 , 11.2.6.1)
                        LS_QBO()->product_option()->update_quantity('off');
                        LS_QBO()->product_option()->update_change_product_status('off');

                    }

                }
            }
        }

        /**
         * Returns whether the current user with LAID key can use Quantity Option base on QuickBooks Information
         * @param null $qbo_info
         * @return bool
         */
        public function can_use_quantity_option($qbo_info = null)
        {
            $bool = true;

            if ($qbo_info == null) {
                $qbo_info = LS_QBO()->options()->getQuickBooksInfo();
            }

            if (isset($qbo_info['version'])) {
                if ('QuickBooks Online Plus' != $qbo_info['version']) {

                    if (isset($qbo_info['trackInventory']) && $qbo_info['trackInventory'] == false) {
                        $bool = false;
                    }

                }
            }

            return $bool;
        }

        /**
         * LS_QBO get self instance
         */
        public static function instance()
        {
            if (is_null(self::$_instance)) {
                self::$_instance = new self();
            }

            return self::$_instance;
        }

        /**
         * Get QBO Product syncing option
         * @return LS_QBO_Product_Option instance
         */
        public function product_option()
        {
            return LS_QBO_Product_Option::instance();
        }

        /**
         * Get QBO Order syncing option
         * @return LS_QBO_Order_Option instance
         */
        public function order_option()
        {
            return LS_QBO_Order_Option::instance();
        }

        public function api()
        {

            if (is_null(self::$api)) {
                $lsApi = LS_QBO()->laid()->getApi();
                self::$api = new LS_QBO_Api($lsApi);
            }

            return self::$api;
        }

        public function laid()
        {
            if (is_null(self::$laid)) {
                self::$laid = new LS_QBO_Laid();
            }

            return self::$laid;
        }

        public function options()
        {
            return LS_QBO_Options::instance();
        }

        /**
         * Include required core files for QBO
         */
        public function includes()
        {


            include_once LS_INC_DIR . 'apps/qbo/classes/class-ls-qbo-laid.php';
            include_once LS_INC_DIR . 'apps/qbo/constants/class-ls-qbo-item-type.php';
            include_once LS_INC_DIR . 'apps/qbo/constants/class-ls-qbo-receipt-type.php';
            include_once LS_INC_DIR . 'apps/qbo/constants/class-ls-qbo-constant.php';
            include_once LS_INC_DIR . 'apps/ls-core-functions.php';
            include_once LS_INC_DIR . 'apps/class-ls-woo-tax.php';
            include_once LS_INC_DIR . 'apps/class-ls-woo-product.php';
            include_once LS_INC_DIR . 'apps/class-ls-woo-order-line-item.php';
            include_once LS_INC_DIR . 'apps/qbo/classes/class-ls-qbo-options.php';

            include_once LS_INC_DIR . 'api/ls-api.php';
            include_once LS_INC_DIR . 'apps/class-ls-product-api.php';
            include_once LS_INC_DIR . 'apps/class-ls-order-api.php';
            include_once LS_INC_DIR . 'apps/qbo/classes/class-ls-qbo-api.php';


            include_once LS_INC_DIR . 'apps/qbo/classes/class-ls-qbo-product-options.php';
            include_once LS_INC_DIR . 'apps/qbo/classes/class-ls-qbo-order-options.php';


            include_once LS_INC_DIR . 'apps/qbo/classes/class-ls-qbo-product-form.php';
            include_once LS_INC_DIR . 'apps/qbo/classes/class-ls-description-handler.php';
            include_once LS_INC_DIR . 'apps/qbo/classes/class-ls-qbo-order-form.php';

            include_once LS_INC_DIR . 'apps/class-ls-product-meta.php';
            include_once LS_INC_DIR . 'apps/class-ls-order-meta.php';
            include_once LS_INC_DIR . 'apps/class-ls-simple-product.php';
            include_once LS_INC_DIR . 'apps/class-ls-variant-product.php';

            include_once LS_INC_DIR . 'apps/class-ls-json-product-factory.php';
            include_once LS_INC_DIR . 'apps/class-ls-json-order-factory.php';

            include_once LS_INC_DIR . 'apps/class-ls-notice-message-builder.php';

            include_once LS_INC_DIR . 'apps/helpers/class-ls-constant.php';
            include_once LS_INC_DIR . 'apps/helpers/class-ls-user-helper.php';
            include_once LS_INC_DIR . 'apps/helpers/class-ls-support-helper.php';
            include_once LS_INC_DIR . 'apps/helpers/class-ls-helper.php';
            include_once LS_INC_DIR . 'apps/helpers/class-ls-order-helper.php';
            include_once LS_INC_DIR . 'apps/helpers/class-ls-product-helper.php';
            include_once LS_INC_DIR . 'apps/helpers/class-ls-message-builder.php';
            include_once LS_INC_DIR . 'apps/qbo/helpers/class-ls-qbo-helper.php';
            include_once LS_INC_DIR . 'apps/qbo/helpers/class-ls-qbo-product-helper.php';
            include_once LS_INC_DIR . 'apps/qbo/helpers/class-ls-qbo-order-helper.php';
            include_once LS_INC_DIR . 'apps/qbo/helpers/class-ls-qbo-log-helper.php';

            include_once LS_INC_DIR . 'apps/qbo/classes/class-ls-qbo-sync.php';
            include_once LS_INC_DIR . 'apps/qbo/classes/customize-column/class-ls-qbo-order-custom-column.php';
            include_once LS_INC_DIR . 'apps/qbo/classes/customize-column/class-ls-qbo-product-custom-column.php';
            include_once LS_INC_DIR . 'apps/class-ls-notice.php';
            include_once LS_INC_DIR . 'apps/qbo/classes/class-ls-qbo-ajax.php';
            require_once LS_INC_DIR . 'apps/qbo/classes/class-ls-qbo-menu.php';
            require_once LS_INC_DIR . 'apps/qbo/classes/class-ls-qbo-view.php';
            require_once LS_INC_DIR . 'apps/qbo/classes/class-ls-qbo-view-config-section.php';
            require_once LS_INC_DIR . 'apps/qbo/classes/class-ls-qbo-view-advance-section.php';
            require_once LS_INC_DIR . 'apps/qbo/classes/class-ls-qbo-notice.php';
            require_once LS_INC_DIR . 'apps/qbo/classes/class-ls-qbo-notice.php';
            include_once LS_INC_DIR . 'apps/qbo/classes/class-ls-qbo-scripts.php';
            include_once LS_INC_DIR . 'classes/class-ls-duplicate-sku-list.php';
            include_once LS_INC_DIR . 'classes/class-ls-modal.php';
            include_once LS_INC_DIR . 'apps/qbo/classes/list/class-ls-qbo-duplicate-sku-list.php';
            include_once LS_INC_DIR . 'apps/qbo/classes/list/class-ls-qbo-connected-product-list.php';
            include_once LS_INC_DIR . 'apps/qbo/classes/list/class-ls-qbo-connected-order-list.php';
            include_once LS_INC_DIR . 'apps/qbo/classes/class-ls-qbo-install.php';
            include_once LS_INC_DIR . 'apps/qbo/classes/class-ls-qbo-hooks.php';


        }

        /**
         * Show Quick books online views
         */
        public function view()
        {
            return new LS_QBO_View();
        }

        public function show_qbo_duplicate_products($duplicate_in = 'in_quickbooks_online')
        {
            $duplicate_where = '<a href="https://qbo.intuit.com/app/items" target="_blank">QuickBook Online</a>';
            if('in_quickbooks_online' != $duplicate_in){
                $duplicate_in = 'in_woocommerce';
                $duplicate_where = 'WooCommerce';
            }
            $duplcateSkuListLink = ' <a target="_blank" href="' . LS_QBO_Menu::linksync_page_menu_url('duplicate_sku&section='.$duplicate_in) . '" > Click here</a>';

            LS_Message_Builder::error('You have duplicate products or empty skus in your '.$duplicate_where.'.' . $duplcateSkuListLink . ' to view the list');
        }

        public function show_woo_duplicate_products($list = null, $emptySkus = array(), $duplicateSkus = array())
        {
            LS_Message_Builder::notice(LS_QBO_Helper::duplicate_sku_message());
        }

        public function show_shipping_and_discount_guide($user_options)
        {

            if (isset($user_options['qbo_info']['allowDiscount']) || isset($user_options['qbo_info']['allowShipping'])) {
                echo '<p> Please go to your
						<a target="_blank" href="https://sg.qbo.intuit.com/app/settings?p=Sales">QuickBooks Settings</a> ';
                if (!$user_options['qbo_info']['allowDiscount'] && !$user_options['qbo_info']['allowShipping']) {
                    echo 'and turn on <b>Discount</b> and <b>Shipping</b> options to use linksync.';
                } else if (!$user_options['qbo_info']['allowDiscount']) {
                    echo 'and turn on <b>Discount</b> option to use linksync.';
                } else if (!$user_options['qbo_info']['allowShipping']) {
                    echo 'and turn on <b>Shipping</b> option to use linksync.';
                }
                echo '</p>';
            } else if (isset($user_options['qbo_info']['errorCode'])) {
                echo '<p class="color-red"><b> Error ', $user_options['qbo_info']['errorCode'], ': ', $user_options['qbo_info']['userMessage'], '</b></p>';
            }


            if (empty($user_options['qbo_info'])) {
                echo '<p class="color-red"><b>QuickBooks Information associated with the api key being used is empty</b></p>';
            }

        }

        public function show_configure_tax_error()
        {
            $taxError =  'Please go to your <a target="_blank" href="https://sg.qbo.intuit.com/app/salestax">QuickBooks Tax Setttings</a> and configure your Tax Rates.';
            return $taxError;
        }

        public function isUsAccount()
        {
            $qboInformation = self::options()->getQuickBooksInfo();
            if (!empty($qboInformation['country']) && 'US' == $qboInformation['country']) {
                return true;
            }

            return false;
        }


        public function updateWebhookConnection()
        {
            $laid = LS_QBO()->laid()->getCurrentLaid();

            $webHookData['url'] = Linksync_QuickBooks::getWebHookUrl();
            $webHookData['version'] = Linksync_QuickBooks::$version;


            $orderImport = 'no';
            $webHookData['order_import'] = $orderImport;

            $productSyncType = LS_QBO()->product_option()->sync_type();
            $productImport = 'no';
            if ('two_way' == $productSyncType || 'qbo_to_woo' == $productSyncType) {
                $productImport = 'yes';
            }

            $webHookData['product_import'] = $productImport;
            $webHook = LS_QBO()->laid()->updateWebHookConnection($webHookData);

            if (!empty($webHook['result']) && $webHook['result'] == 'success') {
                LSC_Log::add('WebHookConnection', 'success', 'Connected to a file ' . $webHookData['url'], $laid);
                update_option('linksync_addedfile', '<a href="' . $webHookData['url'] . '">' . $webHookData['url'] . '</a>');

            } else {
                LSC_Log::add('WebHookConnection', 'fail', 'Cannot connect to ' . $webHookData['url'], $laid);
            }
            return $webHook;
        }

    }

}

if (!function_exists('LS_QBO')) {
    /**
     * Returns the main instance of LS_QBO to prevent the need to use globals.
     */
    function LS_QBO()
    {
        return LS_QBO::instance();
    }
}



