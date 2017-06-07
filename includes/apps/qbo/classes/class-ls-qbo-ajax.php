<?php if (!defined('ABSPATH')) exit('Access is Denied');

class LS_QBO_Ajax
{

    public function __construct()
    {
        add_action('wp_ajax_qbo_set_empty_sku_automatically', array($this, 'setEmptySkuAutomatically'));
        add_action('wp_ajax_qbo_append_product_id_to_duplicate_skus', array($this, 'appendProductIdToDuplicateSku'));
        add_action('wp_ajax_qbo_delete_products_permanently', array($this, 'deleteProductsPermanently'));
        add_action('wp_ajax_qbo_done_syncing_required', array($this, 'doneRequiredResync'));


    }

    public function deleteProductsPermanently()
    {

        if (!empty($_POST['product_ids'])) {

            foreach ($_POST['product_ids'] as $product_id) {
                if (is_numeric($product_id)) {
                    wp_delete_post($product_id, true);
                }
            }
        }
        wp_send_json(array('message' => 'done'));
    }

    public function doneRequiredResync()
    {
        LS_QBO()->options()->done_required_sync();
        die();
    }

    public function setEmptySkuAutomatically()
    {
        if (!empty($_POST['product_ids'])) {
            foreach ($_POST['product_ids'] as $product_id) {
                if (is_numeric($product_id)) {
                    $product_meta = new LS_Product_Meta($product_id);
                    $sku = $product_meta->get_sku();
                    if(empty($sku)){
                        $newSku = 'sku_' . $product_id;
                        $product_meta->update_sku($newSku);
                    }
                }
            }
        } else {

            $emptySkus = LS_Woo_Product::get_woo_empty_sku();
            foreach ($emptySkus as $product_ref) {
                $sku = $product_ref['meta_value'];
                if (empty($sku)) {
                    $product_meta = new LS_Product_Meta($product_ref['ID']);
                    $newSku = 'sku_' . $product_ref['ID'];
                    $product_meta->update_sku($newSku);
                }
            }

        }


        wp_send_json(array('message' => 'done'));
    }

    public function appendProductIdToDuplicateSku()
    {
        if (!empty($_POST['product_ids'])) {
            foreach ($_POST['product_ids'] as $product_id) {
                if (is_numeric($product_id)) {
                    $product_meta = new LS_Product_Meta($product_id);
                    $sku = $product_meta->get_sku();
                    $uniqueSku = 'sku_' . $product_id;
                    if ($sku != $uniqueSku && !empty($sku)) {
                        $newSku = $uniqueSku . $sku;
                        $product_meta->update_sku($newSku);
                    }
                }
            }
        } else {

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

        }



        wp_send_json(array('message' => 'done'));
    }
}

new LS_QBO_Ajax();