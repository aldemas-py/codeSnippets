$(document).ready(function () {
    $('ul').each( function () { 
        //  $(this).append('a');

        thisSelector = $(this);
        if (thisSelector.has('li').length == 0) {
            thisSelector.after('Empty menu')
        }
    });
    
});