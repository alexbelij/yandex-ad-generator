$(function () {
    
    $(document).on('change', '.submit-on-change', function () {
        $(this).closest('form').submit();
    });
    
});