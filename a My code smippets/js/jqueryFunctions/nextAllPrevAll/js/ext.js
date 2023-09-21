$(document).ready(function () {
    // $('.names li:first').append(' (First)');
    // $('.names li:Last').append(' (Last)');

    // $('.names').find('li').first().append(' (First)');
    // $('.names').find('li').first().next().append(' (Second)');
    // $('.names').find('li').last().prev().append(' (Second Last)');
    // $('.names').find('li').last().append(' (last)');

    // next nextAll first find
    $('.menu').find('li').first().addClass('bold').click(function () {
        $(this).nextAll().toggle();
      }).nextAll().hide();

});
