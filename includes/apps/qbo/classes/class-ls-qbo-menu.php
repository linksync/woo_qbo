<?php if (!defined('ABSPATH')) exit('Access is Denied');

class LS_QBO_Menu
{

    public function __construct()
    {
        /**
         * Check if WooCommerce if active before showing plugin menu
         */
        if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            add_action('admin_menu', array($this, 'initialize_admin_menu'));
            add_action('admin_head', array($this, 'remove_first_sub_menu'));
        }

        add_action('admin_footer', array($this, 'footer_scripts'));
    }

    public function remove_first_sub_menu()
    {
        global $submenu;

        if (isset($submenu[LS_QBO::$slug]) && !empty($submenu[LS_QBO::$slug])) {

            if (isset($submenu[LS_QBO::$slug][0])) {
                // Remove 'linksync Vend' sub menu item
                unset($submenu[LS_QBO::$slug][0]);
            }
        }

    }

    public static function get_active_linksync_page()
    {
        $active_page = '';
        if (isset($_REQUEST['linksync_page'])) {
            $active_page = $_REQUEST['linksync_page'];
        }

        return $active_page;
    }

    public static function get_active_page()
    {
        $active_page = 'linksync-qbo';
        if (isset($_REQUEST['page'])) {
            $active_page = $_REQUEST['page'];
        }

        return $active_page;
    }

    public static function get_active_section()
    {
        if (isset($_REQUEST['section'])) {
            return $_REQUEST['section'];
        }

        return null;
    }

    public static function get_active_tab_page()
    {
        $settings_tabs = array(
            'config',
            'product_config',
            'order_config',
            'advance',
            'logs',
            'support',
            'duplicate_sku'
        );
        $active_tab = 'config';
        if (isset($_REQUEST['page'], $_REQUEST['tab'])) {

            if (in_array($_REQUEST['tab'], $settings_tabs)) {
                $active_tab = $_REQUEST['tab'];
            }

        }

        return $active_tab;
    }

    public static function output_menu_tabs()
    {
        $active_tab = self::get_active_tab_page();
        ?>
        <h2 class="ls-tab-menu">

            <a href="<?php echo self::tab_admin_menu_url(); ?>"
               class="ls-nav-tab <?php echo ('config' == $active_tab) ? 'ls-nav-tab-active' : ''; ?>">
                Configuration
            </a>

            <a href="<?php echo self::tab_admin_menu_url('product_config'); ?>"
               class="ls-nav-tab <?php echo ('product_config' == $active_tab) ? 'ls-nav-tab-active' : ''; ?> ">
                Product Syncing Setting
            </a>

            <a href="<?php echo self::tab_admin_menu_url('order_config') ?>"
               class="ls-nav-tab <?php echo ('order_config' == $active_tab) ? 'ls-nav-tab-active' : ''; ?> ">
                Order Syncing Setting
            </a>

            <a href="<?php echo self::tab_admin_menu_url('advance') ?>"
               class="ls-nav-tab <?php echo ('advance' == $active_tab) ? 'ls-nav-tab-active' : ''; ?> ">
                Advanced
            </a>

            <a href="<?php echo self::tab_admin_menu_url('support') ?>"
               class="ls-nav-tab <?php echo ('support' == $active_tab) ? 'ls-nav-tab-active' : ''; ?>">
                Support
            </a>

            <a href="<?php echo self::tab_admin_menu_url('logs') ?>"
               class="ls-nav-tab <?php echo ('logs' == $active_tab) ? 'ls-nav-tab-active' : ''; ?>">
                Logs
            </a>

        </h2>
        <?php
    }


    public static function get_id()
    {
        return 'toplevel_page_' . LS_QBO::$slug;
    }


    public static function get_current_menu_url()
    {
        $subPage = isset($_REQUEST['tab']) ? $_REQUEST['tab'] : '';

        if (empty($subPage)) {
            $subPage = null;
        }

        return self::tab_menu_url($subPage);
    }

    public static function menu_url($page_slug = null, $tab = null, $section = null)
    {
        $url = 'admin.php?page=' . (empty($page_slug) ? LS_QBO::$slug : $page_slug);

        if (null != $tab) {
            $url .= '&tab=' . $tab;
        }

        if (null != $section) {
            $url .= '&section=' . $section;
        }

        return $url;
    }

    public static function admin_url($url = null)
    {
        return admin_url($url);
    }

    public static function page_menu_url($page)
    {
        $url = 'admin.php?page=' . $page;
        return $url;
    }

    public static function linksync_page_menu_url($linksync_page_slug = null)
    {
        $url = 'admin.php?page=' . LS_QBO::$slug;
        if (null != $linksync_page_slug) {
            $url .= '&linksync_page=' . $linksync_page_slug;
        }

        return $url;
    }

    public static function get_linksync_page_menu_ur()
    {
        $url = 'admin.php?page=' . LS_QBO::$slug;
        $linksync_page_slug = '';
        if(!empty($_REQUEST['linksync_page'])){
            $linksync_page_slug = $_REQUEST['linksync_page'];
        }
        if (null != $linksync_page_slug) {
            $url .= '&linksync_page=' . $linksync_page_slug;
        }

        return $url;
    }

    public static function get_linksync_admin_url()
    {
        return self::admin_url(self::page_menu_url(LS_QBO::$slug));
    }

    public static function get_wizard_admin_menu_url($endpoint = null)
    {
        $endpoint = empty($endpoint) ? '' : '&' . $endpoint;
        return self::admin_url(self::page_menu_url('linksync-wizard' . $endpoint));
    }

    public static function tab_menu_url($setting = null)
    {
        $url = 'admin.php?page=' . LS_QBO::$slug;
        if (null != $setting) {
            $url .= '&tab=' . $setting;
        }

        return $url;
    }


    public static function tab_admin_menu_url($url = null)
    {
        return admin_url(self::tab_menu_url($url));
    }


    public function initialize_admin_menu()
    {

        $menu_slug = LS_QBO::$slug;

        add_menu_page(
            __('linksync QuickBooks', $menu_slug),
            __('linksync QuickBooks', $menu_slug),
            'manage_options',
            $menu_slug,
            array(__CLASS__, 'settings'),
            LS_QBO_ASSETS_URL . 'images/linksync/logo-icon.png',
            '55.6'
        );

        add_submenu_page(
            $menu_slug,
            __('linksync Configuration', $menu_slug),
            __('Configuration', 'manage_options'),
            'manage_options',
            self::tab_menu_url('config'),
            null
        );

        add_submenu_page(
            $menu_slug,
            __('linksync Product Settings', $menu_slug),
            __('Product Settings', 'manage_options'),
            'manage_options',
            self::tab_menu_url('product_config'),
            null
        );

        add_submenu_page(
            $menu_slug,
            __('linksync Order Settings', $menu_slug),
            __('Order Settings', 'manage_options'),
            'manage_options',
            self::tab_menu_url('order_config'),
            null
        );

        add_submenu_page(
            $menu_slug,
            __('linksync Synced Products', $menu_slug),
            __('Synced Products', $menu_slug),
            'manage_options',
            self::linksync_page_menu_url('synced_products'),
            null
        );

        add_submenu_page(
            $menu_slug,
            __('linksync Synced Orders', $menu_slug),
            __('Synced Orders', $menu_slug),
            'manage_options',
            self::linksync_page_menu_url('synced_orders'),
            null
        );


//        if (!empty($in_woo_duplicate_skus) || !empty($in_woo_empty_product_skus) || !empty($in_qbo_duplicate_and_empty_skus)) {
//            add_submenu_page(
//                $menu_slug,
//                __('linksync Duplicate SKU', $menu_slug),
//                __('Duplicate SKU', $menu_slug),
//                'manage_options',
//                self::linksync_page_menu_url('duplicate_sku'),
//                null
//            );
//        }

        add_submenu_page(
            $menu_slug,
            __('linksync Support', $menu_slug),
            __('Support', $menu_slug),
            'manage_options',
            self::tab_menu_url('support'),
            null
        );

        add_submenu_page(
            $menu_slug,
            __('linksync Logs', $menu_slug),
            __('Logs', $menu_slug),
            'manage_options',
            self::tab_menu_url('logs'),
            null
        );

        $wizard_page = add_submenu_page(
            null,
            'linksync wizard',
            'linksync wizard',
            'manage_options',
            'linksync-wizard',
            array('Wizard_Model', 'linksync_wizard')
        );

    }

    public static function settings()
    {
        LS_QBO()->view()->display();
    }

    public function footer_scripts()
    {
        $linkSyncQBOMenuId = self::get_id();
        $currentPage = self::get_current_menu_url();
        $mainMenuSelector = '#' . $linkSyncQBOMenuId . ' > a';
        $mainMenuHrefUrl = self::tab_menu_url();
        $subMenuSelector = '#' . $linkSyncQBOMenuId . ' > ul > li';
        $duplicateMenuUrl = self::linksync_page_menu_url('duplicate_sku');
        $activeLinksyncPage = self::get_linksync_page_menu_ur();

        $duplicateMenuClass = '';
        if($duplicateMenuUrl == $activeLinksyncPage){
            $duplicateMenuClass = 'class="current"';
        }

        ?>
        <script>
            (function ($) {

                var currentPage = '<?php echo $currentPage; ?>';
                var currentLinksyncPage = '<?php echo $activeLinksyncPage; ?>';
                $(document).ready(function () {

                    $('<?php echo $mainMenuSelector; ?>').attr("href", "<?php echo $mainMenuHrefUrl; ?>")
                    $('<?php echo $subMenuSelector; ?>').removeClass('current');
                    $('<?php echo $subMenuSelector; ?> a').each(function () {
                        if (
                            $(this).attr('href') == currentPage ||
                            $(this).attr('href') == currentLinksyncPage
                        ) {
                            $(this).parent().addClass('current');
                        }

                        var currentMenuName = $(this).text();
                        if('Synced Orders' == currentMenuName){
                            var $connectedOrderElement = $(this).parent();

                            $.post(ajaxurl, {action: 'qbo_save_product_qbo_duplicates'}).done(function (response) {
                                console.log(response);
                                var qbo_product_duplicates = response.qbo_product_duplicates;
                                var woo_duplicate_product_skus = response.woo_duplicate_skus;
                                var woo_empty_product_skus = response.woo_empty_product_skus;
                                if(
                                    qbo_product_duplicates.length > 0 ||
                                    woo_duplicate_product_skus.length > 0 ||
                                    woo_empty_product_skus.length > 0
                                ){
                                    $connectedOrderElement.after('<li <?php echo $duplicateMenuClass; ?> ><a href="<?php echo $duplicateMenuUrl; ?>">Duplicate SKU</a></li>');
                                }

                            });
                        }
                    });

                });

            }(jQuery));
        </script>
        <?php
    }

}

