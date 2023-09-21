$(document).ready(function () {
  $('input[type="text"]').focus(function (e) { 
    e.preventDefault();
    thisVal = $(this);
    minLength = thisVal.attr('minlength');
    
    if (minLength != 0 && minLength > 0 && thisVal.val().length< minLength) {
      thisVal.after('<span>'+ minLength +' Characters required.</span>')
    }
  }).keyup(function (e) { 
    if (thisVal.val().length() >= minLength) {
      thisVal.next().remove();
    }
  }).blur(function (e) { 
    e.preventDefault();
    thisVal.next().remove();
  });
});

$(selector);