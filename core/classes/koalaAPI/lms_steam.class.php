<?php
require_once( PATH_LIB . "sort_functions.inc.php" );
class lms_steam
{
    public static function connect( $server = STEAM_SERVER, $port = STEAM_PORT, $login = STEAM_GUEST_LOGIN, $password = STEAM_GUEST_PW )
    {
        try {
            $GLOBALS[ "STEAM" ] = steam_connector::connect( $server, $port, $login, $password );
        } catch (ParameterException $p) {
            throw new Exception("Missing or wrong params for server connection", E_CONNECTION);
        } catch ( Exception $e ) {
                throw new Exception(
                        "No connection to sTeam server ($server:$port).",
                        E_CONNECTION
                        );
        }
    }

    public static function is_connected()
    {
        if (isset($GLOBALS[ "STEAM" ])) {
            return $GLOBALS[ "STEAM" ]->get_socket_status();
        } else {
            return false;
        }
    }

    public static function is_logged_in()
    {
        if (isset($GLOBALS["STEAM"])) {
            return $GLOBALS["STEAM"]->get_login_status();
        } else {
            return false;
        }

    }

    public function get_extensionmanager()
    {
        return extensionmanager::get_extensionmanager();
    }

    public static function is_steam_admin( $user )
    {
    $ret = sessioncache::get_value("lms_steam::is_steam_admin:" . $user->get_id());
    if ($ret === CACHE_UNDEFINED) {
      $admins = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), "Admin" );
      $ret = $admins->is_member( $user );
      sessioncache::set_value("lms_steam::is_steam_admin:" . $user->get_id(), $ret);
    }

    return $ret;
    }

    public static function is_koala_admin( $user )
    {
    // check, ob lms_user in session liegt => Benutzer != Gast +
    // der eingeloggte Benutzer ist nicht der koaLA - Gast +
    // der eingeloggte User ist steam Admin => Benutzer is koaLA Admin
    $ret = ( isset($_SESSION[ "LMS_USER" ]) && $_SESSION[ "LMS_USER" ] instanceof lms_user && $_SESSION[ "LMS_USER" ]->is_logged_in() ) && $_SESSION[ "LMS_USER" ]->get_login() != STEAM_GUEST_LOGIN &&  lms_steam::is_steam_admin( $user );

    return $ret;
  }

    public function is_semester_admin( $semester, $user )
    {
    $semestername = $semester->get_groupname();
    $ret = sessioncache::get_value("lms_steam::is_semester_admin(" . $semestername . "):" . $user->get_id());
    if ($ret === CACHE_UNDEFINED) {
      $admins = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), $semestername . ".admins" );
      $ret = FALSE;
      try {
        $ret = $admins->is_member( $user );
      } catch (Exception $e) {
        // Do nothing (but log it)
        error_log("lms_steam::is_semester_admin: no admin group found for semester id=" . $semester->get_id());
      }
      sessioncache::set_value("lms_steam::is_semester_admin(" . $semestername . "):" . $user->get_id(), $ret);
    }

    return $ret;
    }

    /**
     * function get_root_creator:
     *
     * Returns the steam_object which is the creator of the (recursive) root
     * environment of this object. If it is a course subgroup, then the course
     * group will be returned instead.
     *
     * Note: Available on server with version >= 2.7.18, throws exception if
     * server version is less than 2.7.18.
     *
     * Due to the metaphor of virtual knowledge rooms, most objects
     * are located in a room (or a container in general). This function
     * determines the creator of the root environment for this object and
     * returns an instance of the corresponding subclass of steam_object.
     *
     * @param  Object       $obj the steam_object of which to return the root creator
     * @return steam_object the creator of the root environment of this object
     */
    public function get_root_creator( $obj )
    {
        if ( !is_object( $env = $obj->get_root_environment() ) || !is_object( $creator = $env->get_creator() ) )
            return FALSE;
        if ( ($creator instanceof steam_user) || !is_object( $parent = $creator->get_parent_group() ) || !is_string( $parent_type = $parent->get_attribute(OBJ_TYPE) ) )
            return $creator;
        if ( $parent_type === "course" )
            return $parent;
        return $creator;
    }

    /**
     * Returns an array of path elements, each as an array with the following
     * entries:
     *   "name" => display name or description (e.g. object name or "Your clipboard")
     *   "link" => a koaLA URL which leads to the path item
     *   "obj" => the open-sTeam object that corresponds to that path item
     * The root for any links is the result at index 0. For root elements like
     * groups, the group itself will be returned at index -1 and the part where
     * the object belongs (e.g. communication or documents) as index 0.
     *
     * Example:
     * To create a link for an object $obj in a container $container somewhere
     * in a group's workroom, do:
     *   $container_link_path = lms_steam::get_link_path( $container );
     *   $obj_url = $container_link_path[0]["link"] . $obj->get_id() . "/";
     *   $obj_link = "<a href='" . $obj_url . "'>" . $obj->get_name() . "</a>";
     *
     * This function can also return an array of path elements for group
     * hierarchies (a path of sub groups).
     *
     * @param Object $obj    an open-sTeam object whose koaLA path shall be returned
     * @param Int    $offset offset in the path array (only elements starting at this
     *   index are returned, 0 is the root container, -1 might be the root owner)
     * @return Array an array of path elements, each array( "name", "link", "obj" )
     */
    public static function get_link_path( $obj, $offset = FALSE, $length = FALSE )
    {
        $path_objects = array();
        $parent = $obj;
        while ( is_object( $parent ) && ($parent instanceof steam_object) ) {
            $path_objects[] = $parent;
            if ( $parent instanceof steam_user )
                $parent = FALSE;  // don't pass through into the room the user is in
            else if ( $parent instanceof steam_group )
                $parent = $parent->get_parent_group();
            else
                $parent = $parent->get_environment();
        }
        $path_objects = array_reverse( $path_objects );

        $path = array();
        foreach ($path_objects as $path_obj) {
            switch (TRUE) {
                // group:
                case $path_obj instanceof steam_group:
                    // ignore "Course", current semester group and course-subgroups:
                    if ( !isset( $courses_group ) ) $courses_group = steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), STEAM_COURSES_GROUP, CLASS_GROUP );
                    if ( $path_obj->get_id() == $courses_group->get_id() )
                        break;
                    if ( $parent_group = is_object( $path_obj->get_parent_group() ) && $parent_group->get_id() == $courses_group->get_id() && $path_obj->get_name() == STEAM_CURRENT_SEMESTER )
                        break;
                    $obj_type = $path_obj->get_attribute( "OBJ_TYPE" );
                    if ( $obj_type == "course_learners" || $obj_type == "course_staff" )
                        break;
                    // course:
                    if ($obj_type == "course") {
                        $path[] = array(
                            "name" => h($path_obj->get_attribute( "OBJ_DESC" )),
                            "link" => PATH_URL . SEMESTER_URL . "/" . $path_obj->get_parent_group()->get_name() . "/" . $path_obj->get_name() . "/",
                            "obj" => $path_obj,
                        );
                        break;
                    }
                    // other group:
                    $path[] = array(
                        "name" => h($path_obj->get_name()),
                        "link" => PATH_URL . "groups/" . $path_obj->get_id() . "/",
                        "obj" => $path_obj,
                    );
                break;

                // clipboard:
                case $path_obj instanceof steam_user:
                    if ( $path_obj->get_id() == lms_steam::get_current_user()->get_id() ) {
                        // current user's clipboard:
                        $path[] = array(
                            "name" => gettext( "Your clipboard" ),
                            "link" => PATH_URL . "desktop/clipboard/",
                            "obj" => $path_obj,
                        );
                    } else {
                        // another user's clipboard:
                        $path[] = array(
                            "name" => str_replace( "%NAME", h($path_obj->get_full_name()), gettext( "%NAME's clipboard" ) ),
                            "link" => PATH_URL . "user/" . $path_obj->get_name() . "/clipboard/",
                            "obj" => $path_obj,
                        );
                    }
                break;

                // workroom:
                case is_object( $creator = $path_obj->get_creator() ) && is_object( $workroom = $creator->get_workroom() ) && $path_obj->get_id() == $workroom->get_id():
                    if ($creator instanceof steam_group) {
                        if ( $creator->get_attribute( "OBJ_TYPE" ) == "course_learners" ) {
                            // course workroom (learner's workroom):
                            $course = $creator->get_parent_group();
                            $semester = $course->get_parent_group();
                            $path[-1] = array(
                                "name" => h($course->get_attribute( "OBJ_DESC" )),
                                "link" => PATH_URL . SEMESTER_URL . "/" . $semester->get_name() . "/" . $course->get_name() . "/",
                                "obj" => $course,
                            );
                        } else {
                            // group workroom:
                            $group = $creator;
                            $path[-1] = array(
                                "name" => h($group->get_name()),
                                "link" => PATH_URL . "groups/" . $group->get_id() . "/",
                                "obj" => $group,
                            );
                        }
                        if ( ($obj instanceof steam_messageboard) || (is_string( $obj_type = $obj->get_attribute( "OBJ_TYPE" ) ) && array_search( $obj_type, array( "calendar_weblog_koala", "container_wiki_koala", "KOALA_WIKI" ) ) !== FALSE) ) {
                            // group/course communication:
                            $path[0] = array(
                                "name" => gettext( "Communication" ),
                                "link" => $path[-1]["link"] . "communication/",
                                "obj" => $path_obj,
                            );
                        }
                    }
                break;

                default:
                    // documents folder:
                    if ( $path_obj->get_attribute( OBJ_TYPE ) === "room_documents_koala" ) {
                        if ( is_object( $environment = $path_obj->get_environment() ) && is_object( $creator = $environment->get_creator() ) && is_object( $workroom = $creator->get_workroom() ) && $workroom->get_id() == $environment->get_id() ) {
                            // user's documents:
                            if ($creator instanceof steam_user) {
                                if ( $creator->get_id() == lms_steam::get_current_user()->get_id() ) {
                                    // current user's workroom:
                                    $path[] = array(
                                        "name" => gettext( "Your workroom" ),
                                        "link" => PATH_URL . "desktop/documents/",
                                        "obj" => $path_obj,
                                    );
                                } else {
                                    // another user's workroom:
                                    $path[] = array(
                                        "name" => str_replace( "%NAME", h($creator->get_full_name()), gettext( "%NAME's workroom" ) ),
                                        "link" => PATH_URL . "user/" . $creator->get_name() . "/documents/",
                                        "obj" => $path_obj,
                                    );
                                }
                                break;
                            }
                            // group's documents:
                            else {
                                $path[] = array(
                                    "name" => gettext( "Documents" ),
                                    "link" => $path[-1]["link"] . "documents/",
                                    "obj" => $path_obj,
                                );
                                break;
                            }
                        }
                    }
                    // forum:
                    if ($path_obj instanceof steam_messageboard) {
                        $path[] = array(
                            "name" => h($path_obj->get_name()),
                            "link" => PATH_URL . "forums/" . $path_obj->get_id() . "/",
                            "obj" => $path_obj,
                        );
                        break;
                    }
                    $obj_type = $path_obj->get_attribute( "OBJ_TYPE" );
                    if ( !is_string( $obj_type ) ) $obj_type = "";
                    // weblog:
                    if ($obj_type == "calendar_weblog_koala") {
                        $path[] = array(
                            "name" => h($path_obj->get_name()),
                            "link" => PATH_URL . "weblog/" . $path_obj->get_id() . "/",
                            "obj" => $path_obj,
                        );
                        break;
                    }
                    // wiki:
                    if ($obj_type == "container_wiki_koala") {
                        $path[] = array(
                            "name" => h($path_obj->get_name()),
                            "link" => PATH_URL . "wiki/" . $path_obj->get_id() . "/",
                            "obj" => $path_obj,
                        );
                        break;
                    }
                    // documents:
                    if ($path_obj instanceof steam_document) {
                        $path[] = array(
                            "name" => h($path_obj->get_name()),
                            "link" => PATH_URL . "doc/" . $path_obj->get_id() . "/",
                            "obj" => $path_obj,
                        );
                        break;
                    }
                    // any other object:
                    $path[] = array(
                        "name" => h($path_obj->get_name()),
                        "link" => (isset($path[0]) && isset($path[0]["link"])) ? $path[0]["link"] . $path_obj->get_id() . "/" : "",
                        "obj" => $path_obj,
                    );
                break;
            }
        }

        if ( $offset === FALSE )
            return $path;
        else {
            $index = array_search( $offset, array_keys( $path ) );
            if ( $index === FALSE )
                return array();
            if ( $length === FALSE )
                return array_slice( $path, $index );
            else
                return array_slice( $path, $index, $length );
        }
    }

    public static function get_link_path_html( $obj, $offset = FALSE, $length = FALSE )
    {
        if ( is_object( $obj ) )
            $link_path = lms_steam::get_link_path( $obj, $offset );
        else if ( is_array( $obj ) ) {
            if ( $offset === FALSE )
                $link_path = $obj;
            else {
                $index = array_search( $offset, array_keys( $obj ) );
                if ( $index === FALSE )
                    return "";
                if ( $length === FALSE )
                    $link_path = array_slice( $obj, $index );
                else
                    $link_path = array_slice( $obj, $index, $length );
            }
        } else
            $link_path = array();
        $container_path = "";
        foreach ($link_path as $path_item) {
            if ( !empty( $container_path ) ) $container_path .= "&nbsp;/ ";
            if ( empty( $path_item[ "link" ] ) )
                $container_path .= $path_item[ "name" ];
            else
                $container_path .= "<a href='" . $path_item[ "link" ] . "'>" . h( $path_item[ "name" ] ) . "</a>";
        }

        return $container_path;
    }

