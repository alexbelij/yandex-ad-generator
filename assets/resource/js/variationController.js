
$(function () {

    $(document).on('click', '.delete-variation', function (e) {
        e.preventDefault();

        var $this = $(this),
            $container = $this.closest('div');

        bootbox.confirm({
            message: 'Удалить фразу?',
            callback: function (result) {
                if (result) {
                    $.ajax({
                        url: '/generator/variations/delete-variation',
                        method: "POST",
                        data: {
                            shopId: $this.data('shopId'),
                            variationItemId: $this.data('variationItemId')
                        },
                        success: function (result) {
                            if (result.status && result.status == 'success') {
                                $container.remove();
                            }
                        }
                    });
                }
            }
        });
    });

});