// $("#sometext").click(function (e) { 
//     e.preventDefault();
//     $(this).toggleClass('high bold');
// });

$("#input").focus(function (e) { 
    e.preventDefault();
    $(this).toggleClass("high");
}).blur(function (e) { 
    e.preventDefault();
    $(this).toggleClass("high");
    
});;