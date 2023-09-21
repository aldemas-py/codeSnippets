$(document).ready(function () {
    $('.duplicate').on({
        click: function () {
            $(this).after('<input type="button" value="click" class="duplicate">');
        }
    });
});