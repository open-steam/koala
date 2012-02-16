<?php
namespace Semester\Commands;

class Index extends \AbstractCommand implements \IFrameCommand {
	
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
		
		$path = $this->params;
		
		$user = \lms_steam::get_current_user();

		$content = \Semester::getInstance()->loadTemplate("courses_overview.template.html");

		$content->setVariable( "HELP_TEXT", "<b>".gettext('Notice').':</b> '.gettext('You can easily find courses by using the filter. Just type in a part of the course\'s title, it\'s ID or the name of the tutor.') );
		$content->setVariable('LABEL_FILTER',gettext('Filter'));
		
		
		/**
		 * Action Bar
		 */
		$content->setCurrentBlock("BLOCK_ACTIONBAR");
	  
		$isFiltered = false;
	  	$isEditMode = false;
	  	
	  	if(isset($path[0])) {
	  		if((\steam_factory::groupname_to_object($GLOBALS["STEAM"]->get_id(), "courses.".$path[0])) instanceof \steam_group)
	  			$current_semester = \steam_factory::groupname_to_object($GLOBALS["STEAM"]->get_id(), "courses.".$path[0]);
	  		else {
	  			ExtensionMaster::getInstance()->send404Error();
	  		}
	  			
	  	}
	  	else 
	  		$current_semester = \lms_steam::get_current_semester();
	  	
	  	$current_semester_name = $current_semester->get_name();
	  	
	  	if (isset($path[1]) && $path[1] == "booked") $isFiltered = true;
		if (isset($path[2]) && $path[2] == "edit") $isEditMode = true;
	  
	  	if ($isFiltered) {
	  		if(ALL_COURSES) {
	  			$content->setCurrentBlock("BLOCK_ALL_COURSES");
	          	$content->setVariable("LABEL_MY_COURSES", gettext("All courses"));
	          	$content->setVariable("LINK_MY_COURSES", PATH_URL."semester/index/".$current_semester_name."/all".(($isEditMode)? "/edit":"" ));
	  			$content->parse( "BLOCK_ALL_COURSES" );
	  		}
	  	}
	  	else {
	  		if(YOUR_COURSES) {
	  			$content->setCurrentBlock("BLOCK_YOUR_COURSES");
	          	$content->setVariable("LABEL_MY_COURSES", gettext("My courses"));
	          	$content->setVariable("LINK_MY_COURSES", PATH_URL."semester/index/".$current_semester_name."/booked".(($isEditMode)? "/edit":"" ));
	  			$content->parse("BLOCK_YOUR_COURSES");
	  	  	}
	  	}
	  
		if ($isEditMode) {
	   		$content->setCurrentBlock("BLOCK_EDIT_MODE");
	        $content->setVariable("LABEL_EDIT_MODE", gettext("Disable edit mode"));
	        $content->setVariable("LINK_EDIT_MODE", PATH_URL."semester/index/".$current_semester_name.(($isFiltered)? "/booked":"/all"));
	  		$content->parse("BLOCK_EDIT_MODE");
	  	}
	  	else {
	   		$content->setCurrentBlock("BLOCK_EDIT_MODE");
	        $content->setVariable("LABEL_EDIT_MODE", gettext("Enable edit mode"));
	        $content->setVariable("LINK_EDIT_MODE", PATH_URL."semester/index/".$current_semester_name.(($isFiltered)? "/booked":"/all")."/edit");
	  		$content->parse("BLOCK_EDIT_MODE");
	  	}
	  
	  	$is_steam_admin    = \lms_steam::is_steam_admin($user);
	  	if($is_steam_admin || \lms_steam::is_semester_admin($current_semester, $user)) {
			$content->setCurrentBlock("BLOCK_SEMESTER_ADMIN");
	        
			if(ADD_COURSE) {
	        	$content->setCurrentBlock("BLOCK_ADD_COURSE");
		        $content->setVariable("LINK_CREATE_COURSE", PATH_URL."semester/addCourse"."/".$current_semester_name);
		        $content->setVariable("LABEL_CREATE_COURSE", gettext("Create new course"));
		        $content->parse("BLOCK_ADD_COURSE");
			}
	        
			if(IMPORT_COURSE_FROM_PAUL) {
	        	$content->setCurrentBlock("BLOCK_IMPORT_COURSE_FROM_PAUL");
		        $content->setVariable("LINK_CREATE_PAUL_COURSE", PATH_URL."semester/importCourse"."/".$current_semester_name);
				$content->setVariable("LABEL_CREATE_PAUL_COURSE", gettext("Create new course via PAUL"));
		        $content->parse("BLOCK_IMPORT_COURSE_FROM_PAUL");
			}
	        
			if ($is_steam_admin) {
				$content->setCurrentBlock("BLOCK_SERVER_ADMIN");
				
				if(MANAGE_SEMESTER) {
					$content->setCurrentBlock( "BLOCK_MANAGE_SEMESTER" );
	                $content->setVariable("LINK_MANAGE_SEMESTER", PATH_URL."semester/manageSemester"."/".$current_semester_name);
	                $content->setVariable("LABEL_MANAGE_SEMESTER", gettext( "Manage this semester"));
	                $content->parse("BLOCK_MANAGE_SEMESTER");
				}
				
				if(ADD_SEMESTER) {
					$content->setCurrentBlock("BLOCK_ADD_SEMESTER");
	                $content->setVariable("LINK_CREATE_SEMESTER", PATH_URL."semester/addSemester");
	                $content->setVariable("LABEL_CREATE_SEMESTER", gettext("Create new semester"));
	                $content->parse("BLOCK_ADD_SEMESTER");
				}

				$content->parse("BLOCK_SERVER_ADMIN");
			}
	          
	        $content->parse("BLOCK_SEMESTER_ADMIN");
		}
	  	
