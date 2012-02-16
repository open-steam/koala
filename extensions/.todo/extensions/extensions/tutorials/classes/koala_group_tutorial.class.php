<?php

define( 'PERMISSION_TUTORIAL_PUBLIC', 1 );
define( 'PERMISSION_TUTORIAL_PASSWORD', 2 );
define( 'PERMISSION_TUTORIAL_CONFIRMATION', 3 );
define( 'PERMISSION_TUTORIAL_PRIVATE', 4 );

define( 'PERMISSION_TUTORIAL_MATERIALS_COURSE', 1 );
define( 'PERMISSION_TUTORIAL_MATERIALS_TUTORS', 2 );
define( 'PERMISSION_TUTORIAL_MATERIALS_MEMBERS', 3 );

class koala_group_tutorial extends koala_group
{
				public $steam_group_semester;
				public $steam_group_course;
				public $steam_group_course_staff;
				public $steam_group_course_learners;
				private $tutorial_no;
				private $max_learners;
				private $tutor;
				private $short_desc;
				private $long_desc;

				public function __construct( $tutorial_steam_group )
				{
								parent::__construct( $tutorial_steam_group );
								$this->tutorial_no = $tutorial_steam_group->get_attribute("OBJ_NAME");
								$this->max_learners = ( $tutorial_steam_group->get_attribute("GROUP_MAXSIZE") != 0 ) ? $tutorial_steam_group->get_attribute("GROUP_MAXSIZE") : $tutorial_steam_group->get_attribute("TUTORIAL_MAX_LEARNERS");
								$this->tutor = $tutorial_steam_group->get_attribute("TUTORIAL_TUTOR");
								$this->short_desc = $tutorial_steam_group->get_attribute("OBJ_DESC");
								$this->long_desc = $tutorial_steam_group->get_attribute("TUTORIAL_LONG_DESC");
								$this->steam_group_semester  = $tutorial_steam_group->get_parent_group()->get_parent_group()->get_parent_group();
								$this->steam_group_course  = $tutorial_steam_group->get_parent_group()->get_parent_group();
								$this->steam_group_course_staff = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), $this->steam_group_course->get_groupname() . ".staff");
								$this->steam_group_course_learners = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), $this->steam_group_course->get_groupname() . ".learners");
				}

				public function get_display_name() {
					return str_replace( "%NR", $this->get_name(), gettext( "Tutorial %NR" ) );
				}

				public function get_url () {
					$course = new koala_group_course( $this->steam_group_course );
					return $course->get_url() . "tutorials/" . $this->get_id() . "/";
				}

				protected function get_link_path_internal ( $top_object )
				{
					$course = new koala_group_course( $this->steam_group_course );
					$link_path = array();
					// shift parent's link path left by two elements:
					foreach( $course->get_link_path( $top_object ) as $key => $value )
						$link_path[ $key - 2 ] = $value;
					// append tutorials and tutorial link:
					$link_path[ -2 ] = array( 'name' => gettext( 'Tutorials' ), 'link' => $link_path[-3]['link'] . 'tutorials/' );
					$link_path[ -1 ] = $this->get_link();
					return $link_path;
				}

				public function is_private() {
					if ( $this->get_steam_group()->get_attribute("TUTORIAL_PRIVATE") === "TRUE") return true;
					else return false;
				}

				public function get_maxsize() {
				  return $this->max_learners;
				}

				public function get_semester_group()
				{
								return $this->steam_group_semester;
				}

				public function get_course_group()
				{
						return $this->steam_group_course;
				}

				public function get_tutorial_number()
				{
							return $this->tutorial_no;
				}

				public function get_number_of_tutorials()
				{
						return $this->num_of_tutorials_in_course;
				}

				public function get_tutorial_type()
				{
								return $this->get_participant_mngmnt();
				}

				public function add_member( $user, $password = "" )
				{
								$cache = get_cache_function( $user->get_name() );
								$cache->drop( "lms_steam::user_get_groups", $user->get_name(), TRUE );
								$cache->drop( "lms_steam::user_get_groups", $user->get_name(), FALSE );
								$cache = get_cache_function( $this->get_id() );
								$cache->drop( "lms_steam::group_get_members", $this->get_id() );
								return $this->steam_object->add_member( $user, $password );
				}



				public function remove_member( $user )
				{
								$group = $this->steam_object;
								$cache = get_cache_function( $user->get_name() );
								$cache->drop( "lms_steam::user_get_groups", $user->get_name(), TRUE );
								$cache->drop( "lms_steam::user_get_groups", $user->get_name(), FALSE );
								$cache = get_cache_function( $group->get_id() );
								$cache->drop( "lms_steam::group_get_members", $group->get_id() );
								return $group->remove_member( $user );
				}


				public function get_tutorial_dsc_short()
				{
								return $this->short_desc;
				}

				public function get_tutorial_dsc_long()
				{
								return $this->long_desc;
				}


				public function get_learners()
				{
								return $this->get_members();
				}

				public function get_members()
				{
								return $this->steam_object->get_members();
				}

				public function get_admins()
				{
								return $this->steam_group_course_staff->get_members();
				}


				public function is_learner( $steam_user )
				{
								return $this->steam_object->is_member( $steam_user );
				}

				public function is_staff( $steam_user )
				{
								return $this->steam_group_course_staff->is_member( $steam_user );
				}

				public function is_admin( $steam_user )
				{
								return $this->is_staff( $steam_user );
				}

				public function is_member( $steam_user )
				{
								return $this->steam_object->is_member( $steam_user );
				}

				public function count_members()
				{
								return $this->steam_object->count_members();
				}

				public function get_participant_mngmnt()
				{
								return $this->steam_object->get_attribute( "TUTORIAL_PARTICIPANT_MNGMNT" );
				}

				public function is_password_protected()
				{
								return $this->get_steam_group()->has_password();
				}

				public function check_group_pw( $password )
				{
								return $this->steam_object->check_group_pw( $password );
				}

				public function is_moderated()
				{
								if ($this->is_private()) return false;
								$the_learners = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), $this->steam_group_course->get_groupname() . ".learners");
								return ! $this->get_steam_group()->check_access( SANCTION_INSERT, $the_learners );
				}

				public function add_membership_request( $user )
				{
								return $this->steam_object->add_membership_request( $user );
				}

				public function get_membership_requests()
				{
								return $this->steam_object->get_membership_requests();
				}

				public function requested_membership( $user )
				{
								return $this->steam_object->requested_membership( $user );
				}

				public function remove_membership_request( $user )
				{
								return $this->steam_object->remove_membership_request( $user );
				}

				public function get_workroom()
				{
						return $this->steam_object->get_workroom();
				}

				public function add_admin( $user )
				{
					return $this->steam_group_course_staff->add_member( $user );
				}

				public function get_obj_type()
				{
					return $this->get_attribute( "OBJ_TYPE" );
				}

	static public function get_access_descriptions () {
		return array(
			PERMISSION_UNDEFINED => array(
				"label" =>  gettext( "Not defined." ),
				"summary_short" => gettext( "-" ),
			),
			PERMISSION_TUTORIAL_PUBLIC => array(
				"label" =>  gettext( "All participants of the course can sign up for this tutorial without any limitation except the maximum number of learners of the course. The tutorial is accessible for all participants of the course." ),
				"summary_short" => gettext( "Option 1: <b>public tutorial</b>" ),
			),
			PERMISSION_TUTORIAL_PASSWORD => array(
				"label" => gettext( "Participants of the course hav to know a password that you can specify below to sign up for this tutorial. The tutorial is accessible only for registered tutorial participants." ),
				"summary_short" => gettext( "Option 2: <b>password protected tutorial</b>" ),
			),
			PERMISSION_TUTORIAL_CONFIRMATION => array(
				"label" => gettext( "The participation on this tutorial must be requested and confirmed by a course admin explicitely. The tutorial is accessible only for registered tutorial participants." ),
				"summary_short" => gettext( "Option 3: <b>Participant management through membership requests</b>" ),
			),
			PERMISSION_TUTORIAL_PRIVATE => array(
				"label" => gettext( "It is not possible to sign on to this tutorial. Participants can only be added by course admins." ),
				"summary_short" => gettext( "Option 4: <b>private tutorial</b>" ),
			)
		);
	}

	public function set_access ( $access = -1, $access_attribute_key = KOALA_GROUP_ACCESS ) {
		if ($access == PERMISSION_UNDEFINED) return "";
		if ( $access < 0 ) {
			throw new Exception( 'access key must be greater than zero', E_PARAMETER );
		}
		$all_users = steam_factory::groupname_to_object( $GLOBALS[ 'STEAM' ]->get_id(), STEAM_ALL_USER );
    $world_users = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), "Everyone" );

		// Generally reset access rights here to be able to repair access rights on older tutorials
		$this->steam_object->set_sanction_all( $this->steam_group_course_staff );
		$this->steam_object->sanction_meta( SANCTION_ALL, $this->steam_group_course_staff );

		$this->steam_object->set_sanction( $all_users, 0 );
    $this->steam_object->set_sanction( $world_users, 0 );
		// Disable acquiring
		$this->steam_object->set_acquire( FALSE );
		switch( $access )
		{
			case PERMISSION_TUTORIAL_PUBLIC:
				$this->steam_object->set_sanction( $world_users, SANCTION_READ|SANCTION_INSERT );
				break;
			case PERMISSION_TUTORIAL_PASSWORD:
				$this->steam_object->set_sanction( $this->steam_group_course_learners, SANCTION_READ );
				break;
			case PERMISSION_TUTORIAL_CONFIRMATION:
				$this->steam_object->set_sanction( $this->steam_group_course_learners, SANCTION_READ );
				break;
			case PERMISSION_TUTORIAL_PRIVATE:
				$this->steam_object->set_sanction( $this->steam_group_course_learners, SANCTION_READ );
				break;
			default:
				throw new Exception( 'Tried to set invalid access for tutorial group: access=' . $access, E_PARAMETER );
				break;
		}
		$this->set_attribute( $access_attribute_key, $access );
	}

	static public function get_workroom_access_descriptions () {
		return array(
			PERMISSION_UNDEFINED => array(
				"label" =>  gettext( "Not defined." ),
				"summary_short" => gettext( "-" ),
			),
			PERMISSION_TUTORIAL_MATERIALS_COURSE => array(
				"label" =>  gettext( "All registered participants of this course can read and comment the materials in it." ),
				"summary_short" => gettext( "Option 1" ),
			),
			PERMISSION_TUTORIAL_MATERIALS_TUTORS => array(
				"label" => gettext( "Only registered partcipants of this tutorial can read and comment the materials." ),
				"summary_short" => gettext( "Option 2" ),
			),
			PERMISSION_TUTORIAL_MATERIALS_MEMBERS => array(
				"label" => gettext( "In addition to option 2, the participants of the tutorial can upload their own materials." ),
				"summary_short" => gettext( "Option 3" ),
			)
		);
	}

	public function set_workroom_access ( $access = -1, $access_attribute_key = KOALA_ACCESS ) {
		if ($access == PERMISSION_UNDEFINED) return "";
		if ( $access < 0 ) {
			throw new Exception( 'access key must be greater than zero', E_PARAMETER );
		}
		$all_users = steam_factory::groupname_to_object( $GLOBALS[ 'STEAM' ]->get_id(), STEAM_ALL_USER );
		$workroom = $this->get_workroom();

		// Generally reset access rights here to be able to repair access rights on older tutorials
		$workroom->set_sanction_all( $this->steam_group_course_staff );
		$workroom->sanction_meta( SANCTION_ALL, $this->steam_group_course_staff );

		$workroom->set_sanction( $all_users, 0 );
		switch( $access )
		{
			case PERMISSION_TUTORIAL_MATERIALS_COURSE:
				$workroom->set_sanction( $this->steam_group_course_learners, SANCTION_READ|SANCTION_ANNOTATE );
				$workroom->set_sanction( $this->steam_object, SANCTION_READ|SANCTION_ANNOTATE );
				break;
			case PERMISSION_TUTORIAL_MATERIALS_TUTORS:
				$workroom->set_sanction( $this->steam_group_course_learners, 0 );
				$workroom->set_sanction( $this->steam_object, SANCTION_READ|SANCTION_ANNOTATE );
				break;
			case PERMISSION_TUTORIAL_MATERIALS_MEMBERS:
				$workroom->set_sanction( $this->steam_group_course_learners, 0 );
				$workroom->set_sanction( $this->steam_object, SANCTION_READ|SANCTION_ANNOTATE|SANCTION_INSERT );
				break;
			default:
				throw new Exception( 'Tried to set invalid access for tutorial workroom: access=' . $access, E_PARAMETER );
				break;
		}
		$this->set_attribute( $access_attribute_key, $access );
	}

	public function get_members_group() { return $this->steam_object; }
	public function get_staff_group() { return $this->steam_group_course->get_staff_group(); }
	public function get_admins_group() { return $this->steam_group_course->get_admins_group(); }

}
?>
