<?php if ( ! defined( 'ABSPATH' ) ) exit('Access is Denied');

class LS_QBO_Order_Option{

	/**
	 * LS_QBO_Order_Option instance
	 * @var null
	 */
	protected static $_instance = null;

	public static function instance(){

		if( is_null( self::$_instance ) ){
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Get Order sync type
	 * @return string either disabled or woo_to_qbo
	 */
	public function sync_type(){
		return get_option( 'ls_osqbo_sync_type' , 'woo_to_qbo');
	}

	/**
	 * Update syncing type
	 * @param $type
	 * @return string Syncing type either woo_to_qbo or disabled
	 */
	public function update_sync_type( $type ){
		$syn_types = $this->get_all_sync_type();

		if( in_array( $type, $syn_types) ){
			if( !empty( $type ) ){
				update_option( 'ls_osqbo_sync_type', $type );
			}
		}

		return $this->sync_type();
	}

	/**
	 * @return array of posible sync type
	 */
	public function get_all_sync_type(){
		return array( 'woo_to_qbo', 'disabled' );
	}

	/**
	 * Get Export type
	 * @return string custer_data or woocommerce_sale
	 */
	public function customer_export(){
		return get_option( 'ls_osqbo_customer_export', 'export_as_customer_data' );
	}

	public function get_all_export_type(){
		return array( 'export_as_customer_data', 'export_as_woo_sale' );
	}

	/**
	 * Update Customer export type
	 * @param $export_type
	 * @return string selectec export type
	 */
	public function update_customer_export( $export_type ){
		if( !empty( $export_type ) ){
			update_option( 'ls_osqbo_customer_export', $export_type );
		}
		return $this->customer_export();
	}

	/**
	 * Get (Post to Quickbooks as) or (Receipt type)
	 * @return string it could be sales_receipt or sales_invoice
	 */
	public function receipt_type(){
		return get_option( 'ls_osqbo_receipt_type', 'sales_receipt' );
	}

	/**
	 * Update Receipt type
	 * @param $type
	 * @return string it could be sales_receipt or sales_invoice
	 */
	public function update_receipt_type( $type ){

		if( !empty( $type ) ){
			update_option( 'ls_osqbo_receipt_type', $type );
		}
		return $this->receipt_type();

	}
	public function get_all_receipt_type(){
		return array( 'sales_receipt', 'sales_invoice' );
	}

	/**
	 * Get Selected Deposit Account
	 * @return string
	 */
	public function deposit_account(){
		return get_option( 'ls_osqbo_deposit_account', 'Undeposited Funds' );
	}

	/**
	 * Update Deposit Account
	 * @param $deposit_account
	 * @return string
	 */
	public function update_deposit_account( $deposit_account ){

		if( !empty($deposit_account) ){
			update_option( 'ls_osqbo_deposit_account', $deposit_account );
		}

		return $this->deposit_account();
	}

	/**
	 * Get payment method
	 * @return string it could be either woo_order_payment_method or mapped_payment_method
	 */
	public function payment_method(){
		return get_option( 'ls_osqbo_payment_method' , 'mapped_payment_method' );
	}

	/**
	 * Update payment method
	 * @param $payment_method The type of the payment
	 * @return string
	 */
	public function update_payment_method( $payment_method ){

		if( !empty( $payment_method ) ){
			update_option( 'ls_osqbo_payment_method', $payment_method );
		}

		return $this->payment_method();
	}

	public function get_all_payment_method(){
		return array( 'woo_order_payment_method', 'mapped_payment_method' );
	}

	/**
	 * Get the selected Mapped payment method from QBO
	 * @return string the selected Mapped Payment Method
	 */
	public function selected_mapped_payment_method(){
		return get_option( 'ls_osqbo_selected_mapped_payment_method' );
	}

	/**
	 * Update the selected payment method from QBO
	 * @param $selected_payment_method
	 * @return string
	 */
	public function update_selected_mapped_payment_method( $selected_payment_method ){
		if( !empty( $selected_payment_method ) ){
			update_option( 'ls_osqbo_selected_mapped_payment_method', $selected_payment_method );
		}

		return $this->selected_mapped_payment_method();
	}

	/**
	 * Get The selected Sales Invoice
	 * @return string
	 */
	public function selected_invoice(){
		return get_option( 'ls_osqbo_selected_invoice' );
	}

	public function update_selected_invoice( $invoice ){

		if( !empty( $invoice ) ){
			update_option( 'ls_osqbo_selected_invoice', $invoice );
		}

		return $this->selected_invoice();
	}

	/**
	 * @return array of selected order status
	 */
	public function order_status(){
		$default_status = $this->default_order_status();

		return get_option( 'ls_osqbo_order_status', $default_status );
	}

	public function update_order_status( $order_status ){
		if( !empty($order_status) ){
			update_option( 'ls_osqbo_order_status', $order_status );
		}

		return $this->order_status();
	}

	/**
	 * @return array of Default order status for order syncing
	 */
	public function default_order_status(){
		return array( 'wc-completed', 'wc-processing' );
	}

	/**
	 * Selected Order number for QBO, either use_qbo or use_woo
	 * @return string
	 */
	public function order_number(){
		return get_option( 'ls_osqbo_order_number', 'use_qbo' );
	}

	/**
	 * Selected Order number for QBO, either use_qbo or use_woo
	 * @param $which_order_number
	 * @return string
	 */
	public function update_order_number( $which_order_number ){

		if( !empty( $which_order_number ) ){
			update_option( 'ls_osqbo_order_number', $which_order_number );
		}

		return $this->order_status();
	}

	public function get_all_order_number(){
		return array( 'use_qbo', 'use_woo' );
	}

	/**
	 * Selected Tax mapping Wocoomerce to QuickBooks
	 */
	public function tax_mapping(){
		return get_option( 'ls_osqbo_tax_mapping' );
	}

	public function update_tax_mapping( $tax_mapped ){

		if( !empty($tax_mapped) ){
			update_option( 'ls_osqbo_tax_mapping', $tax_mapped );
		}

		return $this->tax_mapping();
	}

	/**
	 * Returns whether the location is enabled
	 * @return string it could be on or off
	 */
	public function location_status(){
		return get_option( 'ls_osqbo_location_status', 'off' );
	}

	/**
	 * Update the location status
	 * @param $status string on or off
	 * @return string
	 */
	public function update_location_status( $status ){

		update_option( 'ls_osqbo_location_status',$status );

		return $this->location_status();
	}

	/**
	 * The selected Location
	 * Note: Only Available for QBO plus
	 * @return string selected location
	 */
	public function selected_location(){
		return get_option( 'ls_osqbo_location' );
	}

	/**
	 * Update the selected Location
	 * @param $location
	 * @return string selected location
	 */
	public function update_selected_location( $location ){
		if( !empty( $location ) ){
			update_option( 'ls_osqbo_location', $location );
		}

		return $this->selected_location();
	}

	/**
	 * Returns whether the class is enabled
	 * @return string it could be on or off
	 */
	public function class_status(){
		return get_option( 'ls_osqbo_class_status', 'off' );
	}

	/**
	 * Update the class status
	 * @param $status either on or off
	 * @return string
	 */
	public function update_class_status( $status ){
		update_option( 'ls_osqbo_class_status', $status );

		return $this->class_status();
	}

	/**
	 * Get the selected Class order
	 * @return string selected class order
	 */
	public function selected_order_class(){
		return get_option( 'ls_osqbo_order_class' );
	}

	/**
	 * Update the selected Class order
	 * @param $order_class
	 * @return string selected class order
	 */
	public function update_selected_order_class( $order_class ){
		if( !empty( $order_class ) ){
			update_option( 'ls_osqbo_order_class', $order_class);
		}

		return $this->selected_order_class();
	}

	/**
	 * Current Users Order syncing settings
	 * @return array
	 */
	public function get_current_order_syncing_settings(){
		return array(
			'sync_type'				=>	$this->sync_type(),
			'customer_export'		=>	$this->customer_export(),
			'post_quikbooks_as'		=>	array(
											'receipt_type'				=>	$this->receipt_type(),
											'deposit_account'			=>	$this->deposit_account(),
											'payment_method'			=>	$this->payment_method(),
											'selected_payment_method'	=>	$this->selected_mapped_payment_method()
										),
			'order_status'			=>	$this->order_status(),
			'order_number'			=>	$this->order_number(),
			'tax_mapping'			=>	$this->tax_mapping(),
			'location'				=>	array(
											'location_status'			=>	$this->location_status(),
											'selected_location'		    =>	$this->selected_location()
										),
			'class'					=>	array(
											'class_status'				=>	$this->class_status(),
											'selected_class'			=>	$this->selected_order_class()
										)
		);
	}

	/**
	 * Reset Default order settings
	 */
	public function reset_options(){
		$this->update_sync_type( 'disabled' );
		$this->update_customer_export( 'export_as_customer_data' );
		$this->update_receipt_type( 'sales_receipt' );
		$this->update_payment_method( 'woo_order_payment_method' );
		$this->update_order_status( $this->default_order_status() );
		$this->update_order_number( 'use_qbo' );
		$this->update_location_status( 'off' );
		$this->update_class_status( 'off' );
	}
}