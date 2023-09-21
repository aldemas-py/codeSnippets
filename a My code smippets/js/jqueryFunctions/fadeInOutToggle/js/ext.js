// document ready hide image default 
$(document).ready(function () {
  $('#image').hide();
});

// fade toggle

$('#button').click(function (e) { 
    e.preventDefault();
    $('#image').fadeToggle(5000, 'linear', function () {
        alert('animation Complete')
    });
});

// //fade in and out

// $('#button').clickToggle(function () { 
//     $(this).val('Hide');
//     $('#image').fadeIn('slow', 'linear', function () {
//       $('#imageFeedback').html('Image Shown');
//   });
//   },function () { 
//     $(this).val('Show');
//     $('#image').fadeOut('slow', 'linear', function () {
//       $('#imageFeedback').html('Image Hidden');
//   });
//   });

// // $(document).ready(function () {
// //   $('#image').hide();
// //   // $('#image').fadeIn('slow');
// //   $('#image').fadeIn('slow', 'swing', function () {
// //     $('#imageFeedback').html('The image has loaded');

// //   });

// // });

// // // fade in using a button

// // // $('#button').click(function (e) { 
// // //   e.preventDefault();
// // //   $('#image').fadeIn();
// // // });

