function createDialog() {
  if (!jQuery('#dialog_wrapper').size()) {
    window.requestAnimationFrame(createDialog);
  }
  else{
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