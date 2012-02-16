<?php
define("POINT_OK", 1);
define("POINTERROR_NOINT", 2);
define("POINTERROR_LOWERZERO", 3);
define("POINTERROR_GREATERMAX", 4);

$backlink = $backlink . $unit->get_id();

function ldap_mnr2login( $mnr = FALSE ) {
  // trim leading "0" to increase convenience using Barcode Scanners
  // entering points
  $mnr = ltrim($mnr,'0');
  try {
    $lms_ldap = new lms_ldap();
    $lms_ldap->bind( LDAP_LOGIN, LDAP_PASSWORD );
  }
  catch ( Exception $e ) {
    throw new Exception( $e->getMessage(), E_LDAP_SERVICE_ERROR );
  }
  $ldap_login = $lms_ldap->studentid2uid( $mnr );
  return $ldap_login;
}


function check_point($point, $max) {
  $point = trim($point);
  if ($point === "") return POINT_OK;
//  if (!preg_match ("/^([0-9]+)$/", $point)) return POINTERROR_NOINT;
  if (!preg_match ('#^[0-9]+(,[0-9]{1,2})?$#', $point)) return POINTERROR_NOINT;
  if ((int)$point < 0) return POINTERROR_LOWERZERO;
  if ((int)$point > $max) return POINTERROR_GREATERMAX;
  return POINT_OK;
}

function check_points($points, $maxpoints) {
  if (!is_array($points)) $points = array();
  if (!is_array($maxpoints)) $maxpoints = array();
  $keys = array_keys($points);
  foreach($keys as $key) {
    if (!is_array($points[$key])) $points[$key] = array();
    $keys2 = array_keys($points[$key]);
    foreach ($keys2 as $key2) {
      if (check_point( $points[$key][$key2], $maxpoints[$key2] ) !== POINT_OK) return FALSE;
    }
  }
  return TRUE;
}

// ABFRAGEN
$problems = "";
$hints    = "";

