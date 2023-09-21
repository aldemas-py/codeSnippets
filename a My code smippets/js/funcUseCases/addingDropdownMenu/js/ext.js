$('.link').click(function (e) { 
  e.preventDefault();
  var item = $(this).text();
  $('#list').append('<option>' + item + '</option>');
});