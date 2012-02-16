<?php
define("UNIT_POINTLIST_MAX", 400);
if (!defined("PATH_TEMPLATES_UNITS_POINTLIST")) define( "PATH_TEMPLATES_UNITS_POINTLIST", PATH_EXTENSIONS . "units_pointlist/templates/" );

$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );
$user = lms_steam::get_current_user();

if ( ! $course->is_admin( $user ) )
{
	throw new Exception( "No course admin!", E_ACCESS );
}

if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" && isset($_POST["values"]))
{
	$values = $_POST[ "values" ];
	
	// ABFRAGEN

	$problems = "";
	$hints    = "";

  if (!$values["name"] || !$values["dsc"]) {
  	$problems .= gettext("One of the required fields is missing.");
  	$hints .= gettext("Please provide a name and a description for the unit.");
  }
  
	if ( empty( $problems ) )
	{
    	if ( ! isset($unit) )
    	{
        $count = (int)$values["count"];
        $maxpoints = array();
        for ($i = 1; $i <= $count; $i++) {
          $maxpoints[$i] = 10;  // default value
        }
        $all_users = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), STEAM_ALL_USER );
        $staff     = $course->get_group_staff();
        $learners     = $course->get_group_learners();
        $participants = steam_factory::get_object($GLOBALS["STEAM"]->get_id(), (int)$values["participants"]);
        // TODO check participant group is learners or subgroup of learners
        if (!is_object($participants) || !($participants instanceof steam_group)) $participants = $learners;
        $pg = $participants->get_parent_group();
        if ((!is_object($pg) || $pg->get_id() !== $learners->get_id()) && $participants->get_id() !== $learners->get_id()) {
          $problems .= gettext("Invalid participant group");
          $hints .= gettext("participant group must be the participant group of the course or a tutorial group of the course");
        }

        $partcount = $participants->count_members();
        if ( $partcount > UNIT_POINTLIST_MAX) {
          $problems .= gettext("Too many participants");
          $hints .= str_replace("%COUNTPART", $partcount , str_replace("%MAXPART", UNIT_POINTLIST_MAX,  str_replace("%LINK", "<a href='mailto:elearning@upb.de?subject=Pointlist%20Problem%20with%20group%20" . $participants->get_id() . "'>" . gettext("contact the support") . "</a>" , gettext("The choosen participant group for this pointlist has %COUNTPART members. Only %MAXPART members can be handled by the pointlist unit. You may divide the %COUNTPART members into different tutorial groups to avoid this problem. If you have questions about this issue please feel free to %LINK"))));
        }
        
        if ($participants->get_id() !== $learners->get_id()) {
          $pl = $participants->get_attribute("UNIT_POINTLIST");
          if (is_object($pl)) {
            $problems .= gettext("Cannot create Pointlist");
            $hints .= str_replace("%TUTORIAL_NAME", $participants->get_name() . "(" . $participants->get_attribute(OBJ_DESC) . ")", str_replace( "%UNIT_NAME", $pl->get_name(),  gettext("The tutorial group '%TUTORIAL_NAME' is already linked with the pointlist unit '%UNIT_NAME'")));
          }
        }
        if ( empty($problems)) {
        
          $env = $course->get_workroom();
          $new_unit = steam_factory::create_room( $GLOBALS[ "STEAM" ]->get_id(), $values[ "name" ], $env, $values[ "dsc" ] );
          $new_unit->set_attributes(array(
                      'UNIT_TYPE' => "units_pointlist",
                      'OBJ_TYPE' => "container_pointlist_unit_koala",
                      'UNIT_DISPLAY_TYPE' => gettext("Pointlist"),
                      'OBJ_LONG_DESC'=> $values[ "long_dsc" ],
                      'UNIT_POINTLIST_COUNT' => $count
                      ));
  
          $participants->set_attribute("UNIT_POINTLIST", $new_unit);
          if ($participants->get_id() !== $learners->get_id()) {
            $new_unit->set_attribute("UNIT_POINTLIST_TUTORIALGROUP", $participants);
          }
          $proxy = steam_factory::create_object( $GLOBALS[ "STEAM" ]->get_id(), "Pointlist Proxy", CLASS_OBJECT, 0);
          // Rechte an der Unit
          $new_unit->set_sanction_all( $staff );
          $new_unit->sanction_meta( SANCTION_ALL, $staff);
          $new_unit->set_sanction( $participants, SANCTION_READ);
          $new_unit->sanction_meta( 0, $participants);
          $new_unit->set_acquire(0);
          // Rechte am Proxy
          $proxy->set_attribute( "UNIT_POINTLIST_MAXPOINTS", $maxpoints );
          $proxy->set_sanction_all( $staff );
          $proxy->sanction_meta( SANCTION_ALL, $staff);
          $proxy->set_sanction( $participants, SANCTION_READ);
          $proxy->sanction_meta( 0, $participants);
          $proxy->set_acquire(0);
          
          $akt_unit->initialize_pointlist($values, $new_unit, $learners);
          $new_unit->set_attribute('OBJ_TYPE', "container_pointlist_unit_kola");
          $new_unit->set_attribute('UNIT_POINTLIST_PROXY', $proxy);
          $new_unit->set_attribute('UNIT_POINTLIST_PARTICIPANTS', $participants );
          $new_unit->set_attribute('UNIT_POINTLIST_BONUS_1', $values["bonus_1"]);
          $new_unit->set_attribute('UNIT_POINTLIST_BONUS_2', $values["bonus2"]);
        }  else  {
          $portal->set_problem_description( $problems, $hints );
        }
    	}  else  {
    		$new_unit = $unit->get_steam_object();
    		$koala_unit = $unit;
        $attrs = $new_unit->get_attributes( array( OBJ_NAME, OBJ_DESC, 'OBJ_LONG_DESC', OBJ_TYPE, 'UNIT_TYPE', 'UNIT_DISPLAY_TYPE', "UNIT_POINTLIST_BONUS_1", "UNIT_POINTLIST_BONUS_2") );
        if ( $attrs[OBJ_NAME] !== $values['name'] )
          $new_unit->set_name( $values['name'] );
        $changes = array();
        if ( $attrs['OBJ_TYPE'] !== 'container_pointlist_unit_koala' )
          $changes['OBJ_TYPE'] = 'container_pointlist_unit_koala';
        if ( $attrs['UNIT_TYPE'] !== 'units_pointlist' )
          $changes['UNIT_TYPE'] = 'units_pointlist';
        if ( $attrs['UNIT_DISPLAY_TYPE'] !== gettext('Pointlist') )
          $changes['UNIT_DISPLAY_TYPE'] = gettext('Pointlist');
        if ( $attrs[ OBJ_DESC ] !== $values['dsc'] )
          $changes[ OBJ_DESC ] = $values["dsc"];
        if ( $attrs[ 'OBJ_LONG_DESC' ] !== $values['long_dsc'] )
          $changes[ 'OBJ_LONG_DESC' ] = $values['long_dsc'];
        if ( $attrs[ "UNIT_POINTLIST_BONUS_1" ] != $values['bonus_1'] )
          $changes[ "UNIT_POINTLIST_BONUS_1" ] = $values["bonus_1"];
        if ( $attrs[ "UNIT_POINTLIST_BONUS_2" ] != $values['bonus_2'] )
          $changes[ "UNIT_POINTLIST_BONUS_2" ] = $values["bonus_2"];          
        $changes["UNIT_POINTLIST_COUNT"] = $values["count"];

        if ( count( $changes ) > 0 )
          $new_unit->set_attributes( $changes );
        }
		$GLOBALS[ "STEAM" ]->buffer_flush();
    if (empty($problems)) {		
      if( !is_object( $unit ) )
        header( "Location: " . $course->get_url() . "units/" );
      else
        header( "Location: " . $unit->get_url() );
      exit;
    }
	}
	else
	{
		$portal->set_problem_description( $problems, $hints );
	}
}
	$content = new HTML_TEMPLATE_IT();
	$content->loadTemplateFile( PATH_TEMPLATES_UNITS_POINTLIST . "units_pointlist_new.template.html" );
  