		$content->parse("BLOCK_ACTIONBAR");
		
		
		

		// AUS DEM SYSTEM AUSLESEN
		$cache     = get_cache_function("ORGANIZATION", 600);
		$semesters = $cache->call("lms_steam::get_semesters");
		
		foreach( $semesters as $s )
		{
			$content->setCurrentBlock("BLOCK_TABS");
		    if ($s["OBJ_NAME" ] == $current_semester_name)
		    {
		    	$content->setVariable("TAB_STATE", "tabOut");
		        $content->setVariable("LINK_SEMESTER", $s["OBJ_NAME"]);
			}
		    else
		    {
		     	$content->setVariable("TAB_STATE", "tabIn");
		        $filter_part = "";
		        $content->setVariable("LINK_SEMESTER", "<a href=\"".PATH_URL."semester/index"."/".$s["OBJ_NAME"]."/".(($isFiltered)? "booked":"all")."\">".$s[ "OBJ_NAME" ]."</a>");
			}
		    $content->parse("BLOCK_TABS");
		}


		$courses = ($isFiltered)? $cache->call("lms_steam::semester_get_courses", $current_semester->get_id(), $user->get_name() ):$cache->call("lms_steam::semester_get_courses", $current_semester->get_id());
		$no_courses = count($courses);
		
