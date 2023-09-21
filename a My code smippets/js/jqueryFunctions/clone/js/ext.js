$(document).ready(function () {
  $('#append').click(function (e) { 
    e.preventDefault();
    $('#span').clone().appendTo('#para2');
  });
  
});