if ( empty($values) ) {
  $values = array();
}
  
if ( isset( $unit ) ) {
    if (empty($values["name"])) $values["name"] = $unit->get_display_name();
    if (empty($values["dsc"])) {
      $values["dsc"] = $unit->get_attribute( OBJ_DESC );
      if ( !is_string( $values["dsc"] ) ) $values["dsc"] = "";
    }
    if (empty($values["long_dsc"])) $values["long_dsc"] = $unit->get_attribute( "OBJ_LONG_DESC" );
    if ( !is_string( $values["long_dsc"] ) ) $values["long_dsc"] = "";
    
    if (empty($values["count"])) $values["count"] = $unit->get_attribute("UNIT_POINTLIST_COUNT");
    if (empty($values["bonus_1"])) $values["bonus_1"] = $unit->get_attribute("UNIT_POINTLIST_BONUS_1");
    if (empty($values["bonus_2"])) $values["bonus_2"] = $unit->get_attribute("UNIT_POINTLIST_BONUS_2");
    if ($values["bonus_1"] == "0") $values["bonus_1"] = "";
    if ($values["bonus_2"] == "0") $values["bonus_2"] = "";

    
    $participant_group = $unit->get_attribute("UNIT_POINTLIST_PARTICIPANTS");
    
		$content->setVariable("LABEL_CREATE", gettext("Save changes"));
} else {
  $content->setVariable( "LABEL_CREATE", gettext("Create unit") );
  $values["count"] = 8;  // 8 is the default
}

  if (!empty($values["name"])) $content->setVariable("VALUE_NAME", $values["name"]); // h-function already applied in get_display_name()
  if (!empty($values["dsc"])) $content->setVariable("VALUE_SHORT_DSC", h($values["dsc"]));
  if (!empty($values["long_dsc"])) $content->setVariable("VALUE_DSC", h($values["long_dsc"]));

  $content->setVariable("LABEL_COUNT", gettext( "Number of sheets" ));
  for ($i = 1; $i < 14; $i++) {
    $content->setCurrentBlock("BLOCK_COUNT_OPTION");
    $content->setVariable("COUNT_OPTION_VALUE", $i);
    if ($i == $values["count"]) $content->setVariable("COUNT_OPTION_SELECTED", "selected='selected'");
    $content->parse("BLOCK_COUNT_OPTION");
  }
  
  $groups = array( $course->get_group_learners() );

  $subgroups = $course->get_group_learners()->get_subgroups();
  if (count($subgroups) >0) {
    $groups = array_merge( $groups, $subgroups);
  }

  $attributes = array(OBJ_DESC, OBJ_TYPE, OBJ_NAME);
  $tnr = array();
  foreach($groups as $group) {
    $tnr[$group->get_id()] = $group->get_attributes($attributes, TRUE);
  }
  $result = $GLOBALS["STEAM"]->buffer_flush();

  $content->setCurrentBlock("BLOCK_PARTICIPANTS");
  $content->setVariable("LABEL_PARTICIPANTS", gettext( "students" ));
  if (isset($unit)) $content->setVariable("PARTICIPANT_GROUPS_DISABLED", " disabled='disabled'");
  foreach ($groups as $group) {
    $type = $result[$tnr[$group->get_id()]][OBJ_TYPE];
    // filter out tutorial groups and the learners group
    if ($type !== "group_tutorial_koala" && $type !== "course_learners") continue;
    $content->setCurrentBlock("BLOCK_PARTICIPANT_GROUP");
    if ($course->get_group_learners()->get_id() == $group->get_id()) {
      $content->setVariable("PARTICIPANT_GROUP_NAME", gettext("All participants of this course"));
    } else {
      $content->setVariable("PARTICIPANT_GROUP_NAME", gettext("Tutorial group") . " " . $result[$tnr[$group->get_id()]][OBJ_NAME] . " (" . $result[$tnr[$group->get_id()]][OBJ_DESC] . ")");
    }
    $content->setVariable("PARTICIPANT_GROUP_ID", $group->get_id());
    if (isset($unit)) {
      if (isset($participant_group) && $participant_group->get_id() == $group->get_id()) $content->setVariable("PARTICIPANT_GROUP_SELECTED", "selected='selected'");
    } else {
      if (isset($values["participants"]) && $group->get_id() == (int)$values["participants"] ) $content->setVariable("PARTICIPANT_GROUP_SELECTED", "selected='selected'");
      if ($course->get_group_learners()->get_id() == $group->get_id()) $content->setVariable("PARTICIPANT_GROUP_SELECTED", "selected='selected'");
    }
    $content->parse("BLOCK_PARTICIPANT_GROUP");
  }
  $content->parse("BLOCK_PARTICIPANTS");

  
  $content->setCurrentBlock("BLOCK_BONUS");
  if (!empty($values["bonus_1"])) $content->setVariable("BONUS_VALUE", h($values["bonus_1"]));
  $content->setVariable("BONUS_LABEL", gettext("First Bonus") .":");
  $content->setVariable("BONUS_NAME", "values[bonus_1]");
  $content->parse("BLOCK_BONUS");
  $content->setCurrentBlock("BLOCK_BONUS");
  if (!empty($values["bonus_2"])) $content->setVariable("BONUS_VALUE", h($values["bonus_2"]));
  $content->setVariable("BONUS_LABEL", gettext("Second Bonus") .":");
  $content->setVariable("BONUS_NAME", "values[bonus_2]");
  $content->parse("BLOCK_BONUS");  
  
  
  
  
  
  
	$content->setVariable( "UNIT_ICON", units_pointlist::get_big_icon() );
	$content->setVariable( "CONFIRMATION_TEXT", gettext( "You are going to add a new unit for this course." ) );

	$content->setVariable( "CONFIRMATION_TEXT_LONG", gettext( "You are going to add a new pointlist unit." ) );
	$content->setVariable( "LABEL_NAME", gettext( "Name" ) );
	$content->setVariable( "LABEL_DSC", gettext( "Description of the unit" ) );
	$content->setVariable( "LABEL_SHORT_DSC", gettext( "Description" ) );
	$content->setVariable( "LABEL_DSC", gettext( "Long description" ) );
	$content->setVariable( "LABEL_LONG_DSC", gettext( "Long description" ) .":" );

  $content->setVariable( "UNIT", "units_pointlist" );

  if( !is_object( $unit ) ) $backlink = $course->get_url() . "units/";
  else $backlink = $unit->get_url();
  
	$content->setVariable( "BACKLINK", " <a class=\"button\" href=\"$backlink\">" . gettext( "back" ) . "</a>" );

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

	$unit_new_html = $content->get();
?>
