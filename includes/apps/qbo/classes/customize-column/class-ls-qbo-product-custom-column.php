<?php if (!defined('ABSPATH')) exit('Access is Denied');

class LS_QBO_Product_Custom_Column
{

    public static function init()
    {
        add_filter('manage_edit-product_columns', array('LS_QBO_Product_Custom_Column', 'is_sync_to_quickbooks_column'));
        add_action('manage_product_posts_custom_column', array('LS_QBO_Product_Custom_Column', 'is_sync_to_quickbooks_row_content'));
    }

    public static function is_sync_to_quickbooks_column($columns)
    {
        $new_columns = array();
        foreach ($columns as $column_name => $column_info) {

            $new_columns[$column_name] = $column_info;
            if ('sku' === $column_name) {
                $new_columns['sync_to_qbo'] = 'In QuickBooks';
            }

        }

        return $new_columns;
    }

    public static function is_sync_to_quickbooks_row_content($column)
    {
        global $post;

        if ('sync_to_qbo' === $column) {

            $product_id = $post->ID;
            $product = new LS_Woo_Product($product_id);
            $productType = $product->get_type();
            $qbo_product_id = $product->get_meta()->get_product_id();

            if('variable' == $productType){
                $childrenIds = $product->get_children();
                if(!empty($childrenIds)){
                    $count = 0;
                    foreach ($childrenIds as $childrenId){
                        $varProduct = new LS_Woo_Product($childrenId);
                        $qbo_product_id = $varProduct->get_meta()->get_product_id();
                        if(!empty($qbo_product_id)){
                            $count++;
                        }
                    }

                    if(!empty($count)){
                        echo $count.' Variations in QuickBooks <br/><span class="yes-in-quickbooks dashicons dashicons-yes"></span>';
                    } else {
                        echo '<span class="no-in-quickbooks dashicons dashicons-no-alt"></span>';
                    }

                }

            } else {

                if(!empty($qbo_product_id)){
                    echo '<span class="yes-in-quickbooks dashicons dashicons-yes"></span>';
                } else {
                    echo '<span class="no-in-quickbooks dashicons dashicons-no-alt"></span>';
                }

            }

        }

    }

}