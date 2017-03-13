<?php if ( ! defined( 'ABSPATH' ) ) exit('Access is Denied');

class LS_QBO_Api{

	/**
	 * The api object
	 * @var null
	 */
	public $api = null;

	public $product = null;

	public $order = null;

	public function __construct(LS_Api $api) {
		$this->api		=	$api;
		$this->product	=	new LS_Product_Api($api);
		$this->order	=	new LS_Order_Api($api);
	}

	public function product(){
		return $this->product;
	}

	public function order(){
		return $this->order;
	}


	public function save_users_settings( $user_settings )
	{
		$savedUserSettings = $this->api->post('config', $user_settings);

		return $savedUserSettings;
	}

	/**
	 * QuickBooks Online Tax
	 * Returns all active tax rates for the current retailer
	 *
	 * @return array
	 */
	public function get_all_tax_rate(){
		$taxes = $this->api->get('qbo/tax');

		/*array_push($taxes['taxes'], array(
			'id'		=> 'no_tax',
			'name'		=> 'No Tax',
			'active'	=> true,
			'rateValue' => 0
		));*/

		return isset($taxes['taxes'])? $taxes['taxes'] : null;
	}

	/**
	 * QuickBooks Online Tax Codes
	 * Returns all active tax codes for the current retailer.
	 *
	 * @return array
	 */
	public function get_all_active_tax_code(){
	    $taxCodes = $this->api->get('qbo/taxcode');
		return isset($taxCodes['taxCodes']) ? $taxCodes['taxCodes'] : null;
	}

    /**
     * QuickBooks Online Chart of Accounts
     * Returns all active Accounts for the current retailer.
     *
     * @param $classification
     * @return array
     */
    public function getAccountsByClassification($classification)
    {
        return $this->api->get('qbo/account?classification=' . $classification);
    }

    public function getDepositAccounts()
    {
        $depositAccounts = $this->get_accounts('depositto');
        return isset($depositAccounts['accounts']) ? $depositAccounts['accounts'] : null;
    }

	/**
	 * QuickBooks Online Chart of Accounts
	 * Returns all active Accounts for the current retailer.
	 * @return array
	 */
	public function get_accounts( $account_type = null ){
		$end_point = 'qbo/account?';

		if( !is_null($account_type) ){
			return $this->api->get($end_point.'accountType='.$account_type);
		}
		return $this->api->get($end_point);
	}

	/**
	 * Returns expense accounts
	 * @return array
	 */
	public function get_expense_accounts(){
		$expense_account = $this->get_accounts('expense&productType=inventory');
		return isset($expense_account['accounts']) ? $expense_account['accounts'] : null ;
	}

	/**
	 * Returns expense accounts for the INVENTORY type of product/service
	 * @return array|null
	 */
	public function get_inventory_expense_accounts(){
		$accounts = $this->get_accounts('expense');
		return isset($accounts['accounts']) ? $accounts['accounts'] : null ;
	}

	/**
	 * Returns expense accounts for the NON-INVENTORY type of product/service
	 * @return array|null
	 */
	public function get_non_inventory_expense_accounts(){
		$accounts = $this->get_accounts('expense');
		return isset($accounts['accounts']) ? $accounts['accounts'] : null ;
	}

	/**
	 * Returns expense accounts for the SERVICE type of product/service
	 * @return array|null
	 */
	public function get_service_expense_accounts(){
		$accounts = $this->get_accounts('expense');
		return isset($accounts['accounts']) ? $accounts['accounts'] : null ;
	}

	/**
	 * Return Income accounts
	 * @return array
	 */
	public function get_income_accounts(){
		$income_accounts = $this->get_accounts('Income');
		return isset($income_accounts['accounts']) ? $income_accounts['accounts'] : null;
	}

	/**
	 * Returns income accounts for the INVENTORY type of product/service
	 * @return array|null
	 */
	public function get_inventory_income_accounts(){
		$account = $this->get_accounts();
		return isset($account['accounts']) ? $account['accounts'] : null;
	}

	/**
	 * Get income accounts for the NON-INVENTORY type of product/service
	 * @return array|null
	 */
	public function get_non_inventory_income_accounts(){
		$account = $this->get_accounts();
		return isset($account['accounts']) ? $account['accounts'] : null;
	}

	/**
	 * Get income accounts for the SERVICE type of product/service
	 * @return array|null
	 */
	public function get_service_income_accounts(){
		$account = $this->get_accounts();
		return isset($account['accounts']) ? $account['accounts'] : null;
	}


	/**
	 * Deposit account came from Quickbooks Online Chart Accounts filtered on 'Asset'
	 * @return array
	 */
	public function get_assets_accounts(){
		$assets_accounts = $this->get_accounts('Asset');
		return isset($assets_accounts['accounts']) ? $assets_accounts['accounts']: null;
	}

	/**
	 * QuickBooks Online Location
	 * Returns all active locations for the current retailer.
	 *
	 * @return array
	 */
	public function get_all_active_location(){
		$location = $this->api->get('qbo/location');
		return isset($location['locations']) ? $location['locations'] : null;
	}

	/**
	 * QuickBooks Online Info
	 * Returns all info for the current retailer.
	 *
	 * @return array
	 */
	public function get_qbo_info(){
		return $this->api->get('qbo/info');
	}

	/**
	 * QuickBooks Online Payment Methods
	 * Returns all active Payment Methods for the current retailer.
	 *
	 * @return array
	 */
	public function get_all_payment_methods(){
		$payment_method = $this->api->get('qbo/payment');
		return isset($payment_method['payments']) ? $payment_method['payments'] : null;
	}

	/**
	 * QuickBooks Online Class
	 *
	 * Returns all active classes for the current retailer.
	 * Classes provide a way to track different segments of the business so they're
	 * not tied to a particular client or project.
	 *
	 * @return array
	 */
	public function get_all_active_clases(){
		$classes = $this->api->get('qbo/class');
		return isset($classes['classes']) ? $classes['classes'] : null;
	}

	/**
	 *
	 * @return array|null
	 */
	public function get_laid_info(){
		$laid = LS_ApiController::get_current_laid();
		if( !empty($laid) ){
			return $this->api->get('laid');
		}
		return null;
	}
}