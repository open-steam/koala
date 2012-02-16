<?php

require_once( PATH_LIB . "format_handling.inc.php" );

class koala_html_course extends koala_html
{
	private $course;

	public function __construct( $course )
	{
		if ( ! $course instanceof koala_group_course )
			throw new Exception( "not a group", E_PARAMETER );

		$this->course = $course;
		parent::__construct( PATH_EXTENSIONS . "content/course/ui/html/group_profile.template.html" );
	}

	public function set_context ( $context, $params = array() )
	{
		parent::set_context( $context, array_merge( $params, array( "owner" => $this->course ) ) );
	}

	public function get_headline ()
	{
		//$cache = get_cache_function( $this->course->get_id() );
		$headline = array();
		
		// try extensions:
		/*foreach ($this->course->get_extensions() as $extension)
		{
			$tmp_headline = $extension->get_headline( $headline, $this->get_context(), $this->get_context_params() );
			if ( is_array( $tmp_headline ) ) {
				break;
			}
			
		}*/
		
		$semester = $this->course->get_semester();
		$course_url = PATH_URL . "course/index" . "/" . h($semester->get_name()) . "/" . h($this->course->get_name()) . "/";
		
		if (SHOW_SEMESTER_IN_HEADLINE) {
			if (ADD_COURSE && ALL_COURSES) {
				$headline[-2] = array( "name" => h($semester->get_name()), "link" => PATH_URL . SEMESTER_URL . "/index/" . h($semester->get_name()) . "/" );
			} else {
				$headline[-2] = array( "name" => h($semester->get_name()) );
			}
		}

		if ( is_string( $context = $this->get_context() ) ) {
		    if ($context == "start") {
				$headline[-1] = array( "name" => $this->course->get_display_name() );
	        } else {
	        	$headline[-1] = array( "name" => $this->course->get_display_name(), "link" => $course_url );
	        }
	        		
			switch ( $context) {
				case "documents":
					//$headline[] = array( "name" => gettext( "Documents" ), "link" => $course_url . "documents/" );
					$headline[] = array( "name" => gettext( "Documents" ) );
					break;
				case "communication":
					$headline[] = array( "name" => gettext( "Communication" ) );
					break;
				case "members":
					$headline[] = array( "name" => gettext( "Learners" ) );
					break;
				case "staff":
					$headline[] = array( "name" => gettext( "Staff" ) );
					break;
				case "units":
					if ( is_array( $tmp_headline ) && count($tmp_headline ) > 0) {
						$headline[] = array( "name" => gettext( "Units" ), "link" => $course_url . "units/" );
						$headline = array_merge($headline, $tmp_headline);
					} else { 
						$headline[] = array( "name" => gettext( "Units" ) );
					}
					break;
			}

		}
		return $headline;
	}

	public function get_menu( $params = array() )
	{
		$steam_group = $this->course->get_steam_group();
    $steam_group->get_attributes(array( OBJ_NAME, "SEM_APP_ID" ));
		$path = PATH_URL . "course" . "/index/" . $this->course->get_semester()->get_name() . "/" . $steam_group->get_name() . "/";
		$menu = array();
		$menu[ "start" ] = array(
			"name" => gettext( "Start page" ),
			"link" => $path
		);
		if (COURSE_COMMUNICATION) $menu[ "communication" ] = array(
			"name" => gettext( "Communication" ),
			"link" => PATH_URL . "course" . "/communication/" . $this->course->get_semester()->get_name() . "/" . $steam_group->get_name() . "/"
		);

		if (!(COURSE_PARTICIPANTS_STAFF_ONLY && !$this->course->is_admin( lms_steam::get_current_user() ))) {
			if (COURSE_PARTICIPANTS && ($this->course->is_member( lms_steam::get_current_user() ) || $this->course->is_admin( lms_steam::get_current_user() )) )
			{
				$menu[ "members" ] = array(
					"name" => gettext( "Participants" ),
					"link" => PATH_URL . "course" . "/learners/" . $this->course->get_semester()->get_name() . "/" . $steam_group->get_name() . "/"
				);
			}
		}

		if (COURSE_SEM_APP_ENABLED) {
			$rlid = $steam_group->get_attribute( "SEM_APP_ID" );
			if ( ! empty( $rlid ) )
			{
				$menu[ "reserve_list" ] = array(
					"name" => gettext( "Reserve List" ),
					"link" => $path . "reserve_list/"
				);
			}
		}
		
		if ( $this->course->is_admin( lms_steam::get_current_user() ) )
		{
			$menu[ "staff" ] = array(
				"name" => gettext( "staff member" ),
				"link" => PATH_URL . "course" . "/staff/" . $this->course->get_semester()->get_name() . "/" . $steam_group->get_name() . "/"
			);
		}

		//TODO: extensionmanager
/*		// extensions menu entries:
		foreach ($this->course->get_extensions() as $extension)
		{
			$extension_menu = $extension->get_menu( $params );
			if ( is_array( $extension_menu ) && !empty( $extension_menu ) )
				$menu[ "{$extension->get_path_name()}" ] = $extension_menu;
		}*/
		return $menu;
	}

