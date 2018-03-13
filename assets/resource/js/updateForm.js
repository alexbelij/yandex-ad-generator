
$(function () {

    var UPDATE_FORM_ID = '#ad-update-modal';

    var pointsUpdate = function() {
        var totalPoints = 0, val;
        $(".brands-list-container").find(".brand-checkbox:checked").each(function(ind) {
            val = parseInt($(this).closest(".checkbox").find(".points-value").text());
            totalPoints += val ? val : 0;
        });
        $(".accounts-list-container .total-points").text(totalPoints);
    };

    $(".show-update-form").click(function (e) {
        var brandIds = [];

        $("[name*=brandsList]:checked").each(function () {
            brandIds.push($(this).val());
        });

        if (brandIds.length == 0) {
            bootbox.alert("Выберите хотя бы один бренд");
            return;
        }

        $.ajax({
            url: "/generator/general/update-form",
            method: "GET",
            data: {
                shopId: $(this).data('shopId'),
                brandIds: brandIds
            },
            success: function (result) {
                var $modal = $(UPDATE_FORM_ID);
                $modal.find('.modal-container').html(result.html);
                $modal.modal('show');
                pointsUpdate();
            }
        });
    });

    $(UPDATE_FORM_ID).on("change", ".brand-checkbox", function(e) {
        var $this = $(this),
            val = $this.val(),
            $container = $this.closest(".brands-list"),
            checkedCount = $container.find(".brand-checkbox:checked:not(.all-brand-checkbox)").length,
            checkboxCount = $container.find(".brand-checkbox").length - 1,
            isChecked = $this.is(":checked");

        if (val == 0) {
            $this.closest(".checkbox").siblings().find(".brand-checkbox").prop("checked", isChecked);
        } else {
            $container.find(".all-brand-checkbox").prop("checked", checkedCount >= checkboxCount);
        }
        pointsUpdate();
    });

    $(UPDATE_FORM_ID).on("change", ".account-checkbox", function(e) {
        var $this = $(this),
            val = $this.val(),
            $container = $this.closest(".accounts-list"),
            checkedCount = $container.find(".account-checkbox:checked:not(.all-account-checkbox)").length,
            checkboxCount = $container.find(".account-checkbox").length - 1,
            isChecked = $this.is(':checked'),
            brandIds = $this.data('brandIds') || [];

        if (val == 0) {
            $this.closest(".checkbox").siblings().find(".account-checkbox").prop("checked", isChecked).trigger('change');
        } else {
            $container.find(".all-account-checkbox").prop("checked", checkedCount >= checkboxCount);
        }

        brandIds.forEach(function (val) {
            $(':checkbox[data-brand-id="' + val + '"]').prop('checked', isChecked).trigger('change');
        });

        pointsUpdate();
    });

    $(UPDATE_FORM_ID).on("click", ".yandex-update", function (e) {
        var brandIds = [];

        $("[name='brandIds[]']:checked", UPDATE_FORM_ID).each(function () {
            brandIds.push($(this).val());
        });

        if (brandIds.length == 0) {
            bootbox.alert("Выберите хотя бы один бренд");
            return;
        }

        $.ajax({
            url: "/generator/general/start-update",
            method: "POST",
            data: {
                shopId: $(UPDATE_FORM_ID).data('shopId'),
                priceFrom: $("[name*=price_from]").val(),
                priceTo: $("[name*=price_to]").val(),
                brandIds: brandIds,
                categoryIds: $.jstree.reference('#categories-tree').get_checked()
            },
            success: function (result) {
                bootbox.alert(result.message);
            }
        });
    });
});