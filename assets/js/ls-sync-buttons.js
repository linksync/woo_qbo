(function( $ ){

    lsSyncButtons =  {

        init: function() {
            this.cacheDom();
        },

        cacheDom: function () {
            this.$btnProductFromQuicBooks = $('#btn_sync_products_from_qbo');
            this.$btnProductToQuickBooks = $('#btn_sync_products_to_qbo');
        },

        showProductFromQuicBooksSyncPopUp: function (callback) {
            if (typeof callback === "function") {
                this.$btnProductFromQuicBooks.on('click', callback);
            }
        },

        showProductToQuickBooksSyncPopUp: function (callback) {
            if (typeof callback === "function") {
                this.$btnProductToQuickBooks.on('click', callback);
            }
        }
    };
}(jQuery));