// strange:
    public function get_link_to_root( $obj )
    {
        $p = explode( "/", $obj->get_path() );
        if ( sscanf( $p[ 1 ], "~%s", $username ) != FALSE && isset( $username ) && is_string( $username ) && is_object( $user = steam_factory::username_to_object( $GLOBALS[ "STEAM" ]->get_id(), $username ) ) ) {
            // Target is a user's clipboard
            if ( $user->get_id() == lms_steam::get_current_user()->get_id() ) $clipboard_text = gettext( "Your clipboard" );
            else $clipboard_text = gettext( "%NAME's clipboard" );
            return array( "name" => str_replace( "%NAME", h($user->get_full_name()), $clipboard_text ), "link" => PATH_URL . "user/" . $username . "/clipboard/" );
        } else {
            // Target is a group
            if ( $obj instanceof steam_group ) $p[ 2 ] = $obj->get_identifier();
            $g = explode( ".", $p[ 2 ] );
            switch (TRUE) {
                case ( strpos( $p[ 2 ], "Courses." ) !== FALSE ):
                    $pos = array_search( "Courses", $g );
                    $groupname = "";
                    for ($i = 0; $i <= $pos + 2; $i++) {
                        if ( $i > 0 )
                            $groupname .= ".";
                        $groupname .= $g[ $i ];
                        if ( $i == $pos + 1 )
                            $semester = $g[ $i ];
                        if ( $i == $pos + 2 )
                            $course = $g[ $i ];
                    }
                    $obj = new koala_group_course( steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), $groupname ));
                    $s = $obj->get_semester()->get_name();
                    $result = array(
                                    array( "name" => $s, "link" => PATH_URL . SEMESTER_URL . "/" . $s . "/" ),
                                    array( "name" => h($obj->get_course_name()), "link" => PATH_URL . SEMESTER_URL . "/" . $s . "/" . $course . "/")
                                    );
                    break;
                default:
          try {
            $group = new koala_group_default( steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), $p[ 2 ] ));
          } catch (Exception $ex) {
            // Constructing a group fails with error (Param is not a steam_group)
            $group = 0;
          }
          if (is_object($group)) {
               $result = array(
                   array( "name" => h($group->get_steam_object()->get_environment()->get_name()), "link" => PATH_URL . "groups/?cat=" . $group->get_steam_object()->get_environment()->get_id() ),
                   array( "name" => h($group->get_name()), "link" => PATH_URL . "groups/" . $group->get_id() . "/" )
                   );
          } elseif ( is_object( $user = steam_factory::username_to_object( $GLOBALS[ "STEAM" ]->get_id(), $p[ 2 ] ) ) ) {
            // Target is a user
            if ( $user->get_id() == lms_steam::get_current_user()->get_id() ) $user_text = gettext( "Your workroom" );
            else $user_text = gettext( "%NAME's workroom" );
            return array( "name" => str_replace( "%NAME", h($user->get_full_name()), $user_text ), "link" => PATH_URL . "user/index/" . $p[ 2 ] . "/" );
          }
                break;
            }
        }
        if ( isset( $result ) ) return $result;
        else return "";
    }

    public static function get_current_user()
    {
    if ( ! lms_steam::is_logged_in() ) {
            throw new Exception( "Not logged in.", E_INVOCATION );
        }

        return $GLOBALS[ "STEAM" ]->get_current_steam_user();
    }

    public function get_current_environment()
    {
        $steam_user = steam_get_current_user();

        return $steam_user->get_environment();
    }

    public function get_user_language()
    {
        $steam_languages = array(
                "english" => "en_US",
                "german"  => "de_DE",
                "chinese" => "zh_TW"
                );
        $steam_user = steam_get_current_user();
        if (isset($steam_languages[$steam_user->get_attribute("USER_LANGUAGE")])) {
            return $steam_languages[$steam_user->get_attribute("USER_LANGUAGE")];
        } else {
            return "de_DE";
        }
    }

    public function count_user()
    {
        $all_user = $GLOBALS["STEAM"]->get_steam_group();
    if (is_object($all_user)) return $all_user->count_members();
        else return -1;
    }

    public function search_user( $pattern, $type="name", $like = TRUE )
    {
        $user_module = $GLOBALS[ "STEAM" ]->get_module( "users" );

        $results = array();
        $result_ids = array();
        foreach ( explode( ',', $pattern ) as $part ) {
            $part = trim( $part );
            switch ($type) {
                case( "login" ):
                    $result_login = $GLOBALS[ "STEAM" ]->predefined_command(
                        $user_module,
                        "lookup_login",
                        array( $part, $like ),
                        0
                    );
                    if ( is_object( $result_login ) )
                        $result_login = array( $result_login );
                    foreach ($result_login as $obj) {
                        if ( array_search( $obj->get_id(), $result_ids ) === FALSE ) {
                            $results[] = $obj;
                            $result_ids[] = $obj->get_id();
                        }
                    }

                    $result_email = $GLOBALS[ "STEAM" ]->predefined_command(
                        $user_module,
                        "lookup_email",
                        array( $part, $like ),
                        0
                    );
                    if ( is_object( $result_email ) )
                        $result_email = array( $result_email );
                    foreach ($result_email as $obj) {
                        if ( array_search( $obj->get_id(), $result_ids ) === FALSE ) {
                            $results[] = $obj;
                            $result_ids[] = $obj->get_id();
                        }
                    }
                break;
                default: // NAME
                      $names = explode(" ", $part);
                    if (sizeof($names) >= 2) {
                        $lastname = $names[ sizeof($names) - 1 ];
                          if ( sizeof($names) > 2 )
                            $firstname = implode( array_slice( $names, 0, sizeof( $names ) - 1 ), ' ' );
                          else
                            $firstname = $names[ 0 ];
                          $result = $GLOBALS[ "STEAM" ]->predefined_command(
                            $user_module,
                            "lookup_name",
                            array( $firstname, $lastname, $like ),
                            0
                        );
                    } else {
                        $result = $GLOBALS[ "STEAM" ]->predefined_command(
                            $user_module,
                            "search_name",
                            array( $part, $part, $like ),
                            0
                        );
                    }
                    if ( is_object( $result ) )
                        $result = array( $result );
                    foreach ($result as $obj) {
                        if ( array_search( $obj->get_id(), $result_ids ) === FALSE ) {
                            $results[] = $obj;
                            $result_ids[] = $obj->get_id();
                        }
                    }
                break;
            }
        }
        $attributes= array( 
            "USER_FIRSTNAME", 
            "USER_FULLNAME", 
            "OBJ_ICON", 
            "OBJ_DESC", 
            "USER_ACADEMIC_TITLE", 
            "USER_PROFILE_FOCUS", 
            "USER_PROFILE_FACULTY" );

        // pre-load needed attribute values with one server request
        steam_factory::load_attributes($GLOBALS["STEAM"]->get_id(), $results, $attributes);
        // working on buffered values from now on
        $i = 0;
        $persons = array();
        foreach ($results as $person) {
            $persons[ $i ] = $person->get_attributes( $attributes );
            $persons[ $i ][ "OBJ_NAME" ] = $person->get_name();
            $persons[ $i ][ "OBJ_ID" ]   = $person->get_id();
            $persons[ $i ][ "USER_PROFILE_FACULTY" ] = lms_steam::get_faculty_name( $persons[ $i ][ "USER_PROFILE_FACULTY" ] );
            if ( is_object( $persons[ $i ][ "OBJ_ICON" ] ) ) {
                $persons[ $i ][ "OBJ_ICON" ] = $persons[ $i ][ "OBJ_ICON" ]->get_id();
            } else {
                $persons[ $i ][ "OBJ_ICON" ] = 0;
            }
            $i++;
        }
        usort( $persons, "sort_buddies" );

        return $persons;
    }

    public function user_add_rssfeed( $obj_id, $rssfile, $type, $context )
    {
        $obj = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $obj_id );
        $user = lms_steam::get_current_user();
        $bookmarked_rss = $user->get_attribute( "USER_RSS_FEEDS" );
        if ( ! is_array( $bookmarked_rss ) )
            $bookmarked_rss = array();

        $bookmarked_rss[ $obj_id ] = array( "name" => $obj->get_name(), "link" => $rssfile, "type" => $type, "context_name" => $context[ "name" ], "context_link" => $context[ "link" ] );
        $user->set_attribute( "USER_RSS_FEEDS", $bookmarked_rss );
    }

    public function user_get_profile( $username )
    {
        $steam_user = steam_factory::username_to_object( $GLOBALS[ "STEAM" ]->get_id(), $username );
        $query = array( 
            "USER_FULLNAME", 
            "USER_LAST_LOGIN", 
            "USER_FIRSTNAME", 
            "USER_EMAIL", 
            "OBJ_ICON", 
            "OBJ_DESC", 
            "USER_ACADEMIC_TITLE", 
            "USER_ACADEMIC_DEGREE", 
            "USER_PROFILE_GENDER", 
            "USER_PROFILE_DSC", 
            "USER_PROFILE_FOCUS", 
            "USER_PROFILE_HOMETOWN", 
            "USER_PROFILE_WANTS", 
            "USER_PROFILE_HAVES", 
            "USER_PROFILE_ORGANIZATIONS", 
            "USER_PROFILE_OTHER_INTERESTS", 
            "USER_PROFILE_FACULTY", 
            "USER_PROFILE_ADDRESS", 
            "USER_PROFILE_TELEPHONE", 
            "USER_PROFILE_PHONE_MOBILE", 
            "USER_PROFILE_WEBSITE_URI", 
            "USER_PROFILE_WEBSITE_NAME", 
            "USER_PROFILE_IM_ICQ", 
            "USER_PROFILE_IM_MSN", 
            "USER_PROFILE_IM_AIM", 
            "USER_PROFILE_IM_YAHOO", 
            "USER_PROFILE_IM_SKYPE", 
            "USER_LANGUAGE", 
            "USER_ADRESS", 
            "bid:user_callto");
        $tmp = $steam_user->get_attributes( $query );
        if ($tmp[ "OBJ_ICON" ] instanceof steam_object) {
            $tmp[ "OBJ_ICON" ] = $tmp[ "OBJ_ICON" ]->get_id();
        } else {
            $tmp[ "OBJ_ICON" ] = 0;
        }

        return $tmp;
    }

    public function user_get_profile_privacy( $login, $is_privacy_page = FALSE )
    {
        $steam_user = steam_factory::username_to_object( $GLOBALS[ "STEAM" ]->get_id(), $login );
        $privacy_object = $steam_user->get_attribute( "KOALA_PRIVACY" );
        $user = lms_steam::get_current_user();
        $my_profile = $user->get_id() == $steam_user->get_id();

        $admin_group = steam_factory::get_group( $GLOBALS["STEAM"]->get_id(), "admin" );
        $isAdmin = ( is_object( $admin_group ) && $admin_group->is_member( $user ) );

        if ( $privacy_object instanceof steam_object && ( $is_privacy_page || ( !$my_profile && !$isAdmin ) ) ) {
            $query = array( 
                "PRIVACY_STATUS", 
                "PRIVACY_GENDER", 
                "PRIVACY_FACULTY", 
                "PRIVACY_MAIN_FOCUS", 
                "PRIVACY_WANTS", 
                "PRIVACY_HAVES", 
                "PRIVACY_ORGANIZATIONS", 
                "PRIVACY_HOMETOWN", 
                "PRIVACY_OTHER_INTERESTS", 
                "PRIVACY_LANGUAGES", 
                "PRIVACY_CONTACTS", 
                "PRIVACY_GROUPS", 
                "PRIVACY_EMAIL", 
                "PRIVACY_ADDRESS", 
                "PRIVACY_TELEPHONE", 
                "PRIVACY_PHONE_MOBILE", 
                "PRIVACY_WEBSITE", 
                "PRIVACY_ICQ_NUMBER", 
                "PRIVACY_MSN_IDENTIFICATION", 
                "PRIVACY_AIM_ALIAS", 
                "PRIVACY_YAHOO_ID", 
                "PRIVACY_SKYPE_NAME" );

            return $privacy_object->get_attributes( $query );
        } else {
                        $deny_all = PROFILE_DENY_ALLUSERS + PROFILE_DENY_CONTACTS;

            return array( 
                "PRIVACY_STATUS" => $deny_all, 
                "PRIVACY_GENDER" => $deny_all, 
                "PRIVACY_FACULTY" => $deny_all,  
                "PRIVACY_MAIN_FOCUS" => $deny_all, 
                "PRIVACY_WANTS" => $deny_all, 
                "PRIVACY_HAVES" => $deny_all, 
                "PRIVACY_ORGANIZATIONS" => $deny_all, 
                "PRIVACY_HOMETOWN" => $deny_all, 
                "PRIVACY_OTHER_INTERESTS" => $deny_all, 
                "PRIVACY_LANGUAGES" => $deny_all, 
                "PRIVACY_CONTACTS" => $deny_all, 
                "PRIVACY_GROUPS" => $deny_all, 
                "PRIVACY_EMAIL" => $deny_all, 
                "PRIVACY_ADDRESS" => $deny_all, 
                "PRIVACY_TELEPHONE" => $deny_all, 
                "PRIVACY_PHONE_MOBILE" => $deny_all, 
                "PRIVACY_WEBSITE" => $deny_all, 
                "PRIVACY_ICQ_NUMBER" => $deny_all, 
                "PRIVACY_MSN_IDENTIFICATION" => $deny_all, 
                "PRIVACY_AIM_ALIAS" => $deny_all, 
                "PRIVACY_YAHOO_ID" => $deny_all, 
                "PRIVACY_SKYPE_NAME" => $deny_all );
        }
    }

