// the wrong way to do it

// $('#button').click(function (e) { 
//     e.preventDefault();
//     alert('something was clicked');
// });


// $('#para').click(function (e) { 
//     e.preventDefault();
//     alert('something  was clicked');
// });

//correct wa to do it

// $('#button, #para').click(function (e) { 
//     e.preventDefault();
//     alert('Something was clicked');
// });

$('input:button, p').click(function (e) { 
    e.preventDefault();
    alert('Something was clicked');
});