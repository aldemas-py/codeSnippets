$(':text').focus(function (e) { 
    e.preventDefault();
    $(this).css('background', 'yellow');
});

$(':text').blur(function (e) { 
    e.preventDefault();
    $(this).css('background', '#fff');
});

$(':button').click(function () {
    $(this).attr('value', 'Please wait...');
    $(this).attr('disabled', true);
})