// Seems to be never used
    public function who_is_online( $max = 5 )
    {
        $logfile = explode( "\n", shell_exec( "tail -n 100 " . LOG_MESSAGES . " | grep LOGIN" ) );
        $ls = count( $logfile );
        $result = array();
        for ($i = $ls - 1; $i > 0; $i--) {
            $line = explode( "\t", $logfile[ $i ] );
            $login = (isset($line[3])?$line[ 3 ]:"");
            if ( ! array_key_exists( $login, $result )  && ( ! empty( $login ) ) ) {
                $user = steam_factory::username_to_object( $GLOBALS[ "STEAM" ]->get_id(), $login );
                if ( !is_object( $user ) ) continue;
                $result[ $login ] = $user->get_attributes( array(
                    "USER_FIRSTNAME",
                    "USER_FULLNAME",
                    "OBJ_ICON",
                    "USER_PROFILE_FACULTY",
                    "USER_PROFILE_FOCUS",
                    "USER_LAST_LOGIN",
                    "USER_ACADEMIC_TITLE" ) );
                if ($result[ $login ][ "OBJ_ICON" ] instanceof steam_object) {
                    $result[ $login ][ "OBJ_ICON" ] = $result[ $login ][ "OBJ_ICON" ]->get_id();
                    $result[ $login ][ "OBJ_NAME" ] = $login;
                    $faf = lms_steam::get_faculty_name( $result[ $login ][ "USER_PROFILE_FACULTY" ] );
                    $faf .= ( empty( $result[ $login ][ "USER_PROFILE_FOCUS" ] ) ) ? "" : ", " . $result[ $login ][ "USER_PROFILE_FOCUS" ];
                    $result[ $login ][ "USER_PROFILE_FACULTY" ] = $faf;
                }
                $max--;
                if ( $max == 0 )
                    break;
            }
        }

        return $result;
    }

    public static function user_count_unread_mails( $login )
    {
        $user = steam_factory::username_to_object( $GLOBALS[ "STEAM" ]->get_id(), $login );
        $mails = $user->get_mails();
        $unread = 0;

    $tnr = array();
    $i = 0;
        foreach ($mails as $mail) {
            $tnr[$i] = lms_steam::is_reader( $mail, $user, TRUE );
      $i++;
        }
    $result = $GLOBALS["STEAM"]->buffer_flush();

    $i = 0;
        foreach ($mails as $mail) {
            if (! $result[$tnr[$i]]) {
                $unread++;
            }
      $i++;
        }

        return $unread;
    }

  public static function user_get_groups( $username, $public = TRUE )
  {
    $cache_groups = get_cache_function( "GROUPS", CACHE_LIFETIME_STATIC );
    $result = array();
    $steam_user = steam_factory::username_to_object( $GLOBALS[ "STEAM" ]->get_id(), $username );
    $groups = $steam_user->get_groups();
    $no_groups = count( $groups );
    $public_group = null;
    $private_group =  null;
    if (defined("STEAM_PUBLIC_GROUP")) {
        $public_group = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), STEAM_PUBLIC_GROUP, CLASS_GROUP );
    }
    if (defined("STEAM_PRIVATE_GROUP")) {
        $private_group = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), STEAM_PRIVATE_GROUP, CLASS_GROUP );
    }

    if (!($public_group instanceof steam_group) || !($private_group instanceof steam_group)) {
        return $result;
    }

    $data_tnr = array();
    for ($i = 0; $i < $no_groups; $i++) {
      $data_tnr[$i] = array();
      $data_tnr[$i]["PARENT"] = $groups[$i]->get_parent_group(TRUE);
      $data_tnr[$i][ "ATTRIBUTES" ] = $groups[$i]->get_attributes(array(OBJ_NAME, OBJ_DESC), TRUE);
      $data_tnr[$i][ "GROUP_NO_MEMBERS" ] = $groups[$i]->count_members(TRUE);
    }
    $data_result = $GLOBALS["STEAM"]->buffer_flush();

    $no_groups = count( $groups );
    for ($i = 0; $i < $no_groups; $i++) {
      $group = $groups[ $i ];
      $parent = $data_result[$data_tnr[$i]["PARENT"]];
      if (!is_object($parent)) continue;
      if ( $public && $parent->get_id() != $public_group->get_id()) {
        continue;
      }
      if (($parent->get_id() != $private_group->get_id()) && ($parent->get_id() != $public_group->get_id())) {
        continue;
      }
      $result[ $i ][ "OBJ_NAME" ] = $data_result[$data_tnr[$i]["ATTRIBUTES"]][OBJ_NAME];
      $result[ $i ][ "OBJ_ID" ] = $group->get_id();
      $result[ $i ][ "OBJ_DESC" ] = $data_result[$data_tnr[$i]["ATTRIBUTES"]][OBJ_DESC];
      $result[ $i ][ "GROUP_NO_MEMBERS" ] = $data_result[$data_tnr[$i]["GROUP_NO_MEMBERS"]];
      $result[ $i ][ "GROUP_LINK" ] = PATH_URL . "groups/" . $group->get_id() . "/";
    }

    return $result;
  }

  public static function get_current_semester()
  {
      $scg = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), STEAM_COURSES_GROUP, CLASS_GROUP );
      $current_semester = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), $scg->get_groupname() . "." . STEAM_CURRENT_SEMESTER );

      return $current_semester;
  }

    public static function get_semesters()
    {
        $semesters = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), STEAM_COURSES_GROUP, CLASS_GROUP );
        $s = $semesters->get_subgroups();
        $no_semester = count( $s );
        $result = array();

    $tnr = array();

        for ($i = 0; $i < $no_semester; $i++) {
      $tnr[ $i ] = array();
            $tnr[ $i ]["ATTRIBUTES"] = $s[ $i ]->get_attributes( array( "OBJ_DESC", "SEMESTER_START_DATE", "SEMESTER_END_DATE", "SEMESTER_HISLSF_ID" ), TRUE );
            $tnr[ $i ][ "OBJ_NAME" ] = $s[ $i ]->get_name(TRUE);
        }

    $steamresult = $GLOBALS["STEAM"]->buffer_flush();

        for ($i = 0; $i < $no_semester; $i++) {
            $result[ $i ] = $steamresult[ $tnr[ $i ][ "ATTRIBUTES" ] ];
            $result[ $i ][ "OBJ_ID" ] 	= $s[ $i ]->get_id();
            $result[ $i ][ "OBJ_NAME" ] = $steamresult[ $tnr[ $i ][ "OBJ_NAME" ] ];
        }

        //usort( $result, "sort_semester_desc" );
    $result = array_reverse($result);

        return $result;
    }

    public static function user_get_booked_courses( $user_id, $semester_obj_id = "" )
    {
        if ( empty( $semester_obj_id ) ) {
            $scg = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), STEAM_COURSES_GROUP, CLASS_GROUP );
            $semester = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), $scg->get_groupname() . "." . STEAM_CURRENT_SEMESTER );
    } else $semester = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $semester_obj_id, CLASS_GROUP  );

        $user = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $user_id, CLASS_USER );

    $groups = $user->get_groups();
    $group_parents_tnr = array();

    foreach ($groups as $group) {
      $group_parents_tnr[$group->get_id()] = $group->get_parent_group(TRUE);
    }
    $group_parents_result = $GLOBALS["STEAM"]->buffer_flush();
    $parent = FALSE;
    $course_data_tnr = array();
    $attributes = array(OBJ_NAME, OBJ_DESC, OBJ_TYPE, "COURSE_UNITS_ENABLED", "COURSE_NUMBER");
    foreach ($groups as $group) {
      $parent = $group_parents_result[$group_parents_tnr[$group->get_id()]];
      if (is_object($parent)) {
        $course_data_tnr[$group->get_id()] = array();
        $course_data_tnr[$group->get_id()]["semester_group"] = $parent->get_parent_group(TRUE);
        $course_data_tnr[$group->get_id()]["attributes"] = $parent->get_attributes($attributes, TRUE);
      }
    }
    $course_data_tnr[$semester->get_id()] = $semester->get_name(TRUE);
    $course_data_result = $GLOBALS["STEAM"]->buffer_flush();
    $result = array();
    foreach ($groups as $group) {
      $id = $group->get_id();
      if (!isset($course_data_tnr[$id]["semester_group"]))
          continue;
      $cd = $course_data_tnr[$id]["semester_group"];
      $course_parent = $course_data_result[$cd];
      $course = $group_parents_result[$group_parents_tnr[$group->get_id()]];
      if (is_object($course_parent) && $course_parent->get_id() == $semester->get_id()) {
        $attributes = array();
        $attributes[ "OBJ_ID" ] = $course->get_id();
        $attributes[ "OBJ_NAME" ] = $course_data_result[$course_data_tnr[$group->get_id()]["attributes"]][OBJ_NAME];
        $attributes[ "COURSE_NAME" ] = $course_data_result[$course_data_tnr[$group->get_id()]["attributes"]][OBJ_DESC] .  " (" . koala_group_course::s_convert_course_id($course_data_result[$course_data_tnr[$group->get_id()]["attributes"]][OBJ_NAME], $course_data_result[$course_data_tnr[$group->get_id()]["attributes"]]["COURSE_NUMBER"]) . ")";
        $attributes[ "COURSE_LINK" ] = PATH_URL . SEMESTER_URL . "/" . $course_data_result[$course_data_tnr[$semester->get_id()]] . "/" . $course_data_result[$course_data_tnr[$group->get_id()]["attributes"]][OBJ_NAME] . "/";
        $attributes[ "SEMESTER_NAME" ] = $course_data_result[$course_data_tnr[$semester->get_id()]];
        $attributes["COURSE_UNITS_ENABLED"] = $course_data_result[$course_data_tnr[$group->get_id()]["attributes"]]["COURSE_UNITS_ENABLED"];
        $result[$course->get_id()] = $attributes;
      }
    }

    return $result;
    }

    public static function semester_get_courses( $semester_obj_id, $user_login_name = "" )
    {
        $semester = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $semester_obj_id, CLASS_GROUP  );
        if ( ! empty( $user_login_name ) )
            $user = steam_factory::username_to_object( $GLOBALS[ "STEAM" ]->get_id(), $user_login_name );
        $courses = $semester->get_subgroups();
        $no_courses = count( $courses );
        if ($no_courses > 0) {
      // Load groupnames
      $names_tnr = array();
      for ($i = 0; $i < $no_courses; $i++) {
        $names_tnr[$i] = $courses[$i]->get_groupname(TRUE);
      }

      $groupnames = $GLOBALS["STEAM"]->buffer_flush();
      $tnr = array();

            for ($i = 0; $i < $no_courses; $i++) {
        $tnr[$i] = array();
        $tnr[$i]["GROUP_LEARNERS"] = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), $groupnames[ $names_tnr[$i] ] . "." . "learners", TRUE );
        $tnr[$i]["GROUP_STAFF"] = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), $groupnames[ $names_tnr[$i] ] . "." . "staff", TRUE );
                $tnr[ $i ]["ATTRIBUTES"] = $courses[$i]->get_attributes( array( "COURSE_PARTICIPANT_MNGMNT", "COURSE_SEMESTER", "COURSE_TUTORS", "COURSE_SHORT_DSC", "COURSE_LONG_DSC", "OBJ_DESC", "OBJ_NAME", "COURSE_HISLSF_ID", "COURSE_NUMBER", KOALA_GROUP_ACCESS ), TRUE );
                $tnr[ $i ][ "OBJ_NAME" ] = $courses[$i]->get_name(TRUE);
            }

      // COURSE_NUMBER is the new visible Key of Courses
      // COURSE_NUMBER is only set if Course was imported from PAUL

      $result = $GLOBALS["STEAM"]->buffer_flush();
      $member_tnr = array();
      for ($i = 0; $i < $no_courses; $i++) {
          $member_tnr[$i] = array();
          if ( !is_object( $result[ $tnr[ $i ][ "GROUP_LEARNERS"] ] )) continue;
          if ( !is_object( $result[ $tnr[ $i ][ "GROUP_STAFF"] ] )) continue;
          if (! empty( $user_login_name )) {
            $member_tnr[$i]["IS_MEMBER"] = $result[ $tnr[ $i ][ "GROUP_LEARNERS"] ]->is_member( $user, TRUE );
            $member_tnr[$i]["IS_STAFF"] = $result[ $tnr[ $i ][ "GROUP_STAFF"] ]->is_member( $user, TRUE );
          }
          $member_tnr[$i]["MAX_PARTICIPANTS"] = $result[ $tnr[ $i ][ "GROUP_LEARNERS"]]->get_attribute("GROUP_MAXSIZE", TRUE);
          $member_tnr[$i]["COURSE_NO_PARTICIPANTS"] = $result[ $tnr[ $i ][ "GROUP_LEARNERS"]]->count_members(TRUE);
      }

      $member_result = $GLOBALS["STEAM"]->buffer_flush();

            $res = array();
            for ($i = 0; $i < $no_courses; $i++) {
                 if ( $groupnames[ $names_tnr[$i] ] == "admin" ) continue;
        if ( !is_object( $result[ $tnr[ $i ][ "GROUP_LEARNERS"] ] )) continue;
        if ( !is_object( $result[ $tnr[ $i ][ "GROUP_STAFF"] ] )) continue;
        if ( ! empty( $user_login_name ) ) {
                    if ( ! ( $member_result[ $member_tnr[$i]["IS_MEMBER"] ] || $member_result[ $member_tnr[$i]["IS_STAFF"] ] ) ) continue;
        }
                $res[ $i ] = $result[ $tnr[ $i ]["ATTRIBUTES"] ];
                $res[ $i ][ "OBJ_ID" ] = $courses[$i]->get_id();
        $res[ $i ][ "COURSE_MAX_PARTICIPANTS" ] = $member_result[$member_tnr[$i]["MAX_PARTICIPANTS"]];
                $res[ $i ][ "COURSE_NO_PARTICIPANTS" ] = $member_result[$member_tnr[$i]["COURSE_NO_PARTICIPANTS"]];
        $res[ $i ]["SORTKEY"] = koala_group_course::convert_course_id($res[ $i ][OBJ_NAME], $res[ $i ]["COURSE_NUMBER"]  );
            }

      usort( $res, "sort_courses" );

      return $res;
        } else {
            return array();
        }
    }

  public function semester_get_user_coursememberships( $semester_obj_id, $user )
  {
        $semester = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $semester_obj_id, CLASS_GROUP  );
    if ( ! is_object($user) ) {
            throw new Exception( "Not a user." . $semester_obj_id, E_PARAMETER );
        }

    $booked_courses = lms_steam::user_get_booked_courses( $user->get_id(), $semester_obj_id);

        if ( count( $booked_courses ) > 0 ) {
      return $booked_courses;
        } else {
            return array();
        }
    }

    public function get_faculties()
    {
        if (defined("STEAM_FACULTIES_GROUP")) {
            $faculties = steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), STEAM_FACULTIES_GROUP, CLASS_GROUP );
        }
        if (!(isset($faculties) && $faculties instanceof steam_group)) {
            return array();
        }
        $tmp_grps  = $faculties->get_subgroups();
        $no_faculties = count( $tmp_grps );
        $result = array();

    steam_factory::load_attributes($GLOBALS["STEAM"]->get_id(), $tmp_grps, array(OBJ_NAME, OBJ_DESC));

        for ($i = 0; $i < $no_faculties; $i++) {
            $result[ $i ][ "OBJ_ID" ] = $tmp_grps[ $i ]->get_id();
            $result[ $i ][ "OBJ_NAME" ] = $tmp_grps[ $i ]->get_name();
            $result[ $i ][ "OBJ_DESC" ] = $tmp_grps[ $i ]->get_attribute( "OBJ_DESC" );
        }

        return $result;
    }

    public function get_faculty_name( $id )
    {
        if ( empty( $id ) )
            return gettext( "miscellaneous" );
        $cache = get_cache_function( "ORGANIZATION", 86400 );
        $faculties = $cache->call( "lms_steam::get_faculties" );
        foreach ($faculties as $faculty) {
            if ( $faculty[ "OBJ_ID" ] == $id )
                break;
        }
        if ( isset($faculty) && $faculty[ "OBJ_ID" ] == $id )
            return $faculty[ "OBJ_NAME" ];
        else
            return gettext( "miscellaneous" );
    }

    public function get_faculties_asc()
    {
        $faculties = lms_steam::get_faculties();
        usort( $faculties, "sort_objects" );

        return $faculties;
    }

    public function user_get_contacts_to_confirm( $username )
    {
    $steam_user = steam_factory::username_to_object( $GLOBALS[ "STEAM" ]->get_id(), $username);
        $contacts_toconfirm = $steam_user->get_attribute("USER_CONTACTS_TOCONFIRM");
    // if there are no contacts to confirm return immediately
    if (!is_array($contacts_toconfirm) || count($contacts_toconfirm) == 0) return array();
        $attributes = array(
            USER_FIRSTNAME,
            USER_FULLNAME,
            "USER_PROFILE_FACULTY",
            "USER_PROFILE_FOCUS",
            OBJ_DESC,
            OBJ_ICON,
            "USER_ACADEMIC_TITLE" );
        $no_tc = count( $contacts_toconfirm );
    $vusers = array();
    foreach ($contacts_toconfirm as $user) {
      if (is_object($user)) $vusers[] = $user;
    }
    // pre-load data of faculties
    $cache = get_cache_function( "ORGANIZATION", 86400 );
        $faculties = $cache->call( "lms_steam::get_faculties" );
    // pre-load attribute values for users
    steam_factory::load_attributes($GLOBALS["STEAM"]->get_id(), $vusers, $attributes);
    $result = array();
    foreach ($vusers as $user) {
      $myres = $user->get_attributes($attributes);
      $myres[OBJ_ID] = $user->get_id();
      if (is_object( $user->get_attribute(OBJ_ICON) )) {
        $myres[OBJ_ICON] = $user->get_attribute(OBJ_ICON)->get_id();
      }
      if (is_object( $user->get_attribute("USER_PROFILE_FACULTY") )) {
        $facname = $faculties[$user->get_attribute("USER_PROFILE_FACULTY")->get_id()];
        if (empty($facname) || $facname = 0 || strlen($facname) == 0) $facname = gettext("miscellaneous");

      } else $facname = gettext("miscellaneous");
      $myres["USER_PROFILE_FACULTY"] =$facname;
      $result[] = $myres;
    }

    return $result;
    }

