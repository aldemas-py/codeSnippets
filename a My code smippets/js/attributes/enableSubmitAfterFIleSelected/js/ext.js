$(document).ready(function () {
    // Runs after the DOM has loaded


    // //methoed 1
    // $('#file').change(function (e) { 
    //     e.preventDefault();
    //     // //this shows out the link of added
    //     // value = $(this).attr('value');
    //     // alert(value);

    //     // This code changes the attrib disabled

    //     // $('#submit').removeAttr('disabled');

    // });

    //Methord 2
    // This methord runds through out the whole code
    $('input[type="file"]').change(function () {
        $(this).next().removeAttr('disabled');
    }).next().attr('disabled', 1);
});