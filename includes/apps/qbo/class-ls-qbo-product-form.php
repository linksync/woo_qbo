<?php if (!defined('ABSPATH')) exit('Access is Denied');

class LS_QBO_Product_Form
{

    /**
     * LS_QBO_Product_form instance
     * @var null
     */
    protected static $_instance = null;

    public $header_title;

    /**
     * Posible sync types
     * @var null
     */
    public $sync_types = null;

    public $options = null;

    public function __construct()
    {
        $this->header_title = 'Product Syncing Configuration';
        if (is_null($this->sync_types)) {
            $this->sync_types = LS_QBO::product_option()->get_all_sync_type();
        }
    }

    public static function instance()
    {

        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function set_users_options($option)
    {
        $this->options = $option;
    }

    public function form_header()
    {
        echo '<h3>', $this->header_title, '</h3>';
    }

    /**
     * @param $sync_type string Sync type
     */
    public function sync_type()
    {
        $sync_type = $this->options['sync_type'];
        ?>
        <fieldset>
            <legend>Product Syncing Type</legend>
            <p>
                <input
                    <?php echo ($sync_type == $this->sync_types[0]) ? 'checked' : ''; ?>
                        name="product_sync_type" type="radio" id="ls-qbo-two-way"
                        value="<?php echo $this->sync_types[0]; ?>">
                <label for="ls-qbo-two-way">Two-way</label>
                <?php
                help_link(array(
                    'title' => 'With this option, product data is kept in sync between both systems, so changes to products and inventory can be made in either your WooCommerce or QuickBooks Online store and those changes will be synced to the other store within a few moments.'
                ));
                ?>

                <input
                    <?php echo ($sync_type == $this->sync_types[1]) ? 'checked' : ''; ?>
                        name="product_sync_type" type="radio" id="ls-qbo-to-woo"
                        value="<?php echo $this->sync_types[1]; ?>">
                <label for="ls-qbo-to-woo">QuickBooks to WooCommerce</label>
                <?php
                help_link(array(
                    'title' => 'With this option, QuickBooks Online is the \'master\' when it comes to managing product and inventory, and product updates are one-way, from QuickBooks Online to Woocommerce - product and inventory data does not update back to QuickBooks Online from WooCommerce.'
                ));
                ?>
                <input
                    <?php echo ($sync_type == $this->sync_types[2]) ? 'checked' : ''; ?>
                        name="product_sync_type" type="radio" id="ls-qbo-disabled"
                        value="<?php echo $this->sync_types[2]; ?>">
                <label for="ls-qbo-disabled">Disabled</label>
                <?php
                help_link(array(
                    'title' => 'Use the Disable option to prevent any product syncing from taking place between your QuickBooks Online and Woocommerce.'
                ));
                ?>
            </p>
        </fieldset>
        <?php
    }


    /**
     * @param $sync_type
     */
    public function sync_bottons()
    {
        $sync_type = $this->options['sync_type'];
        ?>
        <p <?php echo ($sync_type == $this->sync_types[2]) ? 'style="display: none;"' : ''; ?> id="syncing_bottons">
            <input type="button"
                   name="sync_reset_btn"
                   title="Selecting the Sync Reset button resets linksync to update all WooCommerce products with data from Vend, based on your existing Product Sync Settings."
                   value="Sync all products from QuickBooks"
                   class="button button-primary"
                   id="btn_sync_products_from_qbo">

            <input type="button" <?php echo ($sync_type == $this->sync_types[1]) ? 'style="display: none;"' : ''; ?>
                   title="Selecting this option will sync your entire WooCommerce product catalogue to Vend, based on your existing Product Sync Settings. It takes 3-5 seconds to sync each product, depending on the performance of your server, and your geographic location."
                   value="Sync all products to QuickBooks"
                   class="button button-primary"
                   id="btn_sync_products_to_qbo">
        </p>
        <?php
    }

    public function confirm_sync()
    {
        $options = $this->options;

        ?>
        <div id="pop_up" class="ls-pop-ups"
             style="display: <?php echo $options['sync_type'] != $this->sync_types[2] ? $options['pop_up_style'] : 'none'; ?>">
            <div class="close-container">
                <div class="ui-icon ui-icon-close close-reveal-modal btn-no"></div>
            </div>

            <div id="sync_progress_container" style="display: none;">

                <center>
                    <div id="syncing_loader">
                        <p>Please do not close or refresh the browser while syncing is in progress.</p>
                        <img src="../wp-content/plugins/linksync/assets/images/linksync/ajax-loader.gif">
                    </div>
                </center>

                <center><h4 id="sync_message"></h4></center>
                <center><h4 id="sync_progress"></h4></center>
                <br/>

            </div>

            <div id="popup_message">
                <center>
                    <div>
                        <h4 id="sync_pop_up_msg">Your changes will require a full re-sync of product data <br/> Do you
                            want to re-sync now?</h4>
                    </div>
                </center>
            </div>


            <div id="pop_up_btn_container">
                <?php

                if ($options['sync_type'] == $this->sync_types[0]) {
                    ?>
                    <div class="two_way_pop_button">
                        <input type="button" class="product_from_qbo button" value="Product from QuickBooks">
                        <input type="button" class="product_to_qbo button" value="Product to QuickBooks">
                    </div>

                    <div class="sync_all_products_from_qbo pop_button">
                        <input type="button" class="product_from_qbo button btn-yes" value="Yes">
                        <input type="button" class="button btn-no" name="no" value="No">
                    </div>

                    <div class="sync_all_products_to_qbo pop_button">
                        <input type="button" class="product_to_qbo button btn-yes" value="Yes">
                        <input type="button" class="button btn-no" name="no" value="No">
                    </div>

                    <?php
                } else if ($options['sync_type'] == $this->sync_types[1]) {
                    ?>
                    <div class="pop_button">
                        <input type="button" class="product_from_qbo button btn-yes" value="Yes">
                        <input type="button" class="button btn-no" name="no" value="No">
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>

        <?php
    }

    public function save_changes_botton()
    {
        ?>
        <p style="text-align: center;">
            <input class="button button-primary button-large save_changes"
                   type="submit" name="save_product_sync_setting"
                   value="Save Changes">
        </p>
        <?php
    }

    public function match_product_with()
    {
        $option = $this->options['match_product_with'];
        ?>
        <!--Match product Table row-->
        <tr valign="top">
            <th class="titledesc">
                Match product with
                <?php
                help_link(array(
                    'title' => 'When enabled, products are synced with a \'common identifier\' when syncing product information between the two systems by using either of the fields:'
                ));
                ?>
            </th>

            <td class="forminp forminp-checkbox">
                <p>
                    <label>
                        <input type="radio" name="match_product_with"
                               value="name" <?php echo ($option == 'name') ? 'checked' : ''; ?> >Name
                    </label>
                    <label>
                        <input type="radio" name="match_product_with"
                               value="sku" <?php echo ($option == 'sku') ? 'checked' : ''; ?>>SKU
                    </label>
                </p>
            </td>
        </tr>
        <?php
    }

    public function title_or_name()
    {
        $option = $this->options['title_or_name'];
        ?>
        <!--Title or Name table row-->
        <tr valign="top">
            <th class="titledesc">
                Title/Name
                <?php
                help_link(array(
                    'title' => 'When enabled, Product titles will be kept in sync. In WooCommerce this is the product Name and in QuickBooks Online it\'s the Product name.'
                ));
                ?>
            </th>

            <td class="forminp forminp-checkbox">
                <label>
                    <input id="ls-qb-match-with" type="checkbox" <?php echo ($option == 'on') ? 'checked' : ''; ?>
                           name="title_option">
                    Sync the product titles between apps
                </label>
            </td>
        </tr>
        <?php
    }

    public function description()
    {
        $option = $this->options['description'];
        ?>
        <!--Description Table row-->
        <tr valign="top">
            <th class="titledesc">
                Description
                <?php
                help_link(array(
                    'title' => 'When enabled, product descriptions will be kept in sync.'
                ));
                ?>
            </th>

            <td class="forminp">
                <label>
                    <input name="description" type="checkbox" <?php echo ($option == 'on') ? 'checked' : ''; ?>>
                    Sync the product description between apps
                </label>
            </td>
        </tr>
        <?php
    }

    public function price()
    {
        $option = $this->options['price'];
        $tax_classes = LS_Woo_Tax::get_tax_classes();
        $qbo_tax_rates = $this->options['qbo_tax_classes'];

        $selected_tax_classes = $option['tax_classes'];
        ?>
        <!--Price Table row-->
        <tr valign="top">
            <th scope="row" class="titledesc">
                Price
                <?php
                help_link(array(
                    'title' => 'When enabled, prices will be kept in sync.'
                ));
                ?>
            </th>

            <td class="forminp forminp-checkbox">
                <label>
                    <input type="checkbox" <?php echo ($option['price'] == 'on') ? 'checked' : ''; ?> name="price"
                           id="price_checbox">
                    Sync prices between apps
                </label>

                <br><br>

                <div class="sub-option"
                     id="price_options_container" <?php echo ($option['price'] != 'on') ? 'style="display:none;"' : '' ?>>
						<span class="ps_price_sub_options">
							<?php
                            $woo_calc_taxes = get_option('woocommerce_calc_taxes');
                            if ('yes' == $woo_calc_taxes) {
                                ?>
                                <label>
									<input type="checkbox" <?php echo ($option['use_woo_tax'] == 'on') ? 'checked' : ''; ?>
                                           name="use_woo_tax" id="use_woo_tax_checkbox">
									Use WooCommerce Tax Options
                                    <?php
                                    help_link(array(
                                        'title' => 'This option uses the Woocommerce Tax Options settings to determine if your prices are inclusive or exclusive of tax when syncing with QuickBooks Online. You should only need to disable this option if you have altered the standard tax settings in QuickBooks Online.'
                                    ));
                                    ?>
								</label>
                                <?php
                            }
                            ?>
                            <div class="<?php echo ('yes' == $woo_calc_taxes) ? 'sub-option' : ''; ?>"
                                 id="ls-qbo-tax-options" <?php echo ($option['use_woo_tax'] == 'on') ? 'style="display:none;"' : ''; ?>>
								<b>Treat prices in QuickBooks as</b>
                                <?php
                                help_link(array(
                                    'title' => 'When syncing prices with QuickBooks Online, linksync should  treat the QuickBooks Online prices as inclusive or exclusive of tax. Which option you select will depend on whether your prices in Woocommerce include tax or not.'
                                ));
                                ?>
                                <div class="sub-option" id="use_woo_tax_container">
									<ul>
										<li>
											<label>
												<input name="tax_option"
                                                       type="radio" <?php echo ($option['tax_option'] == 'exclusive') ? 'checked' : ''; ?>
                                                       value="exclusive"> Exclusive of Tax
											</label>
										</li>
										<li>
											<label>
												<input name="tax_option"
                                                       type="radio" <?php echo ($option['tax_option'] == 'inclusive') ? 'checked' : ''; ?>
                                                       value="inclusive"> Inclusive of Tax
											</label>
										</li>
									</ul>
								</div>

							</div><br>

							<label class="ps_price_sub_options">
								<br/>
								<b>Tax Mapping</b>
                                <?php
                                help_link(array(
                                    'title' => 'When syncing products, both Vend and WooCommerce have their own tax configurations - use these Tax Mapping settings to \'map\' the Vend taxes with those in your WooCommerce store. Note that the mapping is used to specify the Tax Class for a product in WooCommerce, and the Sales tax for a product in Vend, depending on which Product Syncing Type you select.'
                                ));
                                ?>
							</label>

							<div class="sub-option">
								<p class="description ps_price_sub_options">
									To set the relevant tax rate for a product in WooCommerce
								</p>
								<div>
									<table>
										<thead>
											<tr>
												<th>QuickBooks Taxes</th><th>Woo-Commerce Tax Classes</th>
											</tr>
										</thead>
										<tbody>
										<?php
                                        foreach ($qbo_tax_rates as $qbo_tax_rate) {
                                            ?>
                                            <tr>
												<td><?php echo $qbo_tax_rate['name']; ?></td>
												<td>
													<select name="tax_classes[<?php echo $qbo_tax_rate['id']; ?>]">
														<?php
                                                        foreach ($tax_classes as $tax_key => $tax_class) {
                                                            $selected = '';
                                                            if (!empty($selected_tax_classes)) {
                                                                if (array_key_exists($qbo_tax_rate['id'], $selected_tax_classes)) {
                                                                    if ($tax_key == $selected_tax_classes[$qbo_tax_rate['id']]) {
                                                                        $selected = 'selected';
                                                                    }
                                                                }
                                                            }
                                                            echo '<option ', $selected, ' value="', $tax_key, '">', $tax_class, '</option>';
                                                        }
                                                        ?>
													</select>
												</td>
											</tr>
                                            <?php
                                        }
                                        ?>
										</tbody>
									</table>
								</div>
							</div>

						</span>
                </div>
            </td>
        </tr>
        <?php
    }

    public function quantity()
    {
        $options = $this->options;
        $can_use_quantity_option = LS_QBO()->can_use_quantity_option();


        if ($can_use_quantity_option == false) {
            $style = ' class="bg-grayed color-red" ';
            $th_style = ' style="padding-left: 25px;width: 175px;"';
            ?>
            <tr valign="top" <?php echo $style; ?> >
                <th colspan="2" <?php echo $th_style; ?>>
                    <strong class="color-red">You cannot use this Option.
                        You must have QuickBooks Online Plus subscription and your
                        <a href="https://qbo.intuit.com/app/settings?p=Sales">Tracking inventory</a> should be on
                    </strong>
                </th>
            </tr>
            <?php
        }

        ?>
        <!--Quantity Table Row-->
        <tr valign="top" <?php if (isset($style)) {
            echo $style;
        } ?>>
            <th class="titledesc" <?php if (isset($th_style)) {
                echo $th_style;
            } ?>>
                <strong <?php if (isset($style)) {
                    echo $style;
                } ?> >Quantity</strong>
                <?php
                help_link(array(
                    'title' => 'When enabled, product quantities will be kept in sync.'
                ));
                ?>
            </th>

            <td class="forminp forminp-checkbox">
                <div>
                    <label>
                        <input name="quantity_option"
                               type="checkbox" <?php echo ($options['quantity']['quantity'] == 'on') ? 'checked' : ''; ?>
                               id="quantity_checkbox">
                        Sync product Quantity between apps
                    </label>

                    <div class="ps_quanity_suboptions"
                         id="quantity_options_container" <?php echo ($options['quantity']['quantity'] != 'on') ? 'style="display:none"' : ''; ?>>
                        <div id="change_product_status" class="sub-option">
                            <label>
                                <input name="change_product_status_option"
                                       type="checkbox" <?php echo ($options['quantity']['change_status'] == 'on') ? 'checked' : ''; ?> >
                                Change product status in WooCommerce based on stock quantity
                                <?php
                                help_link(array(
                                    'title' => 'This setting only applies to WooCommerce - select this option if you want product with inventory quantities of 0 (zero) or less to be made unavailable for purchase in your WooCommerce store. In the case of simple products, this option will set them them to ‘draft’, and in the case of Variable products, the variation would be set to Out of stock.'
                                ));
                                ?>
                            </label>

                        </div>

                        <div class="sub-option">

                            <b>
                                Inventory Asset Account
                                <?php
                                help_link(array(
                                    'title' => 'You may choose the default setting to be used when creating the product in QuickBooks Online.'
                                ));
                                ?>
                            </b>
                            <?php
                            if (!empty($options['assets_account'])) {
                                $selected_inv_asset_account = $options['quantity']['inventory_asset_acccount'];

                                echo '<select name="inventory_asset_account_select" style="margin-left: 10px;">';
                                foreach ($options['assets_account'] as $assets_account) {
                                    $selected = '';
                                    if (is_numeric($selected_inv_asset_account) && ($selected_inv_asset_account == $assets_account['id'])) {
                                        $selected = 'selected';
                                    } else if ($selected_inv_asset_account == $assets_account['name']) {
                                        $selected = 'selected';
                                        LS_QBO()->product_option()->update_inventory_asset_account($assets_account['id']);
                                    }

                                    echo '<option ', $selected, ' value="', $assets_account['id'], '">', $assets_account['name'], '</option>';
                                }
                                echo '</select>';
                            } else {
                                echo '<p class="color-red">No data from Inventory Asset Account from QuickBooks</p>';
                            }
                            ?>


                        </div>

                        <div class="sub-option">

                            <b>
                                Expense Account
                                <?php
                                help_link(array(
                                    'title' => 'You may choose the default setting to be used when creating the product in QuickBooks Online. '
                                ));
                                ?>
                            </b>
                            <?php
                            if (!empty($options['expense_account'])) {
                                $selected_expense_account = $options['quantity']['expense_account'];

                                echo '<select name="inventory_expense_account_select" style="margin-left: 60px;">';
                                foreach ($options['expense_account'] as $expense_account) {
                                    echo '<option ', ($selected_expense_account == $expense_account['id']) ? 'selected' : '', ' value="', $expense_account['id'], '">', $expense_account['name'], '</option>';
                                }
                                echo '</select>';
                            } else {
                                echo '<p class="color-red">No data from Expense Account from QuickBooks</p>';
                            }
                            ?>

                        </div>
                    </div>
                </div>

            </td>
        </tr>
        <?php
    }

    public function income_account()
    {
        $option = $this->options['income_accounts'];
        $qty_option = $this->options['quantity']['quantity'];
        $selected_income_account = $this->options['income_account'];

        ?>
        <!--Income Account Table Row-->
        <tr valign="top">
            <th class="titledesc">
                Income Account
                <?php
                help_link(array(
                    'title' => 'This option allows you to select the default setting that is used when creating the product in QuickBooks Online.'
                ));
                ?>
            </th>

            <td class="forminp forminp-checkbox">
                <?php
                $selected_inc_acount = null;
                //Doc 11.2.7.11 and 11.2.7.1.2
                if ('on' == $qty_option) {
                    $selected_inc_acount = 'Sales of Product Income';
                } else {
                    $selected_inc_acount = 'Sales';
                }
                if (!empty($selected_income_account)) {
                    $selected_inc_acount = $selected_income_account;
                }


                if (!empty($option)) {
                    echo '<select name="income_account_select">';
                    foreach ($option as $income_account) {
                        $selected = '';
                        if (is_numeric($selected_inc_acount) && ($selected_inc_acount == $income_account['id'])) {
                            $selected = 'selected';
                        } else if ($selected_inc_acount == $income_account['name']) {
                            $selected = 'selected';
                        }

                        echo '<option ', $selected, ' value="', $income_account['id'], '">', $income_account['name'], '</option>';

                    }
                    echo '</select>';
                } else {
                    echo '<p class="color-red">No data for Income account from QuickBooks<p>';
                }
                ?>
            </td>
        </tr>
        <?php
    }

    public function categories()
    {
        $option = $this->options['category'];
        ?>
        <!--Categories Table Row-->
        <tr id="ps_cat_id_p" valign="top" class="woocommerce_frontend_css_colors">
            <th scope="row" class="titledesc">
                Categories
                <?php
                help_link(array(
                    'title' => 'When enabled, products will sync accordingly to their categories within QuickBooks Online and Woocommerce.'
                ));
                ?>
            </th>

            <td class="forminp">
                <div>
                    <label>
                        <input name="category_option" type="checkbox" <?php echo ($option == 'on') ? 'checked' : ''; ?>>
                        Create categories from QuickBooks in WooCommerce
                    </label>
                </div>

            </td>
        </tr>
        <?php
    }

    public function product_status()
    {
        $option = $this->options['product_status'];
        ?>
        <!--Product Status Table Row-->
        <tr id="ps_create_tr" valign="top" class="woocommerce_frontend_css_colors">
            <th scope="row" class="titledesc">
                Product Status
                <?php
                help_link(array(
                    'title' => 'Enable this option if you want newly created product in WooCommerce to be set to \'Pending Review\', so that you can review and update new products before they are published in your WooCommerce store.'
                ));
                ?>
            </th>

            <td class="forminp">
                <label>
                    <input type="checkbox"
                           name="product_status_option" <?php echo ($option == 'on') ? 'checked' : ''; ?>>
                    Tick this option to Set new product to <strong>Pending</strong>
                </label>
            </td>
        </tr>
        <?php
    }

    public function create_new()
    {
        $option = $this->options['create_new'];
        ?>
        <!--Create New Table Row-->
        <tr id="ps_create_tr" valign="top" class="woocommerce_frontend_css_colors">
            <th scope="row" class="titledesc">
                Create New
                <?php
                help_link(array(
                    'title' => 'Select this option if you want \'new\' products from QuickBooks Online created in WooCommerce automatically. If this option is not enabled, new products will not be created in WooCommerce - you will need to manually create them, after which, they will be kept in sync.'
                ));
                ?>
            </th>

            <td class="forminp">
                <label>
                    <input type="checkbox"
                           name="create_new_product_option" <?php echo ($option == 'on') ? 'checked' : ''; ?>>
                    Create new products from QuickBooks
                </label>
            </td>
        </tr>
        <?php
    }

    public function delete_view()
    {
        $option = $this->options['delete'];
        ?>
        <!--Delete Table Row-->
        <tr valign="top" class="woocommerce_frontend_css_colors">
            <th scope="row" class="titledesc">
                Delete
                <?php
                help_link(array(
                    'title' => 'Select this option if you want products permanently deleted. Depending on which Product Syncing Type you select, if products are deleted in one store, they will immediately be deleted from the other.'
                ));
                ?>
            </th>

            <td class="forminp">
                <input name="delete_product_option" type="checkbox" <?php echo ($option == 'on') ? 'checked' : ''; ?>>
                Sync product deletions between apps<br>
            </td>
        </tr>
        <?php
    }

    /**
     * Show Product syncing form and the users selected option
     * Default option is also set properly in this method
     */
    public function product_syncing_settings()
    {
        $product_syncing = LS_QBO_Product_Form::instance();
        $product_options = LS_QBO()->product_option();
        $qbo_api = LS_QBO()->api();

        $show_hide_pop_up = 'none';
        $user_options = null;


        /**
         * Detect Save Changes was submitted
         */
        if (isset($_POST['form_items'])) {
            $product_syncing->update_product_syncing_settings($_POST['form_items']);
            $show_hide_pop_up = 'block';
        }

        $sync_type = $product_options->sync_type();
        $sync_types = $product_options->get_all_sync_type();

        $hide_on_disabled = ($sync_type == $sync_types[2]) ? 'style="display: none;"' : '';

        $user_options = $product_options->get_current_product_syncing_settings();
        $user_options['pop_up_style'] = $show_hide_pop_up;

        $user_options['qbo_info'] = get_option('ls_qbo_info');
        $user_options['assets_account'] = get_option('ls_asset_accounts');
        $user_options['expense_account'] = get_option('ls_expense_accounts');
        $user_options['income_accounts'] = get_option('ls_income_accounts');
        $user_options['qbo_tax_classes'] = get_option('ls_qbo_tax_classes');

        if ('disabled' != $user_options['sync_type']) {

            //if Discount option and Shipping option is not enabled
            if (isset($user_options['qbo_info'])) {
                if (isset($user_options['qbo_info']['allowDiscount']) && isset($user_options['qbo_info']['allowShipping'])) {
                    if (!$user_options['qbo_info']['allowDiscount'] || !$user_options['qbo_info']['allowShipping']) {
                        LS_QBO()->show_shipping_and_discount_guide($user_options);
                        die();
                    }
                } else {
                    LS_QBO()->show_shipping_and_discount_guide($user_options);
                    die();
                }
            }


            if ('sku' == $user_options['match_product_with']) {

                $duplicate_products = LS_Woo_Product::get_woo_duplicate_sku();
                $emptyProductSkus = LS_Woo_Product::get_woo_empty_sku();
                $products_data = array_merge($duplicate_products, $emptyProductSkus);
                if (count($products_data) > 0) {
                    LS_QBO()->show_woo_duplicate_products($products_data, $emptyProductSkus, $duplicate_products);
                    die();
                }

                $qbo_duplicate = get_option('ls_qbo_duplicate_products');
                if (!empty($qbo_duplicate['products'])) {
                    LS_QBO()->show_qbo_duplicate_products($qbo_duplicate['products']);
                    die();
                }
            }

        }


        $product_syncing->set_users_options($user_options);
        $product_syncing->confirm_sync();

        /**
         * Display the Product Syncing Settings view and form
         */
        echo '<form method="post" id="ps_form_settings">';
        $product_syncing->form_header();
        $product_syncing->sync_type();

        echo '<div id="ls-qbo-product-syncing-settings" ', $hide_on_disabled, '>';

        $product_syncing->sync_bottons();
        echo '<table class="form-table"><tbody>';

        $product_syncing->match_product_with();
        $product_syncing->title_or_name();
        $product_syncing->description();
        $product_syncing->price();


        $product_syncing->quantity();

        $product_syncing->income_account();
        $product_syncing->categories();
        $product_syncing->product_status();
        $product_syncing->create_new();
        $product_syncing->delete_view();

        echo '</tbody></table>';

        echo '</div>';
        $product_syncing->save_changes_botton();

        echo '</form>';

        die();
    }

    /**
     * @param $user_options array of product syncing option
     */
    public function update_product_syncing_settings($user_options)
    {
        $product_options = LS_QBO()->product_option();
        if (!is_array($user_options)) {
            parse_str($user_options, $user_options);
        }

        if (isset($user_options['product_sync_type'])) {
            $product_options->update_sync_type($user_options['product_sync_type']);
        }

        if (isset($user_options['match_product_with'])) {
            $product_options->update_match_product_with($user_options['match_product_with']);
        }

        if (isset($user_options['title_option'])) {
            $product_options->update_title_or_name($user_options['title_option']);
        } else {
            $product_options->update_title_or_name('off');
        }

        if (isset($user_options['description'])) {
            $product_options->update_description($user_options['description']);
        } else {
            $product_options->update_description('off');
        }

        if (isset($user_options['price'])) {
            $product_options->update_price($user_options['price']);
        } else {
            $product_options->update_price('off');
        }

        if (isset($user_options['use_woo_tax'])) {
            $product_options->update_use_woo_tax_option($user_options['use_woo_tax']);
        } else {
            $product_options->update_use_woo_tax_option('off');
        }

        if (isset($user_options['tax_option'])) {
            $product_options->update_tax_option($user_options['tax_option']);
        }

        if (isset($user_options['tax_classes'])) {
            $product_options->update_tax_class($user_options['tax_classes']);
        }

        if (isset($user_options['quantity_option'])) {
            $product_options->update_quantity($user_options['quantity_option']);
        } else {
            $product_options->update_quantity('off');
        }

        if (isset($user_options['change_product_status_option'])) {
            $product_options->update_change_product_status($user_options['change_product_status_option']);
        } else {
            $product_options->update_change_product_status('off');
        }

        if (isset($user_options['inventory_asset_account_select'])) {
            $product_options->update_inventory_asset_account($user_options['inventory_asset_account_select']);
        }

        if (isset($user_options['inventory_expense_account_select'])) {
            $product_options->update_expense_account($user_options['inventory_expense_account_select']);
        }

        if (isset($user_options['inventory_expense_account_select'])) {
            $product_options->update_expense_account($user_options['inventory_expense_account_select']);
        }

        if (isset($user_options['income_account_select'])) {
            $product_options->update_income_account($user_options['income_account_select']);
        }

        if (isset($user_options['category_option'])) {
            $product_options->update_category($user_options['category_option']);
        } else {
            $product_options->update_category('off');
        }

        if (isset($user_options['product_status_option'])) {
            $product_options->update_product_status($user_options['product_status_option']);
        } else {
            $product_options->update_product_status('off');
        }

        if (isset($user_options['create_new_product_option'])) {
            $product_options->update_create_new($user_options['create_new_product_option']);
        } else {
            $product_options->update_create_new('off');
        }

        if (isset($user_options['delete_product_option'])) {
            $product_options->update_delete($user_options['delete_product_option']);
        } else {
            $product_options->update_delete('off');
        }

        LS_QBO()->set_quantity_option_base_on_qboinfo();
    }
}