<?php
/*
  Plugin Name: linksync for WooCommerce
  Plugin URI: http://www.linksync.com/integrate/woocommerce
  Description:  WooCommerce extension for syncing inventory and order data with other apps, including Xero, QuickBooks Online, Vend, Saasu and other WooCommerce sites.
  Author: linksync
  Author URI: http://www.linksync.com
  Version: 2.5.16
 */

/*
 * To handle all languages
 */
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
mb_http_input('UTF-8');
mb_language('uni');
mb_regex_encoding('UTF-8');

class linksync
{

    /**
     * @var string
     */
    public static $version = '2.5.16';

    public function __construct()
    {
        $pluginBaseName = plugin_basename(__FILE__);

        add_action('plugins_loaded', array($this, 'check_required_plugins')); # In order to check WooCommerce Plugin existence
        $this->includes();
        add_action('admin_menu', array($this, 'linksync_add_menu'), 99); # To create custom menu in Wordpress Side Bar

        if(is_vend()){
            add_action('admin_notices', array($this, 'linksync_video_message'));
        }
        add_filter('plugin_action_links_' . $pluginBaseName, array($this, 'plugin_action_links'));



        add_action('init', array($this, 'wizard_process'), 1);
        Linksync_installation::init();
    }

    /**
     * Include Required files for Linksync
     */
    public function includes()
    {

        include_once 'ls-constants.php';
        include_once 'ls-functions.php';

        include_once LS_PLUGIN_DIR . 'plugin-update-checker/plugin-update-checker.php';

        include_once LS_INC_DIR . 'apps/ls-core-functions.php';
        include_once LS_INC_DIR . 'apps/class-ls-product-meta.php';

        include_once LS_INC_DIR . 'apps/class-ls-woo-tax.php';
        include_once LS_INC_DIR . 'apps/class-ls-woo-product.php';
        include_once LS_INC_DIR . 'apps/class-ls-woo-order-line-item.php';

        include_once LS_INC_DIR . 'apps/class-ls-simple-product.php';
        include_once LS_INC_DIR . 'apps/class-ls-variant-product.php';

        include_once LS_INC_DIR . 'apps/class-ls-json-product-factory.php';
        include_once LS_INC_DIR . 'apps/class-ls-json-order-factory.php';

        include_once LS_INC_DIR . 'api/ls-api.php';
        include_once LS_INC_DIR . 'apps/class-ls-product-api.php';
        include_once LS_INC_DIR . 'apps/class-ls-order-api.php';


        require_once LS_INC_DIR . 'apps/vend/ls-vend-log.php';
        require_once LS_INC_DIR . 'apps/vend/controllers/ls-log.php';


        require_once LS_INC_DIR . 'apps/qbo/qbo.php';

        include_once(LS_PLUGIN_DIR . 'classes/class.linksync-install.php');
        include_once(LS_PLUGIN_DIR . 'classes/wizard.php');
    }


    public function ls_custom_styles_and_scripts()
    {

    }

    /**
     * @return string Returns the web hook url of the plugin
     */
    public static function getWebHookUrl()
    {
        $webHookUrlCode = get_option('webhook_url_code');
        if (is_vend()) {
            //Used for Vend update url
            return plugins_url() . '/linksync/update.php?c=' . $webHookUrlCode;
        }

        //Used for QuickBooks update url
        $url = admin_url('admin-ajax.php?action=' . $webHookUrlCode);
        return $url;
    }


    public static function activate()
    {
        global $wpdb;
        $linksync = new linksync();
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        // WEBHOOK CONCEPT
        $webhook_url_code = linksync::linksync_autogenerate_code();
        add_option('webhook_url_code', $webhook_url_code);

        /**
         * Create table for logs
         */
        LSC_Log::instance()->create_table();

        add_option('linksync_do_activation_redirect', 'linksync-wizard');

    }

    public static function clearLogsDetails()
    {
        $fileName = dirname(__FILE__) . '/classes/raw-log.txt';
        if (file_exists($fileName)) {
            /**
             * @reference http://stackoverflow.com/questions/5650958/erase-all-data-from-txt-file-php?answertab=active#tab-top
             * @manual http://php.net/manual/en/function.fopen.php
             */
            $handle = fopen($fileName, "w+");
            if ($handle) {
                fclose($handle);
                LSC_Log::add('Daily Cron', 'Success', 'Older 10000 Lines Removed!', '-');
            }
        }
    }

    public function linksync_add_menu()
    {
        if (get_option('linksync_wooVersion') == 'off') {
            $wizard_page = add_submenu_page(null, 'linksync wizard', 'linksync wizard', 'manage_options', 'linksync-wizard', array($this, 'linksync_wizard'));
        }
    }

    public function wizard_process()
    {
        if (isset($_POST['process']) && $_POST['process'] == 'wizard') {
            if (isset($_POST['action'])) {
                Wizard_Model::processall();
            }
        }
    }

    public function linksync_wizard()
    {
        $apikey = get_option('linksync_laid');
        $response = false;
        if (!empty($apikey)) {
            $response['connected_to'] = LS_QBO()->options()->getConnectedTo();
        }

        if (empty($apikey) && isset($_GET['step']) && $_GET['step'] > 1) {
            update_option('linksync_error_message', 'Please provide the valid API Key before proceeding to next step.');
            wp_redirect(admin_url('admin.php?page=linksync-wizard'));
            exit();
        }

        // Display UI
        Linksync_installation::wizard_handler($response);
    }

    public function check_required_plugins()
    {
        if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            add_action('admin_notices', array('linksync', 'dependent_plugin_error'));
        }
    }

    public static function pluginUpdateChecker()
    {
        if (is_admin()) {
            include_once 'plugin-update-checker/plugin-update-checker.php';
            $myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
                'https://github.com/linksync/woo_qbo',
                __FILE__,
                'linksync-qbo'
            );
        }
    }

    public function plugin_action_links($links)
    {
        $action_links = array(
            'settings' => '<a href="' . admin_url('admin.php?page=linksync') . '" title="' . esc_attr(__('View Linksync Settings', 'linksync')) . '">' . __('Settings', 'linksync') . '</a>',
        );

        return array_merge($action_links, $links);
    }

    public static function linksync_autogenerate_code($length = 6)
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }

}

register_activation_hook(__FILE__, array('linksync', 'activate')); # When plugin get activated it will triger class method "activate"

/**
 * Check if WooCommerce is active
 */
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

    function load_linskync_qbo_after_woocommerce_loaded()
    {
        $linksync = new linksync();
        $orignal_time = time();

        $save_time = get_option('linksync_user_activity') + 3600;
        if (isset($save_time) && !empty($save_time) && isset($orignal_time) && !empty($orignal_time)) {
            if ($orignal_time >= $save_time) {
                update_option('linksync_user_activity', $orignal_time);
            }
        }
        $daily = get_option('linksync_user_activity_daily') + 86400;
        if (isset($daily) && !empty($daily) && isset($orignal_time) && !empty($orignal_time)) {
            if ($orignal_time >= $daily) {
                linksync::clearLogsDetails();
                update_option('linksync_user_activity_daily', $orignal_time);
                LSC_Log::clear_some_dev_logs();
            }
        }

    }
    add_action('woocommerce_init', 'load_linskync_qbo_after_woocommerce_loaded');
    add_action('plugins_loaded', array('linksync', 'pluginUpdateChecker'), 0);
}
