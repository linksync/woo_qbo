<?php if ( ! defined( 'ABSPATH' ) ) exit('Access is Denied');

	/**
	 * Further get_options and update_options for quickbooks should be added on this class
	 *
	 * Class LS_QBO_Options
	 */
	class LS_QBO_Options{
		protected static $_instance = null;
		public $option_prefix = 'linksync_qbo_';

		public static function instance(){

			if( is_null( self::$_instance ) ){
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/**
		 * Get if Calculation of taxes was on or off
		 * @return mixed|void
		 */
		public function woocommerce_calc_taxes(){
			return get_option('woocommerce_calc_taxes');
		}

		/**
		 * Get woocommerce setting if included of tax was choosen
		 * @return mixed|void
		 */
		public function woocommerce_prices_include_tax(){
			return get_option('woocommerce_prices_include_tax');
		}

		/**
		 * Save and return last product update_at key from the product get response plus one second
		 *
		 * @param null $utc_date_time
		 * @return bool|mixed|string|void
		 */
		public function last_product_update($utc_date_time = null){
			return self::instance()->last_update('product_last_update', $utc_date_time);
		}

		/**
		 * Save and return last order update_at key from the order get response plus one second
		 *
		 * @param null $utc_date_time
		 * @return bool|mixed|string|void
		 */
		public function last_order_update($utc_date_time = null){
			return self::instance()->last_update('order_last_update', $utc_date_time);
		}

		/**
		 * Save last update_at value to the database plus one second
		 * @param $type
		 * @param null $utc_date_time
		 * @return bool|mixed|string|void
		 */
		public function last_update($type, $utc_date_time = null){
			$types = array('product_last_update', 'order_last_update');
			if (!in_array($type, $types)) {
				return false;
			}

			$last_updated_at = self::instance()->get_option($type);
			if (empty($utc_date_time)) {
				return $last_updated_at;
			}

			$last_time = strtotime($last_updated_at);
			$time_arg = strtotime($utc_date_time);
			if ($last_time < $time_arg) {
				$lt_plus_one_second = date("Y-m-d H:i:s", $time_arg + 1);
				self::instance()->update_option($type, $lt_plus_one_second);
				return $lt_plus_one_second;
			}

			return false;
		}

		/**
		 * Get site admin email
		 * @return mixed|void
		 */
		public function get_current_admin_email(){
			return get_option('admin_email');
		}

		/**
		 * Uses Wordpress update_option
		 * @param $key
		 * @param $value
		 * @return bool
		 */
		public function update_option($key, $value){
			$key = self::instance()->option_prefix.$key;
			return update_option($key, $value);
		}

		/**
		 * Uses Wordpress get_option
		 *
		 * @param $key
		 * @return mixed|void
		 */
		public function get_option($key){
			$key = self::instance()->option_prefix.$key;
			return get_option($key);
		}

		/**
		 * Make constructor private, so nobody can call "new Class".
		 */
		private function __construct() {}

		/**
		 * Make clone magic method private, so nobody can clone instance.
		 */
		private function __clone() {}

		/**
		 * Make sleep magic method private, so nobody can serialize instance.
		 */
		private function __sleep() {}

		/**
		 * Make wakeup magic method private, so nobody can unserialize instance.
		 */
		private function __wakeup() {}

	}