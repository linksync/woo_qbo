<?php

class LS_QBO_Product_Helper
{
    public static function isSyncAbleToQuickBooks($type)
    {
        $bool = false;
        $product_types = array('product', 'product_variation', 'simple', 'variation', 'subscription', 'bundle');

        if (in_array($type, $product_types)) {
            $bool = true;
        }
        return $bool;
    }

    public static function prepareProductNameForQuickBooks($productName)
    {
        $truncated100CharProductName = mb_substr($productName, 0, 100);
        $truncated100CharProductName =  htmlentities($truncated100CharProductName, ENT_QUOTES);

        return $truncated100CharProductName;
    }


}