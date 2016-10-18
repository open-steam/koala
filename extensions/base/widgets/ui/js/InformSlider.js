function createInformSlider() {

  if(!$('#informSlider').hasClass('ui-dialog-content')){ //dialog already exists

    var origOverflow;

    $('#informSlider').dialog({
          modal: false,
          width: "300px",
          position: { my: "top", at: "top", of: window},
          draggable: false,
          resizable: false,
          beforeClose: function(event, ui) {
            $(this).parent().animate({ left: "-300px" }, {
            	duration: 1000,
            	complete: function() {
                 $("#informSlider").remove();
               	 $("html").css("overflow-x", origOverflow);
            	}
        		});
            return false;
          },
          open: function(event, ui) {
            var that = this;
            origOverflow = $("html").css("overflow-x");
            $("html").css("overflow-x", "hidden");
            $(this).parent().css("left", "-300px").animate({ left: "10px" }, { duration: 1000 });
            setTimeout(function(){
              if($(that).parent().length != 0) $(that).dialog("close")
            }, 8000);
          }
      });
    }
}
