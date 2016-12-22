(function( $ ){

    /**
     * Array of possible sync type
     * @type {string[]}
     */
    var sync_types          = [ 'woo_to_qbo', 'disabled' ];
    var sales_receipt_types = [ 'sales_receipt', 'sales_invoice' ];
    var payment_methods     = [ 'woo_order_payment_method', 'mapped_payment_method' ];

    $(document).ready(function(){

        var ls_wrapper                  =   $('#ls-wrapper');
        var main_view_container         =   $( '#ls-main-views-cont' );
        var form_container              =   $( '#ls-qbo-order-syncing-settings' );

        ls_wrapper.on('submit', '#os_form_settings', function(e){

            ls_wrapper.addClass('ls-loading');
            main_view_container.empty();
            var $data  ={
                'action'      : 'show_or_view',
                'form_items'  : $(this).serialize()
            };


            post_data($data ,function(html_response){
                main_view_container.fadeIn('slow').append(html_response);
                ls_wrapper.removeClass('ls-loading');
            });

            e.preventDefault();
        });

        ls_wrapper.on('change', 'input[name*="order_sync_type"]', function(){

            var form_container              =   $( '#ls-qbo-order-syncing-settings' );
            var sync_type = $(this).val();

            if( sync_type == sync_types[0] ){

                form_container.slideDown(500);

            }else if( sync_type == sync_types[1] ){

                form_container.slideUp();
            }else {

                form_container.slideUp();
            }

        });


        ls_wrapper.on('change', 'input[name*="post_to_quickbooks_as"]', function(){
            var sales_receipt_type = $(this).val();
            var sales_receipt_cont          =   $( '#sales_receipt_container' );
            var sales_invoice_cont          =   $( '#sales_invoice_container' );

            if( sales_receipt_type == sales_receipt_types[0] ){
                sales_receipt_cont.fadeIn();
                sales_invoice_cont.hide();
            }else {
                sales_receipt_cont.hide();
                sales_invoice_cont.fadeIn();
            }
        });

        ls_wrapper.on('change', 'input[name*="payment_method"]', function(){

            var method = $(this).val();
            var mapped_payment_method_cont  =   $( '#mapped_payment_method_container' );

            if( method == payment_methods[1]){
                mapped_payment_method_cont.fadeIn();
            }else {
                mapped_payment_method_cont.fadeOut();
            }
        });



        ls_wrapper.on('change', 'input[name*="location_checkbox"]', function(){

            var locations_select    =   $( 'select[name*="qbo_location"]' );
            if( $(this).prop('checked') == false ){
                locations_select.fadeOut();
            }else{
                locations_select.fadeIn();
            }

        });

        ls_wrapper.on('change', 'input[name*="class_checkbox"]', function(){

            var class_select    =   $( 'select[name*="qbo_class"]' );
            if( $(this).prop('checked') == false ){
                class_select.fadeOut();
            }else{
                class_select.fadeIn();
            }

        });

        function show_order_syncing_settings(){
            ls_wrapper.addClass('ls-loading');
            main_view_container.empty();

            post_data({action: 'save_needed_os_data_from_qbo'}, function(response){

                post_data({action: 'show_or_view'},function(html_response){
                    main_view_container.append(html_response).fadeIn(function(){
                        ls_wrapper.removeClass('ls-loading');
                    });
                });
            });
        }

        show_order_syncing_settings();

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