<?php if (!defined('ABSPATH')) exit('Access is Denied');

class LS_QBO_Laid
{

    public function getApi($apiKey = null)
    {
        $apiConfig = $this->getConfig();
        if (null == $apiKey) {
            $apiKey = $this->getCurrentLaid();
        }
        return new LS_Api($apiConfig, $apiKey);
    }

    /**
     * Return the current laid key
     * @param string $default
     * @return mixed|void
     */
    public function getCurrentLaid($default = '')
    {
        return get_option('linksync_laid', $default);
    }

    public function updateCurrentLaid($value)
    {
        return update_option('linksync_laid', $value);
    }


    public function getCurrentLaidInfo($default = '')
    {
        return get_option('linksync_laid_info', $default);
    }

    public function updateCurrentLaidInfo($laid_info)
    {
        return update_option('linksync_laid_info', $laid_info);
    }

    /**
     * Get Key information
     * @param string $laid_key Linksync API Key http://developer.linksync.com/linksync-api-key-laid
     *
     * @return array LAID key information
     */
    public function getLaidInfo($laid_key = null){
        if( empty($laid_key)){
            $laid_key = $this->getCurrentLaid();
        }
        /**
         * Require api Config
         */
        $apiConfig = $this->getConfig();
        $api = new LS_Api( $apiConfig, $laid_key );

        return $api->get('laid');

    }

    /**
     * Return the previous laid key being used
     * @param string $default
     * @return mixed|void
     */
    public static function getPreviousLaid($default = '')
    {
        return get_option('linksync_previous_laid', $default);
    }


    /**
     * Update previouse laid key being used
     * @param $laid
     * @return bool
     */
    public function updatePreviousLaid($laid)
    {
        return update_option('linksync_previous_laid', trim($laid));
    }


    public function isNew($laid)
    {
        $current_laid = $this->getCurrentLaid();

        if ($laid != $current_laid) {
            return true;
        }

        return false;
    }

    public function updateLaid($laid)
    {

        $return = array();

        $is_new = $this->isNew($laid);

        $return['is_new'] = $is_new;
        if ($is_new) {
            $current_laid = $this->getCurrentLaid();

            $this->updateCurrentLaid($laid);
            $return['current_laid'] = $laid;

            $this->updatePreviousLaid($current_laid);
            $return['previous_laid'] = $current_laid;
        }

        return $return;
    }

    public function updateLaidKeyOptions(array $options)
    {
        $apiConfig = $this->getConfig();
        $this->updateCurrentLaid(isset($options['linksync_laid']) ? $options['linksync_laid'] : '');
        update_option('linksync_last_test_time', isset($options['linksync_last_test_time']) ?: current_time('mysql'));
        update_option('linksync_status', $options['linksync_status']);
        update_option('linksync_connected_url', $apiConfig['url']);
        update_option('linksync_frequency', isset($options['linksync_frequency']) ? $options['linksync_frequency'] : '');
        update_option('linksync_connectedto', isset($options['linksync_connectedto']) ? $options['linksync_connectedto'] : '');
        update_option('linksync_connectionwith', isset($options['linksync_connectionwith']) ? $options['linksync_connectionwith'] : '');
    }

