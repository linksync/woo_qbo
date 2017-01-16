(function ($){

    function get_laid_info(){
        $.post(ajaxurl, {action : 'get_laid_info'});
    }
    get_laid_info();



    $(document).ready(function(){
        var all = $('*');
        var ls_wrapper              =   $('#ls-wrapper');


        //Empty sku ajax handler
        ls_wrapper.on('submit', '#frm-set-sku-automatically', function (e) {
            spinner = $('#ls-spinner');

            all.css({'cursor':'wait'});
            spinner.show();

            $.post(ajaxurl, {action: 'qbo_set_empty_sku_automatically'}, function (data) {
                window.location.href = window.location;
            });
            e.preventDefault();
        });

        //Duplicate sku ajax handler
        ls_wrapper.on('submit', '#frm-append-productid-to-duplicate-sku', function (e) {
            spinner = $('#ls-spinner2');

            all.css({'cursor':'wait'});
            spinner.show();

            $.post(ajaxurl, {action: 'qbo_append_product_id_to_duplicate_skus'}, function (data) {
                window.location.href = window.location;
            });
            e.preventDefault();
        });


    });


}(jQuery));