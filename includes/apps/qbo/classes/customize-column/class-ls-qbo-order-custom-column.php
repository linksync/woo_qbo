<?php if (!defined('ABSPATH')) exit('Access is Denied');

class LS_QBO_Order_Custom_Column
{

    public static function init()
    {
        add_filter('manage_edit-shop_order_columns', array('LS_QBO_Order_Custom_Column', 'is_sync_to_quickbooks_column'));
        add_action('manage_shop_order_posts_custom_column', array('LS_QBO_Order_Custom_Column', 'is_sync_to_quickbooks_row_content'));
    }


    public static function is_sync_to_quickbooks_column($columns)
    {
        $new_columns = array();

        foreach ($columns as $column_name => $column_info) {

            $new_columns[$column_name] = $column_info;
            if ('billing_address' === $column_name) {
                $new_columns['sync_to_qbo'] = 'In QuickBooks';
            }
        }

        return $new_columns;
    }

    public static function is_sync_to_quickbooks_row_content($column)
    {
        global $post;

        if ('sync_to_qbo' === $column) {
            $order_id = $post->ID;
            $orderMeta = new LS_Order_Meta($order_id);
            $orderId = $orderMeta->get_qbo_id();
            $order_syncing_data = $orderMeta->getOrderJsonFromWooToQbo();

            if (!empty($orderId)) {
                $order_type = $order_syncing_data['response']['orderType'];
                $url = LS_QBO_Constant::QBO_INVOICE_URL;
                if ('SalesReceipt' == $order_type) {
                    $url = LS_QBO_Constant::QBO_SALES_RECEIPT_URL;
                }

                echo '<a class="yes-in-quickbooks dashicons dashicons-yes" href="' . $url . $orderId . '" target="_blank"></a>';

            } else {

                echo '<span class="no-in-quickbooks dashicons dashicons-no-alt"></span>';
            }

        }

    }

}