// user_get_unconfirmed_contacts ssems to be never used
    public function user_get_unconfirmed_contacts( $username )
    {
        $steam_user = steam_factory::username_to_object( $GLOBALS[ "STEAM" ]->get_id(), $username );
        $unconfirmed_contacts = $steam_user->get_unconfirmed_contacts();
        $result = array();
        $attributes = array( 
            "USER_FIRSTNAME",
            "USER_FULLNAME",
            "USER_PROFILE_FACULTY",
            "USER_PROFILE_FOCUS",
            "OBJ_DESC",
            "OBJ_ICON",
            "USER_ACADEMIC_TITLE" );
    steam_factory::load_attributes($GLOBALS["STEAM"]->get_id(), $unconfirmed_contacts, $attributes);
        $no_uc = count( $unconfirmed_contacts );
        for ($i = 0; $i < $no_uc; $i++) {
            $contact = $unconfirmed_contacts[ $i ];
            $result[ $i ] = $contact->get_attributes( $attributes );
            if ( is_object( $result[ $i ][ "OBJ_ICON" ] ) ) {
                $result[ $i ][ "OBJ_ICON" ] = $result[ $i ][ "OBJ_ICON" ]->get_id();
                $result[ $i ][ "USER_PROFILE_FACULTY" ] = lms_steam::get_faculty_name( $result[ $i ][ "USER_PROFILE_FACULTY" ] );
            }
        }

        return $result;
    }

    public function user_get_buddies( $username, $confirmed = TRUE , $additional_attributes = array())
    {
        $result = array();
        $steam_user = steam_factory::username_to_object( $GLOBALS[ "STEAM" ]->get_id(), $username );
        if ($confirmed) {
            $confirmed_contacts = $steam_user->get_attribute( "USER_CONTACTS_CONFIRMED" );
            $buddies = array();
            if ( ! is_array( $confirmed_contacts ) )
                $confirmed_contacts = array();

                while ( list( $id, $confirmation ) = each( $confirmed_contacts ) ) {
                    $buddy = new steam_user( $GLOBALS[ "STEAM" ]->get_id(), $id );
                    // ignore invalid buddy entries:
                    if ( is_object( $buddy ) ) $buddies[] = $buddy;
                }
        } else {
            $buddies_tmp = $steam_user->get_attribute( "USER_FAVOURITES" );
            $buddies = array();
            // ignore invalid buddy entries:
            foreach ($buddies_tmp as $buddy) {
                if ( is_object( $buddy ) ) $buddies[] = $buddy;
            }
        }
        $query = array(
            "OBJ_NAME",
            "OBJ_DESC",
            "USER_FIRSTNAME",
            "USER_FULLNAME",
            "OBJ_ICON",
            "USER_PROFILE_FOCUS",
            "USER_PROFILE_FACULTY",
            "USER_ACADEMIC_TITLE" );
        $query = array_merge($query, $additional_attributes);
        $no_buddies = count( $buddies );
        $i = 0;
    steam_factory::load_attributes($GLOBALS["STEAM"]->get_id(), $buddies, $query);
        foreach ($buddies as $buddy) {

            if ($buddy instanceof steam_user) {
                $tmp = $buddy->get_attributes( $query );
                if (! $confirmed) {
                    if ( $steam_user->contact_is_confirmed( $buddy ) ) {
                        $tmp[ "USER_CONFIRMED" ] = 1;
                    } else {
                        $tmp[ "USER_CONFIRMED" ] = 0;
                    }
                }
                $result[ $i ] = $tmp;
                if ( is_object ($result[ $i ][ "OBJ_ICON" ] ) ) {
                    $result[ $i ][ "OBJ_ICON" ] = $result[ $i ][ "OBJ_ICON" ]->get_id();
                } else {
                    $result[ $i ][ "OBJ_ICON" ] = 0;
                }
                $result[ $i ][ "OBJ_ID" ] = $buddy->get_id();
                $i++;
            }
        }
        usort( $result, "sort_buddies" );

        return $result;
    }

    public static function group_get_members( $group_id, $matriculation_numbers = FALSE )
    {
        if (! $group = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $group_id ) ) {
            throw new Exception( "No valid object id ($group_id).", E_PARAMETER );
        }
        if (! $group instanceof steam_group) {
            throw new Exception( "Object is not a group ($group_id).", E_PAREMETER );
        }
        $members = $group->get_members();
        $attributes= array( 
            "USER_FIRSTNAME",
            "USER_FULLNAME",
            "OBJ_ICON",
            "OBJ_DESC",
            "USER_ACADEMIC_TITLE",
            "USER_SUBJECT_AREA",
            "USER_PROFILE_FACULTY",
            "USER_PROFILE_FOCUS",
            "USER_EMAIL",
            "OBJ_NAME",
            "USER_CONTACTS_CONFIRMED",
            "USER_TRASHED" );
        if ($matriculation_numbers) {
            $attributes[] = "ldap:USER_MATRICULATION_NUMBER";
        }
    steam_factory::load_attributes($GLOBALS["STEAM"]->get_id(), $members, $attributes);
        $i = 0;
        $result = array();
        foreach ($members as $member) {
            if ($member instanceof steam_group) {
                continue;
            }
            $result[ $i ] = $member->get_attributes( $attributes );
            $result[ $i ][ "OBJ_NAME" ] = $member->get_name();
            $result[ $i ][ "OBJ_ID" ]   = $member->get_id();
            if ( is_object( $result[ $i ][ "OBJ_ICON" ] ) ) {
                $result[ $i ][ "OBJ_ICON" ] = $result[ $i ][ "OBJ_ICON" ]->get_id();
            } else {
                $result[ $i ][ "OBJ_ICON" ] = 0;
            }
            $faf = lms_steam::get_faculty_name( $result[ $i ][ "USER_PROFILE_FACULTY" ] );
            $faf .= ( empty( $result[ $i ][ "USER_PROFILE_FOCUS" ] ) ) ? "" : ", " . $result[ $i ][ "USER_PROFILE_FOCUS" ];
            $result[ $i ][ "USER_PROFILE_FACULTY" ] = $faf;
            $i++;
        }
        usort( $result, "sort_buddies" );

        return $result;
    }

    public function group_get_subgroups( $group_id )
    {
        if (! $group = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $group_id ) ) {
            throw new Exception( "No valid object id ($group_id).", E_PARAMETER );
        }
        if (! $group instanceof steam_group) {
            throw new Exception( "Object is not a group ($group_id).", E_PAREMETER );
        }

        $subgroups = $group->get_subgroups();
        $result = array();
        $i = 0;
    $attributes = array( OBJ_NAME, OBJ_DESC, OBJ_CREATION_TIME );
    steam_factory::load_attributes($GLOBALS["STEAM"]->get_id(), $subgroups, $attributes);
        foreach ($subgroups as $subgroup) {
            $result[ $i ][ "OBJ_ID" ]   = $subgroup->get_id();
            $result[ $i ][ "OBJ_NAME" ] = $subgroup->get_name();
            $result[ $i ][ "OBJ_DESC" ] = $subgroup->get_attribute( "OBJ_DESC" );
            $result[ $i ][ "NO_MEMBERS" ] = $subgroup->count_members();
            $result[ $i ][ "OBJ_CREATION_TIME" ] = $subgroup->get_attribute( "OBJ_CREATION_TIME" );
            $subsub = $subgroup->get_subgroups();
            $result[ $i ][ "NO_SUBGROUPS" ] = count( $subsub );
            $i++;
        }

        return $result;
    }

    public function group_is_public( $group_id )
    {
        $group = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $group_id, CLASS_GROUP );
        $public = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), STEAM_PUBLIC_GROUP, CLASS_GROUP );
        if ( $public->is_parent( $group ) ) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function group_is_private( $group_id )
    {
        $group = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $group_id, CLASS_GROUP );
        $private = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), STEAM_PRIVATE_GROUP, CLASS_GROUP );
        if ( $private->is_parent( $group ) ) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public static function user_get_events( $ts_start, $ts_end )
    {
        $steam_user = lms_steam::get_current_user();
        $calendar = $steam_user->get_calendar();
        $date_objects = $calendar->get_date_objects( $ts_start, $ts_end );
        $attributes = array(
                "DATE_START_DATE",
                "DATE_END_DATE",
                "DATE_TITLE",
                "DATE_DESCRIPTION",
                "DATE_LOCATION",
                "DATE_URL"
                );
        foreach ($date_objects as $date) {
            $date->get_attributes( $attributes, TRUE, TRUE );
        }
        $GLOBALS[ "STEAM" ]->buffer_flush();
        $result = array();
        foreach ($date_objects as $date) {
            $result[] = array_merge($date->get_attributes( ), array( "DATE_ID" => $date->get_id() ));
        }
        usort( $result, "sort_dates_asc" );

        return $result;
    }

  public function get_annotation_data( $annotation )
  {
      $query = array( "OBJ_NAME", "OBJ_CREATION_TIME","DOC_MIME_TYPE" );
      $result = $annotation->get_attributes( $query );
            if (!strstr($result["DOC_MIME_TYPE"], "text"))
                $result[ "CONTENT" ] = "";
            else
                $result[ "CONTENT" ] = $annotation->get_content();
            $result[ "OBJ_ID" ] = $annotation->get_id();
            $creator = $annotation->get_creator();
            $creator_data = $creator->get_attributes( array(
                "OBJ_NAME",
                "USER_FIRSTNAME",
                "USER_FULLNAME",
                "OBJ_ICON" ));
            $result[ "OBJ_CREATOR" ] = $creator_data[ "USER_FIRSTNAME" ] . " " . $creator_data[ "USER_FULLNAME" ];
            $result[ "OBJ_CREATOR_LOGIN" ] = $creator_data[ "OBJ_NAME" ];
            $icon = $creator_data[ "OBJ_ICON" ];
            if ($icon instanceof steam_object) {
                $result[ "OBJ_ICON" ] = $icon->get_id();
            } else {
                $result[ "OBJ_ICON" ] = 0;
            }

      return $result;
  }

    public function get_annotations( $doc_id )
    {
        $res = array();
        $steam_doc = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $doc_id, CLASS_OBJECT );
        $annotations = $steam_doc->get_annotations();
        ;
        $i = 0;
    $query = array( "OBJ_NAME", "OBJ_CREATION_TIME","DOC_MIME_TYPE" );
    $data_tnr = array();
        foreach ($annotations as $annotation) {
      $data_tnr[$i] = array();
      $data_tnr[$i]["ATTRIBUTES"] = $annotation->get_attributes( $query, TRUE );
            $data_tnr[$i]["CREATOR"] = $annotation->get_creator(TRUE);
            $i++;
        }
    $data_result = $GLOBALS["STEAM"]->buffer_flush();

    $i = 0;
    $more_tnr = array();
        foreach ($annotations as $annotation) {
      $more_tnr[$i] = array();
            if (strstr($data_result[$data_tnr[$i]["ATTRIBUTES"]][DOC_MIME_TYPE], "text")) $more_tnr[$i][ "CONTENT" ] = $annotation->get_content(TRUE);
            $result[ "OBJ_ID" ] = $annotation->get_id();
            $more_tnr[$i]["CREATOR_DATA"] = $data_result[$data_tnr[$i]["CREATOR"]]->get_attributes( array( 
                "OBJ_NAME",
                "USER_FIRSTNAME",
                "USER_FULLNAME",
                "OBJ_ICON" ), TRUE );
            $i++;
        }
    $more_result = $GLOBALS["STEAM"]->buffer_flush();

    $i = 0;
        foreach ($annotations as $annotation) {
      $result = $data_result[$data_tnr[$i]["ATTRIBUTES"]];
            if (strstr($result["DOC_MIME_TYPE"], "text")) $result[ "CONTENT" ] = $more_result[$more_tnr[$i]["CONTENT"]];
            $result[ "OBJ_ID" ] = $annotation->get_id();
            $result[ "OBJ_CREATOR" ] = $more_result[$more_tnr[$i]["CREATOR_DATA"]][ "USER_FIRSTNAME" ] . " " . $more_result[$more_tnr[$i]["CREATOR_DATA"]][ "USER_FULLNAME" ];
            $result[ "OBJ_CREATOR_LOGIN" ] = $more_result[$more_tnr[$i]["CREATOR_DATA"]][ "OBJ_NAME" ];
            $icon = $more_result[$more_tnr[$i]["CREATOR_DATA"]][ "OBJ_ICON" ];
            if ($icon instanceof steam_object) {
                $result[ "OBJ_ICON" ] = $icon->get_id();
            } else {
                $result[ "OBJ_ICON" ] = 0;
            }
      $res[] = $result;
            $i++;
        }

        return $res;
    }

