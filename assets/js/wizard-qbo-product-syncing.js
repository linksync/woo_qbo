(function ($) {

    $(document).ready(function () {

        lsSyncModal.init();
        lsSyncButtons.init();

        lsSyncButtons.showProductFromQuicBooksSyncPopUp(function () {
            lsSyncModal.get_syncing_options(function (response) {
                //console.log(response);
                if ('two_way' == response.product.sync_type || 'qbo_to_woo' == response.product.sync_type) {
                    var option = {
                        sync_direction: 'from_qbo',
                        current_screen: 'wizard'
                    };
                    lsSyncModal.open(option);
                    response.product.sync_type = 'qbo_to_woo';
                    lsSyncModal.syncingModalHtmlBuilder(response);
                } else {
                    window.location.reload();
                }
            });


        });

        lsSyncButtons.showProductToQuickBooksSyncPopUp(function () {

            lsSyncModal.get_syncing_options(function (response) {

                console.log(response);
                if ('two_way' == response.product.sync_type) {

                    var option = {
                        sync_direction: 'qbo_to_woo',
                        current_screen: 'wizard'
                    };
                    lsSyncModal.open(option);
                    response.product.sync_type = 'woo_to_qbo';
                    lsSyncModal.syncingModalHtmlBuilder(response);

                } else {
                    window.location.reload();
                }

            });

        });

    });
}(jQuery));