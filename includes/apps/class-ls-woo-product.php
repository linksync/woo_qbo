<?php if ( ! defined( 'ABSPATH' ) ) exit('Access is Denied');

class LS_Woo_Product{

	public static function update_woo_product_using_qbo_product(
		$current_sync_option,
		LS_Simple_Product $product,
		LS_Product_Meta $product_meta,
		$is_new = false
	){

		//$product_id will not be empty if the product exists
		if(!empty($product_meta->product_id)){
			$match_with = $current_sync_option['match_product_with'];

			//Update product details since product was found
			$product_args = null;

			//get QuickBooks product id
			$qbo_product_id = get_qbo_id($product->get_id());

			if(true == $is_new){
				// Add any default post meta
				$product_meta->setup_defaults();
				$product_meta->set_sku( $product->get_sku() );
			}
			$product_meta->set_product_id( $qbo_product_id );
			$product_meta->set_product_type( $product->get_product_type() );
			$product_meta->set_income_account_id( $product->get_income_account_id() );
			$product_meta->set_expense_account_id( $product->get_expense_account_id() );
			$product_meta->set_asset_account_id( $product->get_asset_account_id() );
			$product_meta->update_tax_value( $product->get_tax_value() );
			$product_meta->update_tax_name( $product->get_tax_name() );
			$product_meta->update_tax_rate( $product->get_tax_rate() );
			$product_meta->update_tax_id( $product->get_tax_id() );
			$qbo_includes_tax = ('false' === $product->does_includes_tax() || false === $product->does_includes_tax() ) ? 'false' : 'true';
			$product_meta->update_qbo_includes_tax( $qbo_includes_tax );

			if( 'name' == $match_with ){
				$product_meta->set_sku( $product->get_sku() );
			}

			if('on' == $current_sync_option['title_or_name'] || 'sku' == $match_with ){
				$product_args['post_title'] = $product->get_name();
			}

			if( 'on' == $current_sync_option['description'] ){
				$product_args['post_content'] = esc_html($product->get_description());
			}

			if( 'on' == $current_sync_option['price']['price']){
				//Sync price between apps is on
				$sell_price = $product->get_sell_price();
				$product_meta->set_price( $sell_price );
				$product_meta->set_regular_price( $sell_price );

				//Tax mapping
				$tax_mapped = $current_sync_option[ 'price' ][ 'tax_classes' ];
				$qbo_tax_id = $product->get_tax_id();
				$qbo_tax_id = !empty( $qbo_tax_id ) ? $qbo_tax_id : 'no_tax';
				if (isset( $tax_mapped[ $qbo_tax_id ] )) {
					$woo_tax_mapped = ('standard' == $tax_mapped[ $qbo_tax_id ] ) ? '': $tax_mapped[ $qbo_tax_id ];
					$product_meta->set_tax_class( $woo_tax_mapped );
				}

			}

			if( 'on' == $current_sync_option['quantity']['quantity']){
				//Sync quantity is on
				$product_meta->set_stock( $product->get_quantity() );
				if( 'on' == $current_sync_option['quantity']['change_status']){
					//Change product status base on quantity is on
					if( $product->get_quantity() <= 0 ){
						$product_args['post_status'] = 'draft';
						$product_meta->set_manage_stock( 'no' );
					}else{
						$product_args['post_status'] = 'publish';
						$product_meta->set_manage_stock( 'yes' );
					}

				}
			}

			//Setting if product should be virtual or not
			if( 'Service' == $product->get_product_type() || 'Non-Inventory' == $product->get_product_type()){
				$product_meta->set_virtual( 'yes' );
			}else if( 'Inventory' == $product->get_product_type() ){
				$product_meta->set_virtual( 'no' );
			}

			//Check if product is active in LWS
			if($product->is_active()){
				$product_args['post_status'] = 'publish';
			}else{
				$product_args['post_status'] = 'draft';
			}

			if(true == $is_new){
				if( 'on' == $current_sync_option['product_status'] ){
					//Tick this option to Set new product to Pending is on
					$product_args['post_status'] = 'pending';
				}
			}

			//Set woocommerce Product to taxable or not
			$tax_status = ('' == $product->get_tax_name()) ? 'none' : 'taxable';
			$product_meta->set_tax_status( $tax_status );

			if( 'on' == $current_sync_option['category']){
				//Create categories from QBO in WooCommerce is on
				$category = $product->get_categories();
				if( isset($category['fullyQualifiedName']) && !empty($category['fullyQualifiedName']) ){
					LS_Woo_Product::create_category_from_quickbooks_category( $product_meta->product_id , $category['fullyQualifiedName'] );
				}
			}

			$visibility = ('publish' == $product_args['post_status']) ? 'visible' : '';
			$product_meta->set_visibility($visibility);
			//update the metas ones only
			$product_meta->update_metas();

			//Set post id
			$product_args['ID'] = $product_meta->product_id;
			wp_update_post( $product_args, true);
		}

	}

