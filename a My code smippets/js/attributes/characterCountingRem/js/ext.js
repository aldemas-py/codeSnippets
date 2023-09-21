$(document).ready(function () {
    var textMax = 55;
    $('#someFeedback').html(textMax + '  charcters remaining');

    $('#textarea').keyup(function (e) { 
        var textLength = $(this).val().length;
        var textRem = textMax - textLength;

        $('#someFeedback').html(textRem + ' characters remaining');
    });
});