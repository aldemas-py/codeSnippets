$('#copyButton').click(function (e) { 
    e.preventDefault();
    var text = $('#text').html();
    $("#copy").html(text);
});