$write_mode=FALSE;
// Process the POST of the whole List
if ( $_SERVER[ "REQUEST_METHOD" ] == "POST")
{
  if ( isset($_POST["enterpoints"])) {
    $portal->add_javascript_onload("unit_pointlist", "document.getElementById('enterpoints_key').focus();");
    $member_id = FALSE;
    $aktsheet = $_POST["sheetnumber"];
    $point = $_POST["enterpoints_value"];
    $member_value = trim($_POST["enterpoints_key"]);
    $mnr_invalid = FALSE;
    $participants = $unit->get_attribute("UNIT_POINTLIST_PARTICIPANTS");
    // Determine if matrikelumber is given
    if (empty($member_value)) {
      $problems .= gettext("Login/Mnr is missing") . ". ";
      $hints .= gettext("Please enter the login name or the matriculation number for the member you want to set points for") . ". ";
    } else {
      // if NOT A NUMBER (if not a matriculationnumber)
      if (!preg_match ('#^[0-9]+#', $member_value)) { 
        // find user using login name
        $mymember = steam_factory::username_to_object($GLOBALS["STEAM"]->get_id(), $member_value);
        if (!is_object($mymember)) {
            $problems .= gettext("User not found") . ". ";
            $hints .= str_replace("%LOGIN", $member_value, gettext("User with login '%LOGIN' not found") . ". ");
        } else {
          $member_id = $mymember->get_id();
          if (!$participants->is_member($mymember)) {
            $show_member = FALSE;
            $problems .= gettext("Not a member") . ". ";
            $hints .= str_replace("%LOGIN", $member_value, gettext("User with login '%LOGIN' is not a member of the participant group of this pointlist") . ". ");
          } else $show_member = $mymember;
        } 
      } else {
        if (units_pointlist::check_matriculation_number($member_value) === FALSE) {
          $problems .= gettext("Invalid Matriculation number.") . " ";
          $hints .= str_replace("%MNR", $member_value, gettext("The Matriculationnumber '%MNR' seems to be invalid.") . " ");
          $mnr_invalid = TRUE;
        } else {
          $ldaperror = FALSE;
          // Find user using the Matriculation number
          try {
            $loginname = ldap_mnr2login( $member_value );
          } catch (Exception $e) {
            $ldaperror = TRUE;
            $problems .= gettext("Cannot set points");
            $hints .= str_replace("%ERROR", $e->getMessage(), gettext("An error occured during LDAP access. The error message is '%ERROR'"));
    }
          if (!$ldaperror) {
            if ($loginname === FALSE || !(strlen($loginname)>0) ) {
              $problems .= gettext("User not found");
              $hints .= str_replace("%MNR", $member_value, gettext("Invalid Matriculation number"));
            } else {
              $mymember = steam_factory::username_to_object($GLOBALS["STEAM"]->get_id(), $loginname );
            }
            if (!is_object($mymember)) {
              $problems .= gettext("User not found");
              $hints .= str_replace("%MNR", $member_value, gettext("User with matriculation number '%MNR' not found in koaLA"));
            } else {
              $member_id = $mymember->get_id();
              if (!$participants->is_member($mymember)) {
                $show_member = FALSE;
                $problems .= gettext("Not a member") . " ";
                $hints .= str_replace("%MNR", $member_value, gettext("User with matriculation number '%MNR' is not a member of the participant group of this pointlist") . " ");
              } else $show_member = $mymember;
            }
          }
        }
      }
    }

    if (empty($point) && $point !="0") {
      $problems .= gettext("Point value is missing") . ". ";
      $hints .= gettext("Please enter a point value you want to set") . ". ";
    }
    
    if(!isset($ldaperror)){$ldaperror=FALSE;}
    if (!$mnr_invalid && !$ldaperror && $member_id === FALSE) {
      $problems .= gettext("Member id is missing") . ". ";
      $hints .= gettext("Please enter a member id") . ". ";
    }

    if (empty($problems)) {
      // Process Data
      $proxy = $unit->get_attribute("UNIT_POINTLIST_PROXY");
      
      $points = $proxy->get_attribute("UNIT_POINTLIST_POINTS_" . $member_id);
      $maxpoints = $proxy->get_attribute("UNIT_POINTLIST_MAXPOINTS");
      if (!is_array($points)) $points = array(); 
      if (!is_array($maxpoints)) $maxpoints = array();
      
      $check_point = check_point( $point, $maxpoints[$aktsheet] );
      if ($check_point === POINT_OK) {
        $member_points = $points;
        if (!is_array($member_points)) $member_points = array();
        $member_points[$aktsheet] = $point;
        $points = $member_points;
        $proxy->set_attribute("UNIT_POINTLIST_POINTS_" . $member_id, $points);
        if (!preg_match ('#^[0-9]+#', $member_value)) {
        $portal->set_confirmation( str_replace( "%POINTS", $point , str_replace( "%SHEET", $aktsheet, str_replace("%USER", $member_value, gettext(" %POINTS Points saved for user '%USER' for sheet %SHEET") ) ) ) );
        } else {
          $portal->set_confirmation( str_replace( "%POINTS", $point , str_replace( "%SHEET", $aktsheet, str_replace("%MNR", $member_value, gettext(" %POINTS Points saved for user with matriculation number '%MNR' for sheet %SHEET") ) ) ) );

        }
      } else {
        switch ($check_point) {
          case POINTERROR_NOINT: $hints .= gettext("Not a number") . ". ";
                            break;
          case POINTERROR_LOWERZERO: $hints .= gettext("Lower than zero") . ". ";
                            break;
          case POINTERROR_GREATERMAX: $hints .= gettext("Greater than max") . ". ";
                            break;
          default: $hints = gettext("Invalid input") . ". ";
                   break;
        };
        if (!empty($hints)) $problems .= gettext("Invalid point value") . ". ";
        if (!empty($problems)) $portal->set_problem_description( $problems, $hints );
      }
    } else {
      $portal->set_problem_description( $problems, $hints );
    }
  }
  // Process setting all 
  if ( isset($_POST["values"]) && isset($_POST["save"])) {
    $write_mode = TRUE;
    if ( ! $course->is_admin( $user )  ) {
    throw new Exception( "No course admin!", E_ACCESS );
    }
    $values = $_POST[ "values" ];
    
    $points = $values["points"];
    $maxpoints = $values["maxpoints"];
    if (!is_array($points)) $points = array();
    
    $error = FALSE;
    foreach($points as $key => $p) {
      if (!check_points(array($key => $p), $maxpoints)) {
        $error = TRUE;
        break; // end the excecution
      }
    }
    if ($error) {
      $problems .= gettext("Invalid values");
      $hints .= gettext("Please correct the invalid values and save again");
    }
    if ( empty( $problems ) )
    {
       $proxy = $unit->get_attribute("UNIT_POINTLIST_PROXY");
       //$proxy->set_attribute("UNIT_POINTLIST_POINTS", $points);
        foreach($points as $key => $p) {
          $akey = "UNIT_POINTLIST_POINTS_" . $key;
          $proxy->set_attribute($akey, $p, TRUE);
        }
        $GLOBALS["STEAM"]->buffer_flush();
       $portal->set_confirmation( gettext("Points saved") );
    }
    else {
      $portal->set_problem_description( $problems, $hints );
    }
  }
} else {
  if (isset($_GET["write_mode"]) && $_GET["write_mode"] === "true") {
    $write_mode = TRUE;
    $proxy = $unit->get_attribute("UNIT_POINTLIST_PROXY");
    $locks = $proxy->get_attribute("UNIT_POINTLIST_LOCKS");
    if (!is_array($locks)) $locks = array();
    foreach($locks as $login => $timestamp) {
      if ($login != $user->get_name() &&  time() - $timestamp < 1800) {
        $portal->set_problem_description( str_replace("%LOGIN" , $login, gettext("Please note that user '%LOGIN' opened this pointlist in write mode within the last 30 minutes.") . "<br/>" . gettext("Please make sure not to open this pointlist in write mode at the same time. Parallel writing may result in overwriting each others input.") ) );
      }
    }
    $user = lms_steam::get_current_user();
    $locks[$user->get_name()] = time();
    $locks = $proxy->set_attribute("UNIT_POINTLIST_LOCKS", $locks);
  }
}

