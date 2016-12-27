<h1>Order Syncing Configuration</h1>
<hr>
<form class="wizard-form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
	<input type="hidden" name="process" value="wizard" />
	<input type="hidden" name="action" value="order-sync" />
	<input type="hidden" name="synctype" value="qbo" />
	<input type="hidden" name="nextpage" value="0" />
	<p class="form-holder">
		<strong>Order syncing type</strong>
		<select name="linksync[order_sync_type]" id="order_syncing_type" class="form-field">
			<option value="woo_to_qbo">WooCommerce to QuickBooks</option>
			<option value="disabled">Disabled</option>
		<select>
	</p>

	<!-- Woo to QuickBooks options -->
	<div id="linksync_order_woo_to_qbo" class="linksync_order_syncing_options" style="display:none;">
		<h3>WooCommerce to QuickBooks Options</h3>
		<p class="form-holder">
			<strong>Would you like to export your Customer Data to QuickBooks?</strong>
			<select name="linksync[order_woo_to_qbo_export_customer]" id="order_woo_to_qbo_export_customer" class="form-field">
				<option value="export_as_customer_data">Yes</option>
				<option value="export_as_woo_sale">No</option>
			<select>
			<br>
			<span><em>If No, customer will be exported as WooCommerce Sale</em></span>
		</p>
		<p class="form-holder">
			<strong>Post to QuickBooks as</strong>
			<select name="linksync[order_woo_to_qbo_post_as]" id="order_woo_to_qbo_post_as" class="form-field">
				<option value="sales_receipt">Sales Receipt</option>
				<option value="sales_invoice">Invoice</option>
			<select>
		</p>
		<p class="form-holder">
			<strong>Order Status</strong>
			<label><input name="linksync[order_woo_to_qbo_order_status][]" value="wc-pending" type="checkbox">Pending Payment</label><br/>
			<label><input name="linksync[order_woo_to_qbo_order_status][]" value="wc-processing" type="checkbox">Processing</label><br/>
			<label><input name="linksync[order_woo_to_qbo_order_status][]" value="wc-on-hold" type="checkbox">On Hold</label><br/>
			<label><input name="linksync[order_woo_to_qbo_order_status][]" value="wc-completed" type="checkbox">Completed</label><br/>
			<label><input name="linksync[order_woo_to_qbo_order_status][]" value="wc-cancelled" type="checkbox">Cancelled</label><br/>
			<label><input name="linksync[order_woo_to_qbo_order_status][]" value="wc-refunded" type="checkbox">Refunded</label>
		</p>
		<p class="form-holder">
			<strong>Order number for QuickBooks</strong>
			<select name="linksync[order_woo_to_qbo_order_number]" id="order_woo_to_qbo_order_number" class="form-field">
				<option value="use_qbo">Use QuickBooks to set order number</option>
				<option value="use_woo">Use Woocommerce to set order number</option>
			<select>
		</p>
	</div>
	<p class="form-holder">
		<input type="submit" name="submit" value="Done" />
	</p>
	<div class="clearfix"></div>
</form>

<script type="text/javascript">
	jQuery(function() {
		// First Load
		jQuery('#order_syncing_type').val('disabled');
		
		jQuery('#order_syncing_type').change(function() {
			var val = jQuery(this).val();
			if(val != 'disabled') {
				switch(val) {
					case 'woo_to_qbo':
						jQuery('#linksync_order_two_way').hide('slow');
						jQuery('#linksync_order_woo_to_qbo').show('slow');
						break;
				}
			} else {
				jQuery('.linksync_order_syncing_options').hide('slow');
			}
		});
	});
</script>