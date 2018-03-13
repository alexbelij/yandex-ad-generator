$(function () {

    var $modal = $("#generator-modal"),
        brandId,
        shopId,
        generationType;

    $(document).on('click', '.generate-keywords', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var $this = $(this),
            brandTitle = $this.closest(".checkbox").find('.brand-title').text(),
            typeTitle;

        brandId = $this.data('brandId');
        shopId = $this.data('shopId');
        generationType = $this.data('type');

        if (brandId == undefined) {
            brandId = [];
            $('[name="GeneratorSettingsForm[brandsList][]"]:checked').each(function () {
                brandId.push($(this).val());
            });
            if (!brandId.length) {
                alert('Выберите хотя бы один бренд!');
                return;
            }
        }

        if ($.isArray(brandId)) {
            if (generationType == 'all') {
                typeTitle = 'Массовая генерация фраз и заголовоков';
            } else {
                typeTitle = 'Массовая генерация фраз';
            }
            $modal.find('.brand-modal-container').text('');
        } else {
            if (generationType == 'all') {
                typeTitle = 'Генерация фраз и заголовоков для ';
            } else {
                typeTitle = 'Генерация фраз для ';
            }
            $modal.find('.brand-modal-container').text(brandTitle);
        }

        $modal.find('.type-title').text(typeTitle);
        $modal.modal("show");
    });

    $('.remove-duplicate').click(function (e) {
        var that = this;
        bootbox.confirm({
            message: 'Удалить дубликаты объявлений?',
            callback: function (result) {
                if (result) {
                    $.ajax({
                        url: '/generator/general/delete-duplicates',
                        method: "POST",
                        data: {
                            shopId: $(that).data('shop-id')
                        },
                        success: function (result) {
                            if (result.status == 'success') {
                                bootbox.alert("Создана задача на удаление дубликатов");
                            } else {
                                bootbox.alert("Ошибка при создании задачи на удаление дубликатов");
                            }
                        }
                    });
                }
            }
        });
    });

    $('.remove-auto-ads').click(function (e) {
        var that = this;
        bootbox.confirm({
            message: 'Удалить автоматически сгенерированные объявления?',
            callback: function (result) {
                var brandIds = [];

                $('.brands-list :checkbox:checked', '#brands').each(function () {
                    brandIds.push($(this).val());
                });

                if (result) {
                    $.ajax({
                        url: '/generator/general/delete-auto-ads',
                        method: "POST",
                        data: {
                            shopId: $(that).data('shop-id'),
                            brandIds: brandIds
                        },
                        success: function (result) {
                            if (result.message) {
                                bootbox.alert(result.message);
                            }
                        }
                    });
                }
            }
        });
    });

    $('.remove-ad-without-brand').click(function (e) {
        var that = this;
        bootbox.confirm({
            message: 'Удалить объявления без вариаций брендов?',
            callback: function (result) {
                if (result) {
                    $.ajax({
                        url: '/generator/general/delete-ad-without-brand',
                        method: "POST",
                        data: {
                            shopId: $(that).data('shop-id')
                        },
                        success: function (result) {
                            if (result.status == 'success') {
                                bootbox.alert("Создана новая задача");
                            } else {
                                bootbox.alert(result.msg || 'Ошибка');
                            }
                        }
                    });
                }
            }
        });
    });

    $(document).on('click', '.run-generator', function (e) {
        var runOption = $(this).data('generatorOption');

        $.ajax({
            url: '/generator/general/generate-keywords',
            method: "POST",
            data: {
                runOption: runOption,
                brandId: brandId,
                shopId: shopId,
                type: generationType,
                priceFrom: +$("[name*=price_from]").val(),
                priceTo: +$("[name*=price_to]").val(),
                dateFrom: $("input[name=dateFrom]").val(),
                dateTo: $("input[name=dateTo]").val(),
                categoryId: $.jstree.reference('#categories-tree').get_checked()
            },
            success: function (result) {
                if (result.message) {
                    bootbox.alert(result.message);
                }
                if (result.status == 'success') {
                    $modal.modal('hide');
                }
            },
            error: function (error) {
                if (error.message) {
                    bootbox.alert(error.message);
                }
            }
        });
    });
});