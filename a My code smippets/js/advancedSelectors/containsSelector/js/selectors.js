// cheak if the doc is ready
$(document).ready(function () {

    // code runs after a key is pushed on the keboard
    $('#searchName').keyup(function () { 
        searchName = $(this).val();

        $("#names li").removeClass('highlight');

        if (jQuery.trim(searchName) != '') {
            $("#names li:contains('" + searchName + "')").addClass('highlight');

        }
    });
});


        // $("#names li:contains('" + searchName + "')").css('background', 'red');