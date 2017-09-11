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


    public static function get_qbo_connected_orders($orderBy = '', $order = 'DESC', $search_key = '')
    {
        global $wpdb;

        $orderBySql = '';
        if (!empty($orderBy)) {
            $orderBySql = 'ORDER BY ' . $orderBy . ' ' . strtoupper($order);
        } else {
            $orderBySql = 'ORDER BY ID DESC';
        }

        $searchWhere = " AND wpmeta.meta_key IN ('_ls_qbo_oid') AND wpmeta.meta_value != '' ";
        if (!empty($search_key)) {

            $prepare_id_search = $wpdb->prepare(" wposts.ID LIKE %s ", '%' . $search_key . '%');;

            $searchWhere = " AND (" . $prepare_id_search . ")";
        }

        $groupBy = ' GROUP BY wposts.ID ';

        $sql = "
					SELECT
							wposts.ID AS ID,
							wposts.post_title AS product_name,
                            wposts.post_status AS product_status,
                            wpmeta.meta_key,
                            wpmeta.meta_value,
                            wposts.post_type AS product_type
					FROM $wpdb->postmeta AS wpmeta
					INNER JOIN $wpdb->posts as wposts on ( wposts.ID = wpmeta.post_id )
					WHERE
					      wposts.post_type IN('shop_order') " . $searchWhere . $groupBy . $orderBySql;


        $results = $wpdb->get_results($sql, ARRAY_A);

        foreach ($results as $key => $result) {
            $qbo_order_id = get_post_meta($result['ID'], '_ls_qbo_oid', true);
            if(empty($qbo_order_id)){
                unset($results[$key]);
            } else {
                $result['qbo_order_id'] = $qbo_order_id;
                $results[$key] = $result;
            }
        }

        return $results;
    }
}