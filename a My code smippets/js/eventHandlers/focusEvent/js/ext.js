/// focus in

$('#name').focusin(function (e) { 
    e.preventDefault();
    $('#someFeedback').html('Enter you name');
});
/// focus out

$('#name').focusout(function (e) { 
    e.preventDefault();
    $('#someFeedback').html('');
});

/// focus in

$('#age').focusin(function (e) { 
    e.preventDefault();
    $('#someFeeback').html('Enter you current age');
});

/// focus out

$('#age').focusout(function (e) { 
    e.preventDefault();
    $('#someFeeback').html('');
});