<?php if (!defined('ABSPATH')) exit('Access is Denied');

class LS_Simple_Product
{

    /**
     * JSON representation of the product
     * @var null
     */
    public $product;

    public $product_variants = null;

    public function __construct($product = null)
    {
        if (!empty($product)) {

            if (!is_array($product)) {
                $this->product = json_decode($product, true);
            } else {
                $this->product = $product;
            }

            if ($this->has_variant()) {

                foreach ($this->get_variants() as $variant) {
                    array_push($this->product_variants, new LS_Variant_Product($this->get_sku(), $variant));
                }

            }
        }
    }

    public function get_data($key)
    {
        return isset($this->product[$key]) ? $this->product[$key] : null;
    }

    public function set_data($key, $value)
    {
        if(!empty($key)){
            $this->product[$key] = $value;
        }

    }

    public function unset_data($key)
    {
        if(isset($this->product[$key])){
            unset($this->product[$key]);
        }
    }


    public function get_purchasing_information()
    {
        return $this->get_data('purchase_description');
    }

    public function has_variant()
    {
        return count($this->get_variants()) > 0 ? true : false;
    }

    public function get_id()
    {
        return $this->get_data('id');
    }

    public function set_id($id)
    {
        $this->set_data('id', $id);
    }

    public function get_name()
    {
        return $this->get_data('name');
    }

    public function set_name($value)
    {
        $this->set_data('name', $value);
    }


    public function get_description()
    {
        return $this->get_data('description');
    }

    public function set_description($value)
    {
        $this->set_data('description', $value);
    }

    public function get_sku()
    {
        return $this->get_data('sku');
    }

    public function set_sku($value)
    {
        $this->set_data('sku', $value);
    }

    public function is_active()
    {
        return $this->get_data('active');
    }

    public function set_active($value)
    {
        $this->set_data('active', $value);
    }

    public function get_cost_price()
    {
        return $this->get_data('cost_price');
    }

    public function set_cost_price($value)
    {
        $this->set_data('cost_price', $value);
    }

    public function get_list_price()
    {
        return $this->get_data('list_price');
    }

    public function set_list_price($value)
    {
        $this->set_data('list_price', $value);
    }

    public function get_sell_price()
    {
        return $this->get_data('sell_price');
    }

    public function set_sell_price($value)
    {
        $this->set_data('sell_price', $value);
    }

    public function get_taxable()
    {
        return $this->get_data('taxable');
    }

    public function set_taxable($value)
    {
        $this->set_data('taxable', $value);
    }

    public function get_tax_value()
    {
        return $this->get_data('tax_value');
    }

    public function set_tax_value($value)
    {
        $this->set_data('tax_value', $value);
    }

    public function get_tax_name()
    {
        return $this->get_data('tax_name');
    }

    public function set_tax_name($value)
    {
        $this->set_data('tax_name', $value);
    }

    public function get_tax_rate()
    {
        return $this->get_data('tax_rate');
    }

    public function set_tax_rate($value)
    {
        $this->set_data('tax_rate', $value);
    }

    public function get_tax_id()
    {
        return $this->get_data('tax_id');
    }

    public function set_tax_id($value)
    {
        $this->set_data('tax_id', $value);
    }

    public function does_includes_tax()
    {
        return $this->get_data('includes_tax');
    }
    public function set_includes_tax($value)
    {
        $this->set_data('includes_tax', $value);
    }

    public function get_quantity()
    {
        return $this->get_data('quantity');
    }
    public function set_quantity($value)
    {
        $this->set_data('quantity', $value);
    }

    public function remove_quantity()
    {
        $this->unset_data('quantity');
    }

    public function get_product_type()
    {
        return $this->get_data('product_type');
    }
    public function set_product_type($value)
    {
        $this->set_data('product_type', $value);
    }

    public function get_income_account_id()
    {
        return $this->get_data('income_account_id');
    }
    public function set_income_account_id($value)
    {
        $this->set_data('income_account_id', $value);
    }

    public function get_expense_account_id()
    {
        return $this->get_data('expense_account_id');
    }
    public function set_expense_account_id($value)
    {
        $this->set_data('expense_account_id', $value);
    }

    public function get_asset_account_id()
    {
        return $this->get_data('asset_account_id');
    }
    public function set_asset_account_id($value)
    {
        $this->set_data('asset_account_id', $value);
    }

    public function get_update_at()
    {
        return $this->get_data('update_at');
    }

    public function remove_update_at()
    {
        $this->unset_data('update_at');
    }

    public function get_deleted_at()
    {
        return $this->get_data('deleted_at');
    }

    public function remove_deleted_at()
    {
        $this->unset_data('deleted_at');
    }

    public function get_images()
    {
        return $this->get_data('images');
    }

    public function get_variants()
    {
        return $this->get_data('variants');
    }
    public function set_variants($value)
    {
        $this->set_data('variants', $value);
    }

    public function get_tags()
    {
        return $this->get_data('tags');
    }
    public function set_tags($value)
    {
        $this->set_data('tags', $value);
    }

    public function get_outlets()
    {
        return $this->get_data('outlets');
    }

    public function get_price_books()
    {
        return $this->get_data('price_books');
    }

    public function get_brands()
    {
        return $this->get_data('brands');
    }

    public function get_categories()
    {
        return $this->get_data('category');
    }

    public function get_product_array()
    {
        return $this->product;
    }

    public function get_product_json()
    {
        return json_encode($this->get_product_array());
    }
}