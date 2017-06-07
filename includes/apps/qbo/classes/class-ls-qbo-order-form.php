<?php if (!defined('ABSPATH')) exit('Access is Denied');

class LS_QBO_Order_Form
{
    /**
     * LS_QBO_Order_Option instance
     * @var null
     */
    protected static $_instance = null;

    public $header_title;

    /**
     * Possible order sync types
     * @var null
     */
    public $sync_types = null;

    /**
     * Possible export type
     * @var null
     */
    public $export_types = null;

    /**
     * Posible receipt types
     * @var null
     */
    public $receipt_types = null;

    /**
     * Payment Method either
     * 'Send WooCommerce order payment method to QBO'
     * or
     * 'Map payment methods'
     * @var
     */
    public $payment_methods = null;

    /**
     * @var null
     */
    public $order_numbers_option = null;

    /**
     * @var LS_QBO_Product_Option
     */
    public $order_options = null;


    public $options = null;

    /**
     * LS_QBO_Order_Form constructor.
     */
    public function __construct()
    {
        $this->header_title = 'Order Syncing Configuration';


        if (is_null($this->order_options)) {
            $this->order_options = LS_QBO_Order_Option::instance();

            $this->sync_types = $this->order_options->get_all_sync_type();
            $this->export_types = $this->order_options->get_all_export_type();
            $this->receipt_types = $this->order_options->get_all_receipt_type();
            $this->payment_methods = $this->order_options->get_all_payment_method();
            $this->order_numbers_option = $this->order_options->get_all_order_number();
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
     * @param $option string either
     */
    public function sync_type()
    {
        $option = $this->options['sync_type'];

        ?>
        <fieldset id="qbo-order-configuration">
            <legend>Order Syncing Type</legend>
            <p>

                <input <?php echo ($option == $this->sync_types[0]) ? 'checked' : ''; ?>
                        name="order_sync_type"
                        type="radio"
                        id="ls-qbo-to-woo"
                        value="<?php echo $this->sync_types[0]; ?>">

                <label for="ls-qbo-to-woo">WooCommerce to QuickBooks</label>
                <?php
                help_link(array(
                    'title' => 'If you\'re using the QuickBooks Online to WooCommerce product syncing option, then you need to enable this option so that any sales in WooCommerce are synced to QuickBooks Online - this ensures that the inventory levels in QuickBooks Online are updated based on any orders entered in WooCommerce.'
                ));
                ?>

                <input name="order_sync_type" type="radio"
                       id="ls-qbo-disabled" <?php echo ($option == $this->sync_types[1]) ? 'checked' : ''; ?>
                       value="<?php echo $this->sync_types[1]; ?>">
                <label for="ls-qbo-disabled">Disabled</label>
                <?php
                help_link(array(
                    'title' => 'Use the Disable option to prevent any orders syncing between QuickBooks Online and WooCommerce stores. This is the default setting if you\'re using ‘Two-way’ product syncing, as this option does not require order syncing.'
                ));
                ?>
            </p>

        </fieldset>
        <?php
    }

    public function customer_export()
    {
        $option = $this->options['customer_export'];

        ?>
        <!--Customer Export Table Row-->
        <tr valign="top">
            <th class="titledesc">
                Customer Export
            </th>
            <td class="forminp forminp-checkbox">

                <label>
                    <input <?php echo ($option == $this->export_types[0]) ? 'checked' : ''; ?>
                            name="customer_export"
                            type="radio"
                            value="<?php echo $this->export_types[0]; ?>">

                    Export Customer data
                    <?php
                    help_link(array(
                        'title' => 'When enabled, customer data, such as Name, Email Address and Shipping and Billing Address, are included when importing/exporting orders.'
                    ));
                    ?>
                </label>

                <label>
                    <input <?php echo ($option == $this->export_types[1]) ? 'checked' : ''; ?>
                            name="customer_export" type="radio"
                            value="<?php echo $this->export_types[1]; ?>">

                    Export as 'WooCommerce Sale'
                    <?php
                    help_link(array(
                        'title' => 'Select this option if you\'re not interested in including the customer information when exporting orders to QuickBooks Online.'
                    ));
                    ?>
                </label>

            </td>
        </tr>
        <?php
    }

    public function post_to_quickbooks_as()
    {
        $option = $this->options;
        ?>
        <!--Post to QuickBooks Table Row-->
        <tr valign="top">
            <th class="titledesc">
                Post to QuickBooks as
                <?php
                help_link(array(
                    'title' => 'Select how you\'d like your orders in Woocommerce to sync to QuickBooks Online by choosing any of the options below.'
                ));
                ?>
            </th>
            <td class="forminp forminp-checkbox">
                <!--Sales Receipt Option-->
                <div>
                    <label>
                        <input <?php echo ($option['post_quikbooks_as']['receipt_type'] == $this->receipt_types[0]) ? 'checked' : ''; ?>
                                name="post_to_quickbooks_as"
                                type="radio"
                                value="<?php echo $this->receipt_types[0]; ?>">

                        Sales Receipt
                        <?php
                        help_link(array(
                            'title' => 'When selecting this, your order in Woocommerce will sync to QuickBooks Online as a Sales Receipt in your order setting. This setting does not require any customer data, and may take an order as a ‘Woocommerce Sale’ only. However, an email address is needed for the sale to sync properly.'
                        ));
                        ?>
                    </label>
                    <div id="sales_receipt_container" <?php echo ($option['post_quikbooks_as']['receipt_type'] != $this->receipt_types[0]) ? 'style="display:none;"' : ''; ?>>

                        <div class="sub-option">
                            <b>Deposit Account</b>

                            <?php
                            if (!empty($option['deposit_accounts'])) {
                                $selected = $option['post_quikbooks_as']['deposit_account'];
                                echo '<select name="deposit_account">';
                                foreach ($option['deposit_accounts'] as $assets_account) {
                                    $selectedDepositAccount = ($selected == $assets_account['id']) ? 'selected' : '';
                                    ?>
                                    <option <?php echo $selectedDepositAccount; ?>
                                            value="<?php echo $assets_account['id']; ?>">
                                        <?php echo $assets_account['fullyQualifiedName']; ?>
                                    </option>
                                    <?php
                                }
                                echo '</select>';
                            } else {
                                echo 'No data from QuickBooks';
                            }

                            ?>

                        </div>
                        <br/>
                        <div class="sub-option">
                            <b>Payment Method</b>
                            <div class="sub-option">

                                <div class=""
                                     id="mapped_payment_method_container" <?php echo ($option['post_quikbooks_as']['payment_method'] != $this->payment_methods[1]) ? 'style="display:none;"' : ''; ?>>
                                    <table>
                                        <thead>
                                        <tr>
                                            <th>Woo-Commerce</th>
                                            <th>QuickBooks Online</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        $wc_payment_method = ls_get_woo_payment_methods();

                                        foreach ($wc_payment_method as $payment_key => $wc_payment) {
                                            ?>
                                            <tr>
                                                <td><?php echo $wc_payment->title; ?></td>
                                                <td>
                                                    <?php
                                                    if (!empty($option['qbo_payment_methods'])) {
                                                        $selected_payment = $option['post_quikbooks_as']['selected_payment_method'];
                                                        echo '<select name="payment_method_select[', $payment_key, ']">';

                                                        foreach ($option['qbo_payment_methods'] as $qbo_payment_method) {
                                                            $payment_info = explode("|", $selected_payment[$payment_key]);
                                                            $selected = ($payment_info[0] == $qbo_payment_method['id']) ? 'selected' : '';
                                                            $option_value = $qbo_payment_method['id'] . "|" . $qbo_payment_method['name'];
                                                            echo '<option ', $selected, ' value="', $option_value, '">', $qbo_payment_method['name'], '</option>';
                                                        }

                                                        echo '</select>';

                                                    } else {
                                                        echo '<p class="color-red">No Payment method from QuickBooks</p>';
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                        ?>
                                        </tbody>
                                    </table>

                                </div>
                            </div>
                            <br/>
                        </div>

                    </div>


                </div>
                <!--Sales Invoice Option-->
                <div>
                    <label>
                        <input <?php echo ($option['post_quikbooks_as']['receipt_type'] == $this->receipt_types[1]) ? 'checked' : ''; ?>
                                name="post_to_quickbooks_as"
                                type="radio"
                                value="<?php echo $this->receipt_types[1]; ?>">
                        Invoice
                        <?php
                        help_link(array(
                            'title' => 'When selecting this, your order in Woocommerce will sync to QuickBooks Online as an ‘Invoice’ in your QuickBooks Online order setting. This setting requires customer data and an email address in order for the sale to sync properly.'
                        ));
                        ?>
                    </label>
                    <div id="sales_invoice_container"
                         class="sub-option" <?php echo ($option['post_quikbooks_as']['receipt_type'] != $this->receipt_types[1]) ? 'style="display:none;"' : ''; ?>>
                        <select name="payment_method_select_invoice" style="display: none !important;">
                            <option value="No Tax-0|standard-tax">Sales Invoice Term</option>
                            <option value="No Tax-0|standard-tax">Sales</option>
                        </select>
                    </div>
                </div>

            </td>
        </tr>
        <?php
    }

    public function order_status()
    {
        $option = $this->options;
        $order_statuses = wc_get_order_statuses();
        unset($order_statuses['wc-failed']);
        ?>
        <!--Order Status Table Row-->
        <tr valign="top">
            <th class="titledesc">
                Orders Status
                <?php
                help_link(array(
                    'title' => 'In the case of QuickBooks Online orders being imported into WooCommerce, use this option to select the default status of the order when it\'s imported. In most cases, you will set this to \'Completed\'.
If you\'re exporting orders from WooCommerce to QuickBooks Online, then use this option to select what status the order must be before it is exported. Keep in mind that an order can only be exported to QuickBooks Online once, and once exported, it can not be edited in QuickBooks Online.'
                ));
                ?>
            </th>
            <td class="forminp forminp-checkbox">
                <?php
                foreach ($order_statuses as $order_status_key => $order_status_value) {
                    ?>
                    <label>
                        <input name="order_status[]"
                               value="<?php echo $order_status_key ?>"
                               type="checkbox"
                            <?php echo in_array($order_status_key, $option['order_status']) ? 'checked' : ''; ?>>
                        <?php echo $order_status_value; ?>
                    </label>
                    <?php
                }
                ?>
            </td>
        </tr>
        <?php
    }

    public function order_number_for_qbo()
    {
        $option = $this->options['order_number'];
        ?>
        <!--Order Number for QBO Row-->
        <tr valign="top">
            <th class="titledesc">
                Order number for QuickBooks
                <?php
                help_link(array(
                    'title' => 'Select the source of the ‘Order Number’ you’d like to use for your orders.'
                ));
                ?>
            </th>
            <td class="forminp forminp-checkbox">
                <label>
                    <input <?php echo ($option == $this->order_numbers_option[0]) ? 'checked' : ''; ?>
                            name="order_number"
                            type="radio"
                            value="<?php echo $this->order_numbers_option[0]; ?>">
                    Use QuickBooks to set order number
                </label>

                <label>
                    <input <?php echo ($option == $this->order_numbers_option[1]) ? 'checked' : ''; ?>
                            name="order_number"
                            type="radio"
                            value="<?php echo $this->order_numbers_option[1]; ?>">
                    Use Woocommerce to set order number
                </label>
            </td>
        </tr>
        <?php
    }

    public function tax_mapping()
    {
        $option = $this->options;
        $tax_classes = LS_Woo_Tax::get_tax_classes();
        $qbo_tax_rates = $this->options['qbo_tax_classes'];
        ?>
        <!--Tax mappting Row-->
        <tr valign="top">
            <th class="titledesc">
                Tax mapping
                <?php
                help_link(array(
                    'title' => 'When syncing orders, both QuickBooks Online and WooCommerce have their own tax configurations - use these Tax Mapping settings to \'map\' the QuickBooks Online taxes with those in your WooCommerce store.'
                ));
                ?>
            </th>
            <td class="forminp forminp-checkbox">
                <table>
                    <thead>
                    <tr>
                        <th>Woo-Commerce</th>
                        <th>QuickBooks Online</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($tax_classes as $tax_key => $tax_class) {

                        $class_tax_rates = LS_Woo_Tax::get_tax_rates($tax_key);

                        ?>
                        <tr>
                            <td colspan="2"><b><?php echo $tax_class; ?></b></td>
                        </tr>
                        <?php
                        if (!empty($class_tax_rates)) {
                            foreach ($class_tax_rates as $tax_rate) {
                                ?>
                                <tr>
                                    <td><?php echo $tax_rate['tax_rate_name']; ?></td>
                                    <td>
                                        <?php
                                        if (!empty($option['qbo_tax_classes'])) {
                                            $id = $option['tax_mapping'][$tax_rate['tax_rate_id']][$tax_key];
                                            echo '<select name="tax_mapping[', $tax_rate['tax_rate_id'], '][', $tax_key, ']">';
                                            foreach ($qbo_tax_rates as $qbo_tax_rate) {
                                                if($qbo_tax_rate['active']){
                                                    $val = $qbo_tax_rate['id'] . '|' . $qbo_tax_rate['name'] . '|' . $qbo_tax_rate['active'] . '|' . (isset($qbo_tax_rate['rateValue']) ? $qbo_tax_rate['rateValue'] : 0);
                                                    $selected = ($id == $val) ? 'selected' : '';
                                                    echo '<option ', $selected, ' value="', $val, '">', $qbo_tax_rate['name'], '</option>';
                                                }
                                            }
                                            echo '</select>';

                                        }
                                        ?>
                                    </td>
                                </tr>
                                <?php
                            }
                        }

                    }
                    ?>
                    </tbody>
                </table>
            </td>
        </tr>
        <?php
    }

    public function location()
    {
        $option = $this->options;
        ?>
        <!--Location Row-->
        <tr valign="top">
            <th>
                Location
                <?php
                help_link(array(
                    'title' => 'When enabled, the option you select will be the default setting of the location where your sales order will appear in QuickBooks Online.'
                ));
                ?>
            </th>
            <td class="forminp forminp-checkbox">
                <label>
                    <?php
                    if (empty($option['location_list'])) {
                        echo '<p class="color-red">No Location data retrieved. You need to set it on QuickBooks. <a target="_blank" href="https://help.linksync.com/hc/en-us/articles/115000147390">See this guide on how to create at least one</a> </p>';
                        LS_QBO()->order_option()->update_location_status('off');
                    } else {
                        ?>
                        <input name="location_checkbox"
                               type="checkbox" <?php echo ($option['location']['location_status'] == 'on') ? 'checked' : ''; ?> >
                        Enable
                        <?php
                        /*help_link(array(
                            'title' => 'Select this option if you\'d like customer data, such as name, email address and shipping and billing address, to be included when exporting orders to Vend.'
                        ));*/
                    }
                    ?>
                </label>
                <div class="sub-option">
                    <?php
                    if (!empty($option['location_list'])) {
                        $display = ($option['location']['location_status'] != 'on') ? 'style="display:none;"' : '';
                        echo '<select name="qbo_location" ', $display, '>';
                        foreach ($option['location_list'] as $location) {

                            $selected = ($option['location']['selected_location'] == $location['id']) ? 'selected' : '';
                            echo '<option value="', $location['id'], '" ',$selected,'>';
                            echo $location['name'];
                            echo '</option>';

                        }
                        echo '</select>';
                    }
                    ?>


                </div>
            </td>
        </tr>
        <?php
    }

    public function order_class()
    {
        $option = $this->options['class'];
        $qbo_classes = $this->options['qbo_classes'];
        ?>
        <!--QBO Location-->
        <tr valign="top">
            <th>
                Class
                <?php
                help_link(array(
                    'title' => 'When enabled, the option you select will be the default setting to where your order will be assigned to.'
                ));
                ?>
            </th>

            <td class="forminp forminp-checkbox">
                <label>
                    <?php
                    if (empty($qbo_classes)) {
                        echo '<p class="color-red">No Classes data retrieved. You need to set it on QuickBooks. <a target="_blank" href="https://help.linksync.com/hc/en-us/articles/115000155504">See this guide on how to create at least one</a> </p>';
                    } else {
                        ?>
                        <input name="class_checkbox"
                               type="checkbox" <?php echo ($option['class_status'] == 'on') ? 'checked' : ''; ?> >
                        Enable
                        <?php
                        /*help_link(array(
                            'title' => 'Select this option if you\'d like customer data, such as name, email address and shipping and billing address, to be included when exporting orders to Vend.'
                        ));*/
                    }
                    ?>
                </label>
                <div class="sub-option">
                    <?php
                    if (!empty($qbo_classes)) {

                        $display = ($option['class_status'] != 'on') ? 'style="display:none;"' : '';
                        echo '<select name="qbo_class" ', $display, ' >';
                        foreach ($qbo_classes as $qbo_class) {
                            $selected = ($option['selected_class'] == $qbo_class['id']) ? 'selected': '';
                            ?>
                            <option value="<?php echo $qbo_class['id']; ?>" <?php echo $selected; ?> >
                                <?php echo $qbo_class['fullyQualifiedName'] ?>
                            </option>
                            <?php
                        }
                        echo '</select>';
                    }
                    ?>


                </div>
            </td>
        </tr>
        <?php
    }

    public function save_changes_botton()
    {
        ?>
        <p style="text-align: center;">
            <input class="button button-primary button-large save_changes"
                   type="submit" name="save_order_sync_setting"
                   value="Save Changes">
        </p>
        <?php
    }

    /**
     * Show Product syncing form and the users selected option
     * Default option is also set properly in this method
     */
    public function order_syncing_settings()
    {
        $order_form = LS_QBO_Order_Form::instance();
        $order_options = LS_QBO()->order_option();

        $product_options = LS_QBO()->product_option();

        $qbo_api = LS_QBO()->api();

        /**
         * Detect Save Changes was submitted
         */
        if (isset($_POST['form_items'])) {
            $order_form->update_order_syncing_settings($_POST['form_items']);
        }


        $users_order_option = $order_options->get_current_order_syncing_settings();
        $orderSyncType = $order_options->get_all_sync_type();
        $hide_on_disabled = ($users_order_option['sync_type'] == $orderSyncType[1]) ? 'style="display: none;"' : '';

        $users_order_option['deposit_accounts'] = LS_QBO()->options()->get_deposit_accounts();
        $users_order_option['location_list'] = LS_QBO()->options()->getQuickBooksLocationList();
        $users_order_option['qbo_classes'] = LS_QBO()->options()->getQuickBooksClasses();
        $users_order_option['qbo_payment_methods'] = LS_QBO()->options()->getQuickBooksPaymentMethods();//get_option('');
        $users_order_option['qbo_tax_classes'] = LS_QBO()->options()->getQuickBooksTaxClasses();

        //if Discount option and Shipping option is not enabled
        $qbo_info['qbo_info'] = LS_QBO()->options()->getQuickBooksInfo();

        $match_with = $product_options->match_product_with();
        if ('sku' == $match_with) {

            $duplicate_products = LS_Woo_Product::get_woo_duplicate_sku();
            $emptyProductSkus = LS_Woo_Product::get_woo_empty_sku();
            $products_data = array_merge($duplicate_products, $emptyProductSkus);
            if (count($products_data) > 0) {
                LS_QBO()->show_woo_duplicate_products($products_data, $emptyProductSkus, $duplicate_products);
                die();
            }
        }

        $product_syncing_form = LS_QBO_Product_Form::instance();
        $product_syncing_form->accounts_error_message();
        $product_syncing_form->require_syncing_error_message();

        echo '<form method="post" id="os_form_settings">';

        $order_form->set_users_options($users_order_option);

        $order_form->form_header();
        $order_form->sync_type();

        echo '<div id="ls-qbo-order-syncing-settings" ', $hide_on_disabled, '>';
        echo '<table class="form-table"><tbody>';

        $order_form->customer_export();
        $order_form->post_to_quickbooks_as();
        $order_form->order_status();
        $order_form->order_number_for_qbo();
        $order_form->tax_mapping();


        if (LS_QBO()->is_qbo_plus()) {
            $order_form->location();
            $order_form->order_class();
        }


        echo '</tbody></table>';
        echo '</div>';

        $order_form->save_changes_botton();
        echo '</form>';

        die();
    }

    /**
     * @param $user_options array of order syncing option
     */
    public function update_order_syncing_settings($user_options)
    {


        $order_option = LS_QBO()->order_option();
        if (!is_array($user_options)) {
            parse_str($user_options, $user_options);
        }

        if (isset($user_options['order_sync_type'])) {
            $order_option->update_sync_type($user_options['order_sync_type']);
        }

        if (isset($user_options['customer_export'])) {
            $order_option->update_customer_export($user_options['customer_export']);
        }

        if (isset($user_options['post_to_quickbooks_as'])) {
            $order_option->update_receipt_type($user_options['post_to_quickbooks_as']);
        }

        if (isset($user_options['deposit_account'])) {
            $order_option->update_deposit_account($user_options['deposit_account']);
        }

        $order_option->update_payment_method('mapped_payment_method');

        if (isset($user_options['payment_method_select'])) {
            $order_option->update_selected_mapped_payment_method($user_options['payment_method_select']);
        }

        if (isset($user_options['payment_method_select_invoice'])) {
            $order_option->update_selected_invoice($user_options['payment_method_select_invoice']);
        }

        if (!empty($user_options['order_status'])) {
            $order_option->update_order_status($user_options['order_status']);
        }

        if (isset($user_options['order_number'])) {
            $order_option->update_order_number($user_options['order_number']);
        }

        if (isset($user_options['tax_mapping'])) {
            $order_option->update_tax_mapping($user_options['tax_mapping']);
        }

        if (isset($user_options['location_checkbox'])) {
            $order_option->update_location_status($user_options['location_checkbox']);
        } else {
            $order_option->update_location_status('off');
        }

        if (isset($user_options['qbo_location'])) {
            $order_option->update_selected_location($user_options['qbo_location']);
        }

        if (isset($user_options['class_checkbox'])) {
            $order_option->update_class_status($user_options['class_checkbox']);
        } else {
            $order_option->update_class_status('off');
        }

        if (isset($user_options['qbo_class'])) {
            $order_option->update_selected_order_class($user_options['qbo_class']);
        }

        LS_QBO()->updateWebhookConnection();
        LS_QBO()->saveUserSettingsToLws();

    }

}
