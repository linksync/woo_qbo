<h1>Product Syncing Configuration</h1>
<hr>
<form class="wizard-form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
	<input type="hidden" name="process" value="wizard" />
	<input type="hidden" name="action" value="product-sync" />
	<input type="hidden" name="synctype" value="qbo" />
	<input type="hidden" name="nextpage" value="3" />
	<p class="form-holder">
		<strong>Product syncing type</strong>
		<select name="linksync[product_sync_type]" id="product_syncing_type" class="form-field">
			<option value="two_way">Two Way</option>
			<option value="qbo_to_woo">QuickBooks to WooCommerce</option>
			<option value="disabled">Disabled</option>
		<select>
	</p>
	
	<!-- Two way options -->
	<div id="linksync_product_two_way" class="linksync_product_syncing_options" style="display:none;">
		<h3>Two Way Options</h3>
		<p class="form-holder">
			<strong>Match product with</strong>
			<select name="linksync[match_product_with]" id="match_product_with" class="form-field">
				<option value="name">Name</option>
				<option value="sku">SKU</option>
			<select>
		</p>
		<p class="form-holder">
			<strong>Name/Title</strong>
			<label for="product_two_way_name_title">
				<input type="checkbox" name="linksync[product_two_way_name_title]" id="product_two_way_name_title" value="1" /> Sync the product titles between apps
			</label>
		</p>
		<p class="form-holder">
			<strong>Description</strong>
			<label for="product_two_way_description">
				<input type="checkbox" name="linksync[product_two_way_description]" id="product_two_way_description" value="1" /> Sync the product description between apps
			</label>
		</p>
		<p class="form-holder">
			<strong>Price</strong>
			<label for="product_two_way_price">
				<input type="checkbox" name="linksync[product_two_way_price]" id="product_two_way_price" value="1" /> Sync prices between apps
			</label>
		</p>
		<p class="form-holder">
			<strong>Quantity</strong>
			<label for="product_two_way_quantity">
				<input type="checkbox" name="linksync[product_two_way_quantity]" id="product_two_way_quantity" value="1" /> Sync product Quantity between apps
			</label>
		</p>
		<p class="form-holder">
			<strong>Categories</strong>
			<label for="product_two_way_categories">
				<input type="checkbox" name="linksync[product_two_way_categories]" id="product_two_way_categories" value="1" /> Create categories from QuickBooks in WooCommerce
			</label>
		</p>
		<p class="form-holder">
			<strong>Product Status</strong>
			<label for="product_two_way_product_status">
				<input type="checkbox" name="linksync[product_two_way_product_status]" id="product_two_way_product_status" value="1" /> Tick this option to Set new product to Pending
			</label>
		</p>
		<p class="form-holder">
			<strong>Create New</strong>
			<label for="product_two_way_create_new">
				<input type="checkbox" name="linksync[product_two_way_create_new]" id="product_two_way_create_new" value="1" /> Create new products from QuickBooks 
			</label>
		</p>
		<p class="form-holder">
			<strong>Delete</strong>
			<label for="product_two_way_delete">
				<input type="checkbox" name="linksync[product_two_way_delete]" id="product_two_way_delete" value="1" /> Sync product deletions between apps
			</label>
		</p>
	</div>
	
	<!-- QuickBooks to Woo options -->
	<div id="linksync_product_qbo_to_woo" class="linksync_product_syncing_options" style="display:none;">
		<h3>QuickBooks to WooCommerce Options</h3>
		<p class="form-holder">
			<strong>Match product with</strong>
			<select name="linksync[product_qbo_to_woo_match_product_with]" id="product_qbo_to_woo_match_product_with" class="form-field">
				<option value="name">Name</option>
				<option value="sku">SKU</option>
			<select>
		</p>
		<p class="form-holder">
			<strong>Name/Title</strong>
			<label for="product_qbo_to_woo_name_title">
				<input type="checkbox" name="linksync[product_qbo_to_woo_name_title]" id="product_qbo_to_woo_name_title" value="1" /> Sync the product titles between apps
			</label>
		</p>
		<p class="form-holder">
			<strong>Description</strong>
			<label for="product_qbo_to_woo_description">
				<input type="checkbox" name="linksync[product_qbo_to_woo_description]" id="product_qbo_to_woo_description" value="1" /> Sync the product description between apps
			</label>
		</p>
		<p class="form-holder">
			<strong>Price</strong>
			<label for="product_qbo_to_woo_price">
				<input type="checkbox" name="linksync[product_qbo_to_woo_price]" id="product_qbo_to_woo_price" value="1" /> Sync prices between apps
			</label>
		</p>
		<p class="form-holder">
			<strong>Quantity</strong>
			<label for="product_qbo_to_woo_quantity">
				<input type="checkbox" name="linksync[product_qbo_to_woo_quantity]" id="product_qbo_to_woo_quantity" value="1" /> Sync product Quantity between apps
			</label>
		</p>
		<p class="form-holder">
			<strong>Categories</strong>
			<label for="product_qbo_to_woo_categories">
				<input type="checkbox" name="linksync[product_qbo_to_woo_categories]" id="product_qbo_to_woo_categories" value="1" /> Create categories from QuickBooks in WooCommerce
			</label>
		</p>
		<p class="form-holder">
			<strong>Product Status</strong>
			<label for="product_qbo_to_woo_product_status">
				<input type="checkbox" name="linksync[product_qbo_to_woo_product_status]" id="product_qbo_to_woo_product_status" value="1" /> Tick this option to Set new product to Pending
			</label>
		</p>
		<p class="form-holder">
			<strong>Create New</strong>
			<label for="product_qbo_to_woo_create_new">
				<input type="checkbox" name="linksync[product_qbo_to_woo_create_new]" id="product_qbo_to_woo_create_new" value="1" /> Create new products from QuickBooks 
			</label>
		</p>
		<p class="form-holder">
			<strong>Delete</strong>
			<label for="product_qbo_to_woo_delete">
				<input type="checkbox" name="linksync[product_qbo_to_woo_delete]" id="product_qbo_to_woo_delete" value="1" /> Sync product deletions between apps
			</label>
		</p>
	</div>
	
	<p class="form-holder">
		<input type="submit" name="submit" value="Next Step" />
	</p>
	<div class="clearfix"></div>
</form>

<script type="text/javascript">
	jQuery(function() {
		// First Load
		jQuery('#product_syncing_type').val('disabled');
		
		jQuery('#product_syncing_type').change(function() {
			var val = jQuery(this).val();
			if(val != 'disabled') {
				switch(val) {
					case 'two_way':
						jQuery('#linksync_product_two_way').show('slow');
						jQuery('#linksync_product_qbo_to_woo').hide('slow');
						break;
						
					case 'qbo_to_woo':
						jQuery('#linksync_product_two_way').hide('slow');
						jQuery('#linksync_product_qbo_to_woo').show('slow');
						break;
				}
			} else {
				jQuery('.linksync_product_syncing_options').hide('slow');
			}
		});
	});
</script>