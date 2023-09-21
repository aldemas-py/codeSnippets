$(':text').focusin(function () {
    $(this).css('background', 'red');
});

// $(':text').focusout(function () {
//     $(this).css('background', 'yellow');
// });

// $(':text').blur(function () {
//     $(this).css('background', 'yellow');
// });

$(':text').blur(function (e) { 
    e.preventDefault();
    $(this).css('background', 'yellow');
    
});