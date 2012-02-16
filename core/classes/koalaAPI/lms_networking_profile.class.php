<?php

class lms_networking_profile
{
				public  $steam_user;
				private $profile_object;

				public function __construct( $steam_user )
				{
								$this->steam_user = $steam_user;
								$profile_object = $steam_user->get_attribute( "LLMS_NETWORKING_PROFILE" );
								if ( ! $profile_object instanceof steam_object && lms_steam::get_current_user()->get_id() != $steam_user->get_id() )
												throw new Exception( "Networking profile not initialized", E_USER_NO_NETWORKINGPROFILE );
								$this->profile_object = $profile_object;
				}

				public function initialize( )
				{
								$profile_object = steam_factory::create_object( $GLOBALS[ "STEAM" ]->get_id(), "networking profile", CLASS_OBJECT );
								$all_user = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), STEAM_ALL_USER );
								$profile_object->set_sanction_all( $all_user );
								$guestbook = steam_factory::create_messageboard( $GLOBALS[ "STEAM" ]->get_id(), "guestbook", FALSE, "guestbook of " . $this->steam_user->get_attribute( "USER_FIRSTNAME" ) . " " . $this->steam_user->get_attribute( "USER_FULLNAME" ) );
								$guestbook->set_read_access( $all_user );
								$guestbook->set_annotate_access( $all_user, TRUE );
								$profile_object->set_attribute( "LLMS_GUESTBOOK", $guestbook );
								$this->steam_user->set_attribute( "LLMS_NETWORKING_PROFILE", $profile_object );
								$this->profile_object = $profile_object;
				}

				public function get_guestbook()
				{
								return $this->profile_object->get_attribute( "LLMS_GUESTBOOK" );
				}

				public function count_profile_visit( $visitor )
				{
								if ( ! $visitor instanceof steam_user )
												throw new Exception( "Visitor is no steam_user", E_PARAMETER );

								$no_visits = $this->get_profile_visits();

								if ( $visitor->get_id() == $this->steam_user->get_id() )
												return $no_visits; // DO NOT COUNT VISITS OF S.O. OWN PROFILE

								if ( is_object($this->get_last_visitor()) && $this->get_last_visitor()->get_id() != $visitor->get_id() )
								{
												$this->set_list_entry( "LLMS_PROFILE_VISITORS", array( "visitor" => $visitor, "referer" => $_SERVER[ "HTTP_REFERER" ] ) );
												$no_visits++;
												$this->profile_object->set_attribute( "LLMS_PROFILE_VISITS", $no_visits );

								}
								return $no_visits;
				}

				public function get_last_visitor()
				{
								$visitors = $this->get_current_visitors();
								if ( count( $visitors ) > 0 )
												return $visitors[ 0 ][ "visitor" ];
								else
												return $this->steam_user;
				}

				public function get_current_visitors()
				{
								$visitors = $this->profile_object->get_attribute( "LLMS_PROFILE_VISITORS" );	
								if ( ! is_array( $visitors ) )
												$visitors = array();
								return $visitors;
				}

				public function get_profile_visits()
				{
								return $this->profile_object->get_attribute( "LLMS_PROFILE_VISITS" );
				}

				private function set_list_entry( $str_attribute, $new_entry, $max_entries = 20 )
				{
								$attribute = $this->profile_object->get_attribute( $str_attribute );
								if ( ! is_array( $attribute ) )
								{
												$attribute = array();
								}
								array_unshift( $attribute, $new_entry );
								if ( count( $attribute ) >= $max_entries - 1 )
								{
												array_pop( $attribute );
								}
								$this->profile_object->set_attribute( $str_attribute, $attribute );
				}
}
?>
