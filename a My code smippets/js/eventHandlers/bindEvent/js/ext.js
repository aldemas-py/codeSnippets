// // the long way
// $('a').mouseenter(function () { 
//     $(this).addClass('bold');
// }).mouseleave(function () {
//     $(this).removeClass('bold');
// });

// the short way
$('a').bind('mouseenter mouseleave', function (e) {
    $(this).toggleClass('bold'); 
});