	/**
	 * This function is similar to wc_product_has_unique_sku core function from wc-product-functions.php
	 * on a small difference to getting the trash post_status and not the published post
	 *
	 * Check if product sku is unique is in trash.
	 *
	 * @param int $product_id
	 * @param string $sku Will be slashed to work around https://core.trac.wordpress.org/ticket/27421
	 * @return bool
	 */
	public static function product_has_unique_sku( $product_id, $sku ) {
		global $wpdb;

		$sku_found = $wpdb->get_var( $wpdb->prepare( "
			SELECT $wpdb->posts.ID
			FROM $wpdb->posts
			LEFT JOIN $wpdb->postmeta ON ( $wpdb->posts.ID = $wpdb->postmeta.post_id )
			WHERE $wpdb->posts.post_type IN ( 'product', 'product_variation' )
			AND $wpdb->posts.post_status = 'trash'
			AND $wpdb->postmeta.meta_key = '_sku' AND $wpdb->postmeta.meta_value = '%s'
			AND $wpdb->postmeta.post_id <> %d LIMIT 1
		 ", wp_slash( $sku ), $product_id ) );

		if ( apply_filters( 'wc_product_has_unique_sku', $sku_found, $product_id, $sku ) ) {
			return false;
		} else {
			return true;
		}
	}

	public static function get_woo_duplicate_sku(){
		global $wpdb;

		//get all duplicate product sku
		$result = $wpdb->get_results( "
				SELECT
						wposts.ID,
						wpmeta.meta_key,
						wpmeta.meta_value
				FROM $wpdb->postmeta AS wpmeta
				JOIN (
						SELECT
							pmeta.meta_key,
							pmeta.meta_value
						FROM  $wpdb->postmeta AS pmeta
						INNER JOIN $wpdb->posts as w_post ON (w_post.ID = pmeta.post_id)
						WHERE pmeta.meta_key = '_sku' AND w_post.post_type IN('product','product_variation')
						GROUP BY pmeta.meta_value
						HAVING COUNT(pmeta.meta_value) > 1
					 ) AS s_wpmeta
						ON wpmeta.meta_value = s_wpmeta.meta_value
				INNER JOIN $wpdb->posts as wposts on ( wposts.ID = wpmeta.post_id )
				WHERE wpmeta.meta_key = '_sku' AND wpmeta.meta_value != '' AND wposts.post_type IN('product','product_variation')
				ORDER BY wpmeta.meta_value ASC
			", ARRAY_A );

		return $result;
	}

	/**
	 * Returns all empty sku field for woocommerce product
	 * @return mixed
	 */
	public static function get_woo_empty_sku(){
		global $wpdb;

		//get all products with empty sku
		$empty_skus = $wpdb->get_results("
					SELECT
							wposts.ID,
							wpmeta.meta_key,
							wpmeta.meta_value
					FROM $wpdb->postmeta AS wpmeta
					INNER JOIN $wpdb->posts as wposts on ( wposts.ID = wpmeta.post_id )
					WHERE wpmeta.meta_key = '_sku' AND wpmeta.meta_value = '' AND wposts.post_type IN('product','product_variation')
					ORDER BY wpmeta.meta_value ASC
				", ARRAY_A);

		return $empty_skus;
	}

	/**
	 * @param $product_identifier = array('name'=> 'name of the product', 'sku'=> 'sku of the product')
	 *
	 * @return bool|int|null Returns the product id or sku if already exist and false if it doesn't exist
	 */
	public static function is_new($product_identifier){
		$new = false;

		$product_by_sku  = self::get_product_id_by_sku( $product_identifier['sku'] );
		if( !empty($product_by_sku) ){
			return $product_by_sku;
		}

		$product_by_name = self::get_product_id_by_name( $product_identifier['name'] );
		if( !empty($product_by_name) ){
			return $product_by_name;
		}

		return $new;
	}

	public static function get_product_ids(){
		global $wpdb;

		$product_ids =	$wpdb->get_results("
						SELECT post.ID
						FROM $wpdb->posts AS post
						WHERE
							post.post_type IN('product','product_variation')
						ORDER BY post.ID ASC
						", ARRAY_A);

		if ( $product_ids ) return  $product_ids;

		return null;
	}

	/**
	 * Get Product using the sku
	 * @param $sku
	 * @return int
	 */
	public static function get_product_id_by_sku( $sku ){
		global $wpdb;

		$product_id =	$wpdb->get_var(
			$wpdb->prepare("
						SELECT post.ID
						FROM $wpdb->posts AS post
						INNER JOIN $wpdb->postmeta AS pmeta ON (post.ID = pmeta.post_id)
						WHERE
							pmeta.meta_key='_sku' AND
							pmeta.meta_value=%s AND
							post.post_type IN('product','product_variation')
						LIMIT 1"
				, $sku )
		);

		if ( $product_id ) return  $product_id;

		return null;
	}

	public static function get_product_id_by_name( $name ){
		global $wpdb;

		$product_id =	$wpdb->get_var(
			$wpdb->prepare("
						SELECT post.ID
						FROM $wpdb->posts as post
						WHERE
							post.post_title = %s AND
							post.post_type IN('product','product_variation')
						LIMIT 1"
				, $name )
		);

		if ( $product_id ) return  $product_id;

		return null;
	}

	public static function create($postarr, $wp_error = false){
		$product_type = array( 'product', 'product_variation');
		$post_type = 'product';
		if( !empty($postarr['post_type']) ){
			if( in_array($postarr['post_type'], $product_type) ){
				$post_type = $postarr['post_type'];
			}else{
				$post_type = 'product';
			}
		}

		$postarr['post_type']	=	$post_type;
		$postarr['post_title']	=	empty($postarr['post_title']) ? 'This product name is empty' : $postarr['post_title'];
		$postarr['post_author']	=	empty($postarr['post_author']) ? 1 : $postarr['post_author'];
		return wp_insert_post( $postarr , $wp_error );
	}

	public static function create_variant($parent_id, $postarr, $wp_error = false){
		$postarr['post_parent'] = $parent_id;
		$postarr['post_type'] = 'product_variation';

		return wp_insert_post($postarr, $wp_error);
	}

	public static function get_category( $product_id, $taxonomy = 'product_cat', $args = array() ){
		return wp_get_object_terms($product_id, $taxonomy);
	}

	public static function set_category( $product_id, $terms, $append = false){
		$taxonomy = 'product_cat';
		return wp_set_object_terms( $product_id, $terms, $taxonomy, $append );
	}

	public static function get_category_ids( $product_id ){
		$term_ids = array();

		$product_terms = self::get_category( $product_id );

		if( !empty( $product_terms ) ){
			if( !is_wp_error($product_terms) ){

				foreach($product_terms as $term){
					$term_ids[] = $term->term_id;
				}
			}
		}

		$term_ids = array_map( 'intval', $term_ids );
		$term_ids = array_unique( $term_ids );

		return $term_ids;
	}

	/**
	 * Check if woo category exist returns false if it does not exist otherwise WP_term
	 * @param $term_name
	 * @return bool|WP_Term
	 */
	public static function category_exists( $term_name ){

		$taxonomy = 'product_cat';
		$cat_name = esc_html(trim($term_name));
		//$ls_term = get_term_by('name', $cat_name, $taxonomy, ARRAY_A);

		$ls_term = term_exists( $cat_name, $taxonomy );

		return $ls_term;
	}

	/**
	 * Create and set the category of a product base on product id
	 * @param $product_id
	 * @param $category
	 */
	public static function create_category_from_quickbooks_category( $product_id, $category ){
		$qbo_categories = null;
		$taxonomy = 'product_cat';
		$cat_ids = array();
		$parent = 0;

		if( is_string($category) && $category != '' ){
			$qbo_categories = explode(':', $category);
		}else if(is_array($category) && !empty($category) ){
			$qbo_categories = $category;
		}

		if( !empty($qbo_categories) ){
			/**
			 * QuickBooks Category for a certain product can only have three sub category
			 * a total of four categories per product
			 */
			foreach( $qbo_categories as $category ){
				$term_exist = self::category_exists($category);

				if( !is_array($term_exist) ){
					//if it doesn't exits then we will create it
					$term_arr = wp_insert_term( $category, $taxonomy, array( 'parent' => $parent ) );
					$term_id = (int) $term_arr['term_id'];

				}else if( is_array($term_exist) ) {
					$term_id = (int) $term_exist['term_id'];
				}

				$cat_ids[] = $term_id;
				$parent = $term_id;

			}

			if( !empty($cat_ids) ){
				$cat_ids = array_map( 'intval', $cat_ids );
				$cat_ids = array_unique( $cat_ids );
				$cat_ids = array_diff( $cat_ids, self::get_category_ids($product_id) );

				if( !empty($cat_ids) ){
					self::set_category( $product_id, $cat_ids, true );
				}

			}

		}
	}

}