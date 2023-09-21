// $(document).ready(function () {
//   $("#image").hide();
// });

$('#start').click(function () { 
  $('#image').slideToggle(5000);
});
$('#stop').click(function () { 
  $("#image").stop();
  
});