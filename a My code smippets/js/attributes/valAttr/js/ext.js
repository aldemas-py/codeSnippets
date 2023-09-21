// $('#button').click(function (e) { 
//     e.preventDefault();
//     var texts = $('#name').val();
//     $('#area').text(texts);

// });
$('#name').keyup(function (e) { 
    e.preventDefault();
    var texts = $('#name').val().length;
    $('#area').text(texts);

});