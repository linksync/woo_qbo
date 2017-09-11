<?php if (!defined('ABSPATH')) exit('Access is Denied');

class LS_QBO_Script
{
    public static function enqueue_scripts_and_styles()
    {
        //Check for linksync plugin page before adding the styles and scripts to wp-admin
        $linkSyncQBOMenuId = LS_QBO_Menu::get_id();
        $currentScreen = get_current_screen();
        $currentScreenId = $currentScreen->id;

        wp_enqueue_style('ls-qbo', LS_ASSETS_URL . 'css/qbo-styles.css');
        wp_enqueue_script('ls-main-qbo-js', LS_ASSETS_URL . 'js/qbo.js', array('jquery'));

        if ($linkSyncQBOMenuId == $currentScreenId) {

            wp_enqueue_script('ls-ajax-handler', LS_ASSETS_URL . 'js/ls-ajax.js', array('jquery'));

            wp_enqueue_style('ls-styles', LS_ASSETS_URL . 'css/style.css');

            //settings tab styles and scripts
            wp_enqueue_style('ls-settings-tab', LS_ASSETS_URL . 'css/admin-tabs/ls-plugins-setting.css');
            wp_enqueue_style('ls-reveal-style', LS_ASSETS_URL . 'css/admin-tabs/ls-reveal.css');
            wp_enqueue_script('ls-tiptip-plugin', LS_ASSETS_URL . 'js/jquery-tiptip/jquery.tipTip.min.js', array('jquery'));
            wp_enqueue_script('ls-reveal-script', LS_ASSETS_URL . 'js/jquery-tiptip/jquery.reveal.js', array('jquery'));
            wp_enqueue_script('ls-jquery-ui-plugin', LS_ASSETS_URL . 'js/jquery-tiptip/jquery-ui.js', array('jquery'));
            wp_enqueue_script('ls-custom-scripts', LS_ASSETS_URL . 'js/ls-custom.js', array('jquery'));

            //ls-plugins-tab-configuration styles and scripts
            wp_enqueue_style('ls-jquery-ui', LS_ASSETS_URL . 'css/jquery-ui/jquery-ui.css');
            wp_enqueue_style('ls-tab-configuration-style', LS_ASSETS_URL . 'css/admin-tabs/ls-plugins-tab-configuration.css');

            LS_Support_Helper::supportScripts();

            $connected_to = get_option('linksync_connectedto');

            $activeTabPage = LS_QBO_Menu::get_active_tab_page();

            if (!empty($activeTabPage)) {

                if ('product_config' == $activeTabPage) {

                    wp_enqueue_script('ls-sync-modal', LS_ASSETS_URL . 'js/ls-sync-modal.js', array('jquery'));
                    wp_enqueue_script('ls-sync-all-buttons', LS_ASSETS_URL . 'js/ls-sync-buttons.js', array('jquery'));
                    wp_enqueue_script('ls-qbo-product-syncing', LS_ASSETS_URL . 'js/qbo-product-syncing.js', array('jquery'));

                    wp_enqueue_style('ls-jquery-ui-css', LS_ASSETS_URL . 'jquery-ui.css');

                } else if ('order_config' == $activeTabPage) {
                    wp_enqueue_script('ls-qbo-order-syncing', LS_ASSETS_URL . 'js/qbo-order-syncing.js', array('jquery'));
                } else if ('advance' == $activeTabPage) {
                    //configuration tab
                    wp_enqueue_script('ls-sync-modal', LS_ASSETS_URL . 'js/ls-sync-modal.js', array('jquery'));
                    wp_enqueue_script('ls-sync-all-buttons', LS_ASSETS_URL . 'js/ls-sync-buttons.js', array('jquery'));
                    wp_enqueue_script('ls-qbo-configuration', LS_ASSETS_URL . 'js/qbo-configuration.js', array('jquery'));
                    wp_enqueue_style('ls-jquery-ui-css', LS_ASSETS_URL . 'jquery-ui.css');
                } else {
                    //configuration tab
                    wp_enqueue_script('ls-sync-modal', LS_ASSETS_URL . 'js/ls-sync-modal.js', array('jquery'));
                    wp_enqueue_script('ls-sync-all-buttons', LS_ASSETS_URL . 'js/ls-sync-buttons.js', array('jquery'));
                    wp_enqueue_script('ls-qbo-configuration', LS_ASSETS_URL . 'js/qbo-configuration.js', array('jquery'));
                    wp_enqueue_style('ls-jquery-ui-css', LS_ASSETS_URL . 'jquery-ui.css');
                }

            } else {
                //configuration tab
                wp_enqueue_script('ls-sync-modal', LS_ASSETS_URL . 'js/ls-sync-modal.js', array('jquery'));
                wp_enqueue_script('ls-sync-all-buttons', LS_ASSETS_URL . 'js/ls-sync-buttons.js', array('jquery'));
                wp_enqueue_script('ls-qbo-configuration', LS_ASSETS_URL . 'js/qbo-configuration.js', array('jquery'));
                wp_enqueue_style('ls-jquery-ui-css', LS_ASSETS_URL . 'jquery-ui.css');
            }
        }

        if (isset($_GET['page']) && $_GET['page'] == 'linksync-wizard') {
            wp_enqueue_style('ls-styles', LS_ASSETS_URL . 'css/style.css');
            add_action('admin_head', array('Wizard_Model', 'remove_all_admin_notices_during_wizard_process'));
            wp_enqueue_script('ls-ajax-handler', LS_ASSETS_URL . 'js/ls-ajax.js', array('jquery'));
            wp_enqueue_script('ls-sync-modal', LS_ASSETS_URL . 'js/ls-sync-modal.js', array('jquery'));
            wp_enqueue_script('ls-sync-all-buttons', LS_ASSETS_URL . 'js/ls-sync-buttons.js', array('jquery'));
            wp_enqueue_style('admin-linksync-style', LS_ASSETS_URL . 'css/wizard/wizard-styles.css');
            wp_enqueue_script('ls-wizard-product-syncing', LS_ASSETS_URL . 'js/wizard-qbo-product-syncing.js', array('jquery', 'jquery-ui-core', 'jquery-ui-progressbar'));

            if (isset($_GET['step']) && 4 == $_GET['step']) {
                wp_enqueue_style('ls-jquery-ui-css', LS_ASSETS_URL . 'jquery-ui.css');
                wp_enqueue_style('ls-styles', LS_ASSETS_URL . 'css/style.css');
            }

        }

        $screen = get_current_screen();
        if ('shop_order' == $screen->id) {
            wp_enqueue_script('ls-shop-order-scripts', LS_ASSETS_URL . 'js/ls-shop-order.js', array('jquery'));
        }
    }


}