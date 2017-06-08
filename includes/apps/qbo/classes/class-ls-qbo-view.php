<?php if (!defined('ABSPATH')) exit('Access is Denied');

class LS_QBO_View
{

    public function display()
    {
        global $currentScreenId, $linkSyncQBOMenuId, $in_woo_duplicate_skus, $in_woo_empty_product_skus, $in_qbo_duplicate_and_empty_skus;

        if ($linkSyncQBOMenuId == $currentScreenId) {
            $subPage = LS_QBO_Menu::get_active_tab_page();
            $linksyncPage = LS_QBO_Menu::get_active_linksync_page();

            if (empty($linksyncPage)) {

                if (empty($subPage)) {

                    $this->display_configuration_tab();

                } else if ('logs' == $subPage) {

                    $this->display_logs_tab();

                } else if ('product_config' == $subPage) {

                    $this->display_product_configuration_tab();

                } else if ('order_config' == $subPage) {

                    $this->display_order_configuration_tab();

                } else if ('support' == $subPage) {

                    $this->display_support_tab();

                } else {
                    $this->display_configuration_tab();

                }

            } else {

                if ('duplicate_sku' == $linksyncPage) {
                    if (!empty($in_woo_duplicate_skus) || !empty($in_woo_empty_product_skus) || !empty($in_qbo_duplicate_and_empty_skus)) {
                        $this->display_duplicate_sku_list();
                    } else {
                        $this->display_configuration_tab();
                    }

                } else {
                    $this->display_configuration_tab();

                }
            }

        }
    }

    public function display_duplicate_sku_list()
    {
        global $in_woo_duplicate_skus, $in_woo_empty_product_skus, $in_qbo_duplicate_and_empty_skus;

        $duplicateSkuList = new LS_QBO_Duplicate_Sku_List();
        $active_section = LS_QBO_Menu::get_active_section();

        if (empty($active_section) || 'in_woocommerce' == $active_section) {

            $duplicateSkuList = new LS_Duplicate_Sku_List(array(
                'duplicate_products' => $in_woo_duplicate_skus,
                'empty_product_skus' => $in_woo_empty_product_skus,
            ));
        }

        if ('in_quickbooks_online' == $active_section) {
            $duplicateSkuList = new LS_QBO_Duplicate_Sku_List(array(
                'duplicate_and_empty_skus' => $in_qbo_duplicate_and_empty_skus,
            ));
        }


        //Fetch, prepare, sort, and filter our data...
        $duplicateSkuList->prepare_items();
        $mainDuplicateSkuListUrl = LS_QBO_Menu::admin_url(LS_QBO_Menu::linksync_page_menu_url('duplicate_sku'));
        if ('in_quickbooks_online' == $active_section) {
            ?>
            <style>
                #frm-duplicate-skus .bulkactions {
                    display: none !important;
                }
            </style>
            <?php
        }
        if (!empty($duplicate_and_empty_skus) || !empty($duplicate_products) || !empty($empty_product_skus)) {
            $linkToKnowledgeBase = '<a target="_blank" href="https://help.linksync.com/hc/en-us/articles/115000710830-What-if-I-have-duplicate-SKUs-in-either-or-both-systems-"> click here</a>.';
            LS_Message_Builder::notice("You have duplicate or empty skus. Please update your skus to make it unique. For more information " . $linkToKnowledgeBase);
        }

        ?>
        <div class="wrap" id="ls-wrapper">
            <div id="icon-users" class="icon32"><br/></div>
            <h2>Duplicate SKU List</h2>
            <ul class="subsubsub">
                <li><a href="<?php echo $mainDuplicateSkuListUrl . '&section=in_woocommerce'; ?>"
                       class="<?php echo (empty($active_section) || 'in_woocommerce' == $active_section) ? 'current' : ''; ?>">In
                        WooCommerce</a> |
                </li>
                <li>
                    <a href="<?php echo $mainDuplicateSkuListUrl . '&section=in_quickbooks_online'; ?>"
                       class="<?php echo ('in_quickbooks_online' == $active_section) ? 'current' : ''; ?>">In QuickBooks
                        Online</a> |
                </li>
            </ul>
            <br/><br/>
            <div class="ls-duplicate-sku-container">
                <form id="frm-duplicate-skus" method="get">
                    <?php $duplicateSkuList->display() ?>
                </form>
            </div>

