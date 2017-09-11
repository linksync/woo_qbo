(function ($) {

    function get_laid_info() {
        $.post(ajaxurl, {action: 'get_laid_info'});
    }

    get_laid_info();

    var duplicateSkuList = {
        lsMainContainer: function () {
            return $('#ls-wrapper');
        },
        progressBar: function () {
            return $('#progressbar');
        },
        progressBarLabel: function () {
            return $(".progress-label");
        },
        modalBackDrop: function () {
            return $('.ls-modal-backdrop');
        },

        initializeModal: function () {

            duplicateSkuList.progressBar().show();
            duplicateSkuList.progressBarLabel().attr('style', '');
            duplicateSkuList.progressBar().progressbar({
                value: true,
                complete: function () {
                    // setTimeout(function () {
                    //
                    // }, 4000);


                }
            });
            duplicateSkuList.progressBar().progressbar("value", 0);
            duplicateSkuList.progressBarLabel().text('Checking duplicate products in QuickBooks Online');
            duplicateSkuList.openModal();
        },
        makeQuickBooksSkuUnique: function (duplicateProductSkuList, product_number) {
            var $progressBar = duplicateSkuList.progressBar();
            var $progressBarLabel = duplicateSkuList.progressBarLabel();
            var $modalBackDrop = duplicateSkuList.modalBackDrop();

            var totalProductCount = duplicateProductSkuList.pagination.results;
            var currentResponsePage = duplicateProductSkuList.pagination.page;
            var currentResponsePages = duplicateProductSkuList.pagination.pages;


            if (typeof product_number == 'undefined') {
                product_number = 0;
            } else if (product_number <= 0) {
                //Make sure we always start to page first product
                product_number = 0;
            }

            product = duplicateProductSkuList.products[product_number];
            if (typeof product != 'undefined') {

                var product_count = product_number + 1;
                if (currentResponsePage > 1) {
                    product_count = product_count + (50 * (currentResponsePage - 1));
                }


                var p_data = {
                    action: 'qbo_product_sku_unique',
                    page: currentResponsePage,
                    product_total_count: totalProductCount,
                    product: product,
                    product_count: product_count,
                    total_pages: currentResponsePages
                };

                lsAjax.post(p_data).done(function (p_res) {

                    console.log(p_res);
                    console.log("progress => " + p_res.percentage);

                    progressVal = $progressBar.progressbar("value");
                    if (progressVal < p_res.percentage) {
                        $progressBar.progressbar("value", p_res.percentage);
                        $progressBarLabel.html("Making " + p_res.product_number + " of " + p_res.product_total_count + " duplicate sku unique. (" + p_res.percentage + "%)");
                    }
                    if (p_res.product_number >= totalProductCount) {
                        //Making sku unique completed
                        duplicateSkuList.closeModal(function () {
                            window.location.reload();
                        });
                        console.log('Making sku unique completed');
                    } else {

                        var new_product_number = product_number + 1;
                        duplicateSkuList.makeQuickBooksSkuUnique(duplicateProductSkuList, new_product_number);
                    }

                });

            } else if (typeof product == 'undefined') {
                console.log('No product index page => ' + currentResponsePage + ' pages => ' + currentResponsePages);
                var page = currentResponsePage + 1;
                if (currentResponsePages >= page) {
                    duplicateSkuList.makeQuickBooksSkusUnique(page);
                }

            }

        },

        makeQuickBooksSkusUnique: function (page) {
            var $progressBar = duplicateSkuList.progressBar();
            var $progressBarLabel = duplicateSkuList.progressBarLabel();
            var $modalBackDrop = duplicateSkuList.modalBackDrop();


            if (typeof page == 'undefined') {
                page = 1;
            } else if (page <= 0) {
                //Make sure we always start to page 1
                page = 1;
            }
            var data = {
                action: 'qbo_get_qbo_duplicate_skus',
                page: page
            };
            lsAjax.post(data).done(function (duplicateProductSkuList) {
                console.log(duplicateProductSkuList.products);
                var product_count = duplicateProductSkuList.products.length;

                var currentResponsePage = duplicateProductSkuList.pagination.page;
                var currentResponsePages = duplicateProductSkuList.pagination.pages;

                if (product_count > 0) {

                    duplicateSkuList.makeQuickBooksSkuUnique(duplicateProductSkuList, 0);

                } else {
                    $progressBar.progressbar("value", 100);
                    $progressBarLabel.html("No duplicate product sku in QuickBooks Online");
                }

                if (currentResponsePage <= currentResponsePages) {

                    page = parseInt(currentResponsePage) + 1;

                    if (page <= currentResponsePages) {
                        duplicateSkuList.makeQuickBooksSkusUnique(page);
                    }

                }

            });
        },

        openModal: function () {
            $('.ls-modal-message').show();
            $modalContent = duplicateSkuList.lsMainContainer().find('.ls-modal-content');
            $modalContent.show();
            duplicateSkuList.modalBackDrop().removeClass('close').addClass('open');
        },

        closeModal: function (callback) {
            setTimeout(function () {
                $('.ls-modal-message').hide();
                duplicateSkuList.progressBar().hide();
                duplicateSkuList.progressBarLabel().css({
                    'font-size': '15px',
                    'font-weight': 'bold',
                    'color': 'black',
                    'padding-bottom': '8px',
                });
                duplicateSkuList.progressBarLabel().html("Making QuickBooks Online sku unique completed!");

                setTimeout(function () {

                    duplicateSkuList.modalBackDrop().removeClass('open').addClass('close');
                    $modalContent = duplicateSkuList.lsMainContainer().find('.ls-modal-content');
                    $modalContent.hide();

                    if (typeof callback === "function") {
                        callback();
                    }
                }, 2000);

            }, 2000);


        }
    };


    $(document).ready(function () {
        var all = $('*');
        var ls_wrapper = $('#ls-wrapper');

        ls_wrapper.on('submit', '#frm-duplicate-skus', function (e) {

            var activeElement = document.activeElement;
            var btnValue = $(activeElement).val();
            var btnName = $(activeElement).attr('name');

            if ('replaceallemptysku' == btnName) {
                //Empty sku ajax handler

                spinner = $('#ls-spinner');
                all.css({'cursor': 'wait'});
                spinner.show();
                $.post(ajaxurl, {action: 'qbo_set_empty_sku_automatically'}, function (data) {
                    window.location.href = window.location;
                });

            } else if ('makewooskuunique' == btnName) {
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
                if ('replace_empty_sku' == action) {
                    string_action = 'qbo_set_empty_sku_automatically';
                } else if ('make_sku_unique' == action) {
                    string_action = 'qbo_append_product_id_to_duplicate_skus';
                } else if ('delete_permanently' == action) {
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


            } else if ('makeqboskuunique' == btnName) {
                spinner = $('#ls-qbo-spinner');
                all.css({'cursor': 'wait'});
                spinner.show();
                duplicateSkuList.initializeModal();
                duplicateSkuList.makeQuickBooksSkusUnique();
            }

            e.preventDefault();

        });

    });


}(jQuery));