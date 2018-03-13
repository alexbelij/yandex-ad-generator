(function ($) {

    function getShopId() {
        return window.location.search.match(/shopId=(\d+)/)[1];
    }

    /**
     * Проверка правильности заполнения заголовоков и ключевых фраз перед сохранением
     * @returns {number}
     */
    function validateBeforeSubmit() {
        var isSuccess = 1;

        $(".is-empty").removeClass("is-empty has-error");

        $(".ads-grid tr").each(function (e) {
            var $adTitle = $(this).find('.ad-title'),
                $adKeywords = $(this).find('.ad-keywords'),
                keywords = $adKeywords.val();

            if ($adTitle.length == 0) {
                return 1;
            }

            if ($.trim($adTitle.val()) == '') {
                isSuccess &= 0;
                $adTitle
                    .closest('td')
                    .addClass('has-error is-empty')
                    .find('.error-msg')
                    .text('Заполните поле "Заголовок"');
            }

            if ($adKeywords.length && $.trim(keywords) == '') {
                isSuccess &= 0;
                $adKeywords
                    .closest('td')
                    .addClass('has-error is-empty')
                    .find('.error-msg')
                    .text('Заполните поле "Ключевые слова"');
            }
        });

        return isSuccess;
    }

    function bindEvents() {
        $(document).on('change', "#keywords-filter-form [name*=brandId]", function () {
            $(this).closest("form").submit();
        });

        $(document).on("click", "#keywords-filter-form :checkbox", function () {
            $(this).closest("form").submit();
        });

        $(document).on('click', '.save-products', function (e) {
            var data = $("[name^=Products], [name^=Ad]").serialize();

            if (!validateBeforeSubmit()) {
                bootbox.alert('Заполните заголовки или ключевые слова');
                return false;
            }

            $.ajax({
                url: "/generator/keywords/save-products?shopId=" + getShopId() ,
                dataType: "json",
                data: data,
                method: "post",
                success: function (data) {
                    console.log(data);
                    $.pjax.reload('#keywords-grid-pjax');
                    bootbox.alert('Данные сохранены');
                }
            });
        });

        $(document).on('click', '.export-to-xls', function (e) {
            var $this = $(this);

            e.preventDefault();

            window.location.href = $this.prop('href') + '&' + $this.closest('form').serialize();
        });

        $(document).on('click', '.is_require_verification', function (e) {
            var $this = $(this);

            $this.siblings('[type=hidden]').prop('disabled', $this.is(':checked'));
        });

        $(document).on('click', '.add-ad', function (e) {
            var $this = $(this),
                $container = $this.closest('.ads-grid'),
                $table = $container.find('table'),
                productId = $container.data('product-id'),
                rowTemplate =
                    "<tr class='tr-ad-row' data-product-id='" + productId + "'>" +
                        "<td>" +
                            "<input type='text' name='Ad[new][" + productId +"][title][]' class='form-control ad-title ad'>" +
                            "<div class='error-msg'></div> " +
                            '<div class="checkbox">' +
                                '<label>' +
                                    '<input type="hidden" class="is-require-verification" name="Ad[new][' + productId +'][is_require_verification][]" value="0">'+
                                    '<input class="is_require_verification" type="checkbox" name="Ad[new][' + productId +'][is_require_verification][]" value="1">Требует проверки' +
                                '</label>' +
                            '</div>' +
                        "</td>" +
                        "<td>" +
                            "<textarea name='Ad[new][" + productId +"][keywords][]' class='form-control ad-keywords ad'></textarea>" +
                            "<div class='error-msg'></div> " +
                            "<div style='margin-top: 10px; float: right;'><button class='btn btn-success save-new-ad'>Сохранить</button></div>" +
                            "<div style='clear: both;'></div>"+
                        "</td>" +
                        "<td><a class='remove-ad' href='#'><i class='glyphicon glyphicon-trash'></i></a></td>" +
                    "</tr>";

            if ($('tbody .empty', $table).length) {
                $('tbody .empty', $table).closest('tr').remove();
            }

            $('tbody', $table).append(rowTemplate);

            e.preventDefault();
            e.stopPropagation();
        });

        $(document).on('click', '.save-new-ad', function (e) {
            e.preventDefault();

            var $this = $(this),
                $container = $this.closest(".tr-ad-row"),
                title = $container.find("input.ad-title").val(),
                productId = $container.data('productId'),
                isRequireVerification = +$container.find('.is_require_verification:not(:hidden)').is(':checked'),
                keywords = $container.find('.ad-keywords').val(),
                productInfo = {};

            if ($container.find('.ad-title').closest('.has-error').length) {
                alert('Перед сохранением необходимо устранить ошибки');
                return;
            }

            $("input[name^='Products[" + productId + "]']").each(function () {
                var $this = $(this),
                    name = $this.prop('name').match(/Products\[\d+\]\[([^\]]+)\]/);

                productInfo[name[1]] = $this.val();
            });

            $.ajax({
                url: "/generator/keywords/save-new-ad",
                method: "POST",
                data: {
                    productId: productId,
                    title: title,
                    keywords: keywords,
                    isRequireVerification: isRequireVerification,
                    product: productInfo
                },
                success: function () {
                    $.pjax.reload('#ad-pjax-' + productId);
                }
            });
        });

        $(document).on('click', '.remove-ad', function (e) {
            e.preventDefault();
            e.stopPropagation();

            var $this = $(this);

            if ($this.data('adId')) {
                bootbox.confirm({
                    message: 'Удалить объявление?',
                    callback: function (result) {
                        if (result) {
                            $.ajax({
                                url: $this.prop('href'),
                                method: "POST",
                                success: function () {
                                    $this.closest('tr').remove();
                                }
                            });
                        }
                    }
                });
            } else {
                $this.closest('tr').remove();
            }
        });

        $(document).on('keyup', '.ad-title', function () {
            var $this = $(this),
                $container = $this.closest('td');
            if ($(this).val().length > 33) {
                $container.addClass('has-error');
                $container.find('.error-msg').text('Заголовок превышает 33 символа');
            } else {
                $container.removeClass('has-error');
            }
        });

        $(document).on('keyup', '.ad', function () {
            var $this = $(this),
                $container = $this.closest('td');

            if ($container.hasClass('is-empty')) {
                $container.removeClass('is-empty has-error');
            }
        });

        $(document).on('click', ".verify", function (e) {
            var $this = $(this),
                url = '/generator/keywords/mark-verify',
                data = $this.closest('form').serialize();

            url += '?shopId=' + $this.data('shopId');
            data += '&verify=' + $this.data('verify');

            bootbox.confirm({
                message: $this.data('verify') && 'Пометить объявления как требует проверки?' || 'Пометить объявления как не требует проверки?',
                callback: function (result) {
                    if (result) {
                        $.ajax({
                            url: url,
                            method: "POST",
                            data: data,
                            success: function (result) {
                                if (result.status == 'success') {
                                    bootbox.alert("Операция выполнена");
                                    window.location.reload();
                                } else {
                                    bootbox.alert("Ошибка");
                                }
                            }
                        });
                    }
                }
            });
        });

        $(document).on('click', '.ad-generate', function (e) {
            e.preventDefault();

            var $this = $(this),
                $modal = $("#generator-modal");

            $modal.data('type', $this.data('type'));
            $modal.modal('show');
        });

        $(document).on('click', '.run-generator', function (e) {

            var $this = $(this),
                $modal = $("#generator-modal");

            e.preventDefault();

            $.ajax({
                url: '/generator/general/generate-keywords',
                method: "POST",
                data: {
                    brandId: $("#productssearch-brandid").val(),
                    shopId: getShopId(),
                    title: $("#productssearch-title").val(),
                    adTitle: $("#productssearch-adtitle").val(),
                    dateFrom: $("#productssearch-datefrom").val(),
                    dateTo: $("#productssearch-dateto").val(),
                    onlyActive: +$("#productssearch-onlyactive").is(":checked"),
                    isRequireVerification: +$("#productssearch-isrequireverification").is(":checked"),
                    withoutAd: +$("#productssearch-withoutad").is(":checked"),
                    runOption: $this.data('generatorOption'),
                    type: $modal.data('type') || 'all',
                    runType: "partial"
                },
                success: function (result) {
                    if (result.status == 'success') {
                        bootbox.alert("Операция выполнена");
                    } else {
                        bootbox.alert(result.message);
                    }
                }
            });
        });

        $(document).on('click', '.add-keywords', function (e) {
            e.preventDefault();

            var $this = $(this),
                $container = $this.closest('div'),
                $textarea = $container.find('.ad-keywords');

            if ($textarea.hasClass('hidden')) {
                $textarea.removeClass('hidden');
                $textarea.prop('disabled', false);
                $this.text('Скрыть');
            } else {
                $textarea.addClass('hidden');
                $textarea.prop('disabled', true);
                $this.text('Добавить');
            }
        });

        $(document).on('click', '.delete-keyword', function (e) {
            e.preventDefault();

            var $this = $(this);

            bootbox.confirm({
                message: 'Удалить фразу?',
                callback: function (result) {
                    if (result) {
                        $.ajax({
                            url: '/generator/keywords/delete-keyword',
                            method: "POST",
                            data: {
                                keyword: $this.data('keyword'),
                                adId: $this.data('adId')
                            },
                            success: function (result) {
                                if (result.status && result.status == 'success') {
                                    $this.closest('.keyword-item').remove();
                                }
                            }
                        });
                    }
                }
            });
        });

        $(document).on('click', ".require-verification :checkbox", function (e) {
            var $this = $(this);

            $.ajax({
                url: "/generator/keywords/require-verification?id=" + $this.closest('.require-verification').data('adId'),
                data: {
                    isRequire: +$this.is(':checked')
                },
                method: "POST",
                success: function () {

                }
            });
        });

        $(document).on('click', '.shuffle-groups', function (e) {
            var $this = $(this),
                value = +$this.is(':checked');

            $.ajax({
                url: "/generator/keywords/shuffle-groups",
                data: {
                    value: value,
                    yandexCampaignId: $this.data('yandexCampaignId')
                },
                method: "POST",
                success: function (result) {

                },
                error: function (error) {
                    alert(error.statusText);
                    console.log(error);
                    $this.prop('checked', !value)
                }
            });
        });
    }

    bindEvents();

    $(".ad-title").trigger('keyup');

}(jQuery));