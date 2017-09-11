(function ($) {

    lsSyncModal = {
        options: '',
        current_screen: 'settings',
        SYNC_LIMIT: 0,
        init: function () {
            this.cacheDom();
            this.bindEvents();
            this.close();
        },

        setOptions: function (options) {
            this.options = options;
        },

        on: function (event, childOrCallback, callback) {
            if (typeof callback == 'undefined') {
                this.$settingsWrapper.on(event, childOrCallback);
            } else {
                this.$settingsWrapper.on(event, childOrCallback, callback);
            }
        },

        click: function (child, callback) {
            this.on('click', child, callback);
        },

        bindEvents: function () {

            this.click('.btn-no', function () {
                lsSyncModal.close();
            });

            this.click('.product_from_qbo', function () {

                lsSyncModal.cacheDom();
                lsSyncModal.hideSyncButtonsAndShowProgress(function () {
                    lsSyncModal.$progressBarLabel.html("Getting products from QuickBooks Online.");
                    lsSyncModal.setOptions({
                        action: 'qbo_get_products',
                        no_products_to_import_woo: 'No products were imported to WooCommerce'
                    });
                    lsSyncModal.syncProductsFromQuickBooks();
                });

            });

            this.click('.product_from_qbo_since_last_sync', function () {

            });

            this.click('.product_to_qbo', function () {

                lsSyncModal.cacheDom();
                lsSyncModal.hideSyncButtonsAndShowProgress(function () {
                    lsSyncModal.$progressBarLabel.html("Exporting products To QuickBooks Online.");
                    lsSyncModal.syncProductsToQuickBooks();
                });

            });


            lsSyncModal.initializeSyncProgress();


        },

        stopSyncing: function (product_sync_response) {
            var syncing_response = product_sync_response.response_product_to_qbo.response;

            if (
                syncing_response &&
                typeof(syncing_response) == "object" &&
                typeof syncing_response.errorCode != 'undefined' &&
                typeof syncing_response.type != 'undefined' &&
                'C400' == syncing_response.type
            ) {
                console.log('Stop syncing now!');
                console.log(syncing_response);

                return true;
            }

            return false;
        },
        showCappingHtmlError: function (htmlErrorMessage) {
            lsSyncModal.$closeContainer.show();
            $('.modal_syncing_error, .ls-trial-message').remove();
            lsSyncModal.$syncProgressContainer.before('<center class="modal_syncing_error"><p style="color:red;">' + htmlErrorMessage + '</p></center>');
            lsSyncModal.$syncProgressContainer.hide();
            var re = /<br\/>If/g;
            htmlErrorMessage = htmlErrorMessage.replace(re, 'If');
            $('.product-capping-error').hide();
            lsSyncModal.$logoContainer.before('<div class="notice notice-error product-capping-error"><p>' + htmlErrorMessage + '</p></div>');

        },

        openQboToWooModal: function () {
            lsSyncModal.cacheDom();
            var options = {
                sync_direction: 'qbo_to_woo'
            };
            lsSyncModal.open(options);

        },

        openWooToQboModal: function () {
            lsSyncModal.cacheDom();
            var options = {
                sync_direction: 'woo_to_qbo'
            };
            lsSyncModal.open(options);

        },

        initializeSyncProgress: function () {
            lsSyncModal.cacheDom();
            lsSyncModal.$progressBar.progressbar({
                value: true,
                complete: function () {
                    lsSyncModal.$progressBarLabel.text("Sync Completed!");
                    lsSyncModal.$dasboardLink.removeClass('hide');
                    lsSyncModal.done_required_sync();
                }
            });

            lsSyncModal.$progressBar.progressbar("value", 0);
        },

        cacheDom: function () {
            this.$settingsWrapper = $('#ls-wrapper');
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

            this.$tabMenu = $('.ls-tab-menu');
            this.$logoContainer = $('.ls-logo-container');
        },

        open: function (option) {

            lsSyncModal.cacheDom();
            lsSyncModal.current_screen = option.current_screen;
            lsSyncModal.initializeSyncProgress();
            lsSyncModal.$backDrop.removeClass('close');
            lsSyncModal.$backDrop.addClass('open');
            lsSyncModal.$popUpButton.hide();
            lsSyncModal.SYNC_LIMIT = 0;
            console.log(option);
            if ('qbo_to_woo' == option.sync_direction) {

                lsSyncModal.$modalMessage.html('Your products from QuickBooks Online will be imported to WooCommerce. <br/> Do you wish to continue?');
                lsSyncModal.$fromQuickBooksButtonsContainer.show();

            } else if ('two_way' == option.sync_direction) {

                lsSyncModal.$twoWayPopUpButton.show();

            } else if ('woo_to_qbo' == option.sync_direction) {

                lsSyncModal.$modalMessage.html('Your WooCommerce products will be exported to QuickBooks Online. <br/> Do you wish to continue?');
                lsSyncModal.$toQuickBooksButtonsContainer.show();

            }

            lsSyncModal.$modalContent.fadeIn();
            lsSyncModal.$syncProgressContainer.hide();
            lsSyncModal.$modalMessage.show();

        },

        syncCompleted: function (delay) {
            if (typeof delay == 'undefined') {
                delay = 4000;
            }
            lsSyncModal.cacheDom();
            lsSyncModal.close();
            lsSyncModal.$tabMenu.before('<div class="notice notice-success  sync-completed" > <p>Sync Completed!</p> </div>');
            lsSyncModal.done_required_sync();
            setTimeout(function () {
                $('.sync-completed').delay(delay).fadeOut('fast');
            }, delay);

        },

        close: function () {
            lsSyncModal.cacheDom();
            lsSyncModal.SYNC_LIMIT = 0;
            this.$modalContent.fadeOut(function () {
                lsSyncModal.$backDrop.removeClass('open');
                lsSyncModal.$backDrop.addClass('close');
                lsSyncModal.$closeContainer.show();
                $('.modal_syncing_error').hide();
            });
        },

        hideSyncButtonsAndShowProgress: function (callback) {
            lsSyncModal.$syncProgressContainer.show();
            lsSyncModal.$modalMessage.hide();
            lsSyncModal.$popUpButton.hide();
            lsSyncModal.initializeSyncProgress();

            if (typeof callback === "function") {
                callback();
            }

        },

        syncProductFromQuickBooks: function (linksync, product_number) {
            if (typeof product_number == 'undefined') {
                product_number = 0;
            } else if (product_number <= 0) {
                //Make sure we always start to page 1
                product_number = 0;
            }

            var json_linksync_products = linksync.products[product_number];
            if (typeof json_linksync_products != 'undefined') {
                console.log('product number => ' + product_number + ' product name => ' + json_linksync_products['name']);
                var product_count = product_number + 1;
                if (linksync.pagination.page > 1) {
                    product_count = product_count + (50 * (linksync.pagination.page - 1));
                }

                if (json_linksync_products.variants.length > 0) {
                    lsSyncModal.SYNC_LIMIT = lsSyncModal.SYNC_LIMIT + json_linksync_products.variants.length;
                } else {
                    lsSyncModal.SYNC_LIMIT = lsSyncModal.SYNC_LIMIT + 1;
                }

                var trialItemCount = linksync.pagination.trialItemCount;
                if (typeof linksync.pagination.trialItemCount == 'undefined') {
                    trialItemCount = 'capping_did_not_exists';
                }

                var p_data = {
                    action: 'import_to_woo',
                    page: linksync.pagination.page,
                    product_total_count: linksync.pagination.results,
                    product: json_linksync_products,
                    product_number: product_count,
                    deleted_product: linksync.pagination.deleted_product,
                    trial_item_count: trialItemCount,
                    sync_limit_count: lsSyncModal.SYNC_LIMIT
                };

                lsAjax.post(p_data).done(function (product_sync_response) {
                    console.log(product_sync_response);
                    console.log('count = ' + lsSyncModal.SYNC_LIMIT);


                    if (typeof product_sync_response.html_error_message != 'undefined') {
                        //Sync should stop
                        console.log('Sync should stop!');
                        lsSyncModal.showCappingHtmlError(product_sync_response.html_error_message);

                    } else {

                        lsSyncModal.$progressBarLabel.html("Imported " + product_sync_response.percentage + "% of products in WooCommerce");
                        progressVal = lsSyncModal.$progressBar.progressbar("value");
                        if (product_sync_response.product_number == linksync.pagination.results) {
                            lsSyncModal.$progressBar.progressbar("value", 100);
                            console.log('current screen ' + lsSyncModal.current_screen);
                            if ('wizard' != lsSyncModal.current_screen) {
                                lsSyncModal.syncCompleted();
                            }

                        } else {

                            if (progressVal < product_sync_response.percentage) {
                                lsSyncModal.$progressBar.progressbar("value", product_sync_response.percentage);
                            }

                            var product_index = product_number + 1;
                            lsSyncModal.syncProductFromQuickBooks(linksync, product_index);
                        }

                    }

                }).fail(function (data) {

                    console.log('Failed AJAX Call of syncProductFromQuickBooks :( Return Data: ');
                    console.log(data);
                    //If ajax failed retry with the same product_number
                    lsSyncModal.syncProductFromQuickBooks(linksync, product_number);
                });

            } else if (typeof json_linksync_products == 'undefined') {
                console.log('No product index page => ' + linksync.pagination.page + ' pages => ' + linksync.pagination.pages);
                var page = linksync.pagination.page + 1;
                console.log('page + 1 = ' + page);
                if (linksync.pagination.pages >= page) {
                    lsSyncModal.syncProductsFromQuickBooks(page);
                } else {
                    //Sync Completed
                }

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

            var action = 'qbo_get_products';
            if (lsSyncModal.options.action != null) {
                action = lsSyncModal.options.action;
            }

            var data_to_request = {
                action: action,
                page: page
            };

            lsSyncModal.$progressBarLabel.html("Getting products from QuickBooks Online.");

            lsAjax.post(data_to_request).done(function (linksync_response) {

                lsSyncModal.$closeContainer.hide();
                lsSyncModal.$progressBarLabel.html("Syncing products from QuickBooks Online to WooCommerce.");
                console.log('Ajax Call Done of syncProductsFromQuickBooks :) Returned Data =>');
                console.log(linksync_response);

                var product_count = linksync_response.products.length;

                if (product_count > 0) {

                    lsSyncModal.syncProductFromQuickBooks(linksync_response, 0);

                } else if (product_count <= 1) {
                    lsSyncModal.$progressBar.progressbar("value", 100);
                    console.log('current screen setting ' + lsSyncModal.current_screen);
                    if ('wizard' != lsSyncModal.current_screen) {
                        lsSyncModal.syncCompleted();
                    }
                    if (lsSyncModal.options.no_products_to_import_woo == null) {
                        lsSyncModal.$progressBarLabel.html("No products were imported to WooCommerce");
                    } else {
                        lsSyncModal.$progressBarLabel.html(lsSyncModal.options.no_products_to_import_woo);
                    }
                }


            }).fail(function (data) {
                console.log('Failed AJAX Call of syncProductsFromQuickBooks :( Return Data: ' + data);
                //Failed then retry with the same page
                lsSyncModal.syncProductsFromQuickBooks(page);
            });
        },

        syncProductToQuickBooks: function (woocommerce_products, product_index) {
            if (typeof product_index == 'undefined') {
                product_index = 0;
            } else if (product_index <= 0) {
                //Make sure we always start to index 1
                product_index = 0;
            }
            var product_total_count = woocommerce_products.length;

            if (product_total_count > 0) {

                if (typeof woocommerce_products[product_index] != 'undefined') {

                    var product_number = product_index + 1;
                    var data = {
                        action: 'import_to_qbo',
                        p_id: woocommerce_products[product_index].ID,
                        product_number: product_number,
                        total_count: product_total_count,
                    };
                    lsAjax.post(data).done(function (product_sync_response) {
                        console.log(product_sync_response);
                        if(product_sync_response){

                            var haltSync = lsSyncModal.stopSyncing(product_sync_response);
                            if (false == haltSync) {

                                lsSyncModal.$progressBarLabel.html("Exported " + product_sync_response.percentage + "% of WooCommerce products to QuickBooks Online");
                                var progressVal = lsSyncModal.$progressBar.progressbar("value");

                                if (product_sync_response.product_number == product_total_count) {

                                    lsSyncModal.$progressBar.progressbar("value", 100);
                                    console.log('current screen setting ' + lsSyncModal.current_screen);
                                    if ('wizard' != lsSyncModal.current_screen) {
                                        lsSyncModal.syncCompleted();
                                    }


                                } else {
                                    if (progressVal < product_sync_response.percentage) {
                                        lsSyncModal.$progressBar.progressbar("value", product_sync_response.percentage);
                                    }

                                    var temp_product_index = product_index + 1;
                                    lsSyncModal.syncProductToQuickBooks(woocommerce_products, temp_product_index);
                                }

                            } else if (true == haltSync) {

                                var htmlErrorMessage = product_sync_response.response_product_to_qbo.response.html_error_message;
                                lsSyncModal.showCappingHtmlError(htmlErrorMessage);

                            }

                        } else {

                            if (progressVal < product_sync_response.percentage) {
                                lsSyncModal.$progressBar.progressbar("value", product_sync_response.percentage);
                            }

                            var temp_product_index = product_index + 1;
                            lsSyncModal.syncProductToQuickBooks(woocommerce_products, temp_product_index);

                        }

                    }).fail(function (data) {

                        console.log('Failed AJAX Call of syncProductToVend :( Return Data: => ');
                        console.log(data);

                        //If failed, retry to sync with the same product index
                        lsSyncModal.syncProductToQuickBooks(woocommerce_products, product_index);
                    });

                } else if (typeof woocommerce_products[product_index] == 'undefined') {

                }
            } else {

                //No Woocommerce products to sync
                lsSyncModal.$progressBar.progressbar("value", 100);
                lsSyncModal.$progressBarLabel.html("No products from WooCommerce to export in Vend");

            }
        },

        syncProductsToQuickBooks: function () {

            lsSyncModal.$progressBar.progressbar("value", 0);
            lsAjax.post({action: 'woo_get_products'}).done(function (woo_products) {

                console.log(woo_products);

                if (!$.isEmptyObject(woo_products)) {
                    var product_total_count = woo_products.length;

                    if (product_total_count > 0) {
                        lsSyncModal.$closeContainer.hide();
                        lsSyncModal.syncProductToQuickBooks(woo_products, 0);

                    } else {
                        lsSyncModal.$progressBar.progressbar("value", 100);
                        lsSyncModal.$progressBarLabel.html("No products from WooCommerce to export in QuickBooks Online");
                    }
                } else {
                    lsSyncModal.$progressBar.progressbar("value", 100);
                    lsSyncModal.$progressBarLabel.html("No products from WooCommerce to export in QuickBooks Online");
                }

            }).fail(function (data) {

                console.log('Failed AJAX Call of syncProductsToQuickBooks :( Return Data: => ');
                console.log(data);
                lsSyncModal.syncProductsToQuickBooks();

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
        },

        syncingModalHtmlBuilder: function (option) {
            console.log(option);
            lsSyncModal.$modalContent.html(lsSyncModal.syncingModalHtml(option));
        },

        syncingModalHtml: function (option) {
            console.log(option);
            var syncingModalHtml = '';
            syncingModalHtml += '<div class="close-container"> <div class="ui-icon ui-icon-close close-reveal-modal btn-no" style="width: 16px !important;height: 17px;"></div> </div>';
            if (true == option.duplicate_sku.has_duplicate_or_empty_sku) {
                syncingModalHtml += '<center> <br/> <h4 style="color: red;">' + option.duplicate_sku.message + '</h4> </center>'
            } else {
                var modalMessage = 'Your products from QuickBooks Online will be imported to WooCommerce.<br/> Do you wish to continue?';
                if ('woo_to_qbo' == option.product.sync_type) {
                    modalMessage = 'Your WooCommerce products will be exported to QuickBooks Online. <br> Do you wish to continue?';
                }

                syncingModalHtml += '<div id="sync_progress_container" style="display: none;"> <center> <br/> <div id="syncing_loader"> <p style="font-weight: bold;">Please do not close or refresh the browser while syncingis in progress.</p> </div> </center> <center> <div> <div id="progressbar"></div> <div class="progress-label">Loading...</div> </div><p class="form-holder hide ls-dashboard-link">' + option.settings_link + '</p> </center> <br/> </div>';
                syncingModalHtml += '<div id="popup_message"> <center> <div> <h4 id="sync_pop_up_msg" class="modal-message">' + modalMessage + '</h4> </div> </center> </div>';
                syncingModalHtml += '<div id="pop_up_btn_container">';
                console.log('syncing type => ' + option.product.sync_type);
                if ('two_way' == option.product.sync_type) {
                    syncingModalHtml += '<div class="two_way_pop_button pop_button" style="width: 401px;display: block;"> <input type="button" class="product_from_qbo button" value="Product from QuickBooks"> <input type="button" class="product_to_qbo button" value="Product to QuickBooks"> </div>';
                }

                if ('qbo_to_woo' == option.product.sync_type) {
                    syncingModalHtml += '<div class="sync_all_products_from_qbo pop_button" style="display: block;"> <input type="button" class="product_from_qbo button btn-yes" value="Yes"> <input type="button" class="button btn-no" name="no" value="No"> </div>';
                }

                if ('woo_to_qbo' == option.product.sync_type) {
                    syncingModalHtml += '<div class="sync_all_products_to_qbo pop_button" style="display: block;"> <input type="button" class="product_to_qbo button btn-yes" value="Yes"> <input type="button" class="button btn-no" name="no" value="No"> </div>';
                }
            }

            syncingModalHtml += '</div>'

            return syncingModalHtml;
        },

        get_syncing_options: function (callback) {
            lsAjax.post({action: 'qbo_get_syncing_options'}).done(function (response) {
                if (typeof callback === "function") {
                    callback(response);
                }
            });
        },

        done_required_sync: function () {
            var data = {
                action: 'qbo_done_syncing_required'
            };
            lsAjax.post(data, function (data) {
                $('.require-resync').hide();
            });
        }

    };
}(jQuery));