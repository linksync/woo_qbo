<?php if ( ! defined( 'ABSPATH' ) ) exit('Access is Denied'); ?>

<div class="se-pre-con"></div>

<?php

    $LAIDKey = LS_ApiController::get_current_laid();

    if (!empty($LAIDKey)) {
        $ls_connected_to    = get_option('linksync_connectedto');
        $ls_connected_with  = get_option('linksync_connectionwith');

        if ( is_vend() ) {
            
            include_once LS_INC_DIR.'apps/vend/vend_order_config.php';

        }else {

            LS_User_Helper::setUpLaidInfoMessage();
        }
    } else {
        ?>
        <div class="error notice">
            <h3><?php echo LS_Constants::NOT_CONNECTED_MISSING_API_KEY; ?> </h3>
        </div>
        <?php
    }
?> 