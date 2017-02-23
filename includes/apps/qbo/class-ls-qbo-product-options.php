<?php if ( ! defined( 'ABSPATH' ) ) exit('Access is Denied');

class LS_QBO_Product_Option{

	/**
	 * LS_QBO_Product_Option instance
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
	 * Get qbo sync option type
	 * @return string it could be two_way, qbo_to_woo or disabled
	 */
	public function sync_type(){
		return get_option( 'ls_psqbo_sync_type', 'two_way' );
	}

	/**
	 * Update qbo sync option type
	 * @param $syn_type string
	 * @return string it could be two_way, qbo_to_woo or disabled
	 */
	public function update_sync_type( $syn_type ){
		$syn_types = $this->get_all_sync_type();

		if( in_array( $syn_type, $syn_types ) ){

			if( !empty( $syn_type ) ){
				update_option( 'ls_psqbo_sync_type', $syn_type);
			}

		}

		return $this->sync_type();
	}

	/**
	 * Get all possible syncing type
	 * @return array of sync typed either two_way, qbo_to_woo or disabled
	 */
	public function get_all_sync_type(){
		return array( 'two_way', 'qbo_to_woo', 'disabled' );
	}

	/**
	 * Get Match product with Option
	 * @return string it could be name or sku
	 */
	public function match_product_with(){
		return get_option( 'ls_psqbo_match_product_with', 'sku' );
	}

	/**
	 * Update Match product with Option
	 * @param $match_with
	 * @return string it could be name or sku
	 */
	public function update_match_product_with( $match_with ){
		if( !empty( $match_with ) ){
			update_option( 'ls_psqbo_match_product_with', $match_with );
		}

		return $this->match_product_with();
	}

	/**
	 * Get Title or Name Option
	 * @return string on or off
	 */
	public function title_or_name(){
		return get_option( 'ls_psqbo_title_or_name', 'on' );
	}

	/**
	 * Update Title or Name Option
	 * @param $on_or_off
	 * @return string on or off
	 */
	public function update_title_or_name( $on_or_off ){
	    $on_or_off = ('on' == $on_or_off) ? 'on': 'off';
        update_option( 'ls_psqbo_title_or_name', $on_or_off );
		return $this->title_or_name();
	}

	/**
	 * Get Description Option
	 * @return string on or off
	 */
	public function description(){
		return get_option( 'ls_psqbo_description', 'on' );
	}
	/**
	 * Update Description Option
	 * @param $on_or_off
	 * @return string on or off
	 */
	public function update_description( $on_or_off ){
        $on_or_off = ('on' == $on_or_off) ? 'on': 'off';
        update_option( 'ls_psqbo_description', $on_or_off );
		return $this->description();
	}

	/**
	 * Get Price Option
	 * @return string on or off
	 */
	public function price(){
		return get_option( 'ls_psqbo_price', 'on' );
	}

	/**
	 * @param $on_or_off
	 * @return string
	 */
	public function update_price( $on_or_off ){
        $on_or_off = ('on' == $on_or_off) ? 'on': 'off';
        update_option( 'ls_psqbo_price', $on_or_off );
		return $this->price();
	}


	/**
	 * Use tax option either on or offf
	 * @return string
	 */
	public function use_woo_tax_option(){
		return get_option( 'ls_psqbo_use_woo_tax_option', 'on');
	}

	/**
	 * Use tax option either on or off
	 * @param $tax_option
	 * @return string
	 */
	public function update_use_woo_tax_option( $tax_option ){
		update_option( 'ls_psqbo_use_woo_tax_option', $tax_option );
		return $this->use_woo_tax_option();
	}

	/**
	 * Get the selected Tax option by the user it could be exclusive or inclusive
	 * @return mixed|void
	 */
	public function tax_option(){
		return get_option( 'ls_psqbo_tax_option', 'exclusive' );
	}
	/**
	 * Update tax option selected
	 * @param $tax_option
	 * @return The taxt option
	 */
	public function update_tax_option( $tax_option ){
		update_option( 'ls_psqbo_tax_option', $tax_option );
		return $this->tax_option();
	}

	/**
	 * Get the Selected tax mapping option by the user
	 * @return string of tax selected by the user
	 */
	public function tax_class(){
		return get_option( 'ls_psqbo_tax_mapping' );
	}

	/**
	 * @return string the selected tax mapping
	 */
	public function update_tax_class( $tax ){
		update_option( 'ls_psqbo_tax_mapping', $tax );
		return $this->tax_class();
	}

	/**
	 * Get the product Option
	 * @return string on or off
	 */
	public function quantity(){
		return get_option( 'ls_psqbo_quantity', 'on' );
	}

	/**
	 * @param $on_or_off string on or off
	 * @return string on or off
	 */
	public function update_quantity( $on_or_off ){
        $on_or_off = ('on' == $on_or_off) ? 'on': 'off';
        update_option( 'ls_psqbo_quantity', $on_or_off );
		return $this->quantity();
	}

	/**
	 * Get Change product status in QBO base on stock quantity
	 * @return string on or off
	 */
	public function change_product_status(){
		return get_option( 'ls_psqbo_change_product_status', 'on' );
	}

	public function update_change_product_status( $on_or_off ){
        $on_or_off = ('on' == $on_or_off) ? 'on': 'off';
        update_option( 'ls_psqbo_change_product_status', $on_or_off );
		return $this->change_product_status();
	}

