<?php if (!defined('ABSPATH')) exit('Access is Denied');

class LS_QBO_Hook
{

    public static function init()
    {
        add_action('init', array('Wizard_Model', 'wizard_process'), 1);
        Linksync_installation::init();

        /**
         * Add Styles and Javascript files to wp-admin area
         */
        add_action('admin_enqueue_scripts', array('LS_QBO_Script', 'enqueue_scripts_and_styles'));

        /**
         * Admin notice action hooks
         */
        $qboNotice = new LS_QBO_Notice();
        add_action('admin_notices', array($qboNotice, 'display'));

        /**
         * Check if WooCommerce is active and show settings link in plugin list
         */
        if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

            $qbo_view = new LS_QBO_View();
            add_filter('plugin_action_links_' . LS_QBO_PLUGIN_BASENAME, array($qbo_view, 'plugin_action_links'));

        }

    }

}