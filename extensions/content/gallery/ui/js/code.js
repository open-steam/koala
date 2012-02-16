

var visible_gallery_id = 0;

function activate_gallery(gallery_id)
{
    if (visible_gallery_id == gallery_id)
      return

    if (visible_gallery_id != 0)
    {
      document.getElementById(visible_gallery_id).className = 'gallery';
    }

    gallery = document.getElementById(gallery_id)
    if (gallery) {
      visible_gallery_id = gallery_id;
      gallery.className = 'gallery_visible';
    }
}




