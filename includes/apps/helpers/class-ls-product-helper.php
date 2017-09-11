<?php

class LS_Product_Helper
{
    protected $product = null;
    public $post_data = null;

    public function __construct(WC_Product $product)
    {
        $this->product = $product;
        $this->post_data = get_post($this->product->get_id());
    }

    public function getPostTitle()
    {
        if (isset($this->post_data->post_title)) {
            return $this->post_data->post_title;
        }
        return null;
    }

    public function getPostContent()
    {
        if (isset($this->post_data->post_content)) {
            return $this->post_data->post_content;
        }
        return null;
    }

    public function getPostParentId()
    {
        if (isset($this->post_data->post_parent)) {
            return $this->post_data->post_parent;
        }
        return null;
    }

    public function getPostStatus()
    {
        if (isset($this->post_data->post_status)) {
            return $this->post_data->post_status;
        }
        return null;
    }

    public function getPostType()
    {
        if (isset($this->post_data->post_type)) {
            return $this->post_data->post_type;
        }
        return null;
    }

    public function getParendId()
    {
        return self::getProductParentId($this->product);
    }

    public function getStatus()
    {
        return self::getProductStatus($this->product);
    }

    public function getDescription()
    {
        return self::getProductDescription($this->product);
    }

    public function getName()
    {
        return self::getProductName($this->product);
    }

    public function getType()
    {
        if (LS_Helper::isWooVersionLessThan_2_4_15()) {
            return $this->product->post->post_type;
        }

        return $this->product->get_type();
    }

    public function isSimple()
    {
        return self::isSimpleProduct($this->product);
    }

    public function isVariable()
    {
        return self::isVariableProduct($this->product);
    }

    public function isVariation()
    {
        return self::isVariationProduct($this->product);
    }

    public function getSku()
    {
        return $this->product->get_sku();
    }

    public static function getProductParentId(WC_Product $product)
    {
        if (LS_Helper::isWooVersionLessThan_2_4_15()) {
            return $product->post->post_parent;
        }

        return $product->get_parent_id();
    }

    public static function hasChildren(WC_Product $product)
    {
        return $product->has_child();
    }

    public static function isVariableAndDontHaveChildren(WC_Product $product)
    {
        if (true == self::isVariableProduct($product)) {
            $has_children = $product->has_child();
            if (true == $has_children) {
                return true;
            }
        }

        return false;
    }

    public static function getProductStatus(WC_Product $product)
    {
        if (LS_Helper::isWooVersionLessThan_2_4_15()) {
            return $product->post->post_status;
        }

        return $product->get_status();
    }

    public static function getProductDescription(WC_Product $product)
    {
        if (LS_Helper::isWooVersionLessThan_2_4_15()) {
            return remove_escaping_str(html_entity_decode($product->post->post_content));
        }

        return remove_escaping_str(html_entity_decode($product->get_description()));
    }

    public static function getProductName(WC_Product $product)
    {
        if (LS_Helper::isWooVersionLessThan_2_4_15()) {
            return html_entity_decode(remove_escaping_str($product->get_title()));
        }

        return html_entity_decode(remove_escaping_str($product->get_name()));
    }

    public static function isSimpleProduct(WC_Product $product)
    {
        return $product->is_type('simple');
    }

    public static function isVariableProduct(WC_Product $product)
    {
        return $product->is_type('variable');
    }

    public static function isVariationProduct(WC_Product $product)
    {
        if (LS_Helper::isWooVersionLessThan_2_4_15()) {
            if ($product->post->post_type == 'product_variation') {
                return true;
            }
            return false;
        }

        return $product->is_type('variation');
    }

    public static function getProductParendId(WC_Product $product)
    {
        if (LS_Helper::isWooVersionLessThan_2_4_15()) {
            return $product->post->post_parent;
        }

        return $product->get_parent_id();
    }

    public static function get_woocommerce_duplicate_or_empty_skus()
    {
        $duplicate_products = LS_Woo_Product::get_woo_duplicate_sku();
        $emptyProductSkus = LS_Woo_Product::get_woo_empty_sku();
        $products_data = array_merge($duplicate_products, $emptyProductSkus);

        return $products_data;
    }


    public static function get_qbo_connected_products($orderBy = '', $order = 'asc', $search_key = '')
    {
        global $wpdb;

        $orderBySql = '';
        if (!empty($orderBy)) {
            $orderBySql = 'ORDER BY ' . $orderBy . ' ' . strtoupper($order);
        } else {
            $orderBySql = 'ORDER BY wpmeta.meta_value ASC';
        }

        $searchWhere = "AND wpmeta.meta_key IN ('_ls_pid') AND wpmeta.meta_value != ''  ";
        if (!empty($search_key)) {
            $prepare_sku_search = $wpdb->prepare("wpmeta.meta_key = '_sku' AND wpmeta.meta_value LIKE %s ", '%' . $search_key . '%');
            $prepare_product_name_search = $wpdb->prepare(" OR wposts.post_title LIKE %s ", '%' . $search_key . '%');
            $prepare_product_desc_search = $wpdb->prepare(" OR wposts.post_content LIKE %s ", '%' . $search_key . '%');

            $searchWhere = " AND (" . $prepare_sku_search . $prepare_product_name_search . $prepare_product_desc_search . ")";
        }

        $groupBy = ' GROUP BY wposts.ID ';
        $sql = "
					SELECT
							wposts.ID,
							wposts.post_title AS product_name,
                            wposts.post_status AS product_status,
                            wpmeta.meta_key,
                            wpmeta.meta_value,
                            wposts.post_type AS product_type,
                            wposts.post_parent AS product_parent
					FROM $wpdb->postmeta AS wpmeta
					INNER JOIN $wpdb->posts as wposts on ( wposts.ID = wpmeta.post_id )
					WHERE
					      wposts.post_type IN('product','product_variation') " . $searchWhere . $groupBy . $orderBySql;

        //get all products with empty sku
        $results = $wpdb->get_results($sql, ARRAY_A);
        foreach ($results as $key => $result) {
            $qbo_id = get_post_meta($result['ID'], '_ls_pid', true);
            if (empty($qbo_id)) {
                unset($results[$key]);
            } else {
                $result['qbo_id'] = $qbo_id;
                $results[$key] = $result;
            }
        }

        return $results;
    }


}