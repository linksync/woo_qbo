<?php

class LS_QBO_Product_Helper
{
    public static function isSyncAbleToQuickBooks($type)
    {
        $bool = false;
        $product_types = array('simple', 'variation');

        if (in_array($type, $product_types)) {
            $bool = true;
        }
        return $bool;
    }
}