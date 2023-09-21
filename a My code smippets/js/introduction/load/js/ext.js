// $('#image').onLoad (function () {
//      // code for after imahe loads

//      alert('image has loaded');
// });


// $('#image').load(function () {
//      // code for after imahe loads

//      alert('image has loaded');
// });

// $(document).ready(function () {
//      //code executes when ready

//      alert('The Page has loaded');
// });



// $(document).ready(function () {
//      //code executes when ready
//      alert('The Page has loaded');

//      $('#image').load(function () {
//           // code for after imahe loads
     
//           alert('image has loaded');
//      });
// });


$(document).ready(function () {
     //code executes when ready
     alert('The Page has loaded');

     $('iframe').on("load", function () {
          // code for after imahe loads
     
          alert('All iframes have loaded');
     });
});