	public function get_context_menu ( $context, $params = array() )
	{
		if ( !isset($_SESSION[ "LMS_USER" ]) || !($_SESSION[ "LMS_USER" ] instanceof lms_user) || !$_SESSION[ "LMS_USER" ]->is_logged_in() )
			return array();

		$current_user   = lms_steam::get_current_user();
		$steam_group  = $this->course->get_steam_group();
		$learners_group = $this->course->get_group_learners();

		if ( !$this->course->is_member( $current_user ) && !$this->course->is_admin( $current_user ) )
		{
			if ( $context == "start" && (!$this->course->is_paul_course() && ($steam_group->get_attribute("COURSE_HISLSF_ID") == 0 || $steam_group->get_attribute("COURSE_HISLSF_ID") == "" ) && ($this->course->get_maxsize() == 0 || ($this->course->get_maxsize() > 0 && $this->course->get_maxsize() > $this->course->count_members()))) || $this->course->is_paul_course() && $this->course->get_attribute(KOALA_GROUP_ACCESS) != PERMISSION_COURSE_PAUL_SYNC  ) {
				return array(
					array( "link" => PATH_URL . "group_subscribe.php?group=" . $steam_group->get_id(),
						"name" => gettext( "Join this course" ) )
				);
			}
			return array();
		}

		$context_menu = array();
		switch ( $context ) {
      case "reserve_list":
     		$rl = $steam_group->get_attribute( "SEM_APP_ID" );
        if ( !empty( $rl[ "SEM_APP_ID" ] ) ) {
          $context_menu[] = array( "link" => PATH_URL . "courses_edit_reserve_list.php?course=" . $steam_group->get_id() . "&rlid=" . $rl,
          "name" => gettext( "Edit reserve list" ) );
          $context_menu[] = array( "link" => PATH_URL . "courses_remove_reserve_list.php?course=" . $steam_group->get_id(),
          "name" => gettext( "Remove reserve list" ) );
        }
        break;
			case "start":
				if (COURSE_PREFERENCES) {
					if (COURSE_KOALAADMIN_ONLY) {
						if (lms_steam::is_koala_admin($current_user)) {
							$context_menu[] = array( "link" => PATH_URL . "course" . "/edit/" . $this->course->get_semester()->get_name() . "/" . $steam_group->get_name() . "/",
								"name" => gettext( "Preferences" ) );
						}
					} else if ( $this->course->is_admin( $current_user ) ) {
						$context_menu[] = array( "link" => PATH_URL . "course" . "/edit/" . $this->course->get_semester()->get_name() . "/" . $steam_group->get_name() . "/",
							"name" => gettext( "Preferences" ) );
						//$context_menu[] = array( "link" => PATH_URL . "group_add_admin.php?group=" . $steam_group->get_id() ,	"name" => gettext( "Add staff member" ) );
				       		$rl = $steam_group->get_attribute( "SEM_APP_ID" );
				          if ( empty( $rl[ "SEM_APP_ID" ] ) ) {
				            if (defined("COURSE_SEM_APP_ENABLED") && COURSE_SEM_APP_ENABLED && defined("SEM_APP_BASE_URL") && SEM_APP_BASE_URL != "") {
				            $context_menu[] = array( "link" => PATH_URL . "courses_add_reserve_list.php?course=" . $steam_group->get_id(),
				              "name" => gettext( "Add reserve list" ) );
				            }
				          }
					}
				}
        if ( COURSE_LEAVE && ((!$this->course->is_paul_course() && ($steam_group->get_attribute("COURSE_HISLSF_ID") == 0 || $steam_group->get_attribute("COURSE_HISLSF_ID") == "" )) || ($this->course->is_paul_course() && $this->course->get_attribute(KOALA_GROUP_ACCESS) != PERMISSION_COURSE_PAUL_SYNC)) ) {
          $context_menu[] = array( "link" => PATH_URL . "group_cancel.php?group=" . $steam_group->get_id(), "name" => gettext( "Cancel membership" ) );
        }
				break;
			case "members":
				if ( lms_steam::is_koala_admin($current_user) || (!COURSE_KOALAADMIN_ONLY && $this->course->is_admin( $current_user )) ) {
					(!COURSE_PARTICIPANTS_CIRCULAR) or $context_menu[] = array( "link" => PATH_URL . "messages_write.php?group=" . $steam_group->get_id(), "name" => gettext( "Write circular" ) );
					(!COURSE_PARTICIPANTS_EXCEL_LIST) or $context_menu[] = array( "link" => PATH_URL . SEMESTER_URL . "/" . $this->course->get_semester()->get_name() . "/" . $steam_group->get_name() . "/learners/excel/", "name" => gettext( "Participant list (Excel)" ) );
					(!COURSE_PARTICIPANTS_ATTENDANCE_LIST) or $context_menu[] = array( "link" => PATH_URL . SEMESTER_URL . "/" . $this->course->get_semester()->get_name() . "/" . $steam_group->get_name() . "/learners/excel/attendance", "name" => gettext( "Attendance list (Excel)" ) );
					(!COURSE_PARTICIPANTS_MANAGE_MEMBERSHIP_REQUESTS) or $context_menu[] = array( "link" => PATH_URL . SEMESTER_URL . "/" . $this->course->get_semester()->get_name() . "/" . $steam_group->get_name() . "/requests/", "name" => gettext( "Manage membership requests" ) );
					(!COURSE_PARTICIPANTS_ADD_MEMBER) or $context_menu[] = array( "link" => PATH_URL . "group_add_member.php?group=" . $steam_group->get_id(), "name" => gettext( "Add member" ) );
				}
				break;
			case "staff":
				if (lms_steam::is_koala_admin($current_user) || (!COURSE_KOALAADMIN_ONLY && $this->course->is_admin( $current_user )) ) {
					(!COURSE_STAFF_CIRCULAR) or $context_menu[] = array( "link" => PATH_URL . "messages_write.php?group=" . $steam_group->get_id(), "name" => gettext( "Write circular" ) );
					(!COURSE_STAFF_EXCEL_LIST) or $context_menu[] = array( "link" => PATH_URL . SEMESTER_URL . "/" . $this->course->get_semester()->get_name() . "/" . $steam_group->get_name() . "/staff/excel/", "name" => gettext( "staff member list (Excel)" ) );
					//$context_menu[] = array( "link" => PATH_URL . SEMESTER_URL . "/" . $this->course->get_semester()->get_name() . "/" . $steam_group->get_name() . "/learners/excel/attendance", "name" => gettext( "Attendance list (Excel)" ) );
					//$context_menu[] = array( "link" => PATH_URL . SEMESTER_URL . "/" . $this->course->get_semester()->get_name() . "/" . $steam_group->get_name() . "/requests/", "name" => gettext( "Manage membership requests" ) );
					(!COURSE_STAFF_ADD_MEMBER) or $context_menu[] = array( "link" => PATH_URL . "group_add_admin.php?group=" . $steam_group->get_id(), "name" => gettext( "Add staff member" ) );
				}
				break;
			case "communication":
				if ( $learners_group->get_workroom()->check_access_insert( $current_user ) ) {
					$context_menu[] = array( "link" => PATH_URL . "weblog_new.php?env=" . $learners_group->get_workroom()->get_id() . "&group=" . $steam_group->get_id() , "name" => gettext( "Create new weblog" ) );
					$context_menu[] = array( "link"  => PATH_URL . "forum_new.php?env=" . $learners_group->get_workroom()->get_id() . "&group=" . $steam_group->get_id() , "name" => gettext( "Create new forum" ) );
					$context_menu[] = array( "link" => PATH_URL . "wiki_new.php?env=" . $learners_group->get_workroom()->get_id() . "&group=" . $steam_group->get_id() , "name" => gettext( "Create new wiki" ) );
				}
				break;
		}

		//TODO: extensionmanager
/*		// extensions context menu entries:
		foreach ($this->course->get_extensions() as $extension)
		{
			$extension_context_menu = $extension->get_context_menu( $context, $params );
			if ( is_array( $extension_context_menu ) && !empty( $extension_context_menu ) ) {
				$context_menu = array_merge( $context_menu, $extension_context_menu );
			}
		}*/
		return $context_menu;
	}

    public function set_html_left( $html_code )
    {
        $this->template->setVariable( "HTML_CODE_LEFT", $html_code );
    }

    public function set_html_right( $html_code )
    {
        $this->template->setVariable( "HTML_CODE_RIGHT", $html_code );
    }

    public function get_discussion()
    {
        $workroom = $this->course->get_steam_group()->get_workroom();
        if ( ! $discussion = $workroom->get_object_by_name( "public_discussion" ) )
        {
            return FALSE;
        }
        else
        {
            return $discussion->get_id();
        }
    }

    public function get_html()
    {
        return $this->template->get();
    }
}

?>
