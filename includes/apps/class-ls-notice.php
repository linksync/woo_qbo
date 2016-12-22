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
		if ('shop_order' == $current_screen->id) {
			$orderId = empty($_GET['post']) ? null : $_GET['post'];
			$order = new WC_Order($orderId);

			$orderSyncError = $order->ls_json_order_error;
			if (isset($orderSyncError['errorCode'])) {

				if (400 == $orderSyncError['errorCode']) {
					$this->errorNotice('Sync Order to QuickBooks: ('.$orderSyncError['technicalMessage'].')');
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

//new LS_Notice();