if (!defined("PATH_TEMPLATES_UNITS_POINTLIST")) define( "PATH_TEMPLATES_UNITS_POINTLIST", PATH_EXTENSIONS . "units_pointlist/templates/" );

$html_handler_course = new koala_html_course( $course );
$html_handler_course->set_context( "units", array( "subcontext" => "unit" ) );
$content = new HTML_TEMPLATE_IT();

$content->loadTemplateFile( PATH_TEMPLATES_UNITS_POINTLIST . "units_pointlist.template.html" );

// Load general data
$attributes = array(
  "OBJ_DESC",
  "OBJ_LONG_DESC",
  "UNIT_POINTLIST_COUNT",
  "UNIT_POINTLIST_PROXY",
  "UNIT_POINTLIST_PARTICIPANTS",
  "UNIT_POINTLIST_TUTORIALGROUP",
  "UNIT_POINTLIST_BONUS_1",
  "UNIT_POINTLIST_BONUS_2",
);

$data = $unit->get_attributes( $attributes );

$proxy = $data["UNIT_POINTLIST_PROXY"];
$group = $data["UNIT_POINTLIST_PARTICIPANTS"];
$bonus_1 = $data["UNIT_POINTLIST_BONUS_1"];
$bonus_2 = $data["UNIT_POINTLIST_BONUS_2"];
if (empty($bonus_1) || $bonus_1 < 1) $bonus_1 = FALSE;
if (empty($bonus_2) || $bonus_2 < 1) $bonus_2 = FALSE;
  
$tutorialgroup = FALSE;
if (is_object($data["UNIT_POINTLIST_TUTORIALGROUP"]) && $data["UNIT_POINTLIST_TUTORIALGROUP"] instanceof steam_group) {
  $tutorialgroup = $data["UNIT_POINTLIST_TUTORIALGROUP"];
}