        </div>
        <?php

    }

    public function display_tab_menu()
    {
        if (is_qbo()) {
            $webHookUpdate = LS_QBO()->updateWebhookConnection();
            if (
                isset($webHookUpdate['errorCode']) &&
                isset($webHookUpdate['userMessage']) &&
                'Connection to the update URL failed.' == $webHookUpdate['userMessage']
            ) {
                LS_Message_Builder::notice('Connection to the update URL failed. Please check our <a href="https://help.linksync.com/hc/en-us/articles/115000591510-Connection-to-the-update-URL-failed" target="_blank">FAQ</a> section to find possible solutions.', 'error ');
            }

        }

        $file_perms = wp_is_writable(plugin_dir_path(__FILE__));

        //Check if not writable
        if (!$file_perms) {
            LS_Message_Builder::error("Alert: File permission on <b>wp-content</b> will prevent linksync from syncing and/or functioning corectly.<a href='https://www.linksync.com/help/woocommerce-perms'>Please click here for more information</a>.");
        }


        if (is_qbo()) {
            LS_QBO_Sync::lwsApiHasUpdates();
            $changeToTaxCodeApi = get_option('ls_apichangetotaxcode', '');
            if (!empty($changeToTaxCodeApi)) {
                LS_Message_Builder::notice($changeToTaxCodeApi, 'error big-error-message');
            }
        }

        $qboLaidObject = LS_QBO()->laid();
        $laid_info = $qboLaidObject->getLaidInfo();

        if (!empty($laid_info)) {
            $qboLaidObject->updateCurrentLaidInfo($laid_info);
        }


        //Check if adding,update api key button was not set then check the current api key
        if (!isset($_POST['add_apiKey']) && !isset($_POST['apikey_update'])) {
            LS_QBO()->laid()->checkApiKey();
        }

        LS_QBO_Menu::output_menu_tabs();

    }

    public function display_configuration_tab()
    {
        $this->settings_header();
        $this->display_tab_menu();
        $this->render_settings('ls-plugins-tab-configuration');
    }

    public function display_logs_tab()
    {
        $this->settings_header();
        $this->display_tab_menu();
        $this->render_settings('ls-plugins-tab-logs');
    }

    public function display_product_configuration_tab()
    {
        $this->settings_header();
        $this->display_tab_menu();
        /**
         * No need to pass a file name of the view because order syncing settings view is loaded via ajax
         */
        $this->render_settings();

    }

    public function display_missing_apikey_message()
    {
        LS_Message_Builder::error(LS_Constants::NOT_CONNECTED_MISSING_API_KEY);
    }

    public function display_order_configuration_tab()
    {
        $this->settings_header();
        $this->display_tab_menu();
        /**
         * No need to pass a file name of the view because product syncing settings view is loaded via ajax
         */
        $this->render_settings();
    }

    public function display_support_tab()
    {
        $this->settings_header();
        $this->display_tab_menu();
        LS_Support_Helper::renderFormForSupportTab();
    }

    public function display_loading_div()
    {
        echo '<div class="se-pre-con"></div>';
    }


    private function render_settings($fileName = null)
    {
        ?>
        <div class="wrap" id="ls-wrapper">
            <div id="response"></div>
            <div id='ls-main-views-cont'>
                <?php

                if (!empty($fileName) && file_exists(LS_INC_DIR . 'view/' . $fileName . '.php')) {
                    include LS_INC_DIR . 'view/' . $fileName . '.php';
                }

                ?>
            </div>
        </div>
        <?php
    }

    public function settings_header()
    {
        ?><h2>Linksync (Version: <?php echo Linksync_QuickBooks::$version; ?>)</h2><?php
    }

    /**
     * Add Settings link in the plugin list view for this vend plugin
     *
     * @param $links
     * @return array
     */
    public function plugin_action_links($links)
    {
        $action_links = array(
            'settings' => '<a href="' . admin_url('admin.php?page=' . LS_QBO::$slug) . '" title="' . esc_attr(__('View Linksync Settings', LS_QBO::$slug)) . '">' . __('Settings', LS_QBO::$slug) . '</a>',
        );

        return array_merge($action_links, $links);
    }

}