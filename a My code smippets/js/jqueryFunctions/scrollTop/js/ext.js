$(document).ready(function () {
    $('#someText').scroll(function () { 
        var scrollTop = $(this).scrollTop();
        $('#feedback').text('Currently at position ' + scrollTop);
    });
});