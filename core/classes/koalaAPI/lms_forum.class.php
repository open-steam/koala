<?php

//require_once( PATH_ETC . "koala.conf.php" );
require_once( PATH_LIB . "format_handling.inc.php" );

function sort_discussions( $a, $b )
{
				if ( $a[ "LATEST_POST_TS" ] == $b[ "LATEST_POST_TS" ] )
				{
								return 0;
				}
				return ( $a[ "LATEST_POST_TS" ] > $b[ "LATEST_POST_TS" ] ) ? -1 : 1;
}

class lms_forum extends koala_object
{
				private $steam_forum;

				public function __construct( $steam_forum )
				{
          if ( ! $steam_forum instanceof steam_messageboard )
          {
                  throw new Exception( "not a forum", E_PARAMETER );
          }
          $this->steam_forum = $steam_forum;
          $this->steam_object = $steam_forum;
				}


				public function get_discussions( $forum_id, $search_result = FALSE, $order = "OBJ_CREATION_TIME" )
				{
				  if ( is_array( $search_result ) ) {
            $discussions = array();
            $tnr = array();
            foreach( $search_result as $annotation ) {
              if ( $annotation instanceof steam_messageboard )
                continue;
              $tnr[$annotation->get_id()] = $annotation->get_annotating(TRUE);
            }
            $buffer_result = $GLOBALS["STEAM"]->buffer_flush();

            foreach( $search_result as $annotation ) {
              if ( $annotation instanceof steam_messageboard )
              continue;
              $parent = $buffer_result[ $tnr[$annotation->get_id()] ];
              if ( $parent instanceof steam_messageboard ) {
                // Annotation is a thread we can add here
                $discussions[ $annotation->get_id() ] = $annotation;
              }
              else {
                // Annotation is a reply, so add the whole thread
                $discussions[ $parent->get_id() ] = $parent;
              }
            }
				  }
				  else {
            $steam_messageboard = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $forum_id, CLASS_MESSAGEBOARD );
            try {
              $discussions = $steam_messageboard->get_annotations();
            } catch (Exception $ex) {
              $discussions = array();
            }
				  }
				  $tmp = array();
				  $i = 0;
				  $query = array("OBJ_NAME", "OBJ_CREATION_TIME", "DOC_MIME_TYPE");

          $basic_tnr = array();
				  foreach( $discussions as $discussion ) {
            $basic_tnr[ $i ] = array();
            $basic_tnr[ $i ]["ATTRIBUTES"] = $discussion->get_attributes( $query, TRUE );
            $basic_tnr[ $i ]["AUTHOR"] = $author = $discussion->get_creator(TRUE);
            $basic_tnr[ $i ]["ANNOTATIONS"] = $discussion->get_annotations(FALSE, TRUE);
            $i++;
				  }
          $basic_result = $GLOBALS["STEAM"]->buffer_flush();

          $i = 0;
          $data_tnr = array();
          $latestid_result = array();
				  foreach( $discussions as $discussion ) {
            $data_tnr[ $i ] = array();
            if ( strstr($basic_result[ $basic_tnr[ $i ]["ATTRIBUTES"] ]["DOC_MIME_TYPE"], "text") )
              $data_tnr[ $i ][ "CONTENT" ] = $discussion->get_content(TRUE);

            $data_tnr[$i]["AUTHOR_ATTRIBUTES"] = $basic_result[$basic_tnr[ $i ]["AUTHOR"]]->get_attributes( array( "OBJ_NAME", "USER_FULLNAME", "USER_FIRSTNAME", "OBJ_ICON", "USER_ACADEMIC_TITLE" ), TRUE );
            $annotations = $basic_result[$basic_tnr[ $i ]["ANNOTATIONS"]];
            $replies = count( $annotations );
            if ( is_array( $annotations ) && $replies > 0 ) {
              $data_tnr[ $i ][ "REPLIES" ] = $replies;
              $latest_post = $annotations[ 0 ];

              $data_tnr[ $i ][ "LATEST_POST_ATTRIBUTES" ] = $latest_post->get_attributes( array("OBJ_CREATION_TIME", "OBJ_NAME"), TRUE );
              $data_tnr[ $i ][ "LATEST_POST_CREATOR" ] = $latest_post->get_creator(TRUE);
              $latestid_result[ $i ] = $latest_post->get_id();
            } else {
              $data_tnr[ $i ][ "LATEST_POST_ATTRIBUTES" ] = $discussion->get_attributes( array("OBJ_CREATION_TIME", "OBJ_NAME"), TRUE );
              $data_tnr[ $i ][ "LATEST_POST_CREATOR" ] = $discussion->get_creator(TRUE);
              $latestid_result[ $i ] = $discussion->get_id();
            }
            $i++;
				  }
          $data_result = $GLOBALS["STEAM"]->buffer_flush();

