<?php
namespace Messageboard\Commands;

class NewMessageboard extends \AbstractCommand implements \IFrameCommand {
	
	private $params;
	
	public function validateData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		
		if (isset($this->params[0])) {
			return true;
		} 
		else {
			return false;
		}
	}
	
	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		
		$portal = \lms_portal::get_instance();
		
		if (!isset($messageboard) || !is_object($messageboard)) {
		  if ( empty( $this->params[0] ) )
		  throw new \Exception( "Environment not set." );
		  if ( empty( $this->params[1] ) )
		  throw new \Exception( "Group not set." );
		  
		  if ( ! $env = \steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $this->params[0] ) )
		  throw new \Exception( "Environment unknown." );
		  if ( ! $grp = \steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $this->params[1] ) )
		  throw new \Exception( "Group unknown" );
		}
		
		$accessmergel = FALSE;
		if (isset($messageboard) && is_object($messageboard)) {
		  $creator = $messageboard->get_creator();
		  if ($messageboard->get_attribute(KOALA_ACCESS) == PERMISSION_UNDEFINED && \lms_steam::get_current_user()->get_id() != $creator->get_id() && !\lms_steam::is_koala_admin( \lms_steam::get_current_user() )) {
		    $accessmergel = TRUE;
		  }
		}
			
		// TODO: Passt der link?
		$backlink = ( empty( $_POST["values"]["backlink"] ) ) ? $_SERVER[ "HTTP_REFERER" ] : $_POST[ "values" ][ "backlink" ];
		
		if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" )
		{
		   $values = $_POST[ "values" ];
			if ( get_magic_quotes_gpc() ) {
				if ( !empty( $values['name'] ) ) $values['name'] = stripslashes( $values['name'] );
				if ( !empty( $values['dsc'] ) ) $values['dsc'] = stripslashes( $values['dsc'] );
			}
		   if ( empty( $values[ "name" ] ) )
		   {
		      $problems = gettext( "The name of new message board is missing." );
		      $hints    = gettext( "Please type in a name." );
		   }
		
		   if ( strpos($values[ "name" ], "/" )) {
		     if (!isset($problems)) $problems = "";
		     $problems .= gettext("Please don't use the \"/\"-char in the the forum name.");
		   }
		   
		   if ( empty( $problems ) )
		   {
		      $group_members = $grp;
		      $group_admins = 0;
		      $group_staff = 0;
		
		      // check if group is a course
		      $grouptype = (string)$grp->get_attribute( "OBJ_TYPE" );
		      if ( $grouptype == "course" ) {
		        $group_staff = \steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), $grp->get_groupname() . ".staff" );
		        $group_admins = \steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), $grp->get_groupname() . ".admins" );
		        $group_members = \steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), $grp->get_groupname() . ".learners" );
		        $workroom = $group_members->get_workroom();
		      } else {
		        $workroom = $grp->get_workroom();
		      }
		
		      if (!isset($messageboard) || !is_object($messageboard)) {
		        $new_forum = \steam_factory::create_messageboard( $GLOBALS[ "STEAM" ]->get_id(), $values[ "name" ], $env, $values["dsc"] );
		        $_SESSION[ "confirmation" ] = str_replace( "%NAME", h($values[ "name" ]), gettext( "New forum '%NAME' created." ) );
		      } else {
		        $messageboard->set_attribute(OBJ_NAME, $values[ "name" ]);
		        $messageboard->set_attribute(OBJ_DESC, $values[ "dsc" ]);
		        $portal->set_confirmation( gettext( "The changes have been saved." ));
		        $new_forum = $messageboard;
		      }
		
		      $koala_forum = new \lms_forum( $new_forum );
		      $access = (int)$values[ "access" ];
		      $access_descriptions = \lms_forum::get_access_descriptions( $grp );
		      if (!$accessmergel) $koala_forum->set_access( $access, $access_descriptions[$access]["members"] , $access_descriptions[$access]["steam"], $group_members, $group_staff, $group_admins );
		
		      $GLOBALS[ "STEAM" ]->buffer_flush();
		      $cache = get_cache_function( \lms_steam::get_current_user()->get_name(), 600 );
		      $cache->drop( "lms_steam::get_inventory_recursive", $workroom->get_id(), CLASS_MESSAGEBOARD, array( "FORUM_LANGUAGE" ) );
		      $cache->drop( "lms_steam::get_group_communication_objects", $workroom->get_id(), CLASS_MESSAGEBOARD | CLASS_CALENDAR | CLASS_CONTAINER | CLASS_ROOM );
		      if (!isset($messageboard) || !is_object($messageboard)) {
		        header( "Location: " . $backlink );
		        exit;
		      }
		   }
		   else
		   {
		      $portal->set_problem_description( $problems, isset($hints)?$hints:"" );
		   }
		}
		
		$content = \Messageboard::getInstance()->loadTemplate("object_new.template.html");
		
		if (isset($messageboard) && is_object($messageboard)) { 
		  $content->setVariable( "INFO_TEXT", str_replace( "%NAME", h($messageboard->get_name()), gettext( "You are going to edit the forum '<b>%NAME</b>'." ) ) );
		  $content->setVariable( "LABEL_CREATE", gettext( "Save changes" ) );
		  $pagetitle = gettext( "Preferences" );
		  if (empty($values)) {
		    $values = array();
		    $values["name"] = $messageboard->get_name();
		    $values["dsc"] = $messageboard->get_attribute(OBJ_DESC);
		    $values["access"] = $messageboard->get_attribute(KOALA_ACCESS);
		  }
		  $breadcrumbheader = gettext("Preferences");
		}
		else {
		  $grpname = $grp->get_attribute(OBJ_NAME);
		  if ($grp->get_attribute(OBJ_TYPE) == "course") {
		    $grpname = $grp->get_attribute(OBJ_DESC);
		  }
		  $content->setVariable( "INFO_TEXT", str_replace( "%ENV", h( $grpname ), gettext( "You are going to create a new forum in '<b>%ENV</b>'." ) ) );
		  $content->setVariable( "LABEL_CREATE", gettext( "Create forum" ) );
		  $pagetitle = gettext( "Create forum" );
		  $breadcrumbheader = gettext("Add new forum");
		}
		
		if (!empty($values)) {
		  if (!empty($values["name"])) $content->setVariable("VALUE_NAME", h($values["name"]));
		  if (!empty($values["dsc"])) $content->setVariable("VALUE_DSC", h($values["dsc"]));
		}
		
		$content->setVariable( "VALUE_BACKLINK", $backlink );
		$content->setVariable( "BACKLINK", "<a href=\"$backlink\">" . gettext( "back" ) . "</a>" );
		$content->setVariable( "LABEL_NAME", gettext( "Name" ) );
		$content->setVariable( "LABEL_DSC", gettext( "Description" ) );
		
		$content->setVariable( "LABEL_BB_BOLD", gettext( "B" ) );
		$content->setVariable( "HINT_BB_BOLD", gettext( "boldface" ) );
		$content->setVariable( "LABEL_BB_ITALIC", gettext( "I" ) );
		$content->setVariable( "HINT_BB_ITALIC", gettext( "italic" ) );
		$content->setVariable( "LABEL_BB_UNDERLINE", gettext( "U" ) );
		$content->setVariable( "HINT_BB_UNDERLINE", gettext( "underline" ) );
		$content->setVariable( "LABEL_BB_STRIKETHROUGH", gettext( "S" ) );
		$content->setVariable( "HINT_BB_STRIKETHROUGH", gettext( "strikethrough" ) );
		$content->setVariable( "LABEL_BB_IMAGE", gettext( "IMG" ) );
		$content->setVariable( "HINT_BB_IMAGE", gettext( "image" ) );
		$content->setVariable( "LABEL_BB_URL", gettext( "URL" ) );
		$content->setVariable( "HINT_BB_URL", gettext( "web link" ) );
		$content->setVariable( "LABEL_BB_MAIL", gettext( "MAIL" ) );
		$content->setVariable( "HINT_BB_MAIL", gettext( "email link" ) );
		
		$content->setVariable( "LABEL_ACCESS", gettext( "Access") );
		
		if ((string) $grp->get_attribute( "OBJ_TYPE" ) == "course") {
		  $access_default = PERMISSION_PUBLIC;
		} else {
		  $access_default = PERMISSION_PUBLIC;
		}
		
		if ($accessmergel) {
		  $mailto = "mailto:'.SUPPORT_EMAIL.'?subject=KoaLA:%20Invalid%20Access%20Rights&body=" . rawurlencode( "\nLink: " . get_current_URL() . "\nCreator: " . $creator->get_identifier() . "\n" );
		  
		  $content->setCurrentBlock("BLOCK_ACCESSMERGEL");
		  $content->setVariable("LABEL_ACCESSMERGEL", str_replace("%MAILTO", $mailto, gettext( "There is a problem with the access settings. Please <a href=\"%MAILTO\">contact the support team</a> to fix it by setting the access rights again." )));
		  $content->parse("BLOCK_ACCESSMERGEL");
		}
		else {
		  $access = \lms_forum::get_access_descriptions( $grp );
		  if (is_array($access)) {
		    $content->setCurrentBlock("BLOCK_ACCESS");
		    foreach($access as $key => $array) {
		      if ( ($key != PERMISSION_UNDEFINED) || ((isset($values) && (int)$values[ "access" ] == PERMISSION_UNDEFINED ))) {
		        $content->setCurrentBlock("ACCESS");
		        $content->setVariable("LABEL", $array["summary_short"] . ": " .$array["label"]);
		        $content->setVariable("VALUE", $key);
		        if ((isset($values) && $key == (int)$values[ "access" ]) || (empty($values) && $key == $access_default)) {
		          $content->setVariable("CHECK", "checked=\"checked\"");
		        }
		        $content->parse("ACCESS");
		      }
		    }
		    $content->parse("BLOCK_ACCESS");
		  }
		}
		
		
		// TODO: Passt der link?
		$rootlink = \lms_steam::get_link_to_root( $grp );
		$headline = array( $rootlink[0], $rootlink[1], array("link" => $rootlink[1]["link"] . "communication/", "name" => gettext("Communication")));
		if (isset($messageboard) && is_object($messageboard)) {
		  $headline[] = array( "link" => PATH_URL . "forums/" . $messageboard->get_id() . "/", "name" => $messageboard->get_name() );
		}
		$headline[] = array( "link" => "", "name" =>  $breadcrumbheader );
		
		$frameResponseObject->setTitle("Messageboard");
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($content->get());
		$frameResponseObject->addWidget($rawHtml);
		
		return $frameResponseObject;
		
	}
}

?>