// Prepare some variables
$show_all = FALSE;
if (isset($_GET["show"]) && $_GET["show"] === "all") $show_all = TRUE;
if (!isset($show_member)) $show_member = FALSE;
$show_matriculationnumber = FALSE;
if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" && (isset($_POST["choosemember_login"]) || isset($_POST["choosemember_mnr"]))) {
  $cm_login = FALSE;
  $cm_mnr = FALSE;
  if (isset($_POST["choosemember_login"]) && isset($_POST["choosemember"])) {
    if (isset($_POST["choosemember"]["login"])) {
      $portal->add_javascript_onload("units_pointlist", "document.getElementById('choosemember[login]').focus();");
      $show_member = steam_factory::username_to_object($GLOBALS["STEAM"]->get_id(), $_POST["choosemember"]["login"]);
      $display_member_value = $_POST["choosemember"]["login"];
      if (!is_object($show_member)) {
        $problems .= gettext("User not found");
        $hints .= str_replace("%LOGIN", $_POST["choosemember"]["login"], gettext("User with login '%LOGIN' not found"));
      } else if (!$group->is_member($show_member)) {
          $show_member = FALSE;
          $problems .= gettext("Not a member");
          $hints .= str_replace("%LOGIN", $_POST["choosemember"]["login"], gettext("User with login '%LOGIN' is not a member of the participant group of this pointlist"));
      }
    }
  }
  if (isset($_POST["choosemember_mnr"]) && isset($_POST["choosemember"]["mnr"])) {
    $show_matriculationnumber = TRUE;
    if ( strlen(trim($_POST["choosemember"]["mnr"])) == 0 ) {
      $problems .= gettext("Matriculation number is missing");
      $hints .= gettext("Please enter a matriculation number to search for");
    } else {
      if (units_pointlist::check_matriculation_number($_POST["choosemember"]["mnr"]) === FALSE) {
          $problems .= gettext("Invalid Matriculation number.") . " ";
          $hints .= str_replace("%MNR", $_POST["choosemember"]["mnr"], gettext("The Matriculationnumber '%MNR' seems to be invalid.") . " ");
          $mnr_invalid = TRUE;
      } else {
        $display_member_value = $_POST["choosemember"]["mnr"];
        $ldaperror = FALSE;
        // Find user using the Matriculation number
        try {
          $loginname = ldap_mnr2login( trim($_POST["choosemember"]["mnr"]) );
        } catch (Exception $e) {
          $ldaperror = TRUE;
          $problems .= gettext("Matriculation number not found");
          $hints .= str_replace("%ERROR", $e->getMessage(), gettext("An error occured during LDAP access. The error message is '%ERROR'"));
        }
        if (!$ldaperror) {
          if ($loginname === FALSE || !(strlen($loginname)>0) ) {
      $problems .= gettext("User not found");
      $hints .= str_replace("%MNR", $_POST["choosemember"]["mnr"], gettext("Invalid Matriculation number"));
    } else {
      $show_member = steam_factory::username_to_object($GLOBALS["STEAM"]->get_id(), $loginname );
    }
    if (!is_object($show_member)) {
      $problems .= gettext("User not found");
      $hints .= str_replace("%MNR", $_POST["choosemember"]["mnr"], gettext("User with matriculation number '%MNR' not found in koaLA"));
    } else if (!$group->is_member($show_member)) {
        $show_member = FALSE;
        $problems .= gettext("Not a member");
        $hints .= str_replace("%MNR", $_GET["choosemember"]["mnr"], gettext("User with matriculation number '%MNR' is not a member of the participant group of this pointlist"));
          }
        }
      }
    }
  }
}

if ($problems != "") {
  $portal->set_problem_description( $problems, $hints );
}

