<?php if ( ! defined( 'ABSPATH' ) ) exit('Access is Denied');

class LS_QBO_Sync{


	public function __construct() {
		add_action( 'wp_ajax_qbo_get_products', array( 'LS_QBO_Sync', 'qbo_get_products_callback' ) );
		add_action( 'wp_ajax_import_to_woo', array( 'LS_QBO_Sync', 'import_qbo_product_to_woo' ) );

		add_action( 'wp_ajax_woo_get_products', array( 'LS_QBO_Sync', 'woo_get_products_callback' ) );
		add_action( 'wp_ajax_import_to_qbo', array( 'LS_QBO_Sync', 'import_woo_product_to_qbo' ) );

		add_action( 'wp_ajax_since_last_sync', array('LS_QBO_Sync','qbo_get_products_since_last_update') );

		$product_options = LS_QBO()->product_option();
		$current_sync_option = $product_options->get_current_product_syncing_settings();

		if ('disabled' != $current_sync_option[ 'sync_type' ]) {

			$delete = $current_sync_option[ 'delete' ];
			if ('on' == $delete) {
				/**
				 *Delete product in quickbooks if delete option is "on" and
				 *user is deleting woocommerce product permanently
				 */
				add_action( 'before_delete_post', array( 'LS_QBO_Sync', 'delete_product' ) );
			}

			//Save product to quickbooks
			self::add_action_save_post();
		}




		$order_options = LS_QBO()->order_option();
		$current_order_options = $order_options->get_current_order_syncing_settings();

		if( 'disabled' !=  $current_order_options['sync_type'] ){
			$order_action_hooks = ls_woo_order_hook_names();
			//Add the action when the user completes the payment and when the order was created via admin page
			//$order_action_hooks[] = 'woocommerce_thankyou';
			$order_action_hooks[] = 'woocommerce_process_shop_order_meta';
			foreach( $order_action_hooks as $order_action_hook ){
				add_action( $order_action_hook, array( 'LS_QBO_Sync', 'import_single_order_to_qbo' ) );
			}
		}

		$wh_code = get_option('webhook_url_code');
		add_action( 'wp_ajax_'.$wh_code, array('LS_QBO_Sync', 'sync_triggered_by_lws') );
		add_action( 'wp_ajax_nopriv_'.$wh_code, array('LS_QBO_Sync', 'sync_triggered_by_lws') );

		add_action( 'wp_ajax_product_meta', array('LS_QBO_Sync', 'product_meta') );
		add_action( 'wp_ajax_nopriv_product_meta',array('LS_QBO_Sync', 'product_meta') );
	}

	public static function product_meta(){
		if(isset($_REQUEST['product_id'])){
			$product_id = $_REQUEST['product_id'];
			echo json_encode(get_post_meta($product_id));
		}
		die();
	}

	public static function sync_triggered_by_lws(){
		$last_sync = LS_QBO()->options()->last_product_update();
		LSC_Log::add_dev_success( 'LS_QBO_Sync::sync_triggered_by_lws', 'Linksync triggered a sync.<br/> Last sync :'.$last_sync.'<br/> Current Server Time: '.current_time('mysql') );

		if( empty($last_sync) ){
			set_time_limit(0);
			LS_QBO_Sync::product_to_woo();
		}else{
			set_time_limit(0);
			LS_QBO_Sync::product_to_woo_since_last_update();
		}

		die();
	}



	/**
	 * @param $product_id
	 * @param $post
	 * @param $update true on update false on save
	 */
	public static function save_product( $product_id, $post, $update ){
		// Dont' send product for revisions or autosaves and auto-draft post_status
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $product_id;
		}

		// Don't save revisions and autosaves
		if ( wp_is_post_revision( $product_id ) || wp_is_post_autosave( $product_id ) ) {
			return $product_id;
		}

		// Check post type is product
		if ( 'product' != $post->post_type || 'auto-draft' == $post->post_status ) {
			return $product_id;
		}

		//Do not send http post to linksync server if user is trashing product
		if ('trash' == $post->post_status) {
			return $product_id;
		}