// Seems to be never used
    public function container_get_objects( $container_id, $object_class = "", $attributes = array( "DOC_SIZE" ), $sort = SORT_NONE, $follow_links = TRUE )
    {
        $container = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $container_id, CLASS_CONTAINER );
    try {
      $inventory = $container->get_inventory( $object_class, $attributes, $sort, $follow_links );
    } catch (Exception $ex) {
      throw new Exception("container_id is invalid or is no container (id=" . $containerid . ")", E_PARAMETER);
    }
        $n = count( $inventory );
        for ($i = 0; $i < $n; $i++) {
            $object = $inventory[ $i ];
            $result[ $i ] = $object->get_attributes();
            $result[ $i ][ "OBJ_CLASS" ] = get_class( $object );
            $result[ $i ][ "OBJ_ID" ] = $object->get_id();
            $result[ $i ][ "OBJ_CREATOR" ] = $object->get_creator()->get_id();
        }

        return $result;
    }

    public static function is_reader( $steam_doc, $steam_user, $pBuffer = FALSE )
    {
        $read_mod = $GLOBALS[ "STEAM" ]->get_module( "table:read-documents" );

        return $GLOBALS[ "STEAM" ]->predefined_command(
                $read_mod,
                "is_reader",
                array( $steam_doc, $steam_user ),
                $pBuffer
                );
    }

    public function search_user_posts( $message_board, $user )
    {
        if ( ! $search_mod = $GLOBALS[ "STEAM" ]->get_module( "package:searchsupport" ) )
            throw new Exception( "sTeam 'package:searchsupport' not installed." );

        return $GLOBALS[ "STEAM" ]->predefined_command(
            $search_mod,
            "search_user_posts",
            array( $message_board->get_id(), $user ),
            0
        );
    }

    public function search_messageboard( $message_board, $pattern )
    {
        if ( ! $search_mod = $GLOBALS[ "STEAM" ]->get_module( "package:searchsupport" ) )
            throw new Exception( "sTeam 'package:searchsupport' not installed." );

        return $GLOBALS[ "STEAM" ]->predefined_command(
            $search_mod,
            "search_messageboard",
            array( $message_board->get_id(), $pattern ),
            0
        );
    }

    public function get_readers( $steam_doc, $pBuffer = FALSE )
    {
        $read_mod = $GLOBALS[ "STEAM" ]->get_module( "table:read-documents" );

        return $GLOBALS[ "STEAM" ]->predefined_command(
                $read_mod,
                "get_readers",
                array( $steam_doc ),
                $pBuffer
                );
    }

    public function get_group_communication_objects( $container_id, $class = CLASS_DOCUMENTS, $attributes = array())
    {
        $user = lms_steam::get_current_user();
        $container = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $container_id, CLASS_CONTAINER);
        $results = array();
        $inventories = array();
        $pattern = CLASS_ROOM | CLASS_CONTAINER;
        $r = 0;
        if ( ! ( ( $pattern & $class ) == $class ) ) {
            $pattern = $pattern | $class;
        }
    $inventories = $container->get_inventory_raw( $pattern );
    $attributes = array(OBJ_NAME, KOALA_ACCESS, OBJ_TYPE, OBJ_DESC, "WEBLOG_LANGUAGE", "FORUM_LANGUAGE", "WIKI_LANGUAGE");
    $tnr = array();
    foreach ($inventories as $item) {
      $tnr[$item->get_id()] = array();
      $tnr[$item->get_id()]["read_access"] = $item->check_access_read( $user, TRUE );
      $tnr[$item->get_id()]["attributes"] = $item->get_attributes($attributes, TRUE);
    }
    $result = $GLOBALS["STEAM"]->buffer_flush();

        foreach ($inventories as $item) {
            if ( ( $item instanceof steam_object ) &&  $result[$tnr[$item->get_id()]["read_access"]] ) {
                if ( ( $item->get_type() & $class )  ) {
                    $results[ $r ][ "OBJ_NAME" ] = $result[$tnr[$item->get_id()]["attributes"]][OBJ_NAME];
                    $results[ $r ][ "OBJ_ID" ] = $item->get_id();
                    $results[ $r ][ "OBJ_DESC" ] = $result[$tnr[$item->get_id()]["attributes"]][OBJ_DESC];
          $results[ $r ][ "OBJ_TYPE" ] = $result[$tnr[$item->get_id()]["attributes"]][OBJ_TYPE];
          $results[ $r ][ "KOALA_ACCESS" ] = $result[$tnr[$item->get_id()]["attributes"]][KOALA_ACCESS];
                    $results[ $r ][ "OBJ_CLASS" ] = get_class( $item );
                    $r++;
                }
            }
        }
    usort( $results, "sort_objects" );

        return $results;
    }

  // TODO: Use Buffering here !!
