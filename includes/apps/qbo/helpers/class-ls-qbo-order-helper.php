<?php

class LS_QBO_Order_Helper
{

    public static function getOrderSyncingError($order_id)
    {
        return get_post_meta($order_id, '_ls_json_order_error');
    }

    public static function updateOrderSyncingError($order_id, $value)
    {
        return update_post_meta($order_id, '_ls_json_order_error', $value);
    }

    public static function deleteOrderSyncingError($order_id)
    {
        return delete_post_meta($order_id, '_ls_json_order_error');
    }
}