		$product = wc_get_product($product_id);
		$has_children = $product->has_child();

		if(true == $has_children){
			$variation_ids = $product->get_children();
			if(!empty($variation_ids)){
				foreach($variation_ids as $variation_id){
					LS_QBO_Sync::import_single_product_to_qbo( $variation_id );
				}
			}
		}else{
			LS_QBO_Sync::import_single_product_to_qbo( $product_id );
		}


	}

	public static function import_woo_product_to_qbo(){

		if( !empty($_POST['p_id']) ){
			LS_QBO_Sync::import_single_product_to_qbo( $_POST['p_id'] );

			$msg = $_POST['product_number']." of ".$_POST['total_count']." Product(s)";
			wp_send_json( $msg );
		}
	}

	/**
	 * @param $product_id
	 */
	public static function import_single_product_to_qbo( $product_id ){

		set_time_limit(0);
		$product = new WC_Product( $product_id );
		$product_meta = new LS_Product_Meta( $product_id );

		$product_options = LS_QBO()->product_option();
		$current_sync_option = $product_options->get_current_product_syncing_settings();

		if( 'two_way' == $current_sync_option['sync_type'] || 'qbo_to_woo' == $current_sync_option['sync_type']){
			$product_type = $product->post->post_type;

			//Check if the post type is product
			if( is_woo_product( $product_type ) ){
				$json_product = new LS_Json_Product_Factory( $product );

				$qbo_product_id = $product_meta->get_product_id();
				if(!empty($qbo_product_id)){
					$json_product->set_id($qbo_product_id);
				}

				$json_product->set_name( $product->get_title() );

				if( 'on' == $product_options->description() ){
					$json_product->set_description( $product->post->post_content );
				}

				if( 'on' == $product_options->price() ){

					//Check whether the product is on sale or has sale price
					$on_sale = $product->is_on_sale();
					if( !empty($on_sale) ){

						$json_product->set_list_price( $product->get_sale_price() );
						$json_product->set_sell_price( $product->get_sale_price() );

					}else{

						$json_product->set_list_price( $product->get_regular_price() );
						$json_product->set_sell_price( $product->get_regular_price() );

					}

				}


				if( 'on' == $product_options->quantity() ){
					$stock_quantity = $product->get_stock_quantity();

					$quantity = !empty($stock_quantity) ? $stock_quantity : 0 ;
					$json_product->set_quantity( $quantity );

				}

				//Setting product type in qbo
				$json_product->set_product_type( LS_QBO_ItemType::NONINVENTORY );
				if( $product->is_virtual() ){
					$json_product->set_product_type( LS_QBO_ItemType::SERVICE );
					$json_product->set_quantity(null);
				}
				if( LS_QBO()->is_qbo_plus()){

					$json_product->set_product_type( LS_QBO_ItemType::INVENTORY );
					if( $product->is_virtual() ){
						$json_product->set_product_type( LS_QBO_ItemType::SERVICE );
						$json_product->set_quantity(null);
					}

				}

				$sku = $product->get_sku();
				if (empty($sku)) {
					$sku = 'sku_' . $product_id;
					$product_meta->update_sku($sku);
				}
				$json_product->set_sku($sku);
				$json_product->set_active( ($product->post->post_status == 'publish') ? 1 : 0 );

				$income_account_id = ('' != $product_meta->get_income_account_id()) ? $product_meta->get_income_account_id() : $product_options->income_account();
				$expense_account_id = ('' != $product_meta->get_expense_account_id()) ? $product_meta->get_expense_account_id() :$product_options->expense_account();
				$asset_account_id = ('' != $product_meta->get_expense_account_id()) ? $product_meta->get_asset_account_id() : $product_options->inventory_asset_account();

				$json_product->set_income_account_id( $income_account_id );
				$json_product->set_expense_account_id( $expense_account_id );
				$json_product->set_asset_account_id( $asset_account_id );

				$qbo_includes_tax = ('false' === $product_meta->get_qbo_includes_tax()) ? false: true;
				$json_product->set_includes_tax( $qbo_includes_tax );

				$tax_id = ('' != $product_meta->get_tax_id()) ? $product_meta->get_tax_id() : null;
				$json_product->set_tax_id($tax_id);

				$tax_name = ('' != $product_meta->get_tax_name()) ? $product_meta->get_tax_name() : null;
				$json_product->set_tax_name($tax_name);

				$tax_value = ('' != $product_meta->get_tax_value()) ? $product_meta->get_tax_value() : null;
				$json_product->set_tax_value($tax_value);

				$tax_rate = ('' != $product_meta->get_tax_rate()) ? $product_meta->get_tax_rate() : null;
				$json_product->set_tax_rate($tax_rate);


				$j_product = $json_product->get_json_product();
				$result = LS_QBO()->api()->product()->save_product( $j_product );

				if(!empty($result['id'])){
					$product_meta->update_product_id( get_qbo_id($result['id']) );

					$product = new LS_Simple_Product($result);
					$product_meta->update_tax_id($product->get_tax_id());
					$product_meta->update_tax_name($product->get_tax_name());
					$product_meta->update_tax_rate($product->get_tax_rate());
					$product_meta->update_tax_value($product->get_tax_value());

					$qbo_tax_includes_tax = (false === $product->does_includes_tax()) ? 'false' : 'true';
					$product_meta->update_qbo_includes_tax($qbo_tax_includes_tax);

					$product_meta->update_income_account_id($product->get_income_account_id());
					$product_meta->update_expense_account_id($product->get_expense_account_id());
					$product_meta->update_asset_account_id($product->get_asset_account_id());
					$product_meta->update_product_type($product->get_product_type());

					LSC_Log::add_dev_success('LS_QBO_Sync::import_single_product_to_qbo', 'Product was imported to QuickBooks <br/> Product json being sent <br/>'.$j_product.'<br/> Response: <br/>'.json_encode($result));

				} else {
					LSC_Log::add_dev_failed('LS_QBO_Sync::import_single_product_to_qbo','Product ID: '.$product_id.'<br/><br/>Json product being sent: '.$j_product.'<br/><br/> Response: '.json_encode($result));
				}
			}
		}

	}


	/**
	 * Importing qbo orders to woocommerce
	 */
	public static function order_to_woo(){

	}

	/**
	 * Importing orders from qbo to woocommerce per one order
	 */
	public static function import_single_order_to_woo(){

	}

	/**
	 * Importing order from woocommerce to qbo per one order
	 */
	public static function import_single_order_to_qbo( $order_id ){
		set_time_limit(0);
		$order_option			=	LS_QBO()->order_option();

		$order					=	wc_get_order( $order_id );
		$selected_order_status = ls_selected_order_status_to_trigger_sync();

		$order_status = $order->get_status();
		if(!in_array($order_status, $selected_order_status)){
			//Do not continue importing if it status was not selected
			return;
		}

		$json_order				=	new LS_Order_Json_Factory();
		$included_tax           =   LS_Woo_Tax::is_included();
		$globalTaxCalculation   =   'TaxExcluded';
		if (true === $included_tax) {
			$globalTaxCalculation = 'TaxInclusive';
		} else if (null === $included_tax) {
			$globalTaxCalculation = 'NotApplicable';
		}

		$items					=	$order->get_items();
		$user					=	$order->get_user();
		$order_tax              =   $order->get_taxes();
		$shipping_method        =   $order->get_shipping_method();
		$qbo_tax                =   '';
		$tax_mapping            =   $order_option->tax_mapping();
		$total_discount         =   0;

		$primary_email_address	=	null;

		if( !empty($items) ){

			foreach( $items as $item ){
				if (isset($item['variation_id']) && !empty($item['variation_id'])) {
					$product_id = $item['variation_id'];
				} else {
					$product_id = $item['product_id'];
				}
				$pro_object = new WC_Product($product_id);
				$product_meta = new LS_Product_Meta($product_id);
				$orderLineItem = new LS_Woo_Order_Line_Item($item);
				$price = $pro_object->get_price();
				$discount = $orderLineItem->get_discount_amount();

				$qbo_tax = LS_Woo_Tax::get_mapped_quickbooks_tax_for_product(
					$tax_mapping,
					$order_tax,
					$orderLineItem->get_tax_class()
				);

				$taxName = ('' == $product_meta->get_tax_name()) ? null : $product_meta->get_tax_name();
				$taxId = ('' == $product_meta->get_tax_id()) ? null : $product_meta->get_tax_id();
				$taxRate = ('' == $product_meta->get_tax_rate()) ? null : $product_meta->get_tax_rate();
				$taxValue = ('' == $product_meta->get_tax_value()) ? null : $product_meta->get_tax_value();
				if ('' != $qbo_tax) {
					$taxName = (isset($qbo_tax[1])) ? $qbo_tax[1] : $taxName;
					$taxId =  (isset($qbo_tax[0])) ? $qbo_tax[0] : $taxId;
					$taxRate = (isset($qbo_tax[3])) ? $qbo_tax[3] : $taxRate;
				}

				$qboTaxName = $product_meta->get_tax_name();
				if ('none' == $product_meta->get_tax_status() && empty($qboTaxName) ) {
					$taxName = null;
					$taxId = null;
					$taxRate = null;
					$taxValue = null;
				}


				$products[] = array(
					'id'				=>	$product_meta->get_product_id(),
					'sku'				=>	$pro_object->get_sku(),
					'title'				=>	$pro_object->get_title(),
					'price'				=>	$price,
					'quantity'			=>	$item['qty'],
					'discountAmount'	=>	$discount,
					'taxName'			=>	$taxName,
					'taxId'				=>	$taxId,
					'taxRate'			=>	$taxRate,
					'taxValue'			=>	$taxValue,
					'discountTitle'		=>	'',
				);
				//Calculate total discount
				$total_discount += $discount;
			}

		}

		//set the total order discount to send
		if(0 !== $total_discount){
			$products[] = array(
				'title' => 'discount',
				'price' => $total_discount,
				'sku' => "discount"
			);
		}

		$export_types = $order_option->get_all_export_type();
		if( $export_types[0] == $order_option->customer_export() ){

			$phone = !empty($_POST['_billing_phone']) ? $_POST['_billing_phone'] : $order->billing_phone;
			// Formatted Addresses
			$filtered_billing_address = apply_filters( 'woocommerce_order_formatted_billing_address', array(
				'firstName'		=>	!empty($_POST['_billing_first_name']) ? $_POST['_billing_first_name'] : $order->billing_first_name,
				'lastName'		=>	!empty($_POST['_billing_last_name']) ? $_POST['_billing_last_name'] : $order->billing_last_name,
				'phone'			=>	$phone,
				'street1'		=>	!empty($_POST['_billing_address_1']) ? $_POST['_billing_address_1'] : $order->billing_address_1,
				'street2'		=>	!empty($_POST['_billing_address_2']) ? $_POST['_billing_address_2'] : $order->billing_address_2,
				'city'			=>	!empty($_POST['_billing_city']) ? $_POST['_billing_city'] : $order->billing_city,
				'state'			=>	!empty($_POST['_billing_state']) ? $_POST['_billing_state'] : $order->billing_state,
				'postalCode'    =>	!empty($_POST['_billing_postcode']) ? $_POST['_billing_postcode'] : $order->billing_postcode,
				'country'		=>	!empty($_POST['_billing_country']) ? $_POST['_billing_country'] : $order->billing_country,
				'company'		=>	!empty($_POST['_billing_company']) ? $_POST['_billing_company'] : $order->billing_company,
				'email_address'	=>	!empty($_POST['_billing_email']) ? $_POST['_billing_email'] : $order->billing_email
			), $order );

			$billing_address = array(
				'firstName'		=>	$filtered_billing_address['firstName'],
				'lastName'		=>	$filtered_billing_address['lastName'],
				'phone'			=>	$filtered_billing_address['phone'],
				'street1'		=>	$filtered_billing_address['street1'],
				'street2'		=>	$filtered_billing_address['street2'],
				'city'			=>	$filtered_billing_address['city'],
				'state'			=>	$filtered_billing_address['state'],
				'postalCode'    =>	$filtered_billing_address['postalCode'],
				'country'		=>	$filtered_billing_address['country'],
				'company'		=>	$filtered_billing_address['company'],
				'email_address'	=>	$filtered_billing_address['email_address']
			);

			$filtered_shipping_address = apply_filters( 'woocommerce_order_formatted_shipping_address', array(
				'firstName'		=>	!empty($_POST['_shipping_first_name']) ? $_POST['_shipping_first_name'] : $order->shipping_first_name,
				'lastName'		=>	!empty($_POST['_shipping_last_name']) ? $_POST['_shipping_last_name'] : $order->shipping_last_name,
				'phone'			=>	$phone,
				'street1'		=>	!empty($_POST['_shipping_address_1']) ? $_POST['_shipping_address_1'] : $order->shipping_address_1,
				'street2'		=>	!empty($_POST['_shipping_address_2']) ? $_POST['_shipping_address_2'] : $order->shipping_address_2,
				'city'			=>	!empty($_POST['_shipping_city']) ? $_POST['_shipping_city'] : $order->shipping_city,
				'state'			=>	!empty($_POST['_shipping_state']) ? $_POST['_shipping_state'] : $order->shipping_state,
				'postalCode'    =>	!empty($_POST['_shipping_postcode']) ? $_POST['_shipping_postcode'] : $order->shipping_postcode,
				'country'		=>	!empty($_POST['_shipping_country']) ? $_POST['_shipping_country'] : $order->shipping_country,
				'company'		=>	!empty($_POST['_shipping_company']) ? $_POST['_shipping_company'] : $order->shipping_company,
			), $order );

			$delivery_address = array(
				'firstName'		=>	$filtered_shipping_address['firstName'],
				'lastName'		=>	$filtered_shipping_address['lastName'],
				'phone'			=>	$filtered_shipping_address['phone'],
				'street1'		=>	$filtered_shipping_address['street1'],
				'street2'		=>	$filtered_shipping_address['street2'],
				'city'			=>	$filtered_shipping_address['city'],
				'state'			=>	$filtered_shipping_address['state'],
				'postalCode'	=>	$filtered_shipping_address['postalCode'],
				'country'		=>	$filtered_shipping_address['country'],
				'company' 		=>	$filtered_shipping_address['company']
			);
			$primary_email = !empty($primary_email_address) ? $primary_email_address : $billing_address['email_address'];
			unset( $billing_address['email_address'] );
		}

		//UTC Time
		date_default_timezone_set("UTC");
		$order_created = date( "Y-m-d H:i:s", time() );

		$order_no = $order->get_order_number();
		if (strpos($order_no, '#') !== false) {
			$order_no = str_replace('#', '', $order_no);
		}

		$source = 'WooCommerce';
		$comments = $source.' Order: '.$order_no;


		if(!empty($shipping_method)){

			$qbo_tax = LS_Woo_Tax::get_mapped_quickbooks_tax_for_shipping($tax_mapping, $order_tax);
			$shipping_cost = $order->get_total_shipping();
			$shipping_tax = $order->get_shipping_tax();


			$products[] = array(
				"price" => isset($shipping_cost) ? $shipping_cost : null,
				"quantity" => 1,
				"sku" => "shipping",
				'taxName' => ('' == $qbo_tax) ? null : (isset($qbo_tax[1])) ? $qbo_tax[1] : null,
				'taxId' => ('' == $qbo_tax) ? null : (isset($qbo_tax[0])) ? $qbo_tax[0] : null,
				'taxRate' => ('' == $qbo_tax) ? null : (isset($qbo_tax[3])) ? $qbo_tax[3] : null,
				'taxValue' => null
			);
		}

		$products = !empty($products) ? $products : null;

		$site_admin_email = LS_QBO()->options()->get_current_admin_email();
		$primary_email = !empty($primary_email) ? $primary_email : $site_admin_email;
		$billing_address = !empty($billing_address) ? $billing_address : null;
		$delivery_address = !empty($delivery_address) ? $delivery_address : null;

		$order_type = 'Invoice';
		$receipt_types = $order_option->get_all_receipt_type();
		$order_total = $order->get_total();

		if( $order_option->receipt_type() == $receipt_types[0] ){
			$order_type = 'SalesReceipt';

			$payment_method = $order_option->payment_method();
			$order_transaction_id = ('' == $order->get_transaction_id()) ? null : $order->get_transaction_id();
			$order_payment_method = $order->payment_method;
			$qbo_payment_method_id = null;
			if (!empty($order_payment_method)) {
				$payment = array();

				if ('mapped_payment_method' == $payment_method) {
					//If 'Map payment methods' is selected

					$selected_payment_method = $order_option->selected_mapped_payment_method();
					if (isset($selected_payment_method[$order_payment_method])) {
						$qbo_payment_method = explode("|", $selected_payment_method[$order_payment_method]);
						$qbo_payment_method_id = isset($qbo_payment_method[0]) ? $qbo_payment_method[0] : null;
						$payment = array(
							'retailer_payment_type_id' => $qbo_payment_method_id,
							'amount' => isset($order_total) ? $order_total : 0,
							'method' => isset($qbo_payment_method[1]) ? $qbo_payment_method[1] : null,
							'transactionNumber' => $order_transaction_id
						);
					}
				} elseif ('woo_order_payment_method' == $payment_method) {
					// If 'Send WooCommerce order payment method to QBO' is selected

					$payment = array(
						'retailer_payment_type_id' => null,
						'amount' => isset($order_total) ? $order_total : 0,
						'method' => isset($order_payment_method) ? $order_payment_method : null,
						'transactionNumber' => $order_transaction_id
					);
				}
				$json_order->set_payment($payment);
			}

			//Set payment_type_id
			$json_order->set_payment_type_id($qbo_payment_method_id);
		}


		$json_order->set_uid( null );
		$json_order->set_orderId( $order_no );
		$json_order->set_idSource( $order->id );
		$json_order->set_orderType( $order_type );
		$json_order->set_created( $order_created );
		$json_order->set_source( $source );
		$json_order->set_primary_email( $primary_email );
		$json_order->set_total( $order->get_total() );
		$json_order->set_taxes_included( $included_tax );
		$json_order->set_global_tax_calculation($globalTaxCalculation);
		$json_order->set_comments( $comments );
		$json_order->set_register_id( null );
		$json_order->set_user_name( null );

		$json_order->set_products( $products );
		$json_order->set_billingAddress( $billing_address );
		$json_order->set_deliveryAddress( $delivery_address );


		$order_json_data = $json_order->get_json_orders();
		$post_order = LS_QBO()->api()->order()->save_orders( $order_json_data );

		if (!empty($post_order['id'])) {
			$note = sprintf(__('Order exported to QBO: %s', 'woocommerce'), $order_no);
			$order->add_order_note($note);
			delete_post_meta($order_id, '_ls_json_order_error');
			LSC_Log::add_dev_success('LS_QBO_Sync::import_single_order_to_qbo', 'Woo Order ID: ' . $order_id . '<br/><br/>Json order being sent: ' . $order_json_data . '<br/><br/> Response: ' . json_encode($post_order));
		} else {
			update_post_meta($order_id, '_ls_json_order_error', $post_order);
			LSC_Log::add_dev_failed('LS_QBO_Sync::import_single_order_to_qbo', 'Woo Order ID: ' . $order_id . '<br/><br/>Json order being sent: ' . $order_json_data . '<br/><br/> Response: ' . json_encode($post_order));
		}

	}



	/**
	 * Importing all products to Woocommerce from page one to the last page.
	 * @param $page page number of the products
	 */
	public static function product_to_woo( $page = 1){
		$products = LS_QBO()->api()->product()->get_product_by_page( $page );

		if( !empty($products['products']) ){
			foreach( $products['products'] as $product ){

				$product = new LS_Simple_Product( $product );
				self::import_single_product_to_woo( $product );

			}
		}

		if( $products['pagination']['page'] <= $products['pagination']['pages'] ){

			$page = $products['pagination']['page'] + 1;
			if( $page <= $products['pagination']['pages'] ){
				self::product_to_woo( $page );
			}
		}
		return $products;
	}

	public static function remove_action_save_post(){
		$product_options = LS_QBO()->product_option();
		$current_sync_option = $product_options->get_current_product_syncing_settings();

		if ('two_way' == $current_sync_option['sync_type']) {
			remove_action('save_post', array('LS_QBO_Sync', 'save_product'), 999);
		}
	}

	public static function add_action_save_post(){
		$product_options = LS_QBO()->product_option();
		$current_sync_option = $product_options->get_current_product_syncing_settings();

		if ('two_way' == $current_sync_option['sync_type']) {
			add_action('save_post', array('LS_QBO_Sync', 'save_product'), 999, 3);
		}
	}

	/**
	 * Importing products from qbo to Woocommerce since last update from page one to the last page
	 * @param $page
	 * @return array|null
	 */
	public static function product_to_woo_since_last_update( $page = 1 ){
		$last_sync = LS_QBO()->options()->last_product_update();
		$params = array(
			'page'	=>	$page,
			'since'	=>	$last_sync
		);
		$products 	=	LS_QBO()->api()->product()->get_product( $params );
		LSC_Log::add_dev_success(
			'LS_QBO_Sync::product_to_woo_since_last_update',
			'Parameters being used: '.json_encode($params).'<br/>Product Get Response: <br/>'.json_encode($products)
		);
		if( !empty($products['products']) ){

			foreach( $products['products'] as $product ){

				$product = new LS_Simple_Product( $product );
				self::import_single_product_to_woo( $product );

			}
		}

		if( $products['pagination']['page'] <= $products['pagination']['pages'] ){

			$page = $products['pagination']['page'] + 1;
			if( $page <= $products['pagination']['pages'] ){
				self::product_to_woo_since_last_update( $page );
			}
		}
		return $products;
	}

	/**
	 * syncing part will happen for each single product
	 *
	 * Woocommerce WC_Meta_Box_Product_Data::save(posid,post) handles product meta saving
	 */
	public static function import_single_product_to_woo( $product ){


		//Make sure its the instance of LS_Simple_Product class
		if( !$product instanceof LS_Simple_Product){
			$product = new LS_Simple_Product( $product );
		}

		$product_options = LS_QBO()->product_option();
		$current_sync_option = $product_options->get_current_product_syncing_settings();

		if( 'disabled' == $current_sync_option['sync_type'] ){
			//return if sync type is disabled
			return;
		}

		$match_with = $current_sync_option['match_product_with'];

		$product_id = null;
		if( 'name' == $match_with ){
			$product_name = $product->get_name();
			if ('on' == $current_sync_option[ 'delete' ]) {
				$product_name = trim($product_name);
				$str_delete = substr($product_name, -9);
				if('(deleted)' === $str_delete){
					$product_name = rtrim( $product_name, '(deleted)' );
				}
			}
			$product_name = remove_escaping_str($product_name);
			$product_id = LS_Woo_Product::get_product_id_by_name($product_name);

		}else if( 'sku' == $match_with){
			$product_id = LS_Woo_Product::get_product_id_by_sku($product->get_sku());
		}

		//Last Check if the product exist in woocommerce to attempt query product id using quickbooks id
		if(empty($product_id)){
			$qboId = get_qbo_id($product->get_id());
			$product_id = LS_Woo_Product::get_product_id_by_quickbooks_id($qboId);
		}

		$deleted = false;
		$product_deleted = $product->get_deleted_at();
		$product_active = $product->is_active();
		if (0 == $product_active) {

			if ('on' == $current_sync_option[ 'delete' ] && !empty( $product_id )) {
				$deleted = ( false != wp_delete_post( $product_id, true ) ) ? true : false;
			}
			$deleted = true;

		}

		// If it has been deleted in qbo there is no point in creating or updating it to woocommerce
		if( false == $deleted ){
			remove_all_actions('save_post');
			remove_action( 'pre_post_update','wp_save_post_revision' );
			//$product_id will not be empty if the product exists
			if( !empty($product_id) ){

				//Get the product meta object for product
				$product_meta = new LS_Product_Meta( $product_id );

				LS_Woo_Product::update_woo_product_using_qbo_product(
					$current_sync_option,
					$product,
					$product_meta
				);

				//Enable back the revision for other plugin to still use it
				add_action( 'pre_post_update','wp_save_post_revision' );
				LS_QBO_Sync::add_action_save_post();


			}else if( empty($product_id) ){

				//product was not found therefore check if create new was on and create the product
				if( 'on' == $current_sync_option['create_new'] ){
					$product_description = $product->get_description();
					//Create the product array
					$product_args['post_title']     =	$product->get_name();
					$product_args['post_content'] 	=	empty($product_description)? '&nbsp' : $product_description;

					$product_id = LS_Woo_Product::create($product_args, true);
				}

				//Product was created
				if( !empty($product_id) ){

					//Get the product meta object for product
					$product_meta = new LS_Product_Meta( $product_id );
					LS_Woo_Product::update_woo_product_using_qbo_product(
						$current_sync_option,
						$product,
						$product_meta,
						true
					);

					//Enable back the revision for other plugin to still use it
					add_action( 'pre_post_update','wp_save_post_revision' );
					LS_QBO_Sync::add_action_save_post();

				}
			}
		}

		//set last sync to the current UTC time
		LS_QBO()->options()->last_product_update($product->get_update_at());
	}

	/**
	 * Delete product in the api database
	 * @param $product_id
	 */
	public static function delete_product( $product_id ){
		$product = new WC_Product($product_id);
		$product_type = $product->post->post_type;

		if (is_woo_product( $product_type )) {
			$sku = $product->get_sku();
			if (!empty( $sku )) {
				$delete = LS_QBO()->api()->product()->delete_product( $sku );
				if (!empty( $delete )) {
					LSC_Log::add_dev_success( 'LS_QBO_Sync::delete_product', 'Woo Product id: ' . $product_id.' <br/><br/>Response from server: '.json_encode($delete) );
				}
			}
		}

	}

	/**
	 * Returns all the woocommerce product ids
	 */
	public static function woo_get_products_callback(){
		wp_send_json(LS_Woo_Product::get_product_ids());
	}

	/**
	 * For getting product using AJAX
	 * Get the product by page
	 * @param page $_POST['page]
	 * @param action $_POST['qbo_get_products']
	 */
	public static function qbo_get_products_callback(){

		$page = isset($_POST['page']) ? $_POST['page'] : 1;
		$products = LS_QBO()->api()->product()->get_product_by_page( $page );
		wp_send_json($products);

	}

	public static function qbo_get_products_since_last_update(){

		$page 		=	isset($_POST['page']) ? $_POST['page'] : 1;
		$products 	=	LS_QBO()->api()->product()->get_product( array(
			'page'	=>	$page,
			'since'	=>	get_last_sync()
		));

		wp_send_json($products);
	}

	/**
	 * Importing products using AJAX
	 * @param json $_POST['product']
	 * @param action $_POST['import_to_woo']
	 */
	public static function import_qbo_product_to_woo(){

		if( !empty( $_POST['product'] ) ){
			$product_total_count = (int) $_POST['product_total_count'] - (int) $_POST['deleted_product'];

			$product = new LS_Simple_Product($_POST['product']);
			self::import_single_product_to_woo( $product );

			$product_number = $_POST['product_number'];
			$product_number = ($product_number > $product_total_count) ? $product_total_count: $product_number;
			$msg = $product_number." of ".$product_total_count." Product(s)";

			wp_send_json( $msg );
		}
	}

}

new LS_QBO_Sync();