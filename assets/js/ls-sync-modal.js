(function ($) {

    lsSyncModal = {
        init: function () {
            this.cacheDom();
            this.bindEvents();
            this.close();
        },

        bindEvents: function () {

            this.$mainContainer.on('click', '.btn-no', function () {
                lsSyncModal.close();
            });

            this.$fromQuickBooksButtonsContainer.hide();
            this.$toQuickBooksButtonsContainer.hide();

            this.$productFromQuickBooks.on('click', function () {
                lsSyncModal.hideSyncButtonsAndShowProgress(function () {
                    lsSyncModal.$progressBarLabel.html("Getting products from QuickBooks Online.");
                    lsSyncModal.syncProductsFromQuickBooks();
                });
            });

            this.$productToQuickBooks.on('click', function () {

                lsSyncModal.hideSyncButtonsAndShowProgress(function () {
                    lsSyncModal.$progressBarLabel.html("Exporting products To QuickBooks Online.");
                    lsSyncModal.syncProductToQuickBooks();
                });

            });

            lsSyncModal.$progressBar.progressbar({
                value: true,
                complete: function () {
                    lsSyncModal.$progressBarLabel.text("Sync Completed!");
                    lsSyncModal.$dasboardLink.removeClass('hide');
                    lsAjax.done_required_sync();
                }
            });
        },

        cacheDom: function () {
            this.$mainContainer = $('.ls-sync-modal');
            this.$modalContent = $('.ls-modal-content');
            this.$backDrop = $('.ls-modal-backdrop');
            this.$fromQuickBooksButtonsContainer = $('.sync_all_products_from_qbo');
            this.$toQuickBooksButtonsContainer = $('.sync_all_products_to_qbo');
            this.$modalMessage = $('.modal-message');
            this.$productFromQuickBooks = $('.product_from_qbo');
            this.$productToQuickBooks = $('.product_to_qbo');
            this.$syncProgressContainer = $('#sync_progress_container');
            this.$popUpButton = $('.pop_button');
            this.$progressBar = $("#progressbar");
            this.$progressBarLabel = $(".progress-label");
            this.$dasboardLink = $('.ls-dashboard-link');
            this.$twoWayPopUpButton = $('.two_way_pop_button');
            this.$closeContainer = $('.close-container');
        },

        open: function (option) {
            this.$backDrop.removeClass('close');
            this.$backDrop.addClass('open');
            this.$modalContent.fadeIn();
            this.$twoWayPopUpButton.hide();
            if ('from_qbo' == option) {
                this.$modalMessage.html('Your products from QuickBooks Online will be imported to WooCommerce. <br/> Do you wish to continue?');
                this.$fromQuickBooksButtonsContainer.show();
                this.$toQuickBooksButtonsContainer.hide();
            } else {
                this.$modalMessage.html('Your WooCommerce products will be exported to QuickBooks Online. <br/> Do you wish to continue?');
                this.$fromQuickBooksButtonsContainer.hide();
                this.$toQuickBooksButtonsContainer.show();
            }
        },

        close: function () {
            this.$modalContent.fadeOut(function () {
                lsSyncModal.$backDrop.removeClass('open');
                lsSyncModal.$backDrop.addClass('close');
            });
        },

        hideSyncButtonsAndShowProgress: function (callback) {
            lsSyncModal.$syncProgressContainer.show();
            lsSyncModal.$modalMessage.hide();
            lsSyncModal.$popUpButton.hide();
            if (typeof callback === "function") {
                callback();
            }

        },

        syncProductsFromQuickBooks: function (page) {
            ajax_flag = 0;

            //check if page is undefined then we set it to one
            if (typeof page == 'undefined') {
                page = 1;
            } else if (page <= 0) {
                //Make sure we always start to page 1
                page = 1;
            }
            var product_number = 0;

            lsSyncModal.$progressBarLabel.html("Getting products from QuickBooks Online.");
            lsSyncModal.$progressBar.progressbar("value", 1);

            lsAjax.get_product_by_page(page, function (res) {
                lsSyncModal.$progressBar.progressbar("value", 2);

                var product_count = res.products.length;
                var totalProductCount = res.pagination.results;
                console.log("total product result " + totalProductCount);
                console.log(res);

                if (product_count > 0) {
                    for (var i = 0; i < product_count; i++) {
                        product = res.products[i];
                        if (product.deleted_at == null) {
                            product_number = i + 1;
                        }

                        if (res.pagination.page > 1) {
                            product_number += 50;
                        }


                        var p_data = {
                            action: 'import_to_woo',
                            page: res.pagination.page,
                            product_total_count: res.pagination.results,
                            product: product,
                            product_number: product_number,
                            deleted_product: res.pagination.deleted_product
                        };

                        lsAjax.post(p_data, function (p_res) {

                            progressVal = lsSyncModal.$progressBar.progressbar("value");
                            if (progressVal < p_res.percentage) {
                                lsSyncModal.$progressBar.progressbar("value", p_res.percentage);
                                lsSyncModal.$progressBarLabel.html("Imported " + p_res.msg + " in WooCommerce (" + p_res.percentage + "%)");
                            }
                            console.log(p_res);
                            console.log("progress => " + p_res.percentage);
                        });
                    }

                } else if (product_count <= 1) {
                    lsSyncModal.$progressBar.progressbar("value", 100);
                    lsSyncModal.$progressBarLabel.html("No products were imported to WooCommerce");
                }

                if (res.pagination.page <= res.pagination.pages) {

                    page = parseInt(res.pagination.page) + 1;

                    if (page <= res.pagination.pages) {
                        lsSyncModal.syncProductsFromQuickBooks(page);
                    }

                }
            });
        },

        syncProductToQuickBooks: function () {
            var product_number = 0;
            lsSyncModal.$progressBar.progressbar("value", 1);
            lsAjax.post({action: 'woo_get_products'}, function (woo_products) {
                lsSyncModal.$progressBar.progressbar("value", 2);
                console.log(woo_products);

                if (!$.isEmptyObject(woo_products)) {
                    var product_total_count = woo_products.length;

                    if (product_total_count > 0) {
                        for (var i = 0; i < product_total_count; i++) {

                            product_number = i + 1;
                            var data = {
                                action: 'import_to_qbo',
                                p_id: woo_products[i].ID,
                                product_number: product_number,
                                total_count: product_total_count,
                            };
                            lsAjax.post(data, function (p_res) {
                                progressVal = lsSyncModal.$progressBar.progressbar("value");
                                if (progressVal < p_res.percentage) {
                                    lsSyncModal.$progressBar.progressbar("value", p_res.percentage);
                                    lsSyncModal.$progressBarLabel.html("Exported " + p_res.msg + " to QuickBooks Online (" + p_res.percentage + "%)");
                                }
                                console.log(response);

                            });
                        }
                    } else {
                        lsSyncModal.$progressBar.progressbar("value", 100);
                        lsSyncModal.$progressBarLabel.html("No products from WooCommerce to export in QuickBooks Online");
                    }
                } else {
                    lsSyncModal.$progressBar.progressbar("value", 100);
                    lsSyncModal.$progressBarLabel.html("No products from WooCommerce to export in QuickBooks Online");
                }
            });
        },

        showSyncModal: function () {
            $("#progressbar").progressbar({
                value: true,
                complete: function () {
                    $(".progress-label").text("Sync Completed!");
                   lsSyncModal.hideSyncModal();
                }
            });
            $("#progressbar").progressbar("value", 0);
            $('.ls-modal-backdrop').removeClass('close').addClass('open');
        },

        hideSyncModal: function (delay) {
            var syncing_loader = $('#syncing_loader');
            var ls_pop_ups = $('.ls-pop-ups');
            var btn_no = $('.btn-no');
            var popup_message = $('#popup_message');
            var sync_progress_cont = $('#sync_progress_container');
            var popup_btn_con = $('#pop_up_btn_container');

            ls_pop_ups.delay(3100).fadeOut('slow', function () {
                //Reset the popup
                btn_no.show();
                popup_message.show();
                sync_progress_cont.hide();
                popup_btn_con.show();
                syncing_loader.show();
                $('.ls-modal-backdrop').removeClass('open').addClass('close');
            });
        }

    };
}(jQuery));