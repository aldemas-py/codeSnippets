// the wrong way

// $('#button').click(function (e) { 
//     e.preventDefault();
//     $('#button').attr('value', 'Please wait...');
// });

// the correct way


$('#button').click(function (e) { 
    e.preventDefault();
    $(this).attr('value', 'Please wait...');
});