    /**
     * Update the current Laid key connection if its connected or invalid
     *
     * @param null|string $laid LAID key or the Api key used in connecting to Linksync Server
     * @return array|null|string
     */
    public function checkApiKey($laid = null)
    {
        //if laid is null then get the current laid key connection to check its validity
        $laid_key = (null == $laid) ? $this->getCurrentLaid() : $laid;

        if (empty($laid_key)) {
            return array(
                'errorCode' => 400,
                'error_message' => 'API Key is Empty'
            );
        }

        $current_laid_key_info = $this->getLaidInfo($laid_key);
        $laid_connection['lws_laid_key_info'] = $current_laid_key_info;

        if (!empty($current_laid_key_info['errorCode'])) {

            $laid_key_options = array(
                'linksync_laid' => $laid_key,
                'linksync_status' => 'Inactive',
                'linksync_frequency' => $current_laid_key_info['userMessage']
            );
            if (!$this->isNew($laid_key)) {
                $this->updateLaidKeyOptions($laid_key_options);
            }


            $laid_connection['errorCode'] = $current_laid_key_info['errorCode'];
            $laid_connection['error_message'] = $current_laid_key_info['userMessage'];

            return $laid_connection;

        } else {
            $connected_to = $this->getConnectedApp($current_laid_key_info['connected_app']);
            $connected_with = $this->getConnectedApp($current_laid_key_info['app']);
            $laid_connection = array();
            $laid_connection['connected_to'] = $connected_to;
            $laid_connection['connected_with'] = $connected_with;

            if (
                ('QuickBooks Online' == $connected_to || 'QuickBooks Online' == $connected_with) &&
                ('WooCommerce' == $connected_to || 'WooCommerce' == $connected_with)
            ) {
                set_time_limit(0);
                $laid_connection['laid_update'] = $this->updateLaid($laid_key);
                $qbo_api = LS_QBO()->api();
                $connected_to = 'QuickBooks Online';
                $connected_with = 'WooCommerce';
                $this->updateLaidKeyOptions(array(
                    'linksync_laid' => $laid_key,
                    'linksync_status' => 'Active',
                    'linksync_frequency' => !empty($current_laid_key_info['message']) ? $current_laid_key_info['message'] : '',
                    'linksync_connectedto' => $connected_to,
                    'linksync_connectionwith' => $connected_with
                ));

                if (isset($laid_connection['laid_update']['is_new']) && $laid_connection['laid_update']['is_new']) {
                    $product_options = LS_QBO()->product_option();
                    $accounts_error = '';

                    $product_options->delete_expense_account();
                    $product_options->delete_income_account();
                    $product_options->delete_inventory_asset_account();

                    $assetAccounts = $qbo_api->get_assets_accounts();
                    if (!empty($assetAccounts[0]['id'])) {
                        $product_options->update_inventory_asset_account($assetAccounts[0]['id']);
                    } elseif (empty($assetAccounts[0]['id'])) {
                        $accounts_error .= 'Please check your QuickBooks Inventory Asset Account to sync products properly.<br/>';
                    }

                    $expenseAccounts = $qbo_api->get_expense_accounts();
                    if (!empty($expenseAccounts[0]['id'])) {
                        $product_options->update_expense_account($expenseAccounts[0]['id']);
                    } elseif (empty($expenseAccounts[0]['id'])) {
                        $accounts_error .= 'Please check your QuickBooks Expense Account to sync products properly.<br/>';
                    }

                    $incomeAccounts = $qbo_api->get_income_accounts();
                    if (!empty($incomeAccounts[0]['id'])) {
                        $product_options->update_income_account($incomeAccounts[0]['id']);
                    } elseif (empty($incomeAccounts[0]['id'])) {
                        $accounts_error .= 'Please check your QuickBooks Income Account to sync products properly.<br/>';
                    }

                    $require_resync = 'You have added or changed your API key, please configure your product syncing settings and resync your products';
                    LS_QBO()->options()->require_syncing($require_resync);

                    if (!empty($accounts_error)) {
                        LS_QBO()->options()->set_accounts_error_message($accounts_error);
                    }

                    LS_Woo_Product::deleteQuickBookDatas();
                }

                $updateWebHook = $this->updateWebHookConnection();
                $laid_connection['webhook_response'] = $updateWebHook;
                if (isset($updateWebHook['errorCode'])) {
                    $laid_connection['errorCode'] = $updateWebHook['errorCode'];
                }

                if (isset($updateWebHook['userMessage'])) {
                    $laid_connection['userMessage'] = $updateWebHook['userMessage'];
                    $laid_connection['error_message'] = $updateWebHook['userMessage'];
                }


                $qbo_api->get_all_tax_rate(); // send request to qbo/tax api to create zero tax rate

            } else {
                $laid_connection['errorCode'] = 400;
                $laid_connection['error_message'] = 'Invalid API key';
            }
            return $laid_connection;
        }

        return null;

    }

    /**
     * Get Linksync config
     */
    public function getConfig()
    {
        /**
         * Get the configuration or the selection of api config.
         */
        $config = require(LS_PLUGIN_DIR . 'ls-api-config.php');

        /**
         * Check if test mode is set to true
         */
        if ($config['testmode']) {
            $config['api'] = 'test';
            update_option('linksync_test', 'on');
        } else {
            update_option('linksync_test', 'off');
        }

        /**
         * Require api information
         */
        $apiConfig = require(LS_INC_DIR . 'api/ls-api-info.php');

        return $apiConfig[$config['api']];
    }

    /**
     * @return array of possible app connection
     */
    public function getApps()
    {
        return array(
            '4' => 'Xero',
            '7' => 'MYOB RetailManager',
            '8' => 'Saasu',
            '13' => 'WooCommerce',
            '15' => 'QuickBooks Online',
            '18' => 'Vend'
        );
    }

    public function updateWebHookConnection($web_hook_data = null)
    {

        $url = Linksync_QuickBooks::getWebHookUrl();
        $webhookURL = isset($web_hook_data['url']) ? $web_hook_data['url'] : $url;

        $laid = null;
        if (!empty($web_hook_data['laid_key'])) {
            $laid = $web_hook_data['laid_key'];
        }
        $web_hook_data = array(
            "url" => $webhookURL,
            "version" => Linksync_QuickBooks::$version,
            "order_import" => isset($web_hook_data['order_import']) ? $web_hook_data['order_import'] : 'yes',
            "product_import" => isset($web_hook_data['product_import']) ? $web_hook_data['product_import'] : 'yes'
        );
        return $this->getApi($laid)->post('laid', json_encode($web_hook_data));
    }


    /**
     * Get connected Application
     * @param int get the connected application name based on its id
     * @return string
     */
    public function getConnectedApp($app){
        $apps = $this->getApps();

        if(is_numeric($app) && array_key_exists($app, $apps)) {
            return $apps[$app];
        }

        return false;

    }

    public function generate_code($length = 6)
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }
}

