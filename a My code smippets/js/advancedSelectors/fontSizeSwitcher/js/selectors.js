// function change size
function changeSize(element, size) {
    var current = parseInt(element.css('font-size'));
    if (size == 'bigger') {
        // this block adds 2 px to the font
        var newSize = current + 2;
    }else if (size == 'smaller') {
        // this block subtracts 2 px from the font
        var newSize = current - 2;
    }
    element.css('font-size', newSize + 'px')
}

// This will make the text smalller
$('#smaller').click(function (e) { 
    e.preventDefault();
    changeSize($('p'), 'smaller');
});

// This will make the text bigger
$('#bigger').click(function (e) { 
    e.preventDefault();
    changeSize($('p'), 'bigger');
});