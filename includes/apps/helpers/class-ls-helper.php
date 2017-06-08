<?php

class LS_Helper
{
    public static function isWooVersionLessThan_2_4_15()
    {
        $wooCommerceVersion = LS_QBO()->options()->get_woocommerce_version();
        if (version_compare($wooCommerceVersion, '2.6.15', '<')) {
            return true;
        }

        return false;
    }
}