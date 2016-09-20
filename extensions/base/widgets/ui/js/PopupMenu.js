jQuery(document).click(function() {
  jQuery('.popupmenuwrapper').parent().html('');
  jQuery('.open').removeClass('open');
  jQuery('#footer_wrapper').css('padding-top', '0px');
})

function adjustFooter() {
  var popupBottom = jQuery('.popupmenuwrapper').position().top + jQuery('.popupmenuwrapper').height() + 20;
  var footerTop = jQuery('#footer_wrapper').position().top;
  if(popupBottom > footerTop) jQuery('#footer_wrapper').css('padding-top', popupBottom - footerTop + 'px');
}