// Seems to be never used
    public function get_inventory_recursive( $container_id, $class = CLASS_DOCUMENTS, $attributes = array() )
    {
        $user = lms_steam::get_current_user();
        $container = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $container_id, CLASS_CONTAINER);
        $results = array();
        $inventories = array();
        $pattern = CLASS_ROOM | CLASS_CONTAINER;
        $r = 0;
        if ( ! ( ( $pattern & $class ) == $class ) ) {
            $pattern = $pattern | $class;
        }
    try {
      if ( $container->check_access_read( $user ) ) {
        $inventories = $container->get_inventory( $pattern, $attributes, TRUE );
      }
    } catch (Exception $ex) {
      throw new Exception( "Is not a container: id=" . $container_id, E_PARAMETER );
    }
        while ( $item  = array_shift( $inventories ) ) {
            if ( ( $item instanceof steam_object ) &&  $item->check_access_read( $user ) ) {
                if ($item instanceof steam_container) {
                    $inventory = $item->get_inventory( $pattern, $attributes, TRUE );
                    foreach ($inventory as $i) {
                        $inventories[] = $i;
                    }
                }
                if ( ( $item->get_type() & $class )  ) {
                    $results[ $r ][ "OBJ_NAME" ] = $item->get_name();
                    $results[ $r ][ "OBJ_ID" ] = $item->get_id();
                    $results[ $r ][ "OBJ_DESC" ] = $item->get_attribute( "OBJ_DESC" );
          $results[ $r ][ "KOALA_ACCESS" ] = $item->get_attribute( KOALA_ACCESS );
                    $results[ $r ][ "OBJ_CLASS" ] = get_class( $item );
                    foreach ($attributes as $attribute) {
                        $results[ $r ][ $attribute ] = $item->get_attribute( $attribute );
                    }
                    $r++;

                }
            }
        }

        return $results;
    }

    public function get_icon( $class_type )
    {
        switch ($class_type) {
            case "steam_document":
                return PATH_STYLE . "images/knowit.document.gif";
            break;
            case "steam_container":
                return PATH_STYLE . "images/folder.png";
            break;
        }
    }

    public function get_environment_link( $object )
    {
        $env = $object->get_environment();

        return array( "name" => gettext( "Context" ) . ": " . $env->get_name(), "link" => PATH_URL . "environment/" . $env->get_id() . "/" );
    }

    public static function disconnect()
    {
        if (isset($GLOBALS[ "STEAM" ])) {
            return $GLOBALS[ "STEAM" ]->disconnect();
        }
    }

    /**
     * function mail:
     *
     * Sends a message to the user through the open sTeam mail system.
   * If the Recipient is a Group the mail will be delivered to all groupmembers.
   * A Copy of this message will be stored within the steam mailbox of the user.
     * If the user has set its attribute USER_FORWARD_MSG as true, this message
   * will be delivered also as e-mail to its account.
     *
   * As this Method reads some values from the parameter $pSender (in case
   * $pSender is an object) it is not allowed to call this method using the
   * buffer of the steam_connector.
   *
   * The E-Mail was sent as text/html in any case. The given messagebody will be
   * converted to html by default
   *
   * @param Object  $pUserOrGroup a user or a group to send the given mail to
     * @param String           $pSubject     message's subject
     * @param String           $pMessageBody Message. As Plaintext by default.
     * @param Object_or_String $pSender      The user sending the E-Mail or a string
   * containing the sender as string in teh following format: "\"" . $name .
   * "\"<login@server.com>" with $name as printed quotable encoded string. You
   * may use steam_connector::quoted_printable_encode() to encode the name
   * correctly. An Example: $userobject->mail( "a subject", " a message", "\"" .
   * steam_connector::quoted_printable_encode("Firstname Lastname") . "\"<login@server.com>");
   * @param String  $pMimeType The mimetype of the given Messagebody
     */
   public function mail($pUserOrGroup, $pSender = 0, $pSubject, $pMessageBody, $pMimeType = "text/plain")
   {
       if (! SYSTEMCONFIG_FUNCTION_MAIL) {
           logging::write_log( LOG_MESSAGES, "Mail function is disabled. Mail " . $pSubject . " wasn't sent.");

           return;
       }

     if ( !is_object( $pUserOrGroup ) ) {
       throw new Exception( "Invalid Recipient of E-Mail in lms_steam::mail()", E_PARAMETER );
     }
     if ( strtolower($pUserOrGroup->get_name()) == "steam") {
        $time1 = microtime(TRUE);
        logging::write_log( LOG_MESSAGES, "lms_steam::mail()\tSPOOF\tAttempt to send mail to steam group \tname=" . lms_steam::get_current_user()->get_name() . " (id=" . lms_steam::get_current_user()->get_id() . ")\tsubject=" . $pSubject . "\tbody=" . $pMessageBody);
       throw new Exception( "Invalid Recipient of E-Mail in lms_steam::mail():  Sending Mails to group steam is vorbidden", E_USER_RIGHTS );
     }
     if ( is_object( $pSender ) ) {
       // Construct valid Sender-String as it is used within the koaLA System
       // Firstname, Lastname and USER_EMAIL
       $pSender->get_attributes(array(
           "USER_FULLNAME",
           "USER_FIRSTNAME",
           "USER_EMAIL"));
       $name = $pSender->get_attribute("USER_FIRSTNAME") . " " . $pSender->get_attribute("USER_FULLNAME");
       // Provide the correct encoding (quoted printable)
       // Hint: your PHP installation must have ctype_alpha
       // TODO: Enable if LDAP returns umlauts in first and lastname. Check
       // wether the given string contains only ascii chars and skip the
       // encoding to reduce SPAM Score
       // $name = steam_connector::quoted_printable_encode($name);
       // construct a valid senderstring to avoid SPAM Rating
       $senderstring = "\"" . $name . "\"" . "<" . $pSender->get_attribute(USER_EMAIL) . ">";
     } elseif ( is_string($pSender) ) {
       $senderstring = $pSender;
     } else {
       throw new Exception( "Invalid Sender of E-Mail in lms_steam::mail()", E_PARAMETER );
     }
     if ($pMimeType === "text/plain") {
       // replace linebreaks with <br />
       $HtmlMessageBody = str_replace( "\n", "", nl2br( str_replace( "\r\n", "\n", $pMessageBody ) ) );
     } else $HtmlMessageBody = $pMessageBody;
     // send the mail to the user or group
//error_log("lms_steam::mail(): sending email. subject=" . $pSubject . " messagebody=" . $HtmlMessageBody . " senderstring=" . $senderstring);
     $pUserOrGroup->mail($pSubject, $HtmlMessageBody, $senderstring);
   }

    public function delete( $obj = FALSE )
    {
        $trashbin = $GLOBALS["STEAM"]->get_current_steam_user()->get_trashbin();
        if ( !is_object( $obj ) )
            throw new Exception( "Delete failed. given obj is not an object", E_PARAMETER );

    $annotating = $obj->get_annotating();
    if (is_object($annotating)) {
      $annotating->remove_annotation( $obj );
    }

        if ( is_object( $trashbin ) )
            $ret = $obj->move( $trashbin );
        else
            $ret = $obj->delete();

        return $ret;
    }

   public function to_clipboard( $obj = FALSE )
   {
      $clipboard = $GLOBALS["STEAM"]->get_current_steam_user();
      if (!is_object( $obj )) {
        throw new Exception( "Cannot move object to clipboard, given obj is not an object", E_PARAMETER );
      }
      if (is_object($clipboard)) {
        $obj->move($clipboard);
        $ret = TRUE;
      } else $ret = FALSE;

      return $ret;
   }
}
