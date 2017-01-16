<?php if ( ! defined( 'ABSPATH' ) ) exit('Access is Denied');

/**
 * Check if WooCommerce is active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {


	class LS_QBO{

		/**
		 * @var LS_QBO instance
		 */
		protected static $_instance = null;

		public static $api = null;

		/**
		 * LS_QBO constructor.
		 */
		public function __construct() {
			$this->includes();
			$this->load_hooks();

			do_action( 'ls_qbo_loaded' );
		}

		public function load_hooks(){
			$this->load_ajax_hooks();
		}

		public function load_ajax_hooks(){
			add_action('wp_ajax_save_needed_ps_data_from_qbo', array($this, 'save_needed_ps_data_from_qbo'));
			add_action('wp_ajax_show_ps_view', array( LS_QBO_Product_Form::instance(), 'product_syncing_settings'));

			add_action('wp_ajax_save_needed_os_data_from_qbo', array($this, 'save_needed_os_data_from_qbo'));
			add_action('wp_ajax_show_or_view', array( LS_QBO_Order_Form::instance(), 'order_syncing_settings'));

			add_action( 'wp_ajax_get_laid_info' , array($this, 'get_qbo_laid_info') );
		}

		public function show_notices_product_edit_screen(){
			global $typenow;
			if( 'product' == $typenow && isset($_GET['post'])){
				$product_id = (int) $_GET['post'];
				$product = new WC_Product( $product_id );
				$sku = $product->get_sku();
				$unique_sku = LS_Woo_Product::product_has_unique_sku($product_id, $sku);

				$errors = null;
				if ( ! $unique_sku ) {
					$url = admin_url('edit.php?post_status=trash&post_type=product&s='.$sku);
					$errors[] = 'Linksync detected that the SKU of this product existed in product trash. <a href="'.$url.'" target="_blank">Click here to view</a>';
				}else if( '' == $sku ){
					$errors[] = 'Linksync detected that the SKU of this product is empty.';
				}

				if( !empty($errors)){
					LS_QBO()->show_errors($errors);
				}
			}

		}

		public function show_errors( $errors ){
			?>
			<div id="ls_errors" class="error">
				<?php
					foreach($errors as $error){
						echo '<p>'.wp_kses_post($error).'</p>';
					}
				?>
			</div>
			<?php
		}

		/**
		 * get the current laid info from LWS api
		 */
		public function get_qbo_laid_info(){
			$qbo_api = LS_QBO()->api();
			update_option( 'ls_laid_info', $qbo_api->get_laid_info() );
			wp_send_json( get_option('ls_laid_info') );
		}

		/**
		 * Returns Current Laid information
		 * @return mixed|void
		 */
		public function get_laid_info(){
			return get_option('ls_laid_info');
		}

		/**
		 * returns string UTC time of the last syncing
		 * @return string
		 */
		public function get_last_sync(){
			return get_option( 'ls_last_sync' );
		}

		/**
		 * returns string UTC time of the last syncing
		 * @return string
		 */
		public function update_last_sync(){
			$laid_info = LS_QBO()->get_laid_info();
			update_option( 'ls_last_sync', $laid_info['time'] );
			return get_last_sync();
		}

		/**
		 * @param $account_name
		 * @return array|null
		 */
		public function get_expense_account_by( $key, $value ){
			if( !empty($account_name) ){
				$expense_accounts = get_option( 'ls_expense_accounts' );
				if( !empty($expense_accounts)){
					return LS_QBO()->search_accounts( $key, $value, $expense_accounts );
				}
			}

			return null;
		}

		/**
		 * @param $account_name
		 * @return array|null
		 */
		public function get_inventory_asset_account_by( $key, $value ){
			if( !empty($account_name)){
				$inventory_asset_accounts = get_option( 'ls_asset_accounts' );
				if(!empty($inventory_asset_accounts)){
					return LS_QBO()->search_accounts( $key , $value, $inventory_asset_accounts );
				}
			}

			return null;
		}

		/**
		 * @param $account_name
		 * @return array|null
		 */
		public function get_income_account_by( $key, $value){
			if( !empty($account_name) ){
				$income_accounts = get_option( 'ls_income_accounts' );
				if( !empty($income_accounts) ){
					return LS_QBO()->search_accounts( $key, $value, $income_accounts );
				}
			}
			return null;
		}

		/**
		 * Search Accounts array
		 * @param $key
		 * @param $value
		 * @param $array
		 * @return null|array
		 */
		public function search_accounts( $key, $value , $array ){
			$found = null;
			if( is_array($array)){
				foreach( $array as $account ){
					if( $account[$key] == $value ){
						$found = $account;
						break;
					}
				}
			}
			return $found;
		}

		public function save_needed_ps_data_from_qbo(){
			$qbo_api = LS_QBO()->api();
			$product_options = LS_QBO()->product_option();

			set_time_limit(0);
			$qbo_info = $qbo_api->get_qbo_info();

			update_option( 'ls_qbo_info', $qbo_info );
			update_option( 'ls_asset_accounts', $qbo_api->get_assets_accounts());
			update_option( 'ls_expense_accounts', $qbo_api->get_expense_accounts());
			update_option( 'ls_income_accounts', $qbo_api->get_income_accounts());
			update_option( 'ls_qbo_tax_classes', $qbo_api->get_all_tax_rate());
			update_option( 'ls_qbo_duplicate_products', $qbo_api->product()->get_duplicate_products());

			LS_QBO()->set_quantity_option_base_on_qboinfo( $qbo_info );

			die();
		}

		public function save_needed_os_data_from_qbo(){
			$qbo_api = LS_QBO()->api();

			set_time_limit(0);
			$qbo_info = $qbo_api->get_qbo_info();
			$qbo_classes = $qbo_api->get_all_active_clases();

			update_option( 'ls_asset_accounts', $qbo_api->get_assets_accounts());
			update_option( 'ls_location_list', $qbo_api->get_all_active_location());
			update_option( 'ls_qbo_classes', $qbo_classes);
			update_option( 'ls_qbo_tax_classes', $qbo_api->get_all_tax_rate());
			update_option( 'ls_qbo_payment_methods', $qbo_api->get_all_payment_methods());
			update_option( 'ls_qbo_info', $qbo_info );

			LS_QBO()->set_quantity_option_base_on_qboinfo( $qbo_info );

			die();
		}

		/**
		 * Will return QBO subscription
		 * @return null|string
		 */
		public function get_subscription(){
			$qbo_info = get_option( 'ls_qbo_info' );
			if( !empty($qbo_info) && isset($qbo_info['version']) ){
				return $qbo_info['version'];
			}
			return null;
		}

		/**
		 * Whether the subscription related to the laid key is 'QuickBooks Online Plus'
		 * @return bool
		 */
		public function is_qbo_plus(){
			$bool = false;

			$subscription = LS_QBO()->get_subscription();
			if( !empty($subscription) ){
				if( 'QuickBooks Online Plus' == $subscription){
					$bool = true;
				}
			}
			return $bool;
		}

		public function get_update_url(){
			$url = admin_url( 'admin-ajax.php?action='.get_option('webhook_url_code') );
			return $url;
		}

		/**
		 * @param $qbo_info
		 */
		public function set_quantity_option_base_on_qboinfo( $qbo_info = null ){

			if( $qbo_info == null){
				$qbo_info = get_option( 'ls_qbo_info' );
			}

			if( isset($qbo_info['version']) ){
				if( 'QuickBooks Online Plus' != $qbo_info['version'] ){

					if( isset($qbo_info['trackInventory']) && $qbo_info['trackInventory'] == false ){
						//Make sure to off this option if qbo version is not Quickbooks Onlie Plus (Docs 11.2.6 , 11.2.6.1)
						LS_QBO()->product_option()->update_quantity( 'off' );
						LS_QBO()->product_option()->update_change_product_status( 'off' );

					}

				}
			}
		}

		/**
		 * Returns whether the current user with LAID key can use Quantity Option base on QuickBooks Information
		 * @param null $qbo_info
		 * @return bool
		 */
		public function can_use_quantity_option( $qbo_info = null ){
			$bool = true;

			if( $qbo_info == null){
				$qbo_info = get_option( 'ls_qbo_info' );
			}

			if( isset($qbo_info['version']) ){
				if( 'QuickBooks Online Plus' != $qbo_info['version'] ){

					if( isset($qbo_info['trackInventory']) && $qbo_info['trackInventory'] == false ){
						$bool = false;
					}

				}
			}

			return $bool;
		}

		/**
		 * LS_QBO get self instance
		 */
		public static function instance(){
			if( is_null( self::$_instance ) ){
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/**
		 * Get QBO Product syncing option
		 * @return LS_QBO_Product_Option instance
		 */
		public static function product_option(){
			return LS_QBO_Product_Option::instance();
		}

		/**
		 * Get QBO Order syncing option
		 * @return LS_QBO_Order_Option instance
		 */
		public static function order_option(){
			return LS_QBO_Order_Option::instance();
		}

		public function api(){

			if( is_null(self::$api) ){
				self::$api = new LS_QBO_Api(LS_ApiController::get_api());
			}

			return self::$api;
		}

        public function options(){
            return LS_QBO_Options::instance();
        }

		/**
		 * Include required core files for QBO
		 */
		public function includes(){

			include_once LS_INC_DIR. 'apps/qbo/constants/class-ls-qbo-item-type.php';
			include_once LS_INC_DIR. 'apps/qbo/constants/class-ls-qbo-receipt-type.php';
			include_once LS_INC_DIR. 'apps/ls-core-functions.php';
			include_once LS_INC_DIR. 'apps/class-ls-woo-tax.php';
			include_once LS_INC_DIR. 'apps/class-ls-woo-product.php';
			include_once LS_INC_DIR. 'apps/class-ls-woo-order-line-item.php';
            include_once LS_INC_DIR. 'apps/qbo/class-ls-qbo-options.php';

			include_once LS_INC_DIR. 'api/ls-api.php';
			include_once LS_INC_DIR. 'api/ls-api-controller.php';
			include_once LS_INC_DIR. 'apps/class-ls-product-api.php';
			include_once LS_INC_DIR. 'apps/class-ls-order-api.php';
			include_once LS_INC_DIR. 'apps/qbo/class-ls-qbo-api.php';


			include_once LS_INC_DIR. 'apps/qbo/class-ls-qbo-product-options.php';
			include_once LS_INC_DIR. 'apps/qbo/class-ls-qbo-order-options.php';


			include_once LS_INC_DIR. 'apps/qbo/class-ls-qbo-product-form.php';
			include_once LS_INC_DIR. 'apps/qbo/class-ls-qbo-order-form.php';

			include_once LS_INC_DIR. 'apps/class-ls-product-meta.php';
			include_once LS_INC_DIR. 'apps/class-ls-simple-product.php';
			include_once LS_INC_DIR. 'apps/class-ls-variant-product.php';

			include_once LS_INC_DIR. 'apps/class-ls-json-product-factory.php';
			include_once LS_INC_DIR. 'apps/class-ls-json-order-factory.php';

			if(is_qbo()){
				include_once LS_INC_DIR. 'apps/qbo/class-ls-qbo-sync.php';
				include_once LS_INC_DIR. 'apps/class-ls-notice.php';
				include_once LS_INC_DIR. 'apps/qbo/class-ls-qbo-ajax.php';
			}


		}

		/**
		 * Load QBO related styles and scripts
		 */
		public function enqueue_scripts_and_styles(){

			wp_enqueue_style( 'ls-qbo', LS_ASSETS_URL.'css/qbo-styles.css' );
			wp_enqueue_script( 'ls-main-qbo-js', LS_ASSETS_URL.'js/qbo.js', array( 'jquery' ) );

			if (isset($_GET['page']) && $_GET['page'] == 'linksync') {

				if( isset($_GET['setting']) ){
					if ($_GET['setting'] == 'product_config') {

						wp_enqueue_script( 'ls-qbo-product-syncing', LS_ASSETS_URL.'js/qbo-product-syncing.js', array( 'jquery' ) );

					} elseif ($_GET['setting'] == 'order_config') {

						wp_enqueue_script( 'ls-qbo-product-syncing', LS_ASSETS_URL.'js/qbo-order-syncing.js', array( 'jquery' ) );

					}
				}else{
					//configuration tab
					wp_enqueue_script( 'ls-qbo-configuration', LS_ASSETS_URL.'js/qbo-configuration.js', array( 'jquery' ) );
				}
			}

		}

		/**
		 * Show Quick books online views
		 */
		public function view(){

			if (isset($_GET['setting'],$_GET['page']) && $_GET['page'] == 'linksync') {


				if ($_GET['setting'] == 'logs') {

					include_once LS_INC_DIR. 'view/ls-plugins-tab-logs.php';

				}  elseif ($_GET['setting'] == 'product_config') {



				} elseif ($_GET['setting'] == 'order_config') {



				} else {
					include_once LS_INC_DIR . 'view/ls-plugins-tab-configuration.php';
				}
			} else {
				include_once LS_INC_DIR . 'view/ls-plugins-tab-configuration.php';
			}

		}

		public function show_qbo_duplicate_products( $list ){
			?>
			<div>
				<p>You have duplicate products or empty skus in your <a href="https://qbo.intuit.com/app/items" target="_blank">QuickBook Online</a> </p>
				<table class="widefat">
					<thead>
					<tr>
						<th>Name</th>
						<th>SKU</th>
					</tr>
					</thead>
					<tbody>
					<?php
					foreach($list as $product_ref ){
						$product = new LS_Simple_Product($product_ref);
						?>
						<tr>
							<td><?php echo $product->get_name(); ?></td>
							<td><?php echo ($product->get_sku() == '' ) ? "Empty SKU('')" : $product->get_sku() ; ?></td>
						</tr>
						<?php
					}

					?>
					</tbody>
				</table>
			</div>
			<?php
		}

		public function show_woo_duplicate_products( $list, $emptySkus = array(), $duplicateSkus = array() ){
			?>
			<div>
                <p>
                    You have duplicate products or empty skus in your
                    <a target="_blank"
                       href="<?php echo admin_url('edit.php?post_type=product'); ?>">Woocommerce</a>

                    <table>
                        <tr>
                            <?php
                                if(!empty($emptySkus)){
                                    ?>
                                    <td>
                                        <form method="post" id="frm-set-sku-automatically">
                                            <input id="set-sku-automatically"
                                                   class="button button-primary button-large "
                                                   type="submit"
                                                   name="setskuautomatically"
                                                   value="Set Empty SKU Automatically"
                                                   style="float: left;">

                                            <span id="ls-spinner" class="spinner is-active"
                                                  style="float: left;display: none;"></span><br/><br/>
                                        </form>
                                    </td>
                                    <?php
                                }

                                if(!empty($duplicateSkus)){
                                    ?>
                                    <td>
                                        <form method="post" id="frm-append-productid-to-duplicate-sku">
                                            <input id="append-sku-automatically"
                                                   class="button button-primary button-large "
                                                   type="submit"
                                                   name="setskuautomatically"
                                                   value="Make SKU Unique"
                                                   style="float: left;">

                                            <span id="ls-spinner2" class="spinner is-active"
                                                  style="float: left;display: none;"></span><br/><br/>
                                        </form>
                                    </td>
                                    <?php
                                }
                            ?>


                        </tr>
                    </table>

                </p>
				<table class="widefat">
					<thead>
						<tr>
							<th>Name</th>
							<th>SKU</th>
							<th>Status</th>
						</tr>
					</thead>
					<tbody>
					<?php
						foreach($list as $product_ref ){
							$product = new WC_Product($product_ref['ID']);
							$edit_product_link = get_edit_post_link($product_ref['ID']);
							if( empty($edit_product_link)){
								$edit_product_link = get_edit_post_link($product->get_parent());
							}

							if( 'trash' == $product->post->post_status ){
								$edit_product_link = admin_url('edit.php?post_status=trash&post_type=product&s='.$product->get_sku());
							}
							?>
							<tr>
								<td><?php echo '<a href="', $edit_product_link ,'" target="_blank">'.$product->get_title().'</a>';?></td>
								<td><?php echo ($product_ref['meta_value'] == '' ) ? "Empty SKU('')" : $product_ref['meta_value'] ; ?></td>
								<td><?php echo $product->post->post_status; ?></td>
							</tr>
							<?php
						}

					?>
					</tbody>
				</table>
			</div>
			<?php
		}

		public function show_shipping_and_discount_guide($user_options){

			if( isset($user_options['qbo_info']['allowDiscount']) || isset($user_options['qbo_info']['allowShipping']) ){
				echo '<p> Please go to your
						<a target="_blank" href="https://sg.qbo.intuit.com/app/settings?p=Sales">QuickBooks Settings</a> ';
				if(!$user_options['qbo_info']['allowDiscount'] && !$user_options['qbo_info']['allowShipping']){
					echo 'and turn on <b>Discount</b> and <b>Shipping</b> options to use linksync.';
				}else if(!$user_options['qbo_info']['allowDiscount']){
					echo 'and turn on <b>Discount</b> option to use linksync.';
				}else if(!$user_options['qbo_info']['allowShipping']){
					echo 'and turn on <b>Shipping</b> option to use linksync.';
				}
				echo '</p>';
			}else if($user_options['qbo_info']['errorCode']){
				echo '<p class="color-red"><b> Error ',$user_options['qbo_info']['errorCode'],': ',$user_options['qbo_info']['userMessage'],'</b></p>';
			}

		}

	}

	/**
	 * Returns the main instance of LS_QBO to prevent the need to use globals.
	 */
	function LS_QBO() {
		return LS_QBO::instance();
	}

	// Global for backwards compatibility.
	$GLOBALS['ls_qbo'] = LS_QBO();

	add_action( 'admin_notices', array( LS_QBO(), 'show_notices_product_edit_screen') );
}
