<?php

class koala_group_course extends koala_group
{
				public $steam_group_admins;
				public $steam_group_staff;
				public $steam_group_learners;
				public $steam_group_semester;

				public static function get_course_group( $steam_group, $vars = FALSE )
				{
          if (is_array($vars) && isset($vars["OBJ_TYPE"])) {
            $obj_type = $vars["OBJ_TYPE"];
          } else {
            $obj_type = $steam_group->get_attribute( OBJ_TYPE );
          }
					if ( $obj_type === "course" )
						return $steam_group;
            if ( $obj_type === "course_learners" || $obj_type === "course_staff" || $obj_type === "course_admins" ) {
              if (is_array($vars) && isset($vars["parentgroup"])) {
                return $vars["parentgroup"];
              } else {
                return $steam_group->get_parent_group();
              }
            }
					return FALSE;
				}

public function __construct( $steam_group )
{	
		  parent::__construct( $steam_group );
          $tnr = array();
          $tnr["groupname"] = $steam_group->get_groupname(TRUE);
          $tnr["name"] = $steam_group->get_name(TRUE);
          $tnr["OBJ_TYPE"] = $steam_group->get_attribute(OBJ_TYPE, TRUE);
          $tnr["parentgroup"] = $steam_group->get_parent_group(TRUE);
          $result = $GLOBALS["STEAM"]->buffer_flush();
          $name = $result[$tnr["name"]];
          $groupname = $result[$tnr["groupname"]];

          $vars["parentgroup"] = $result[$tnr["parentgroup"]];
          $vars["OBJ_TYPE"] = $result[$tnr["OBJ_TYPE"]];
          $course_group = self::get_course_group( $steam_group, $vars );

					if ( is_object( $course_group ) )
						$steam_group = $course_group;
					parent::__construct( $steam_group );
					if ( !is_object( $this->steam_group_staff = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), $groupname . ".staff" ) ) )
						throw new Exception( "Subgroup staff not found in course " . $name . "/" . $steam_group->get_id() );
					if ( !is_object( $this->steam_group_learners  = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), $groupname . ".learners" ) ) )
						throw new Exception( "Subgroup learners not found in course " . $name );
					$this->steam_group_admins = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), $groupname . ".admins" );
					// don't complain about missing admins group, since old courses don't have an admins subgroup...
					$this->steam_group_semester = $result[$tnr["parentgroup"]];
				}

				public function get_html_handler()
				{
					return new koala_html_course( $this );
				}

				public function get_display_name()
				{
					return h( $this->get_attribute( OBJ_DESC ) );
				}

				public function get_url()
				{
					return PATH_URL . SEMESTER_URL . "/" . $this->get_semester()->get_name() . "/" . $this->get_name() . "/";
				}

				public function get_maxsize() {
				  return $this->steam_group_learners->get_attribute("GROUP_MAXSIZE");
				}

				public function get_semester()
				{
								return $this->steam_group_semester;
				}

				public function get_course_name()
				{
              $this->get_attributes(array(OBJ_DESC, OBJ_NAME));
							return $this->get_attribute( OBJ_DESC );
				}

				public function add_member( $user, $password = "" )
				{
								$cache = get_cache_function( $user->get_name() );
								$cache->drop( "lms_steam::user_get_groups", $user->get_name(), TRUE );
								$cache->drop( "lms_steam::user_get_groups", $user->get_name(), FALSE );
								$cache = get_cache_function( $this->get_id() );
								$cache->drop( "lms_steam::group_get_members", $this->steam_group_learners->get_id() );
								 return $this->steam_group_learners->add_member( $user, $password );
				}

				public function add_admin( $user )
				{
								return $this->steam_group_staff->add_member( $user );
				}

				public function remove_member( $user )
				{
								if ( $this->is_learner( $user ) )
									$group = $this->steam_group_learners;
								elseif( $this->is_staff( $user ) )
									$group = $this->steam_group_staff;
								else
									return FALSE;
								$cache = get_cache_function( $user->get_name() );
								$cache->drop( "lms_steam::user_get_groups", $user->get_name(), TRUE );
								$cache->drop( "lms_steam::user_get_groups", $user->get_name(), FALSE );
								$cache = get_cache_function( $this->get_id() );
								$cache->drop( "lms_steam::group_get_members", $group->get_id() );
								return $group->remove_member( $user );
				}

				public function get_course_id( $cn = FALSE )
				{
          if ( $cn === FALSE) $cn = $this->get_attribute("COURSE_NUMBER");
          
					return $this->convert_course_id($this->get_name(), $cn);
				}

				public function get_course_dsc_short()
				{
								return $this->get_attribute( "COURSE_SHORT_DSC" );
				}

				public function get_course_dsc_long()
				{
								return $this->get_attribute( "COURSE_LONG_DSC" );
				}

				public function get_staff()
				{
								return $this->get_admins();
				}

				public function get_learners()
				{
								return $this->get_members();
				}

				public function get_admins()
				{
					return $this->steam_group_staff->get_members();
					//TODO: use admins group, not only staff
					//return $this->steam_group_admins->get_members();
				}

				public function get_group_learners()
				{
								return $this->steam_group_learners;
				}

				public function get_members_group() { return $this->steam_group_learners; }

				public function get_group_staff() {
					return $this->steam_group_staff;
				}

				public function get_staff_group() { return $this->steam_group_staff; }

				public function get_group_admins() {
					return $this->steam_group_staff;
					//TODO: use admins group, not only staff
					//return $this->steam_group_admins;
				}

				public function get_admins_group() { return $this->steam_group_admins; }

				public function get_members()
				{
								return $this->steam_group_learners->get_members();
				}

				public function is_learner( $steam_user )
				{
								return $this->steam_group_learners->is_member( $steam_user );
				}

				public function is_staff( $steam_user )
				{
								return $this->steam_group_staff->is_member( $steam_user );
				}

				public function is_admin( $steam_user )
				{
          $ret = $this->is_staff( $steam_user );
          if (!$ret) {
            $ret = lms_steam::is_koala_admin( $steam_user );
          }
					return $ret;
				}

				public function is_member( $steam_user )
				{
								return ( $this->steam_group_learners->is_member( $steam_user ) );
				}

				public function count_members()
				{
								return $this->steam_group_learners->count_members();
				}

				public function get_participant_mngmnt()
				{
								return $this->steam_group_course->get_attribute( "COURSE_PARTICIPANT_MNGMNT" );
				}

				public function is_password_protected()
				{
          if ( $this->get_attribute(KOALA_GROUP_ACCESS) != PERMISSION_COURSE_UNDEFINED && $this->get_attribute(KOALA_GROUP_ACCESS) != PERMISSION_COURSE_PASSWORD  ) return FALSE;
					return $this->steam_group_learners->has_password();
				}

				public function check_group_pw( $password )
				{
								return $this->steam_group_learners->check_group_pw( $password );
				}

				public function is_moderated()
				{
								// $user = lms_steam::get_current_user();
								$all_users = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), STEAM_ALL_USER );
                $world_users = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), "Everyone" );

								return !($this->steam_group_learners->check_access( SANCTION_INSERT, $all_users) || $this->steam_group_learners->check_access( SANCTION_INSERT, $world_users ) );
				}

				public function add_membership_request( $user )
				{
								return $this->steam_group_learners->add_membership_request( $user );
				}

				public function get_membership_requests()
				{
								return $this->steam_group_learners->get_membership_requests();
				}

				public function requested_membership( $user )
				{
								return $this->steam_group_learners->requested_membership( $user );
				}

				public function remove_membership_request( $user )
				{
								return $this->steam_group_learners->remove_membership_request( $user );
				}

				public function get_workroom()
				{
						return $this->steam_group_learners->get_workroom();
				}

        public function is_paul_course( $cn = FALSE ) {
          if ( $cn === FALSE) $cn = $this->get_attribute("COURSE_NUMBER");
          if (isset($cn) && is_string($cn) && $cn != "0" && strlen($cn) > 0) return TRUE;
          return FALSE;
        }

        /**
         *  encapsulated method to convert the manual PAUL-Numbers beginning
         *  with "L-" by replacing the "-" with ".".
         */
        public function convert_course_id( $id, $paul_id = FALSE ) {
          if ($paul_id !== FALSE && self::is_paul_course($paul_id)) $id = $paul_id;
          if (strpos( $id, "L-") === FALSE && strpos( $id, "K-") === FALSE) return $id;
          else {
            return str_replace("-", ".", $id);
          }
        }
        
        /**
         *  encapsulated method to convert the manual PAUL-Numbers beginning
         *  with "L-" by replacing the "-" with ".".
         */
        public static function s_convert_course_id( $id, $paul_id = FALSE ) {
          if ($paul_id !== FALSE && self::is_paul_course($paul_id)) $id = $paul_id;
          if (strpos( $id, "L-") === FALSE && strpos( $id, "K-") === FALSE) return $id;
          else {
            return str_replace("-", ".", $id);
          }
        }

        static public function get_access_descriptions($a = 0) {
          return array(
            PERMISSION_UNDEFINED => array(
              "label" =>  gettext( "Not defined." ),
              "summary_short" => gettext("-"),
            ),
            PERMISSION_COURSE_PUBLIC => array(
              "label" =>  gettext( "All users can join the course." ),
              "summary_short" => gettext("Public"),
            ),
            PERMISSION_COURSE_PASSWORD => array(
              "label" => gettext( "Only users which are aware of the course password are able to join the course." ) ,
              "summary_short" => gettext("Password"),
            ),
            PERMISSION_COURSE_CONFIRMATION => array(
              "label" => gettext( "Users can request membership and must be confirmed by the staff members in order to participate in this course." ) ,
              "summary_short" => gettext("Confirmation"),
            ),
            PERMISSION_COURSE_PAUL_SYNC => array(
              "label" => gettext( "The participants will be imported from the PAUL system. The maximum number of participants set in koaLA will be overridden if participants will be imported from PAUL. Participants will not be removed automatically if they applied for the course in koaLA before the PAUL Import method was chosen." ) . " <b>" . gettext("(The import of participants from PAUL will be started after the application for courses in PAUL is ended. The import of participants will start as of 30.04.2009)") . "</b>" ,
              "summary_short" => gettext("PAUL Import"),
            )

          );
        }

        public function set_access($access = -1, $learners = 0, $staff = 0, $admins = 0, $access_attribute_key = KOALA_GROUP_ACCESS, $a = 0, $b = 0) {
          if ($access == PERMISSION_UNDEFINED) return "";
          if ( !is_object( $learners ) ) {
            throw new Exception( "learners is no group object", E_PARAMETER );
          }
          if ( !is_object( $staff ) ) {
            throw new Exception( "staff is no group object", E_PARAMETER );
          }
          if ( $access < 0 ) {
            throw new Exception( "access key must be greater than zero", E_PARAMETER );
          }
          $all_users = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), STEAM_ALL_USER );
          $world_users = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), "Everyone" );

          // Generally reset access rights here to be able to repair access rights on older courses
          $staff->set_sanction_all( $staff );
          $staff->sanction_meta( SANCTION_ALL, $staff);
          $learners->set_sanction_all( $staff);
          $learners->sanction_meta( SANCTION_ALL, $staff );
          $this->steam_object->set_sanction_all( $staff );
          $this->steam_object->sanction_meta( SANCTION_ALL, $staff );

          if (is_object( $admins)) {
            $admins->set_sanction_all( $admins );
            $admins->sanction_meta( SANCTION_ALL, $admins);
            $staff->set_sanction_all( $admins );
            $staff->sanction_meta( SANCTION_ALL, $admins);
            $learners->set_sanction_all( $admins );
            $learners->sanction_meta( SANCTION_ALL, $admins );
            $this->steam_object->set_sanction_all( $admins );
            $this->steam_object->sanction_meta( SANCTION_ALL, $admins );
          }
          // Disable acquiring
          $learners->set_acquire(FALSE);
          switch( $access )
          {
            case (PERMISSION_COURSE_PAUL_SYNC):
              $learners->set_insert_access( $all_users, FALSE );
              $learners->set_insert_access( $world_users, FALSE );
              $learners->set_read_access( $world_users, FALSE );
            break;
            case( PERMISSION_COURSE_HISLSF ):
              // Set Access in Case of HIS Sync as before
              // TODO: Check if SANCTION_INSERT is required for group sTeam in case of LSF Sync because this may be used to get into the koala-course via the backend
              //$learners->set_insert_access( $all_users, TRUE );
              $learners->set_insert_access( $all_users, FALSE );
              $learners->set_insert_access( $world_users, FALSE );
              $learners->set_read_access( $world_users, FALSE );
              $access = PERMISSION_COURSE_HISLSF;
            break;
            case( PERMISSION_COURSE_PASSWORD ):
              $learners->set_insert_access( $all_users, FALSE );
              $learners->set_insert_access( $world_users, FALSE );
              $learners->set_read_access( $world_users, FALSE );
            break;
            case( PERMISSION_COURSE_CONFIRMATION ):
              $learners->set_insert_access( $world_users, FALSE );
              $learners->set_read_access( $world_users, FALSE );
              $learners->set_insert_access( $all_users, FALSE );
            break;
            case( PERMISSION_COURSE_PUBLIC ):
              $learners->set_insert_access( $world_users, TRUE );
              $learners->set_read_access( $world_users, TRUE );
              $learners->set_insert_access( $all_users, TRUE );
            break;
            default:
              throw new Exception( "try to set invalid access on course access=" . $access, E_PARAMETER );
            break;
          }
          $this->set_attribute($access_attribute_key, $access);
        }
}

?>