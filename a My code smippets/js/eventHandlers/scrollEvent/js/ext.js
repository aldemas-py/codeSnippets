$('#someText').scroll(function () { 
    var scrollPos = $(this).scrollTop();
    $('#someFeedback').html('you have scrollled and are at pos: ' + scrollPos);
});