if (defined("LOG_DEBUGLOG")) {
  $time1 = microtime(TRUE);
  $login = lms_steam::get_current_user()->get_name();
  logging::write_log( LOG_DEBUGLOG, "units_pointlist:\t " . $login . "\t" . $unit->get_display_name() . "\t" . $group->get_identifier() . "\t" . $group->count_members() . "\t" . date( "d.m.y G:i:s", time() ) . "... " );
}

if ($_SERVER[ "REQUEST_METHOD" ] == "POST" && isset($_POST["values"])) {
  $values = $_POST[ "values" ];
  $pointlist = $values["points"];
  $maxpoints = $values["maxpoints"];
}
else {
  
  // TODO Cut down Serverload by dividing the pointlist data storing the points for each participant in a single attribute
  // Load points
/*
  $time1= microtime(TRUE);
  $tnr_pointlist = $proxy->get_attribute("UNIT_POINTLIST_POINTS", TRUE);
  $tnr_maxpoints = $proxy->get_attribute("UNIT_POINTLIST_MAXPOINTS", TRUE);
  $result = $GLOBALS["STEAM"]->buffer_flush();
  $pointlist = $result[$tnr_pointlist];
  $maxpoints = $result[$tnr_maxpoints];
  error_log("Loading the pointlist took " . round((microtime(TRUE) - $time1) * 1000 ) . " ms");
*/
  if ($show_all) {
    $time1 = microtime(TRUE);
    $proxy_data = $proxy->get_all_attributes();
    //error_log("Loading proxy data took " . round((microtime(TRUE) - $time1) * 1000 ) . " ms");
    $maxpoints = $proxy_data["UNIT_POINTLIST_MAXPOINTS"];
    if (!is_array($maxpoints)) $maxpoints = array();
    $pointlist = units_pointlist::extract_pointlist($proxy_data);
  } else {
    $maxpoints = $proxy->get_attribute("UNIT_POINTLIST_MAXPOINTS");
    // Handle Point Data for single member separately
  }
}

// Check data integrity
if (!isset($pointlist) || !is_array($pointlist)) $pointlist = array();
if (!isset($maxpoints) || !is_array($maxpoints)) $maxpoints = array();
$count = $data["UNIT_POINTLIST_COUNT"];
if ($count < 0 || $count > 15) $count = 8;

// Fill template
$content->setVariable( "VALUE_DESC", $data["OBJ_DESC"] );
$content->setVariable( "VALUE_LONG_DESC", get_formatted_output( $data["OBJ_LONG_DESC"] ) );

// Table header
$content->setVariable("LABEL_NAME", gettext("Name"));
for ($i = 1; $i <= $count; $i++) {
  $content->setCurrentBlock("BLOCK_TABLE_HEADER");
  $content->setVariable("TABLE_HEADER_LABEL", $i);
  $content->parse("BLOCK_TABLE_HEADER");
}
//SUM
  $content->setCurrentBlock("BLOCK_TABLE_HEADER");
  $content->setVariable("TABLE_HEADER_LABEL", "&sum;");
  $content->parse("BLOCK_TABLE_HEADER");
// BONUS
  $content->setCurrentBlock("BLOCK_TABLE_HEADER");
  $content->setVariable("TABLE_HEADER_LABEL", gettext("Bonus"));
  $content->parse("BLOCK_TABLE_HEADER");

$user = lms_steam::get_current_user();

if (is_object($tutorialgroup)) {
  $content->setCurrentBlock("BLOCK_TUTORIAL");
  $content->setVariable("TUTORIALGROUP_INFO", str_replace( "%LINK", "<a href='" . $course->get_url() . "tutorials/" . $tutorialgroup->get_id() . "/'>" . $tutorialgroup->get_name() . " (" . $tutorialgroup->get_attribute(OBJ_DESC) . ")" . "</a>", gettext("This pointlist is associated with tutorial the group '%LINK'")) );
  $content->parse("BLOCK_TUTORIAL");
}

$is_admin =  $course->is_admin( $user );

