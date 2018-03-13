(function ($) {

    function bindEvents() {

        $(".save-settings").click(function (e) {
            var selectedCategoriesIds = $.jstree.reference('#categories-tree').get_checked(),
                brandIds = [];

            e.preventDefault();

            $('.brands-list :checkbox:checked', '#brands').each(function (index, value) {
                brandIds.push($(this).val());
            });

            $.ajax({
                url: "/generator/general/save-settings?shopId=" + $("#settings-container").data('shopId'),
                method: "post",
                data: {
                    categoryIds: selectedCategoriesIds,
                    brandsList: brandIds,
                    price_from: $("#generatorsettingsform-price_from").val(),
                    price_to: $("#generatorsettingsform-price_to").val()
                },
                success: function (result) {
                    if (result.status == 'error' || result.error) {
                        bootbox.alert(result.error || 'Ошибка при сохранении');
                        console.log(result.error);
                    } else {
                        bootbox.alert("Данные успешно сохранены");
                    }
                }
            });
        });

        $(".campaign-update").click(function (e) {
            $.ajax({
                url: "/generator/general/start-campaigns-update",
                method: "POST",
                data: {
                    shopId: $(this).data('shopId')
                },
                success: function (result) {
                    alert(result.message);
                }
            });
        });

        $(".manual-import").click(function (e) {
            e.preventDefault();

            $.ajax({
                url: "/generator/general/manual-import",
                method: "POST",
                data: {
                    shopId: $(this).data('shopId')
                },
                success: function (result) {
                    bootbox.alert(result.message);
                }
            });
        });

        $(".ajax-link").click(function (e) {
            e.preventDefault();

            var $this = $(this);

            $.ajax({
                url: $this.attr('href'),
                method: "POST",
                data: {
                    shopId: $(this).data('shopId')
                },
                success: function (result) {
                    bootbox.alert(result.message);
                }
            });
        });

        $(".start-update-products").click(function (e) {
            var selectedCategoriesIds = $.jstree.reference('#categories-tree').get_checked(),
                brandIds = [];

            e.preventDefault();

            $('.brands-list :checkbox:checked').each(function (index, value) {
                brandIds.push($(this).val());
            });

            $.ajax({
                url: $(this).prop('href'),
                method: "post",
                data: {
                    categoryIds: selectedCategoriesIds,
                    brandIds: brandIds,
                    priceFrom: $("#generatorsettingsform-price_from").val(),
                    priceTo: $("#generatorsettingsform-price_to").val()
                },
                success: function (result) {
                    if (result.status == 'error' || result.msg) {
                        bootbox.alert(result.msg || 'Ошибка при сохранении');
                        console.log(result.msg);
                    } else {
                        bootbox.alert(result.msg);
                    }
                }
            });
        });
    }

    bindEvents();

}(jQuery));