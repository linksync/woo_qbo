(function( $ ){

    /**
     * Perpage constant value
     * @type {number}
     */
    var PER_PAGE = 50;

    var ajax_flag = null;

    $(document).ready(function(){

        /**
         * Array of possible sync type
         * @type {string[]}
         */
        var sync_types = [ 'two_way', 'qbo_to_woo', 'disabled' ];

        var ls_wrapper              =   $('#ls-wrapper');
        var main_view_container     =   $( '#ls-main-views-cont' );

        ls_wrapper.on('change', 'input[name*="checkbox_toogle_description"]:checkbox', function () {

            $tblDescriptionBody = $('#tbl_description_error_body');
            $toggleIndicator = $('#ls-toggle-indicator');

            if( $(this).prop('checked') == false ){
                $showDescriptionError = 'false';
                $toggleIndicator.addClass('toggle-indicator-down');
                $toggleIndicator.removeClass('toggle-indicator-up');

            }else{
                $showDescriptionError = 'true';
                $toggleIndicator.addClass('toggle-indicator-up');
                $toggleIndicator.removeClass('toggle-indicator-down');

            }

            $tblDescriptionBody.toggle()
            var $data = {
                'action'            :   'qbo_description_toggle',
                'show_description'  :   $showDescriptionError
            }
            console.log($data);
            lsAjax.post($data, function (response) {
                console.log(response);
            })
        });

        /**
         * On saving product syncing settings
         */
        ls_wrapper.on('submit', '#ps_form_settings', function(e){
            ls_wrapper.addClass('ls-loading');
            main_view_container.empty();
            var form = $(this);
            var form_items = form.serialize();
            var $data  ={
                'action'      : 'show_ps_view',
                'form_items'  : form_items
            };
            ajax_flag = 1;

            lsAjax.post($data, function (html_response) {

                main_view_container.fadeIn('slow').append(html_response);
                ls_wrapper.removeClass('ls-loading');
                ajax_flag = 1;
                var syncType = $("input[name='product_sync_type']:checked").val();
                if ('disabled' != syncType) {
                    var option = {
                        sync_direction: syncType,
                        current_screen: 'settings'
                    };
                    lsSyncModal.open(option);
                }

            });

            e.preventDefault();
        });

        ls_wrapper.on('change', 'input[name*="product_sync_type"]:radio',function(){
            var sync_type               =   $(this).val();

            var form_container          =   $( '#ls-qbo-product-syncing-settings' );
            var btn_products_from_qbo   =   $( '#btn_sync_products_from_qbo' );
            var btn_products_to_qbo     =   $( '#btn_sync_products_to_qbo' );
            var btn_container           =   $( '#syncing_bottons' );
            var tbl_to_qbo_tax_mapping  =   $( '#ls-tax-map-to-qbo' );

            if( sync_type == sync_types[0] ){

                form_container.slideDown(500);
                btn_products_from_qbo.show();
                btn_products_to_qbo.fadeIn();
                tbl_to_qbo_tax_mapping.show();
                btn_container.fadeIn();

            }else if( sync_type == sync_types[1] ){

                form_container.slideDown(500);
                btn_products_to_qbo.fadeOut();
                btn_products_from_qbo.show();
                //tbl_to_qbo_tax_mapping.hide();
                btn_container.fadeIn();

            }else if( sync_type == sync_types[2] ){

                btn_container.fadeOut();
                form_container.slideUp();
            }else {

                btn_container.fadeOut();
                form_container.slideUp();
            }
        });

        ls_wrapper.on('change', 'input[name*="price"]:checkbox', function(){

            var price_options_cont      =   $( '#price_options_container' );
            if( $(this).prop('checked') == false ){
                price_options_cont.fadeOut();
            }else{
                price_options_cont.fadeIn();
            }

        });

        ls_wrapper.on('change', 'input[name*="use_woo_tax"]:checkbox' ,function(){

            var qbo_tax_option_cont = $( '#ls-qbo-tax-options' );
            if( $(this).prop( 'checked' ) == false ){
                qbo_tax_option_cont.fadeIn();
            }else {
                qbo_tax_option_cont.fadeOut();
            }

        });

        ls_wrapper.on('change', 'input[name*="quantity_option"]:checkbox', function(){

            var quantity_options_cont   =   $( '#quantity_options_container' );
            if( $(this).prop( 'checked' ) == false ){
                quantity_options_cont.fadeOut();
            }else {
                quantity_options_cont.fadeIn();
            }
        });

        ls_wrapper.on('click', '.btn-no', function(){
            var ls_pop_ups              =   $('.ls-pop-ups');
            ls_pop_ups.fadeOut();
        });



        /**
         * Click event for syncing product comming from qbo to woocommerce
         */
        ls_wrapper.on('click', '.product_from_qbo', function(){
            // lsSyncModal.showSyncModal();
            // product_from_qbo_to_woo();
            // done_required_sync();
        });

        /**
         * Click event for syncing prodcut from woocommerce to qbo
         */
        ls_wrapper.on('click', '.product_to_qbo', function(){
            // lsSyncModal.showSyncModal();
            // product_from_woo_to_qbo();
            // done_required_sync();
        });

        ls_wrapper.on('click', '#btn_sync_products_from_qbo', function(){
            lsSyncModal.openQboToWooModal();
        });

        ls_wrapper.on('click', '#btn_sync_products_to_qbo', function(){
            lsSyncModal.openWooToQboModal();
            // var sync_pop_up_msghtml     =   $('#sync_pop_up_msg');
            // var two_buttons_cont        =   $('.two_way_pop_button');
            // var sync_all_products_to    =   $('.sync_all_products_to_qbo');
            // var sync_all_products_from  =   $('.sync_all_products_from_qbo');
            // var ls_pop_ups              =   $('.ls-pop-ups');
            //
            // sync_pop_up_msghtml.html('Your WooCommerce products will be exported to QuickBooks Online. <br/> Do you wish to continue?');
            // two_buttons_cont.hide();
            // sync_all_products_to.show();
            // sync_all_products_from.hide();
            // ls_pop_ups.fadeIn();
        });

        function done_required_sync() {
            var data = {
                action : 'qbo_done_syncing_required'
            };
            lsAjax.post(data, function (data) {
                $('.require-resync').hide();
            });
        }

        /**
         * @param page Number
         */
        function product_from_qbo_to_woo( page ){
            ajax_flag = 0;

            var btn_no                  =   $('.btn-no');
            var popup_message           =   $('#popup_message');
            var sync_progress_cont      =   $('#sync_progress_container');
            var popup_btn_con           =   $('#pop_up_btn_container');
            var sync_message            =   $('#sync_message');
            var sync_progress           =   $('#sync_progress');
            var bigErrorMsg             =   $('.big-error-message');

            btn_no.hide();
            popup_message.hide();
            sync_progress_cont.fadeIn();
            popup_btn_con.hide();
            $(".progress-label").html("Getting products from QuickBooks Online.");
            $("#progressbar").progressbar("value", 1);

            //check if page is undefined then we set it to one
            if(typeof page == 'undefined'){
                page = 1;
            }else if( page <= 0){
                //Make sure we always start to page 1
                page = 1;
            }
            var product_number = 0;

            lsAjax.get_product_by_page(page, function(res){
                var product_count = res.products.length;

                if(product_count > 0){

                    for( var i = 0; i < product_count; i++){


                        product = res.products[i];
                        if( product.deleted_at == null ){
                            product_number = i+1;
                        }


                        if(res.pagination.page > 1){
                            product_number += PER_PAGE;
                        }

                        var p_data = {
                            action              :   'import_to_woo',
                            page                :   res.pagination.page,
                            product_total_count :   res.pagination.results,
                            product             :   product,
                            product_number      :   product_number,
                            deleted_product     :   res.pagination.deleted_product
                        };

                        lsAjax.post(p_data,function(p_res){
                            console.log(p_res);
                            progressVal = $("#progressbar").progressbar( "value" );
                            if(progressVal < p_res.percentage){
                                $("#progressbar").progressbar("value", p_res.percentage);
                                $(".progress-label").html("Importing " + p_res.msg + " to WooCommerce (" + p_res.percentage + "%)");
                            }

                            console.log(p_res);
                            console.log("progress => " + p_res.percentage);

                        });
                    }

                } else if (product_count <= 1){
                    $("#progressbar").progressbar("value", 100);
                    $(".progress-label").html("No products were imported to WooCommerce");
                }



                if(res.pagination.page <= res.pagination.pages){

                    page = parseInt(res.pagination.page) + 1;

                    if(page <= res.pagination.pages){
                        product_from_qbo_to_woo(page);
                    }

                }

            });

        }


        function product_from_woo_to_qbo(){
            console.log('woo products to qbo');
            ajax_flag = 0;
            var btn_no                  =   $('.btn-no');
            var popup_message           =   $('#popup_message');
            var sync_progress_cont      =   $('#sync_progress_container');
            var popup_btn_con           =   $('#pop_up_btn_container');
            var sync_message            =   $('#sync_message');
            var sync_progress           =   $('#sync_progress');

            btn_no.hide();
            popup_message.hide();
            sync_progress_cont.fadeIn();
            popup_btn_con.hide();
            sync_message.html("Starting...").fadeIn().delay(20000).fadeIn().html("Exporting Product to QuickBooks");
            sync_progress.html("");
            $(".progress-label").html("Getting WooCommerce products to be exported in QuickBooks Online.");
            $("#progressbar").progressbar("value", 1);

            lsAjax.post({action: 'woo_get_products'}, function(woo_products){
                $("#progressbar").progressbar("value", 2);
                console.log(woo_products);
                if(!$.isEmptyObject(woo_products)){
                    var product_total_count = woo_products.length;

                    if(product_total_count > 0){
                        for( var i = 0; i < product_total_count; i++){

                            product_number = i+1;
                            var data = {
                                action              :   'import_to_qbo',
                                p_id                :   woo_products[i].ID,
                                product_number      :   product_number,
                                total_count         :   product_total_count,
                            };
                            lsAjax.post(data, function(p_res){
                                console.log(p_res);
                                sync_progress.html(p_res);
                                progressVal = $("#progressbar").progressbar( "value" );
                                if(progressVal < p_res.percentage){
                                    $("#progressbar").progressbar("value", p_res.percentage);
                                    $(".progress-label").html("Exported " + p_res.msg + " to QuickBooks Online (" + p_res.percentage + "%)");
                                }
                            });
                        }
                    }
                } else {
                    $("#progressbar").progressbar("value", 100);
                    $(".progress-label").html("No products from WooCommerce to export in QuickBooks Online");
                }
            });
        }


        /**
         * When all the ajax request of importing products is done
         */
        ls_wrapper.ajaxStop(function() {
            if(ajax_flag == 0){
                var syncing_loader          =   $('#syncing_loader');
                var ls_pop_ups              =   $('.ls-pop-ups');
                var btn_no                  =   $('.btn-no');
                var popup_message           =   $('#popup_message');
                var sync_progress_cont      =   $('#sync_progress_container');
                var popup_btn_con           =   $('#pop_up_btn_container');


                // ls_pop_ups.delay(3000).fadeOut('slow',function(){
                //     //Reset the popup
                //     btn_no.show();
                //     popup_message.show();
                //     sync_progress_cont.hide();
                //     popup_btn_con.show();
                //     syncing_loader.show();
                // });
            }
        });


        


        function show_product_syncing_settings(){

            ajax_flag = 1;

            ls_wrapper.addClass('ls-loading');
            main_view_container.empty();

            lsAjax.post({action: 'save_needed_ps_data_from_qbo'}, function(response){

                lsAjax.post({action: 'show_ps_view'},function(html_response){
                    main_view_container.append(html_response).fadeIn(function(){
                        ls_wrapper.removeClass('ls-loading');
                    });

                    ajax_flag = 1;
                });
                console.log(response);
                ajax_flag = 1;
            });
        }

        //show view
        show_product_syncing_settings();

        lsSyncModal.init();

    });


}( jQuery ));