if (isset($point) && empty($problems)) {
  $display_point = "";
} else {
	if(!isset($point)) $point=0;
	$display_point = $point;
}
if (!isset($display_member_value)) {
if (isset($member_value) && empty($problems)) {
  $display_member_value = "";
    //$display_member_value = $member_value;
} else {
	if(!isset($member_value)) $member_value=""; //fixed notice
	$display_member_value = $member_value;
	}
}

if ($is_admin) {
  if (isset($_POST) && isset($_POST["sheetnumber"])) $aktsheet = $_POST["sheetnumber"];
  else $aktsheet = 13;
  
  $content->setCurrentBlock("BLOCK_ENTERPOINTS"); 
  $content->setVariable("LABEL_ENTERPOINTS_KEY", gettext("Login/Mnr") . ":");
  $content->setVariable("LABEL_ENTERPOINTS_VALUE", gettext("Points") . ":");
  $content->setVariable("LABEL_ENTERPOINTS_SHEET", gettext("Sheet") . ":");
  $content->setVariable("NAME_ENTERPOINTS_SUBMIT", "enterpoints");
  $content->setVariable("LABEL_ENTERPOINTS", gettext("Enter Points") . ":");
  $content->setVariable("LABEL_ENTERPOINTS_SUBMIT", gettext("Save Points"));
  $content->setVariable("ENTERPOINTS_COLSPAN", $count+2);
  $content->setVariable("VALUE_ENTERPOINTS_KEY", $display_member_value);
  $content->setVariable("NAME_ENTERPOINTS_KEY", "enterpoints_key");
  $content->setVariable("VALUE_ENTERPOINTS_VALUE", $display_point);
  $content->setVariable("NAME_ENTERPOINTS_VALUE", "enterpoints_value");
  for ($i = 1; $i <= $count; $i++) {
    $content->setCurrentBlock("BLOCK_SHEET");
    $content->setVariable("SHEET_NR", $i);
    $content->setVariable("SHEET_NAME", $i);
    if ($i == $aktsheet) {
      $content->setVariable("SHEET_SELECTED", "selected='selected'");
    }
    $content->parse("BLOCK_SHEET");
  }
  $content->parse("BLOCK_ENTERPOINTS");

  $choosemember_login = "";
  if (isset($_POST) && isset($_POST["choosemember"]) && isset($_POST["choosemember"]["login"])) $choosemember_login = $_POST["choosemember"]["login"];
  $content->setCurrentBlock("BLOCK_CHOOSEMEMBER");
    $content->setVariable("FORM_ACTION_CHOOSEMEMBER", $backlink);
  $content->setVariable("LABEL_CHOOSEMEMBER", gettext("Login") . ":");
  $content->setVariable("LABEL_CHOOSEMEMBER_SUBMIT", gettext("Display Points"));
  $content->setVariable("VALUE_CHOOSEMEMBER", $choosemember_login);
  $content->setVariable("CHOOSEMEMBER_COLSPAN", $count+2);
  $content->setVariable("VALUE_CHOOSEMEMBER_SHEETNUMBER", $aktsheet);
  $content->setVariable("NAME_CHOOSEMEMBER_SUBMIT", "choosemember_login");
  $content->setVariable("NAME_CHOOSEMEMBER", "choosemember[login]");
  $content->parse("BLOCK_CHOOSEMEMBER");
  
  $choosemember_mnr = "";
  if (isset($_POST) && isset($_POST["choosemember"]) && isset($_POST["choosemember"]["mnr"])) $choosemember_mnr = $_POST["choosemember"]["mnr"];
  $content->setCurrentBlock("BLOCK_CHOOSEMEMBER");
  $content->setVariable("FORM_ACTION_CHOOSEMEMBER", $backlink);
  $content->setVariable("NAME_CHOOSEMEMBER_SUBMIT", "choosemember_mnr");
  $content->setVariable("LABEL_CHOOSEMEMBER", gettext("Matriculation Number") . ":");
  $content->setVariable("VALUE_CHOOSEMEMBER_SHEETNUMBER", $aktsheet);
  $content->setVariable("LABEL_CHOOSEMEMBER_SUBMIT", gettext("Display Points"));
  $content->setVariable("CHOOSEMEMBER_COLSPAN", $count+2);
  $content->setVariable("VALUE_CHOOSEMEMBER", $choosemember_mnr);
  $content->setVariable("NAME_CHOOSEMEMBER", "choosemember[mnr]");
  $content->parse("BLOCK_CHOOSEMEMBER");
}

