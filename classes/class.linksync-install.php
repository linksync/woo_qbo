<?php
/* 
 * All installation process
 */
 
class Linksync_installation {
	
	public static function init()
	{
		add_action( 'init', array( __CLASS__, 'install' ), 5 );
	}
	
	public static function install()
	{
		if(get_option('linksync_do_activation_redirect')) {
			delete_option( 'linksync_do_activation_redirect' );
			wp_safe_redirect( admin_url( 'admin.php?page=linksync-wizard' ) );
			exit();
		}
	}
	
	public static function wizard_handler()
	{
		?>
			<p id="logo"><img src="<?php echo LS_PLUGIN_URL ?>assets/images/linksync/logo.png" alt="" /></p>
			<div class="wizard-content">
				<div class="content-wrap">
					<?php
					
					$step = isset($_GET['step'])?$_GET['step']:1;
					
					switch($step)
					{
						case 1:
							// Set up API Key
							include_once(LS_PLUGIN_DIR.'includes/view/wizard/setup-api.php');
							break;
							
						case 2:
							// Set up Product syncing options
							include_once(LS_PLUGIN_DIR.'includes/view/wizard/product-syncing.php');
							break;
							
						case 3:
							// Set up Order syncing options
							include_once(LS_PLUGIN_DIR.'includes/view/wizard/order-syncing.php');
							break;
					}
					
					?>
				</div>
			</div>
		<?php
	}
}