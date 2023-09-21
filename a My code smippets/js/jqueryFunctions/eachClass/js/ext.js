$(document).ready(function () {
    $('#combine').click(function (e) { 
        e.preventDefault();
        // var combinedText = '';
        var failed = false;

        $('input[type="text"').each(function (index) {
            // alert(index);
            // combinedText += $(this).val() + ' ';
            // alert(combinedText); 
            if ($(this).val() == '') {
                failed = true;
            }

            if (failed == true) {
                $("#combined").text('Fill in all fields');
                
            } else if(failed == false){
                $("#combined").text('Thanks for filling');
                
            }
            
        });
    });
});