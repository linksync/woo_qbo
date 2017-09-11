<?php
/*
  Plugin Name: linksync for WooCommerce
  Plugin URI: http://www.linksync.com/integrate/woocommerce
  Description:  WooCommerce extension for syncing inventory and order data with other apps, including Xero, QuickBooks Online, Vend, Saasu and other WooCommerce sites.
  Author: linksync
  Author URI: http://www.linksync.com
  Version: 2.5.18
 */

if (!class_exists('Linksync_QuickBooks')) {

    final class Linksync_QuickBooks
    {

        /**
         * @var string
         */
        public static $version = '2.5.18';
        protected static $_instance = null;

        /**
         * Cloning is forbidden.
         */
        public function __clone()
        {
            wp_die('Cheatin&#8217; huh?');
        }

        /**
         * Unserializing instances of this class is forbidden.
         */
        public function __wakeup()
        {
            wp_die('Cheatin&#8217; huh?');
        }

        public static function instance()
        {
            if (is_null(self::$_instance)) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }


        public function __construct()
        {
            $this->define_constants();
            $this->includes();
            $this->init();
        }

        /**
         * Plugin directories and Url
         * Set Globals Linksync QuickBooks Constant
         */
        public function define_constants()
        {
            $pluginBaseName = plugin_basename(__FILE__);
            $this->define('LS_PLUGIN_BASE_NAME', $pluginBaseName);
            $this->define('LS_QBO_PLUGIN_BASENAME', $pluginBaseName);
            $this->define('LS_PLUGIN_DIR', plugin_dir_path(__FILE__));
            $this->define('LS_INC_DIR', LS_PLUGIN_DIR . 'includes/');
            $this->define('LS_PLUGIN_URL', plugin_dir_url(__FILE__));
            $this->define('LS_ASSETS_URL', LS_PLUGIN_URL . 'assets/');
            $this->define('LS_QBO_ASSETS_URL', LS_PLUGIN_URL . 'assets/');
        }

        public function run()
        {
             LS_QBO()->run();
        }

        /**
         * Linksync QuickBooks initialization
         */
        private function init()
        {
            /**
             * To handle all languages
             */
            mb_internal_encoding('UTF-8');
            mb_http_output('UTF-8');
            mb_http_input('UTF-8');
            mb_language('uni');
            mb_regex_encoding('UTF-8');

            $this->init_hooks();

        }

        private function init_hooks()
        {
            add_action('plugins_loaded', array(__CLASS__, 'pluginUpdateChecker'), 0);
            register_activation_hook(__FILE__, array('LS_QBO_Install', 'plugin_activate'));
            /**
             * Check if WooCommerce is active
             */
            if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

                add_action('woocommerce_init', array('LS_QBO_Log_Helper', 'log_users_activity_time'));

            }

        }


        /**
         * Include Required files for Linksync
         */
        public function includes()
        {
            include_once LS_PLUGIN_DIR . 'ls-functions.php';
            include_once LS_PLUGIN_DIR . 'plugin-update-checker/plugin-update-checker.php';

            require_once LS_INC_DIR . 'apps/vend/ls-vend-log.php';
            require_once LS_INC_DIR . 'apps/vend/controllers/ls-log.php';
            include_once LS_PLUGIN_DIR . 'classes/class.linksync-install.php';
            include_once LS_PLUGIN_DIR . 'classes/wizard.php';

            require_once LS_INC_DIR . 'apps/qbo/qbo.php';

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

        /**
         * Define constant if not already set.
         *
         * @param  string $name
         * @param  string|bool $value
         */
        private function define($name, $value)
        {
            if (!defined($name)) {
                define($name, $value);
            }
        }

    }

}

Linksync_QuickBooks::instance()->run();