          $latestauthor_tnr = array();
          $i = 0;
				  foreach( $discussions as $discussion ) {
            $annotations = $basic_result[$basic_tnr[ $i ]["ANNOTATIONS"]];
            $replies = count( $annotations );
            if (  is_array( $annotations ) && $replies > 0 ) {
              $latestauthor_tnr[$i] = array();
              $latest_post = $annotations[ 0 ];
              $latestauthor_tnr[$i] = $data_result[$data_tnr[ $i ][ "LATEST_POST_CREATOR" ]]->get_attributes( array( "USER_FULLNAME", "USER_FIRSTNAME", "USER_ACADEMIC_TITLE", "OBJ_ICON" ), TRUE );
            } else {
              $latestauthor_tnr[$i] = array();
              $latest_post = $discussion;
              $latestauthor_tnr[$i] = $data_result[$data_tnr[ $i ][ "LATEST_POST_CREATOR" ]]->get_attributes( array( "USER_FULLNAME", "USER_FIRSTNAME", "USER_ACADEMIC_TITLE", "OBJ_ICON" ), TRUE );
            }
            $i++;
				  }
          $latestauthor_result = $GLOBALS["STEAM"]->buffer_flush();

          // Ergebnisarray aus den bisher geholten Daten generieren
          // Ab hier keine Serveranfrage mehr
          $tmp = array();
          $i = 0;
				  foreach( $discussions as $discussion ) {
            $tmp[ $i ] = $basic_result[$basic_tnr[ $i ]["ATTRIBUTES"]];
            $tmp[ $i ][ "OBJ_ID" ] = $discussion->get_id();
            if ( !isset($data_result[$data_tnr[ $i ][ "CONTENT" ]]) )
              $tmp[ $i ][ "CONTENT" ] = "";
            else
              $tmp[ $i ][ "CONTENT" ] = $data_result[$data_tnr[ $i ][ "CONTENT" ]];
            $author_data = $data_result[$data_tnr[$i]["AUTHOR_ATTRIBUTES"]];
            $icon = $author_data[ "OBJ_ICON" ];
            if ( $icon instanceof steam_object ) {
              $icon_id = $icon->get_id();
            }
            else {
              $icon_id = 0;
            }
            $tmp[$i][ "OBJ_ICON" ] = $icon_id;
            $tmp[$i][ "AUTHOR_LOGIN" ] = $author_data["OBJ_NAME"];
            //$tmp[ $i ] = array_merge ( $tmp[ $i ], $author_data );
            $tmp[ $i ]["USER_FIRSTNAME"] = $author_data["USER_FIRSTNAME"];
            $tmp[ $i ]["USER_FULLNAME"] = $author_data["USER_FULLNAME"];
            $annotations = $basic_result[$basic_tnr[ $i ]["ANNOTATIONS"]];
            $replies = count( $annotations );
            $tmp[ $i ][ "REPLIES" ] = $replies;
            $tmp[ $i ][ "LATEST_POST_TS" ] = $data_result[$data_tnr[ $i ][ "LATEST_POST_ATTRIBUTES" ]]["OBJ_CREATION_TIME" ];
            $tmp[ $i ][ "LATEST_POST_TITLE" ] = $data_result[$data_tnr[ $i ][ "LATEST_POST_ATTRIBUTES" ]]["OBJ_NAME" ];
            $tmp[ $i ][ "LATEST_POST_ID" ] = $latestid_result[ $i ];
            $title = ( ! empty( $latestauthor_result[$latestauthor_tnr[$i]]["USER_ACADEMIC_TITLE"] ) ) ? $latestauthor_result[$latestauthor_tnr[$i]]["USER_ACADEMIC_TITLE"] . " " : "";
            $tmp[ $i ][ "LATEST_POST_AUTHOR" ] = $title . $latestauthor_result[$latestauthor_tnr[$i]][ "USER_FIRSTNAME" ] . " " . $latestauthor_result[$latestauthor_tnr[$i]][ "USER_FULLNAME" ];
            $i++;

				  }
				  usort( $tmp, "sort_discussions" );
				  return $tmp;
				}

        // TODO: Delete (only kept because of the complexity, maybe a rollback
        // will be necessary, we'll see....
				public function get_discussions_old( $forum_id, $search_result = FALSE, $order = "OBJ_CREATION_TIME" )
				{
				  if ( is_array( $search_result ) ) {
            $discussions = array();
            foreach( $search_result as $annotation ) {
              // TODO:
              if ( $annotation instanceof steam_messageboard )
              continue;

              $parent = $annotation->get_annotating();
              if ( $parent instanceof steam_messageboard ) {
              // Annotation is a thread we can add here
              $discussions[ $annotation->get_id() ] = $annotation;
              }
              else {
              // Annotation is a reply, so add the whole thread
              $discussions[ $parent->get_id() ] = $parent;
              }
            }
				  }
				  else {
            $steam_messageboard = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $forum_id, CLASS_MESSAGEBOARD );
            try {
              $discussions = $steam_messageboard->get_annotations();
            } catch (Exception $ex) {
              $discussions = array();
            }
				  }
				  $tmp = array();
				  $i = 0;
				  $query = array("OBJ_NAME", "OBJ_CREATION_TIME", "DOC_MIME_TYPE");
				  foreach( $discussions as $discussion ) {
            $tmp[ $i ] = $discussion->get_attributes( $query );
            $tmp[ $i ][ "OBJ_ID" ] = $discussion->get_id();
            if ( !strstr($tmp[$i]["DOC_MIME_TYPE"], "text") )
              $tmp[ $i ][ "CONTENT" ] = "";
            else
              $tmp[ $i ][ "CONTENT" ] = $discussion->get_content();

            $author = $discussion->get_creator();
            $author_data = $author->get_attributes( array( "USER_FULLNAME", "USER_FIRSTNAME", "OBJ_ICON", "USER_ACADEMIC_TITLE" ) );
            $icon = $author_data[ "OBJ_ICON" ];
            if ( $icon instanceof steam_object ) {
              $icon_id = $icon->get_id();
            }
            else {
              $icon_id = 0;
            }
            $author_data[ "OBJ_ICON" ] = $icon_id;
            $author_data[ "AUTHOR_LOGIN" ] = $author->get_name();
            $tmp[ $i ] = array_merge ( $tmp[ $i ], $author_data );
            $annotations = $discussion->get_annotations();
            $replies = count( $annotations );
            if (  is_array( $annotations ) && $replies > 0 ) {
              $tmp[ $i ][ "REPLIES" ] = $replies;
              $latest_post = $annotations[ 0 ];

              $tmp[ $i ][ "LATEST_POST_TS" ] = $latest_post->get_attribute( "OBJ_CREATION_TIME" );
              $tmp[ $i ][ "LATEST_POST_TITLE" ] = $latest_post->get_name();
              $tmp[ $i ][ "LATEST_POST_ID" ] = $latest_post->get_id();
              $lp_creator = $latest_post->get_creator();
              $lpca = $lp_creator->get_attributes( array( "USER_FULLNAME", "USER_FIRSTNAME", "USER_ACADEMIC_TITLE" ) );
              $title = ( ! empty( $lpca[ "USER_ACADEMIC_TITLE" ] ) ) ? $lpca[ "USER_ACADEMIC_TITLE" ] . " " : "";
              $tmp[ $i ][ "LATEST_POST_AUTHOR" ] = $title . $lpca[ "USER_FIRSTNAME" ] . " " . $lpca[ "USER_FULLNAME" ];
            }
            else {
              $tmp[ $i ][ "REPLIES" ] = 0;
              $tmp[ $i ][ "LATEST_POST_TS" ]    = $tmp[ $i ][ "OBJ_CREATION_TIME" ];
              $tmp[ $i ][ "LATEST_POST_ID" ] = $tmp[ $i ][ "OBJ_ID" ];
              $tmp[ $i ][ "LATEST_POST_TITLE" ] = $tmp[ $i ][ "OBJ_NAME" ];
              $title = ( ! empty( $author_data[ "USER_ACADMIC_TITLE" ] ) ) ? $author_data[ "USER_ACADMIC_TITLE" ] . " " : "";
              $tmp[ $i ][ "LATEST_POST_AUTHOR" ] = $title . $author_data[ "USER_FIRSTNAME" ] . " " . $author_data[ "USER_FULLNAME" ];
            }
            $i++;

				  }
				  usort( $tmp, "sort_discussions" );
				  return $tmp;
				}

				public function search_pattern( $messageboard_id, $pattern )
				{
                if (defined("LOG_DEBUGLOG")) {
                  $time1 = microtime(TRUE);
                  logging::write_log( LOG_DEBUGLOG, "lms_forum::search_pattern(" . $messageboard_id . ", " . $pattern . ") \t" . $GLOBALS["STEAM"]->get_login_user_name() . " \t" . $messageboard_id .  " \t... " );
                }
								if ( ! $search_mod = $GLOBALS[ "STEAM" ]->get_module( "package:searchsupport" ) )
												throw new Exception( "sTeam 'package:searchsupport' not installed." );
								$messageboard = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $messageboard_id, CLASS_MESSAGEBOARD );
								$search_results = $GLOBALS[ "STEAM" ]->predefined_command(
																$search_mod,
																"search_messageboard",
																array( $messageboard, $pattern ),
																0
																);
								$result = lms_forum::get_discussions( $messageboard->get_id(), $search_results );
                if (defined("LOG_DEBUGLOG")) {
                  logging::append_log( LOG_DEBUGLOG, " \t" . round((microtime(TRUE) - $time1) * 1000 ) . " ms \t" . count($result));
                }
                return $result;
				}

				public function search_user_posts( $messageboard_id, $user_name )
				{
                if (defined("LOG_DEBUGLOG")) {
                  $time1 = microtime(TRUE);
                  logging::write_log( LOG_DEBUGLOG, "lms_forum::search_user_posts(" . $messageboard_id . ", " . $user_name . ") \t" . $GLOBALS["STEAM"]->get_login_user_name() . " \t" . $messageboard_id . " \t... " );
                }
								if ( ! $search_mod = $GLOBALS[ "STEAM" ]->get_module( "package:searchsupport" ) )
												throw new Exception( "sTeam 'package:searchsupport' not installed." );
								$messageboard = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $messageboard_id, CLASS_MESSAGEBOARD );
								$user = steam_factory::username_to_object( $GLOBALS[ "STEAM" ]->get_id(), $user_name );
								$search_results = $GLOBALS[ "STEAM" ]->predefined_command(
																$search_mod,
																"search_user_posts",
																array( $messageboard, $user ),
																0
																);
								$result = lms_forum::get_discussions( $messageboard->get_id(), $search_results);
                if (defined("LOG_DEBUGLOG")) {
                  logging::append_log( LOG_DEBUGLOG, " \t" . round((microtime(TRUE) - $time1) * 1000 ) . " ms \t" . count($result));
                }
                return $result;
				}

  static public function get_access_descriptions( $grp ) {
    $private = gettext("Private");
    $public = gettext("Public");
    $staff_only = gettext("Staff only");
    $ret = array(
      PERMISSION_UNDEFINED => array(
      "label" =>  gettext( "Not defined." ),
      "summary_short" => gettext("-"))
      );
    if ( (string) $grp->get_attribute( "OBJ_TYPE" ) == "course" )
    {
      $ret += array(
        PERMISSION_PUBLIC => array(
          "label" => gettext( "All users can read and make posts." ),
          "summary_short" => $public,
          "members" => 0,
          "steam" => SANCTION_READ | SANCTION_ANNOTATE,
        ),
        PERMISSION_PUBLIC_READONLY => array(
          "label" => gettext( "All users can read. Only members can make posts." ),
          "summary_short" => $public,
          "members" => SANCTION_ANNOTATE,
          "steam" => SANCTION_READ,
        ),
        PERMISSION_PRIVATE => array(
          "label" => gettext( "Only members can read and make posts." ),
          "summary_short" => $private,
          "members" =>  SANCTION_READ | SANCTION_ANNOTATE,
          "steam" => 0,
        ),
        PERMISSION_PRIVATE_STAFF => array(
          "label" => gettext( "Only staff members can read and make posts." ),
          "summary_short" => $staff_only,
          "members" =>  0,
          "steam" => 0,
        ),
      );
    } else {
      $ret += array(
        PERMISSION_PUBLIC =>array(
          "label" => gettext( "All users can read and make posts." ),
          "summary_short" => $public,
          "members" => 0,
          "steam" => SANCTION_READ | SANCTION_ANNOTATE,
        ),
        PERMISSION_PUBLIC_READONLY => array(
          "label" => gettext( "All users can read. Only members can comment and make posts." ) ,
          "summary_short" => $public,
          "members" => SANCTION_ANNOTATE,
          "steam" => SANCTION_READ,
        ),
        PERMISSION_PRIVATE => array(
          "label" => gettext( "Only members can read, comment and make posts."),
          "summary_short" => $private,
          "members" => SANCTION_READ | SANCTION_ANNOTATE,
          "steam" => 0,
        ),
      );
    }
    return $ret;
  }
}
?>
