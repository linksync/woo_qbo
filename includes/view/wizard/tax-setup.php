<?php
$tax_classes = LS_QBO()->options()->getQuickBooksTaxClasses();
$apikey_info = LS_QBO()->laid()->getCurrentLaidInfo();

if (empty($tax_classes)) {

    /**
     * Get tax data from quickbooks to be used
     */
    $tax_classes = LS_Woo_Tax::getQuickBookTaxDataToBeUsed($apikey_info);
    LS_QBO()->options()->updateQuickBooksTaxClasses($tax_classes);

}

$woocommerce_tax_rates = array();
$tax_classes = LS_Woo_Tax::get_tax_classes();
foreach ($tax_classes as $tax_key => $tax_class) {
    $class_tax_rates = LS_Woo_Tax::get_tax_rates($tax_key);
}


?>

<h1>Tax <?php echo (empty($tax_classes) ? 'Set up': 'Import')?></h1>
<hr>

<form class="wizard-form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <input type="hidden" name="process" value="wizard"/>
    <input type="hidden" name="action" value="<?php echo (empty($tax_classes) ? 'taxsetup': 'taximport')?>"/>
    <input type="hidden" name="nextpage" value="3"/>

    <?php
    if (empty($tax_classes)) {
        $taxError = LS_QBO()->show_configure_tax_error();
        echo '<p class="error-message">' . $taxError . '</p>';
    } else {
        ?>
        <p>
            Do you want to import your Woocommerce tax rates?
        </p>
        <p class="form-holder">
            <a class="wizard-form-button" id="runImport" href="#">Run Import</a>
        </p>

        <p class="form-holder">
            <a class="wizard-form-button" href="<?php echo LS_QBO_Menu::get_wizard_admin_menu_url('step=3'); ?>">Skip</a>
        </p>

        <div class="ls-wizard-modal">
            <div class="ls-sync-modal">
                <div id="pop_up" class="ls-pop-ups ls-modal-content" style="display: none;">

                    <div id="sync_progress_container" style="display: none;">

                        <center>
                            <br>
                            <div id="syncing_loader">
                                <p style="font-weight: bold;">Please do not close or refresh the browser while importing Tax.</p>
                            </div>
                        </center>
                        <center>
                            <div>
                                <div id="progressbar" class="ui-progressbar ui-widget ui-widget-content ui-corner-all" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="ui-progressbar-value ui-widget-header ui-corner-left" style="display: none; width: 0%;"></div></div>
                                <div class="progress-label">Loading...</div>
                            </div>
                        </center>
                        <br>

                    </div>

                    <div id="pop_up_btn_container">


                    </div>
                </div>
                <div class="ls-modal-backdrop close"></div>
            </div>

        </div>
        <?php
    }
    ?>


    <div class="clearfix"></div>
</form>

<script>
    (function ($) {
        $(document).ready(function () {
            $btnRunImport = $('#runImport');

            $btnRunImport.on('click', function () {
                $('#sync_progress_container').show();
                $('.ls-modal-content').fadeIn();
                $('.ls-modal-backdrop').removeClass('close');

                lsAjax.post({
                    action : 'qbo_save_qbo_tax_agencies_to_wpdb'
                }).done(function (response) {

                    lsAjax.post({
                        action : 'qbo_save_woo_taxrates_to_qbo'
                    }).done(function (linksyncResponseTaxratesSaving) {
                        console.log(linksyncResponseTaxratesSaving);
                    });
                });
            });
        });
    }(jQuery));
</script>
