$(function () {

    $(document).on('click', '.upload-file', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var $file = $("input[name=uploadFile]"),
            $this = $(this);

        if (!$file.val()) {
            bootbox.alert('Выберите файл');
            return;
        }

        var formData = new FormData();
        formData.append('uploadFile', $file.get(0).files[0]);
        formData.append('shopId', $this.data('shopId'));

        $.ajax({
            url: "/generator/general/upload-file",
            type: "POST",
            data: formData,
            dataType: "json",
            processData: false,
            contentType: false,
            success: function (result) {
                if (result.error) {
                    bootbox.alert(result.error);
                } else {
                    bootbox.alert('Файл успешно загружен');
                    $file.val('');
                }

            },
            error: function (error) {
                var errorMsg = error.responseJSON && error.responseJSON.message || error.statusText;

                console.log(error);

                bootbox.alert('Ошибка при загрузке файла: ' + errorMsg.toLowerCase());
            }
        });
    });

});