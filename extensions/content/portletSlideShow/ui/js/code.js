var visible_gallery_id = 0;


function fullscreen() {
  var element = document.getElementById('colorbox');

  if (element.requestFullScreen) {
    if (!document.fullScreen) {
      element.requestFullscreen();
    } else {
      document.exitFullScreen();
    }
  } else if (element.mozRequestFullScreen) {
    if (!document.mozFullScreen) {
      element.mozRequestFullScreen();
    } else {
      document.mozCancelFullScreen();
    }
  } else if (element.webkitRequestFullScreen) {
    if (!document.webkitIsFullScreen) {
      element.webkitRequestFullScreen();
    } else {
      document.webkitCancelFullScreen();
    }
  }
  setTimeout(function(){$('a.slideshow').colorbox()},1000);
}

function initialize(classname) {
  $(classname).colorbox(
    {
      rel: classname, slideshow:true, scalePhotos: true,photo:true, width: '100%', height:'100%',slideshowAuto:false, transition:'elastic', escKey:false, reposition:true,

      onOpen: function(){
        jQuery('#cboxContent').append('<img id=\"cboxFullscreen\" onclick=\"fullscreen()\" src=\"' + PATH_URL + 'photoalbum/asset/icons/image_fullscreen_grey.png\">');

        jQuery('#gallery').hide();

        $('#cboxFullscreen').mouseover(
          function(){
            this.src = PATH_URL + "photoalbum/asset/icons/image_fullscreen_black.png";
          }
        );
        $('#cboxFullscreen').mouseout(
          function(){
            this.src = PATH_URL + "photoalbum/asset/icons/image_fullscreen_grey.png";
          }
        );
      },

      onCleanup: function(){
        jQuery('#gallery').show();
        var element = document.getElementById('colorbox');
        if (element.requestFullScreen) {
          if (!document.fullScreen) {
          } else {
            document.exitFullScreen();
          }
        } else if (element.mozRequestFullScreen) {
          if (!document.mozFullScreen) {
          } else {
            document.mozCancelFullScreen();
          }
        } else if (element.webkitRequestFullScreen) {
          if (!document.webkitIsFullScreen) {
          } else {
            document.webkitCancelFullScreen();
          }
        }
      }
    }
  );
}

$(document).bind('cbox_complete', function(){
  if($('#cboxTitle').height() > 22){ 
    $("#cboxTitle").hide(); 
    $("<div class='colorbox-longtitle'>"+$("#cboxTitle").html()+"</div>").css({color: $("#cboxTitle").css('color')}).insertBefore(".cboxPhoto"); 
    $.fn.colorbox.resize(); 
  }
});
