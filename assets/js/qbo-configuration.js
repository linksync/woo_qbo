(function ($) {
    /**
     * Perpage constant value
     * @type {number}
     */
    var PER_PAGE = 50;

    var ajax_flag = null;

    $(document).ready(function () {

        var ls_wrapper = $('#ls-wrapper');

        ls_wrapper.on('click', '.btn-no', function () {
            var ls_pop_ups = $('.ls-pop-ups');
            ls_pop_ups.fadeOut();
        });

        ls_wrapper.on('click', '.manual_sync', function () {
            var sync_pop_up_msghtml = $('#sync_pop_up_msg');
            var two_buttons_cont = $('.two_way_pop_button');
            var sync_all_products_to = $('.sync_all_products_to_qbo');
            var sync_all_products_from = $('.sync_all_products_from_qbo');
            var ls_pop_ups = $('.ls-pop-ups');

            sync_pop_up_msghtml.html('Your products from QuickBooks Online will be imported to WooCommerce.<br/>Do you wish to continue?');
            two_buttons_cont.hide();
            sync_all_products_to.hide();
            sync_all_products_from.show();
            ls_pop_ups.fadeIn();
        });

        /**
         * Click event for syncing product comming from qbo to woocommerce
         */
        ls_wrapper.on('click', '.product_from_qbo', function () {
            lsSyncModal.showSyncModal();
            product_from_qbo_to_woo();
        });


        /**
         * @param page Number
         */
        function product_from_qbo_to_woo(page) {
            ajax_flag = 0;

            var btn_no = $('.btn-no');
            var popup_message = $('#popup_message');
            var sync_progress_cont = $('#sync_progress_container');
            var popup_btn_con = $('#pop_up_btn_container');
            var sync_message = $('#sync_message');
            var sync_progress = $('#sync_progress');

            btn_no.hide();
            popup_message.hide();
            sync_progress_cont.fadeIn();
            popup_btn_con.hide();
            sync_message.html("Importing Products to Woocomerce.");
            $(".progress-label").html("Getting QuickBooks products since last sync.");

            //check if page is undefined then we set it to one
            if (typeof page == 'undefined') {
                page = 1;
            } else if (page <= 0) {
                //Make sure we always start to page 1
                page = 1;
            }
            $("#progressbar").progressbar("value", 1);
            lsAjax.get_product_since_last_update(page, function (res) {
                $("#progressbar").progressbar("value", 2);
                console.log(res);
                var product_count = res.products.length;

                if (product_count > 0) {
                    for (var i = 0; i < product_count; i++) {

                        var product_number = i + 1;
                        if (res.pagination.page > 1) {
                            product_number += PER_PAGE;
                        }

                        var p_data = {
                            action: 'import_to_woo',
                            page: res.pagination.page,
                            product_total_count: res.pagination.results,
                            product: res.products[i],
                            product_number: product_number
                        }

                        lsAjax.post(p_data, function (p_res) {
                            progressVal = $("#progressbar").progressbar( "value" );
                            if(progressVal < p_res.percentage){
                                $("#progressbar").progressbar("value", p_res.percentage);
                                $(".progress-label").html("Importing " + p_res.msg + " to WooCommerce (" + p_res.percentage + "%)");
                            }
                            console.log(p_res);
                        });
                    }

                } else if (product_count <= 1) {
                    $("#progressbar").progressbar("value", 100);
                    $(".progress-label").html("No product updates from QuickBooks since last sync.");
                }


                if (res.pagination.page <= res.pagination.pages) {

                    page = parseInt(res.pagination.page) + 1;

                    if (page <= res.pagination.pages) {
                        product_from_qbo_to_woo(page);
                    }

                }

            });

        }

    });

}(jQuery));