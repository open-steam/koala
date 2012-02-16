<?php
include_once( "../etc/koala.conf.php" );
include_once( "../classes/PHPsTeam/steam_types.conf.php" );

$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );
$portal->set_page_title( gettext( "Buddy Icon" ) );

function clean_iconcache($icon) {
   $thumbs = array(
   "140x185",
   "40x47",
   "30x40",
   "20x24",
   );
   if ($icon instanceof steam_object) {
      $iconcache = get_icon_cache( );
      $iconcache->remove($icon->get_id());
      $cache = get_icon_cache( 3600 );
      foreach($thumbs as $icongroup) {
         $cache->remove("icon_id_" . $icon->get_id(), $icongroup);
      }
   }
}

function clean_usericoncache($user) {
   if ($user instanceof steam_user) {
      $user->delete_value("OBJ_ICON");
      $icon = $user->get_attribute("OBJ_ICON");
      clean_iconcache($icon);
      // Clean Icon data from cache
      require_once( "Cache/Lite.php" );
      $cache = new Cache_Lite( array( "cacheDir" => PATH_CACHE ) );
      $cache->clean( $user->get_name() );
      // Clean profile data from cache
      $cache = get_cache_function( $user->get_name(), 86400 );
      $cache->drop( "lms_steam::user_get_profile", $user->get_name() );
      // TODO: In Menu "Your Desktop" some Icon data comes from lms_user
      // stored in session => delete/refresh this value in session here
      $portal = $GLOBALS[ "portal" ];
      $steam_user = $portal->get_user();
      $steam_user->init_attributes();
   }
}

$user = lms_steam::get_current_user();
if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" )
{
   $problem = "";
   $hint = "";

   if (isset($_POST["action"]) && $_POST["action"] == "deleteicon") {
      // need to set OBJ_ICON to "0" and then to icons module to avoid weird
      // effects in icon handling (server side fix done but not testedyet)
      $old_icon = $user->get_attribute( "OBJ_ICON" );
      $user->set_acquire_attribute( "OBJ_ICON", 0 );
      $user->set_attribute( "OBJ_ICON", 0 );
      // set the default user icon by acquiring OBJ_ICON from icons module
      $user->set_acquire_attribute( "OBJ_ICON", $GLOBALS[ "STEAM" ]->get_module("icons"));
      // delete previous user icon object
      if ( $old_icon instanceof steam_document )
      {
         if ( $old_icon->get_path() != "/images/doctypes/user_unknown.jpg" && $old_icon->check_access_write( $user ) ) {
            clean_iconcache($old_icon);
            $old_icon->delete();
         }
      }
      $portal->set_confirmation(gettext( "Your profile icon has been deleted." ));
      clean_usericoncache($user);
   }
   else {
      // upload new icon
      if ( count( $_FILES ) == 0 )
      {
         $problem = gettext( "No image specified." ) . " ";
         $hint    = gettext( "Please choose an image on your local disk to upload." ) . " ";
      }
      if ( strpos( $_FILES[ "icon" ][ "type" ], "image" ) === FALSE )
      {
         $problem .= gettext( "File is not an image." ) . " ";
         $hint    .= gettext( "The icon has to be an image file (JPG, GIF or PNG)." );
      }
      if ( (int) $_FILES[ "icon" ][ "size" ] > 256000 )
      {
         $problem .= gettext( "File is larger than 250 KByte." );
         $hint    .= gettext( "It is only allowed to upload profile icons with file size smaller than 250 KByte." );
      }
      if ( empty( $problem ) )
      {
         $user->set_acquire_attribute( "OBJ_ICON", 0 );
         $user->delete_value("OBJ_ICON");
         $old_icon = $user->get_attribute( "OBJ_ICON" );
         ob_start();
         readfile( $_FILES[ "icon" ][ "tmp_name" ]);
         $content = ob_get_contents();
         ob_end_clean();
         $filename = str_replace( array( "\\", "'" ), array( "", "" ), $_FILES[ "icon" ][ "name" ] );
         if ($old_icon instanceof steam_document && $old_icon->check_access_write( $user )) {
            $new_icon = $old_icon;
            $new_icon->set_attribute("OBJ_NAME", $filename);
            $new_icon->set_content($content);
            $new_icon->set_attribute("DOC_MIME_TYPE", $_FILES[ "icon" ][ "type" ]);
         }
         else {
            $new_icon = steam_factory::create_document(
            $GLOBALS[ "STEAM" ]->get_id(),
            $filename,
            $content,
            $_FILES[ "icon" ][ "type" ],
            FALSE
            );
            $new_icon->set_attribute("OBJ_TYPE", "document_icon_usericon");
         }
         $user->set_attribute( "OBJ_ICON", $new_icon );
         $all_user = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), "sTeam" );
         $new_icon->set_read_access( $all_user );
         $GLOBALS[ "STEAM" ]->buffer_flush();
         // clean cache-related data
         clean_usericoncache($user);
         $portal->set_confirmation( gettext( "Your profile icon has been changed." ));
      }
      else
      {
         // print_r( $_FILES);
         $portal->set_problem_description( $problem, $hint );
      }
   }

}

$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "profile_icon.template.html" );
$content->setVariable( "INFO_TEXT", gettext( "Your buddy icon is what we use to represent you when you're in koaLA." ) );
$content->setVariable( "WINDOW_CONFIRM_TEXT", gettext( "Are you sure you want to delete your current buddy icon?" ) );
$content->setVariable( "LABEL_DELETE", gettext( "DELETE" ) );
$user->delete_value("OBJ_ICON");
$icon = $user->get_attribute( "OBJ_ICON" );
if ( $icon instanceof steam_object )
{
   $icon_id = $icon->get_id();
   // if user icon is acquired (= default icon) hide the delete button
   if ( is_object($user->get_acquire_attribute("OBJ_ICON")) ) {
      $content->setVariable( "HIDE_BUTTON", "style='display:none;'");
   }
}
else
{
   $icon_id = 0;
   $content->setVariable( "HIDE_BUTTON", "style='display:none;'");
}
// use it in 140x185 standard thumb size to optimize sharing of icon cache data
$icon_link = ( $icon_id == 0 ) ? PATH_STYLE . "images/anonymous.jpg" : PATH_URL . "cached/get_document.php?id=" . $icon_id . "&type=usericon&width=140&height=185";
$content->setVariable( "USER_IMAGE", $icon_link );
$content->setVariable( "LABEL_YOUR_BUDDY_ICON", gettext( "This is your buddy icon at the moment." ) );
$content->setVariable( "LABEL_REPLACE", gettext( "Replace with an image" ) );
$content->setVariable( "LABEL_UPLOAD_INFO", gettext( "The uploaded file has to be an image file (JPG, GIF or PNG), should have the dimensions of 140 x 185 pixels and <b>may not be larger than 250 KByte</b>. " ));
$content->setVariable( "LABEL_UPLOAD", gettext( "Upload" ) );

$breadcrumb = array(
					array( "name" => $user->get_attribute( "USER_FIRSTNAME" ) . " " . $user->get_attribute( "USER_FULLNAME" ), "link" => PATH_URL . "user/" . $user->get_name() . "/" ),
					array( "name" => gettext( "Profile" ), "link" => PATH_URL . "user/" . $user->get_name() . "/" ),
					array( "name" => gettext( "Your buddy icon" ) )		
					);

$portal->set_page_main($breadcrumb, $content->get(), "");
$portal->show_html();
?>