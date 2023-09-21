// this code runs when the submit button is clicke
$('#signUp').submit(function (e) { 
    e.preventDefault();
    var userEmail = $('#userEmail').val();

    $('#signUpFeedback').html('Thanks ' + userEmail + " for regestering");
    
});