$('#userText').keyup(function (e) { 
    // this code runs when a lkey is input on the keyboard

    var userText = $('#userText').val();
    $('#userTextFeedback').html(userText);
});