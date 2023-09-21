$(document).ready(function () {
  function moveDiv() {
    windowWidth = $(window).width();
    windowHeight = $(window).height();

    // alert('Width ' + windowWidth+ ' Height' + windowHeight);
    objWidth = $('#myDiv').width();
    objHeight = $('#myDiv').height();
    // alert('Width ' + objWidth+ ' Height' + objHeight);

    // $('#myDiv').css('top', (windowHeight/2) - (objHeight/2)).css('left', (windowWidth/2) - (objWidth/2));
    $('#myDiv').css('top', (windowHeight - objHeight)/2).css('left', (windowWidth - objWidth)/2);
    
    
  }
  moveDiv();

  $(window).resize(function () { 
    moveDiv();
  });
});