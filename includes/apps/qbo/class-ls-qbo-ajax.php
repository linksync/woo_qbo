<?php if ( ! defined( 'ABSPATH' ) ) exit('Access is Denied');

class LS_QBO_Ajax
{

    public function __construct()
    {
        add_action( 'wp_ajax_qbo_set_empty_sku_automatically', array( $this, 'setEmptySkuAutomatically' ) );
        add_action( 'wp_ajax_qbo_append_product_id_to_duplicate_skus', array( $this, 'appendProductIdToDuplicateSku' ) );
    }

    public function setEmptySkuAutomatically()
    {
        $emptySkus = LS_Woo_Product::get_woo_empty_sku();
        foreach($emptySkus as $product_ref ){
            $sku = $product_ref['meta_value'];
            if(empty($sku)){
                $product_meta = new LS_Product_Meta($product_ref['ID']);
                $newSku = 'sku_'.$product_ref['ID'];
                echo $newSku;
                $product_meta->update_sku($newSku);
            }
        }

        die();
    }

    public function appendProductIdToDuplicateSku()
    {
        $duplicateSkus = LS_Woo_Product::get_woo_duplicate_sku();
        foreach ($duplicateSkus as $duplicateSku) {
            $sku = $duplicateSku['meta_value'];
            $productMeta = new LS_Product_Meta($duplicateSku['ID']);
            $uniqueSku = 'sku_' . $duplicateSku['ID'];
            if ($sku != $uniqueSku) {
                $newSku = $uniqueSku . $sku;
                $productMeta->update_sku($newSku);
            }

        }

        die();
    }
}

new LS_QBO_Ajax();