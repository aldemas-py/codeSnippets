$('#aButton').click(function (e) { 
    e.preventDefault();
    // $('#aDiv').hide('slow');
    // $('#aDiv').hide('fast');
    // $('#aDiv').hide(5000);
    // $('#aDiv').hide('slow', 'linear');
    $('#aDiv').hide('slow', 'linear', function () {
        alert('element hidden');
      });
});