$('#list').change(function (e) { 
    e.preventDefault();
    var listValue = $(this).val();
    $('#listFeedback').html('You selected: ' + listValue);
});