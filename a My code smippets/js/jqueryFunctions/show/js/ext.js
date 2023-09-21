$(document).ready(function () {
  $('#aDiv').hide();
});
$('#aButton').click(function (e) { 
    e.preventDefault();
    // $('#aDiv').hide('slow');
    // $('#aDiv').hide('fast');
    // $('#aDiv').hide(5000);
    // $('#aDiv').hide('slow', 'linear');
    $('#aDiv').show('slow', 'linear', function () {
        alert('element shown');
      });
});