// // slide Down slide up effect

// $(document).ready(function () {
//     var speed =1000;
//     $('#topMessage').slideDown(speed, 'linear');
//     $("#hideMessage").click(function (e) { 
//         e.preventDefault();
//         $('#topMessage').slideUp(speed);
//     });
// });

//slide toggle Effect

$(document).ready(function () {
    $("#image").hide();
});
$('#button').click(function (e) { 
    e.preventDefault();
    $("#image").slideToggle(2000);
    
});