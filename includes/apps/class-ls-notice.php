<?php if (!defined('ABSPATH')) exit('Access is Denied');

class LS_Notice
{

    public function __construct()
    {
        add_action('admin_notices', array($this, 'orderNotice'), 16);
    }


    public function orderNotice()
    {
        $current_screen = get_current_screen();
        $postid = empty($_GET['post']) ? null : $_GET['post'];
        if ('shop_order' == $current_screen->id) {

            $order = new WC_Order($postid);

            $orderSyncError = $order->ls_json_order_error;
            if (isset($orderSyncError['errorCode'])) {

                if (400 == $orderSyncError['errorCode']) {
                    $this->errorNotice('Sync Order to QuickBooks Failed: (' . $orderSyncError['userMessage'] . ')');
                }
            }

        }

        if ('product' == $current_screen->id) {
            $productMeta = new LS_Product_Meta($postid);
            $productSyncError = $productMeta->_ls_json_product_error;
            if (isset($productSyncError['errorCode'])) {
                $toUserMessage = empty($productSyncError['technicalMessage']) ? $productSyncError['userMessage'] : $productSyncError['technicalMessage'];
                if (!empty($toUserMessage)) {
                    $this->errorNotice('Sync Product to QuickBooks Failed: (' . $toUserMessage . ')');
                }

            }
        }

    }

    public function notice($message, $class = 'error')
    {
        ?>
        <div class="<?php echo $class; ?> notice">
            <p><?php echo $message; ?></p>
        </div>
        <?php
    }

    public function errorNotice($message)
    {
        $this->notice($message);
    }

    public function updateNotice($message)
    {
        $this->notice($message, 'updated');
    }

}

new LS_Notice();