if ( $is_admin && $show_all ) {
  for ($i = 1; $i <= $count; $i++) {
    $content->setCurrentBlock("BLOCK_MAXPOINT");
    $content->setVariable("MAXPOINT_INDEX", $i);
    $content->setVariable("MAXPOINT_VALUE", $maxpoints[$i]);
    $content->parse("BLOCK_MAXPOINT");
  }

  // Get member data
  $cache = get_cache_function( $group->get_id(), CACHE_LIFETIME_STATIC );
  $members = $cache->call( "lms_steam::group_get_members", $group->get_id(), TRUE );
  usort($members, "sort_objects");
  
  $id = 0;
  $name = "";
  foreach ($members as $member) {
    $id = $member["OBJ_ID"];
    $name = $member["OBJ_NAME"];
    $firstname = $member["USER_FIRSTNAME"];
    $fullname = $member["USER_FULLNAME"];
    $matriculation_number = $member["ldap:USER_MATRICULATION_NUMBER"];
    $points = $pointlist[$id];
    units_pointlist::display_memberdata_admin($content, $id, $name, $firstname, $fullname, $matriculation_number, $count, $points, $maxpoints, $write_mode, $bonus_1, $bonus_2);
  }
  // Disable the write mode
  /*
  if ($write_mode) {
    $content->setCurrentBlock("BLOCK_SUBMIT");
    $content->setVariable("LABEL_SUBMIT", gettext("Save changes"));
    $content->setVariable("SUBMIT_COLSPAN", $count+3);
    $content->setVariable("BACKLINK", " " . "<a href='" . $backlink . "/?show=all'>" . gettext("go back") . "</a>" );
    $content->parse("BLOCK_SUBMIT");
  } else {
    $content->setCurrentBlock("BLOCK_WRITE");
    $content->setVariable("WRITE_COLSPAN", $count+3);
    $content->setVariable("LINK_ENABLE_WRITE", "<a href='" . $backlink . "/?show=all&write_mode=true'>" . gettext("Enable Write Mode") . "</a>" );
    $content->parse("BLOCK_SUBMIT");
  }
  */

} else {
  if ($is_admin) {
    if (is_object($show_member)) $member = $show_member;
  }
  else $member = $user;
  if (isset($member) && is_object($member)) {
    $id = $member->get_id();
    $matriculation_number = $member->get_attribute("ldap:USER_MATRICULATION_NUMBER");
    $name = ($user->get_id() == $id?gettext("Your Points"):$member->get_name() . "<br /><small>(" . ($matriculation_number!=0?$matriculation_number:"n.a.") . ")</small>" );
    $firstname = $member->get_attribute("USER_FIRSTNAME");
    $fullname = $member->get_attribute("USER_FULLNAME");
    $points = $proxy->get_attribute("UNIT_POINTLIST_POINTS_" . $id);
    if (!is_array($points)) $points = array();
  }
  if (!isset($name)) {
    $name = gettext("n.a.");
    $id = -1;
    $firstname="";
    $fullname="";
  }
  if(!isset($points)){
  	$points=array();
  }
  units_pointlist::display_memberdata($content, $id, $name, $firstname, $fullname, $count, $points, FALSE, $bonus_1, $bonus_2);
}

units_pointlist::display_maxpoints_data($content, $count, $maxpoints, $bonus_1, $bonus_2);

if (defined("LOG_DEBUGLOG")) {
  logging::append_log( LOG_DEBUGLOG, " \t" . round((microtime(TRUE) - $time1) * 1000 ) . " ms");
}

$html_handler->set_html_left( $content->get() );
$portal->set_page_main( $html_handler->get_headline(), $html_handler->get_html(), "");
$portal->show_html(); 
exit;
?>
