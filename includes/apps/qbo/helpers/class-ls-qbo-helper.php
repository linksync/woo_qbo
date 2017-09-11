<?php

class LS_QBO_Helper
{
    public static function duplicate_sku_message()
    {
        $wooCommerceProductListLink = '<a target="_blank" href="' . admin_url('edit.php?post_type=product') . '">Woocommerce</a>. ';
        $duplcateSkuListLink = ' <a target="_blank" href="' . LS_QBO_Menu::linksync_page_menu_url('duplicate_sku') . '" > Click here</a>';
        return 'You have duplicate or empty skus in your ' . $wooCommerceProductListLink . $duplcateSkuListLink . ' to view the list and resolved it.';
    }
}