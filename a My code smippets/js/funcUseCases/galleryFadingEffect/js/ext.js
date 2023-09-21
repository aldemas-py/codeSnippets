$(document).ready(function () {
  // $('.fadeTo').hide();
  // $(".fadeTo").click(function () {
  //     $(this).fadeTo(100, 0.4, function(){
  //       // alert('animation complete');
        
  //     });
  // })
  $('.fadeTo').css('opacity', 0.4);
$('.fadeTo').mouseover(function () {

  //fade in occours after fade out of previous image
  $(this).fadeTo(100, 1);
  $('.fadeTo').not(this).fadeTo(100, 0.4);

  //fade in occours before fade out of previous image
  // $(this).fadeTo(1000, 1, function(){
  //   $('.fadeTo').not(this).fadeTo(1000, 0.4);
  // })

});


});
