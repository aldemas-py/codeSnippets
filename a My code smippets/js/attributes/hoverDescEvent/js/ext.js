// this func can be use to add a goover desc to any element on the wbsite
$('.hoverLink').mousemove(function (e) { 
    // values: e.clientX, e.clientY, e.pageX, e.pageY
    // $('#co').text('x: ' + e.clientX + ' y:' + e.clientY);
    // alert('this that');
    var hovertext = $(this).attr("hovertext");
    
    $('.hoverDiv').text(hovertext).show();
    $('.hoverDiv').css("top", e.clientY+10).css("left", e.clientX+10);
}).mouseout(function () {
    $('.hoverDiv').hide();
  });