    public function inventory_asset_account($default = '')
    {
        $inventoryAssetAccount = get_option('ls_ps_qbo_inventory_asset_account', $default);
        if(empty($inventoryAssetAccount)){
            $expenseAccounts = LS_QBO()->options()->getAssetAccounts();
            return !empty($expenseAccounts[0]['id']) ? $expenseAccounts[0]['id'] : '';
        }
        return $inventoryAssetAccount;
    }

    public function update_inventory_asset_account($inventory_asset_account)
    {

        if (is_numeric($inventory_asset_account)) {
            update_option('ls_ps_qbo_inventory_asset_account', $inventory_asset_account);
        }

        return $this->inventory_asset_account();

    }

    public function delete_inventory_asset_account()
    {
        return delete_option('ls_ps_qbo_inventory_asset_account');
    }


    public function expense_account($default = '')
    {
        $expenseAccount = get_option('ls_ps_qbo_expense_account', $default);
        if(empty($expenseAccount)){
            $expenseAccounts = LS_QBO()->options()->getExpenseAccounts();
            return !empty($expenseAccounts[0]['id']) ? $expenseAccounts[0]['id'] : '';
        }
        return $expenseAccount;
    }

    public function update_expense_account($expense_account)
    {

        update_option('ls_ps_qbo_expense_account', $expense_account);
        return $this->expense_account();
    }

    public function delete_expense_account()
    {
        return delete_option('ls_ps_qbo_expense_account');
    }


    public function income_account($default = '')
    {
        $incomeAccount = get_option('ls_psqbo_income_account', $default);
        if(empty($incomeAccount)){
            $incomeAccounts = LS_QBO()->options()->getIncomeAccounts();
            return !empty($incomeAccounts[0]['id']) ? $incomeAccounts[0]['id'] : '';
        }
        return $incomeAccount;
    }

	/**
	 * Update selected income account
	 * @param $income_account
	 * @return string
	 */
	public function update_income_account( $income_account ){
		update_option( 'ls_psqbo_income_account', $income_account );
		return $this->income_account();
	}

    public function delete_income_account()
    {
        return delete_option('ls_psqbo_income_account');
	}

    /**
     * Get Category Option
     * @return mixed|void
     */
	public function category(){
		return get_option( 'ls_psqbo_category', 'on' );
	}

	/**
	 * @param $on_or_off
	 * @return mixed|void
	 */
	public function update_category( $on_or_off ){
        $on_or_off = ('on' == $on_or_off) ? 'on': 'off';
		update_option( 'ls_psqbo_category', $on_or_off);
		return $this->category();
	}

	/**
	 * Get Product status option
	 * @return string on or off
	 */
	public function product_status(){
		return get_option( 'ls_psqbo_product_status', 'off' );
	}

	/**
	 * Update product status option
	 * @param $on_or_off
	 * @return string on or off
	 */
	public function update_product_status( $on_or_off ){
        $on_or_off = ('on' == $on_or_off) ? 'on': 'off';
        update_option( 'ls_psqbo_product_status', $on_or_off );
		return $this->product_status();
	}

	/**
	 * Get Create new Option
	 * @return string on or off
	 */
	public function create_new(){
		return get_option( 'ls_psqbo_create_new' , 'on');
	}

	public function update_create_new( $on_or_off ){
        $on_or_off = ('on' == $on_or_off) ? 'on': 'off';
        update_option( 'ls_psqbo_create_new', $on_or_off );
		return $this->create_new();
	}

	/**
	 * Get The Delete option
	 * @return string on or off
	 */
	public function delete(){
		return get_option( 'ls_psqbo_delete_option', 'off');
	}

	/**
	 * Update the option either on or off
	 * @param $on_or_off
	 * @return string on or off
	 */
	public function update_delete( $on_or_off ){
        $on_or_off = ('on' == $on_or_off) ? 'on': 'off';
        update_option( 'ls_psqbo_delete_option', $on_or_off );
		return $this->delete();
	}

	/**
	 * Current Users Product syncing settings
	 * @return array
	 */
	public function get_current_product_syncing_settings(){
		return array(
			'sync_type' 			=>	$this->sync_type(),
			'match_product_with'	=> 	$this->match_product_with(),
			'title_or_name'			=> 	$this->title_or_name(),
			'description'			=> 	$this->description(),
			'price'					=> 	array(
											'price'         =>  $this->price(),
											'use_woo_tax'	=>	$this->use_woo_tax_option(),
											'tax_option'	=>	$this->tax_option(),
											'tax_classes'   =>  $this->tax_class()
										),
			'quantity'				=> 	array(
											'quantity'		            =>  $this->quantity(),
											'change_status'	            =>  $this->change_product_status(),
											'inventory_asset_acccount'  =>  $this->inventory_asset_account(),
											'expense_account'           =>  $this->expense_account()
										),
			'income_account'		=>	$this->income_account(),
			'category'				=>	$this->category(),
			'product_status'		=>	$this->product_status(),
			'create_new'			=>	$this->create_new(),
			'delete'				=>	$this->delete()
		);
	}

	/**
	 * Reset Default order settings
	 */
	public function reset_options() {
		$this->update_sync_type( 'disabled' );
		$this->update_match_product_with( 'name' );
		$this->update_title_or_name( 'off' );
		$this->update_description( 'on' );
		$this->update_price( 'on' );
		$this->update_quantity( 'on' );
		$this->update_change_product_status( 'on' );
		$this->update_category( 'off' );
		$this->update_product_status( 'off' );
		$this->update_create_new( 'on' );
		$this->update_delete( 'off' );
	}
}