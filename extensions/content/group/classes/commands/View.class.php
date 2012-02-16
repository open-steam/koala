<?php
namespace Group\Commands;

class View extends \AbstractCommand implements \IFrameCommand {
	
	private $params;
	
	public function validateData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		
		if (isset($this->params[0]))
			return true;
		else
			return false;
	}
	
	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		
		$path = $this->params;
		$user = \lms_steam::get_current_user();
		$public = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), STEAM_PUBLIC_GROUP, CLASS_GROUP);
		$id = $path[0];

		try {
		  $steam_group = ( ! empty( $id ) ) ? \steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $id) : FALSE;
		} catch (Exception $ex) {
		  include( "bad_link.php" );
		  exit;
		}
		$html_handler_group = new \koala_html_group( $steam_group );
		$html_handler_group->set_context( "start" );

		$group_is_private = FALSE;
		if ( $steam_group && is_object($steam_group) ) {
			switch( (string) $steam_group->get_attribute( "OBJ_TYPE" ) ) {
				case( "course" ):
					$group = new \koala_group_course( $steam_group );
					// TODO: Passt der backlink?
					$backlink = PATH_URL . SEMESTER_URL . "/" . $group->get_semester()->get_name() . "/" . h($group->get_name()) . "/";
				break;
				default:
					$group = new \koala_group_default( $steam_group );
					// TODO: Passt der backlink?
					$backlink = PATH_URL . "groups/" . $group->get_id() . "/";
				    // Determine if group is public or private
				    $parent = $group->get_parent_group();
				    if ($parent->get_id() == STEAM_PRIVATE_GROUP ) 
				    	$group_is_private = TRUE;
					break;
			}
		}

		if ($group_is_private) {
		  if ( !$steam_group->is_member( $user ) && !\lms_steam::is_koala_admin($user) )
		    throw new Exception( gettext( "You have no rights to access this group" ), E_USER_RIGHTS );
		}

		
		if ( $id != STEAM_PUBLIC_GROUP ) {
			//TODO: Somethings wrong here... again a 404 error while loading koala_html_group
			/*
			 $html_handler_group = new \koala_html_group( $group );
			$html_handler_group->set_context( "start" );
			*/
			$content = \Group::getInstance()->loadTemplate("group_start.template.html");
			$content->setVariable( "LABEL_DESCRIPTION", gettext( "Description" ) );
			
			$desc = $group->get_attribute("OBJ_DESC");
			
			if ( empty( $desc ) )
			{
				$content->setVariable( "OBJ_DESC", gettext( "No description available." ) );
			}
			else
			{
				$content->setVariable( "OBJ_DESC", get_formatted_output( $desc ) );
			}
			$about = $group->get_attribute( "OBJ_LONG_DSC" );
			if ( ! empty( $about ) )
			{
				$content->setCurrentBlock( "BLOCK_ABOUT" );
				$content->setVariable( "VALUE_ABOUT", get_formatted_output( $about ) );
				$content->parse( "BLOCK_ABOUT" );
			}
			$content->setVariable( "LABEL_ADMINS", gettext( "Moderated by" ) );
			
			if ($group->get_maxsize() > 0) {
			  $content->setCurrentBlock("BLOCK_GROUPSIZE");
			  $content->setVariable("LABEL_MAXSIZE_HEADER", gettext("The number of participants of this group is limited."));
			  $content->setVariable("LABEL_MAXSIZE_DESCRIPTION", str_replace("%MAX", $group->get_maxsize(), str_replace("%ACTUAL", $group->count_members() ,  gettext("The actual participant count is %ACTUAL of %MAX."))));
			  $content->parse("BLOCK_GROUPSIZE");
			}
			
			$admins = $group->get_admins();
			
			if ( count( $admins ) > 0  )
			{
			foreach( $admins as $admin )
			{
				$content->setCurrentBlock( "BLOCK_ADMIN" );
				$admin_attributes = $admin->get_attributes( array( "USER_FIRSTNAME", "USER_FULLNAME", "OBJ_ICON", "OBJ_DESC", "OBJ_NAME" ) );
				if ( $admin instanceof \steam_user )
				{
					$content->setVariable( "ADMIN_NAME", h($admin_attributes[ "USER_FIRSTNAME" ]) . " " . h($admin_attributes[ "USER_FULLNAME" ]) );
					$content->setVariable( "ADMIN_LINK", PATH_URL . "user/" . h($admin->get_name()) . "/" );
				}
				else
				{
					$content->setVariable( "ADMIN_NAME", h($admin_attributes[ "OBJ_NAME" ] ));
					$content->setVariable( "ADMIN_LINK", PATH_URL . "groups/" . $admin->get_id() . "/" );
				}
				$content->setVariable( "ADMIN_ICON", PATH_URL . "cached/get_document.php?id=" . $admin_attributes[ "OBJ_ICON" ]->get_id() . "&type=usericon&width=40&height=47" );
				
				$admin_desc = ( empty( $admin_attributes[ "OBJ_DESC" ] ) ) ? "student" :$admin_attributes[ "OBJ_DESC" ];
				$content->setVariable( "ADMIN_DESC", secure_gettext($admin_desc) );
				$content->parse( "BLOCK_ADMIN" );
			}
			}
			else
			{
				$content->setVariable( "LABEL_UNMODERATED", gettext( "Group is unmoderated." ) );
			}
			
			//TODO: Somethings wrong here... again a 404 error while loading koala_html_group
			//$html_handler_group->set_html_left( $content->get() );
			
			// TODO: Portal...!
			//$portal->set_page_main( $html_handler_group->get_headline(), $html_handler_group->get_html() , "" );
			//$portal->show_html();
		}
		else {
			//TODO: Wann wird das hier aufgerufen??		
			$public = \steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), STEAM_PUBLIC_GROUP, CLASS_GROUP );
			$user = \lms_steam::get_current_user();
			$content = \Group::getInstance()->loadTemplate("groups_public.template.html");
			
			if(MANAGE_GROUPS_MEMBERSHIP || CREATE_GROUPS){
				$content->setCurrentBlock( "BLOCK_ACTION_BAR_GROUPS" );
				if(MANAGE_GROUPS_MEMBERSHIP){
					$content->setCurrentBlock( "BLOCK_MANAGE_GROUPS_MEMBERSHIP" );
					$content->setVariable( "LINK_MANAGE_SUBSCRIPTIONS", PATH_URL . "user/" . $user->get_name(). "/groups/" );
					$content->setVariable( "LABEL_MANAGE_SUBSCRIPTIONS", gettext( "Manage subscriptions"  ) );
					$content->parse( "BLOCK_MANAGE_GROUPS_MEMBERSHIP" );
				}
				if(CREATE_GROUPS){
					$content->setCurrentBlock( "BLOCK_CREATE_GROUPS" );
					$content->setVariable( "LINK_CREATE_NEW_GROUP", PATH_URL . "groups_create_dsc.php?parent=" . (isset($_GET["cat"])?$_GET["cat"]:$public->get_id()) );
					$content->setVariable( "LABEL_CREATE_NEW_GROUP", gettext( "Create new group" ) );
					$content->parse( "BLOCK_CREATE_GROUPS" );
				}
				$content->parse( "BLOCK_ACTION_BAR_GROUPS" );
			}
			$content->setVariable( "LABEL_NAME", gettext( "Name" ) );
			$content->setVariable( "LABEL_DESC", gettext( "Description" ) );
			
			if ( ! empty( $_GET[ "cat" ] ) ) {
							
				// EINE KATEGORIE ANZEIGEN
				if ( ! $category = \steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $_GET[ "cat" ] ) ) {
					include( "bad_link.php" );
					exit;
				}
				if ( ! $category instanceof \steam_container ) {
					include( "bad_link.php" );
					exit;
				}
				$subgroups = $category->get_inventory( CLASS_GROUP );
				$content->setVariable( "LABEL_ALL_CATEGORIES", str_replace( array( "%i", "%NAME" ), array( count( $subgroups ), $category->get_name()), gettext( "%i groups in %NAME" ) ) );
				$content->setVariable( "LABEL_SUBGROUPS_MEMBERS", gettext( "Members" ) );
	        
		        $tnr = array();
		        $attributes = array(OBJ_NAME, OBJ_DESC);
		        
		        $tnr = array();
		        $attributes = array(OBJ_NAME, OBJ_DESC);
		        foreach( $subgroups as $subgroup ) {
			    	$tnr[$subgroup->get_id()] = array();
			        $tnr[$subgroup->get_id()][OBJ_NAME] = $subgroup->get_attribute(OBJ_NAME, TRUE);
			        $tnr[$subgroup->get_id()][OBJ_DESC] = $subgroup->get_attribute(OBJ_DESC, TRUE);
			        $tnr[$subgroup->get_id()]["membercount"] = $subgroup->count_members(TRUE);
			    }
		        $result = $GLOBALS["STEAM"]->buffer_flush();
			    foreach( $subgroups as $subgroup ) {
			    	$subgroup->set_value(OBJ_NAME, $result[$tnr[$subgroup->get_id()][OBJ_NAME]]);
			        $subgroup->set_value(OBJ_DESC, $result[$tnr[$subgroup->get_id()][OBJ_DESC]]);
			    }
			
			    usort( $subgroups, "sort_objects_new" );
			
				foreach( $subgroups as $subgroup ) {
				
					$content->setCurrentBlock( "BLOCK_CATEGORY_GROUP" );
					$content->setVariable( "VALUE_LINK", PATH_URL . "groups/" . $subgroup->get_id() . "/" );
					$content->setVariable( "VALUE_NAME", h($subgroup->get_name()) );
					$content->setVariable( "VALUE_SUBGROUPS_MEMBERS", $result[$subgroup->get_id()]["membercount"] );
					$content->setVariable( "VALUE_DESC", h($subgroup->get_attribute( "OBJ_DESC" )) );
					$content->parse( "BLOCK_CATEGORY_GROUP" );
				}
				$headline = array( array( "link" => PATH_URL . "groups/", "name" => gettext( "Public Groups" ) ), array( "link" => "", "name" => h($category->get_name()) ) );
			}
			else {
				
				// KATEGORIEN ANZEIGEN
				$categories = $public->get_workroom()->get_inventory( CLASS_ROOM | CLASS_CONTAINER );
				$content->setVariable( "LABEL_ALL_CATEGORIES", str_replace( "%i", count( $categories ), gettext( "%i categories in Public Groups" ) ) );
				$content->setVariable( "LABEL_SUBGROUPS_MEMBERS", gettext( "Groups" ) );
			      
				if ( count( $categories ) > 0 ) {
			          
					$tnr = array();
			        $attributes = array(OBJ_NAME, OBJ_DESC);
			        foreach( $categories as $category ) {
			        	
			        	$tnr[$category->get_id()] = array();
			            $tnr[$category->get_id()]["attributes"] = $category->get_attributes($attributes, TRUE);
			            $tnr[$category->get_id()]["inventory"] = $category->get_inventory_raw(CLASS_GROUP, TRUE);
			        }
			        $result = $GLOBALS["STEAM"]->buffer_flush();
			        foreach( $categories as $category ) {
			        	$category->set_value(OBJ_NAME, $result[$tnr[$category->get_id()]["attributes"]][OBJ_NAME]);
			            $category->set_value(OBJ_DESC, $result[$tnr[$category->get_id()]["attributes"]][OBJ_DESC]);
			        }
			          
			        usort( $categories, "sort_objects_new" );
			          
			        foreach( $categories as $category ) {
			        	
			        	$content->setCurrentBlock( "BLOCK_CATEGORY_GROUP" );
			            $content->setVariable( "VALUE_LINK", PATH_URL . "groups/?cat=" . $category->get_id() );
			            $content->setVariable( "VALUE_NAME", h($category->get_name()) );
			            $content->setVariable( "VALUE_DESC", h($category->get_attribute( OBJ_DESC )) );
			            $subgroups = $result[$tnr[$category->get_id()]["inventory"]];
			            $no_subgroups = count( $subgroups );
			            if ( $no_subgroups == 0 ) {
			            	$content->setVariable( "VALUE_SUBGROUPS_MEMBERS", "-" );
			            }
			            else {
			            	$content->setVariable( "VALUE_SUBGROUPS_MEMBERS", $no_subgroups );
			            }
			            $content->parse( "BLOCK_CATEGORY_GROUP" );
					}
				}
				$headline = gettext( "Public Groups" );
			}
			$portal->set_page_main( $headline, $content->get(), "" );
			$portal->show_html();
		}		
		
		$html_handler_group->set_html_left( $content->get() );
		
		$frameResponseObject->setTitle("Group");
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($html_handler_group->get_html());
		$frameResponseObject->addWidget($rawHtml);
		
		return $frameResponseObject;
	}
}

?>