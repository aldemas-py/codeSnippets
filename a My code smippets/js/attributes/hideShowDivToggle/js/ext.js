// // This was my attempt

(function($) {
    $.fn.clickToggle = function(func1, func2) {
        var funcs = [func1, func2];
        this.data('toggleclicked', 0);
        this.click(function() {
            var data = $(this).data();
            var tc = data.toggleclicked;
            $.proxy(funcs[tc], this)();
            data.toggleclicked = (tc + 1) % 2;
        });
        return this;
    };
}(jQuery));

// // $(document).ready(function () {
// //     $('#showHide').clickToggle(function (e) { 
// //         e.preventDefault();
// //         alert('');
// //         $('#message').hide();
// //     }, function (e) { 
// //         e.preventDefault();
// //         $('#message').show();
// //     });
// // });

// // $('#showHide').click(function () {
// //     $('#message').toggle();
// //   });

// $('#showHide').clickToggle(function () {
//     $('#message').show();
//     $(this).text('Hide');
    
// },function () {
//     $(this).text('Show');
//     $('#message').hide();
    
// });

// // Alex's attempt
// added an new func

$('#showHide').clickToggle(function () {
        $('#message').toggle();
        $(this).text('Show');
        
    },function () {
        $('#message').toggle();
        $(this).text('Hide');
        
});