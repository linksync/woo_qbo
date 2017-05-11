(function( $ ){

    $(document).ready(function(){

        lsSyncModal.init();
        lsSyncButtons.init();

        lsSyncButtons.showProductFromQuicBooksSyncPopUp(function () {
            lsSyncModal.$closeContainer.hide();
            lsSyncModal.open('from_qbo');
        });

        lsSyncButtons.showProductToQuickBooksSyncPopUp(function () {
            lsSyncModal.$closeContainer.hide();
            lsSyncModal.open('to_qbo');
        });

    });
}( jQuery ));