		if($no_courses > 0) {
		
		  $content->setCurrentBlock("BLOCK_COURSES_AVAILABLE");
		  $content->setVariable("LABEL_ID", gettext("Course ID"));
		  $content->setVariable("LABEL_NAME", gettext("Course Name"));
		  $content->setVariable("LABEL_DESC", gettext("Information"));
		  $content->setVariable("LABEL_TUTORS", gettext("Staff members"));
		  $content->setVariable("LABEL_STUDENTS", gettext("Students"));
		  $content->setVariable("LABEL_ACTION", gettext("Action"));
		
		  $memberships = \lms_steam::semester_get_user_coursememberships($current_semester->get_id(), \lms_steam::get_current_user() );
		
			foreach($courses as $course) {
		    
			  	$course_found = TRUE;
			    
			
			    /* THIS WAS ALREADY COMMENTED OUT!
			    //Cannot be determined after performance optimization, so deleted courses remain in course list for CACHE_LIFETIME_STATIC (1 Hour)
			    if ( !isset( $memberships[ $course["OBJ_ID"] ] ) ) {
			      error_log("courses_overview.php: Found deleted course in cache-data of semester=" . $current_semester->get_name() . " courseid=" . $course["OBJ_NAME"] ." description=" . $course["OBJ_DESC"] . " objectid=" . $course[ "OBJ_ID" ]);
			      $course_found = FALSE;
			    }
			    */
				if ($course_found) {
			    	$is_subscribed = isset($memberships[$course["OBJ_ID"]]);
			      	$content->setCurrentBlock( "BLOCK_COURSE" );
			      	if ( \koala_group_course::is_paul_course( $course[ "COURSE_NUMBER" ] ) ) {
			      		$label_course_id = $course[ "COURSE_NUMBER" ];
			      	} 
			      	else {
			        	$label_course_id = \koala_group_course::convert_course_id($course[ "OBJ_NAME" ]);
			      	}
			      
			      	$actions = "";
			      	if ( $isEditMode )
			      	{
			      		$actions .= "<br><a href=\"" . PATH_URL . "course_delete.php?course=" . $course[ "OBJ_ID" ] . "\">" . gettext( "Delete course" ) . "</a>";
			      		$actions .= "<br><a href=\"" . PATH_URL . "copy_weblog_wiki.php?course=" . $course[ "OBJ_ID" ] . "\">" . gettext( "Copy Weblog/Wiki" ) . "</a>";
			      	}
			
			      	$content->setVariable( "VALUE_ID", h($label_course_id) );
			      	// TODO: Passt der Link?
			      	$content->setVariable( "COURSE_LINK", PATH_URL . SEMESTER_URL . "/" . h($current_semester->get_name()). "/" . h($course[ "OBJ_NAME" ]) . "/" );
			      	$content->setVariable( "COURSE_NAME", h($course[ "OBJ_DESC" ]) );
			      	$content->setVariable( "COURSE_TUTORS", h($course[ "COURSE_TUTORS" ]) );
			      	$content->setVariable( "VALUE_STUDENTS", $course[ "COURSE_NO_PARTICIPANTS" ] . ((isset($course["COURSE_MAX_PARTICIPANTS"]) &&  $course["COURSE_MAX_PARTICIPANTS"] > 0)?" / " . $course["COURSE_MAX_PARTICIPANTS"]:"") );
			      	$content->setVariable( "VALUE_COURSE_DESC", h($course[ "COURSE_SHORT_DSC" ]));
			      	
			      	if ( $is_subscribed ) {
			        	if ( $course[ "COURSE_HISLSF_ID" ] > 0 ) {
			          		$content->setVariable( "COURSE_ACTION", "Kursabmeldung erfolgt ausschlie&szlig;&uuml;ber <b><a href=\"https://lsf.uni-paderborn.de/qisserver/rds?state=wsearchv&search=2&veranstaltung.veranstid=" . trim( $course[ "COURSE_HISLSF_ID" ] ) . "\" target=\"_blank\">HIS-LSF</a></b>. Die Synchronisation mit koaLA kann bis zu einer Stunde dauern.");
			        	} 
			        	elseif ( $course[KOALA_GROUP_ACCESS] == PERMISSION_COURSE_PAUL_SYNC) {
			          		$content->setVariable( "COURSE_ACTION", gettext("You are member.") . "<br />" .  gettext("The participants for this course will be imported from the PAUL system as of 30.04.2009"));
			          		$noop = gettext("The participant management for this course is imported from PAUL. To unsubscribe this course unsubscribe this course in PAUL. Your unsubscription will be synchronized with koaLA within one hour.");
			        	}
			        	else {
			          		$content->setVariable( "COURSE_ACTION", "<a href=\"" . PATH_URL . "group_cancel.php?group=" . $course[ "OBJ_ID" ] . "\">" . gettext( "Resign" ) . "</a>" . $actions );
			        	}
			      	}
			      	else {
			        	if ( $course[ "COURSE_HISLSF_ID" ] > 0 ) {
			          		$content->setVariable( "COURSE_ACTION", "Kursbuchung erfolgt ausschlie&szlig;lich &uuml;ber <b><a href=\"https://lsf.uni-paderborn.de/qisserver/rds?state=wsearchv&search=2&veranstaltung.veranstid=" . trim( $course[ "COURSE_HISLSF_ID" ] ) . "\" target=\"_blank\">HIS-LSF</a></b>. Die Synchronisation mit koaLA kann bis zu einer Stunde dauern.");
			        	} 
			        	elseif ( $course[KOALA_GROUP_ACCESS] == PERMISSION_COURSE_PAUL_SYNC) {
			          		$content->setVariable( "COURSE_ACTION",  gettext("You are not member.") . "<br />" . gettext("The participants for this course will be imported from the PAUL system as of 30.04.2009"));
			          		$noop = gettext("The participant management for this course is imported from PAUL. To subscribe this course subscribe this course in PAUL. Your subscription will be synchronized with koaLA within one hour.");
			        	} 
			        	elseif ( isset($course["COURSE_MAX_PARTICIPANTS"]) && (int)$course["COURSE_MAX_PARTICIPANTS"] > 0 && (int)$course["COURSE_MAX_PARTICIPANTS"] <= (int)$course[ "COURSE_NO_PARTICIPANTS" ] ) {
			          		$content->setVariable( "COURSE_ACTION", gettext( "Group is full" ));
			        	}
			        	else {
			          		$content->setVariable( "COURSE_ACTION", "<a href=\"" . PATH_URL . "group_subscribe.php?group=" . $course[ "OBJ_ID" ] . "\">" . gettext( "Sign on" ) . "</a>" );    
			        	}
			      	}
			      	$content->parse( "BLOCK_COURSE" );
				}
			}
			$content->parse( "BLOCK_COURSES_AVAILABLE" );
		}
		else {
			$content->setCurrentBlock( "BLOCK_NO_COURSE" );
		  	$content->setVariable( "NO_COURSE_TEXT", gettext( "No courses available yet." ) );
		  	$content->parse( "BLOCK_NO_COURSE" );
		}

//$headline = ( isset($_GET[ "filter" ]) && $_GET[ "filter" ] == "booked" ) ? gettext( "My courses in %SEMESTER" ) : gettext( "All courses in %SEMESTER" );

//$portal->set_page_title( $current_semester->get_attribute( "OBJ_DESC" ));
//$portal->set_page_main( str_replace( "%SEMESTER", $current_semester->get_attribute( "OBJ_DESC" ), $headline), $content->get(), "" );
//$portal->show_html();

		
	
		$frameResponseObject->setTitle("Semester - " . $current_semester_name);
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($content->get());
		$frameResponseObject->addWidget($rawHtml);
		
		return $frameResponseObject;
		
	}
}

?>