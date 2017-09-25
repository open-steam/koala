function createDialog() {
  if (!jQuery('#dialog_wrapper').size()) {
    window.requestAnimationFrame(createDialog);
  }
  else{

    jQuery('#dialog_wrapper').css({"right": $(window).width() / 2 - jQuery('#dialog').width() / 2 + "px"});

    var resizeTimer = null;

    jQuery(window).bind('resize', function() {
      if (resizeTimer) clearTimeout(resizeTimer);
      resizeTimer = setTimeout(function() {jQuery('#dialog_wrapper').css({"right": $(window).width() / 2 - jQuery('#dialog').width() / 2 + "px"});}, 100);
    });
    
    jQuery('#dialog').slideDown("slow").css('overflow', 'scroll');

    //close the dialog on escape
    jQuery(document).keyup(function(e) {
        if (e.keyCode == 27) {
            //unset possibly set event
            $(window).unbind('beforeunload');
            closeDialog();
        }
    });
  }
}

createDialog();
