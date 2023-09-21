$(document).ready(function () {
    $('#agree').change(function (e) { 
        e.preventDefault();
        state = $(this).prop('checked');
        // alert(state);
        if (state == true) {
            $('#continue').removeAttr('disabled');
            // alert('checked');
        }else if (state == false) {
            $("#continue").attr("disabled", "disabled");
            // alert('unchecked');
        }
    });
}); 
// // if ($("input[type=checkbox]").prop(
// //     ":checked")) {
// //       alert("Check box in Checked");
// //   } else {
// //       alert("Check box is Unchecked");
// //   }
