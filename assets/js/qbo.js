(function ($) {

    function get_laid_info() {
        $.post(ajaxurl, {action: 'get_laid_info'});
    }

    get_laid_info();


    $(document).ready(function () {
        var all = $('*');
        var ls_wrapper = $('#ls-wrapper');

        ls_wrapper.on('submit', '#frm-duplicate-skus', function (e) {

            btnValue = $(document.activeElement).val();


            if ('Replace All Empty SKU' == btnValue) {
                //Empty sku ajax handler

                spinner = $('#ls-spinner');
                all.css({'cursor': 'wait'});
                spinner.show();
                $.post(ajaxurl, {action: 'qbo_set_empty_sku_automatically'}, function (data) {
                    window.location.href = window.location;
                });

            } else if ('Make SKU Unique' == btnValue) {
                //Duplicate sku ajax handler

                spinner = $('#ls-spinner2');
                all.css({'cursor': 'wait'});
                spinner.show();

                $.post(ajaxurl, {action: 'qbo_append_product_id_to_duplicate_skus'}, function (data) {
                    window.location.href = window.location;
                });


            } else if ('Apply' == btnValue) {

                var searchIDs = $("input:checkbox:checked").map(function () {
                    return $(this).val();
                }).get();

                var action = $('#bulk-action-selector-top').val();


                string_action = 'qbo_append_product_id_to_duplicate_skus';
                if('replace_empty_sku' == action){
                    string_action = 'qbo_set_empty_sku_automatically';
                } else if('make_sku_unique' == action){
                    string_action = 'qbo_append_product_id_to_duplicate_skus';
                } else if('delete_permanently' == action){
                    string_action = 'qbo_delete_products_permanently';
                }
                data = {
                    action: string_action,
                    product_ids: searchIDs
                };

                $.post(ajaxurl, data, function (data) {
                    console.log(data);
                    window.location.href = window.location;
                });


            }

            e.preventDefault();

        });

    });


}(jQuery));