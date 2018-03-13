$(function () {

    $(".download-feed").click(function (e) {
        var selectedCategoriesIds = $.jstree.reference('#categories-tree').get_checked(),
            brandIds = [];

        e.preventDefault();

        $('.brands-list :checkbox:checked', '#brands').each(function (index, value) {
            brandIds.push($(this).val());
        });

        brandIds = brandIds.filter(function (id) {
            return id > 0;
        });

        selectedCategoriesIds = selectedCategoriesIds.filter(function (id) {
            return id > 0;
        });

        var url = "/feed/feed/download-feed?feedId=" + $('#feed-id').val() +
            '&brand_id=' + brandIds.join(',') + '&category_id=' + selectedCategoriesIds.join(',')+
            '&priceFrom=' + $("#feeditemsearch-pricefrom").val() +
            '&priceTo=' + $("#feeditemsearch-priceto").val() +
            '&item_text=' + $("#feeditemsearch-item_text").val() +
            '&only_view=' + ($(this).data('view') ? 1 : 0);

        if ($(this).data('view')) {
            window.open(url, '_blank');
        } else {
            window.location = url;
        }

    });

    $(".is-active").on('click', function () {
        var $this = $(this);

        $.ajax({
            url: '/feed/feed-items/activate?id=' + $this.data('id'),
            method: 'POST',
            data: {
                active: +$this.is(':checked')
            }
        });
    });

    $('.search-feed').on('click', function () {
        var $this = $(this),
            selectedCategoriesIds = $.jstree.reference('#categories-tree').get_checked(),
            $categoriesContainer = $('#categories-container');

        $categoriesContainer.html('');

        selectedCategoriesIds.forEach(function (categoryId) {
            $categoriesContainer.append(
                '<input type="hidden" name="FeedItemSearch[category_id][]" value="' + categoryId + '"/>'
            );
        });

        if ($this.data('onlySearch')) {
            $categoriesContainer.append(
                '<input type="hidden" name="only_search" value="1"/>'
            );
        }

        $this.closest('form').submit();
    });

});