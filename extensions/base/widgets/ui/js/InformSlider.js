function createInformSlider() {

  if(!$('#informSlider').hasClass('ui-dialog-content')){ //dialog already exists

    $('#informSlider')

      .live("dialogopen", function() {
      		var dialog = $(this);
          var widget = $(this).dialog("widget");
          var button = dialog.prev().children()[1];
          $(button).hide();
          var origOverflow = $("html").css("overflow-x");
          $("html").css("overflow-x", "hidden");
    			widget
          .css("left", "-300px")
          .animate({ left: "10px" }, { duration: 1000 })
          .delay(10000).animate({ left: "-300px" }, {
          	duration: 1000,
          	complete: function() {
             	 dialog.dialog("close");
               dialog.parent().prev().remove();
               dialog.remove();
             	 $("html").css("overflow-x", origOverflow);
          	}
      		})
      })

      .dialog({
          modal: false,
          width: "300px",
          position: { my: "top", at: "top", of: window},
          draggable: false,
          resizable: false
      });
    }
}
