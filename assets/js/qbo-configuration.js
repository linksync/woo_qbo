(function( $ ){
    /**
     * Perpage constant value
     * @type {number}
     */
    var PER_PAGE = 50;

    var ajax_flag = null;

    $(document).ready(function(){

        var ls_wrapper              =   $('#ls-wrapper');

        ls_wrapper.on('click', '.btn-no', function(){
            var ls_pop_ups              =   $('.ls-pop-ups');
            ls_pop_ups.fadeOut();
        });

        ls_wrapper.on('click', '.manual_sync', function(){
            var sync_pop_up_msghtml     =   $('#sync_pop_up_msg');
            var two_buttons_cont        =   $('.two_way_pop_button');
            var sync_all_products_to    =   $('.sync_all_products_to_qbo');
            var sync_all_products_from  =   $('.sync_all_products_from_qbo');
            var ls_pop_ups              =   $('.ls-pop-ups');

            sync_pop_up_msghtml.html('Do you want to sync your product(s) from QuickBooks to Woocommerce?');
            two_buttons_cont.hide();
            sync_all_products_to.hide();
            sync_all_products_from.show();
            ls_pop_ups.fadeIn();
        });

        /**
         * Click event for syncing product comming from qbo to woocommerce
         */
        ls_wrapper.on('click', '.product_from_qbo', function(){
            product_from_qbo_to_woo();
        });

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

            btn_no.hide();
            popup_message.hide();
            sync_progress_cont.fadeIn();
            popup_btn_con.hide();
            sync_message.html("Importing Products to Woocomerce.");

            //check if page is undefined then we set it to one
            if(typeof page == 'undefined'){
                page = 1;
            }else if( page <= 0){
                //Make sure we always start to page 1
                page = 1;
            }

            get_product_since_last_update(page, function(res){
                var product_count = res.products.length;

                if(product_count > 0){
                    for( var i = 0; i < product_count; i++){

                        var product_number = i+1;
                        if(res.pagination.page > 1){
                            product_number += PER_PAGE;
                        }

                        var p_data = {
                            action              :   'import_to_woo',
                            page                :   res.pagination.page,
                            product_total_count :   res.pagination.results,
                            product             :   res.products[i],
                            product_number      :   product_number
                        }

                        post_data(p_data,function(p_res){
                            sync_progress.html(p_res);
                            console.log(p_res);
                        });
                    }

                }



                if(res.pagination.page <= res.pagination.pages){

                    page = parseInt(res.pagination.page) + 1;

                    if(page <= res.pagination.pages){
                        product_from_qbo_to_woo(page);
                    }

                }

            });

        }

        /**
         * Get products by page, each page contains a maximum of 50 products
         * @param page
         * @param callback
         */
        function get_product_since_last_update( page, callback ){
            var data = {
                action: 'since_last_sync',
                page: page
            };

            post_data(data, function(response){

                if(!$.isEmptyObject(response)){

                    if(!$.isEmptyObject(response.products)){
                        callback(response);
                    }
                }
            });

        }

        /**
         * When all the ajax request of importing products is done
         */
        ls_wrapper.ajaxStop(function() {
            if(ajax_flag == 0){
                var syncing_loader          =   $('#syncing_loader');
                var sync_progress           =   $('#sync_progress');
                var sync_message            =   $('#sync_message');
                var ls_pop_ups              =   $('.ls-pop-ups');
                var btn_no                  =   $('.btn-no');
                var popup_message           =   $('#popup_message');
                var sync_progress_cont      =   $('#sync_progress_container');
                var popup_btn_con           =   $('#pop_up_btn_container');

                syncing_loader.fadeOut('fast');
                sync_progress.fadeOut('fast');
                sync_message.css({"font-size":"15px", "margin-top": "15px"}).html("Product syncing successfully completed!!!");

                ls_pop_ups.delay(2500).fadeOut('slow',function(){
                    //Reset the popup
                    btn_no.show();
                    popup_message.show();
                    sync_progress_cont.hide();
                    popup_btn_con.show();
                    syncing_loader.show();
                    sync_progress.html("").show();
                    sync_message.css({"font-size":"1em", "margin-top": "0px"}).html("");
                });
            }
        });

        function show_configuration_tab(){

        }


        show_configuration_tab();


        /**
         * ajax post request
         * @param data
         * @param callback
         */
        function post_data(data, callback){
            /**
             * since wordpress 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
             * @reference https://codex.wordpress.org/AJAX_in_Plugins#Ajax_on_the_Administration_Side
             */
            $.post(ajaxurl, data, callback);
        }
    });

}( jQuery ));