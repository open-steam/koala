function showMessage(id) {
 
    $('#'+id).find('.only_headline').each(function (element) {
        $(this).removeClass('only_headline');
    });
    
    $('#'+id).find('.only_subheadline').each(function (element) {
        $(this).removeClass('only_subheadline');
    });
    
    $('#'+id).find('.messageShow').each(function (element) {
        $(this).hide();
    });
}