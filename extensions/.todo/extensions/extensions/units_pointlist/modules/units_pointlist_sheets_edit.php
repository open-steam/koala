<?php
define("POINT_OK", 1);
define("POINTERROR_NOINT", 2);
define("POINTERROR_LOWERZERO", 3);
define("POINTERROR_NOTSPECIFIED", 5);

function check_maxpoint($point) {
  $point = trim($point);
  if ($point === "") return POINT_NOTSPECIFIED;
  if (!preg_match ("/^([0-9]+)$/", $point)) return POINTERROR_NOINT;
  if ((int)$point < 0) return POINTERROR_LOWERZERO;
  return POINT_OK;
}

function check_maxpoints($count, $maxpoints) {
  for ($i = 1; $i <= $count; $i++) {
    if (check_maxpoint($maxpoints[$i]) !== POINT_OK) return FALSE;
  }
  return TRUE;
}

// Process the POST
if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" && isset($_POST["values"]))
{
  if ( ! $course->is_admin( $user )  ) {
  throw new Exception( "No course admin!", E_ACCESS );
  }
	$values = $_POST[ "values" ];

  $maxpoints = $values["maxpoints"];
  if (!is_array($maxpoints)) $maxpoints = array();
  
	// ABFRAGEN
	$problems = "";
	$hints    = "";

  if (!check_maxpoints($values["count"], $maxpoints)) {
    $problems .= gettext("Invalid values");
    $hints .= gettext("Please correct the invalid values and save again");
  }
  
	if ( empty( $problems ) )
	{
     $proxy = $unit->get_attribute("UNIT_POINTLIST_PROXY");
     $proxy->set_attribute("UNIT_POINTLIST_MAXPOINTS", $maxpoints);
     $portal->set_confirmation( gettext("Saved max points for sheets") );
	}
	else {
		$portal->set_problem_description( $problems, $hints );
	}
}

function display_maxpoints($template, $count, $maxpoints) {
  if (!is_array($maxpoints)) $maxpoints = array();
  for ($i = 1; $i <= $count; $i++) {
    if ( !isset($maxpoints[$i]) ) $p = "";
    else $p = $maxpoints[$i];
    $template->setCurrentBlock("BLOCK_MAXPOINT");
    $template->setVariable("VALUE_MAXPOINT", trim($p));
    $template->setVariable("VALUE_SHEETID", $i);
    $style = "text-align: right;";
    $check = check_maxpoint($maxpoints[$i]);
    if ($check !== POINT_OK) {
      $hint = "";
      $style .= " background-color: #D11E01; color: #ffffff;";
      switch ($check) {
        case POINTERROR_NOINT: $hint = gettext("Not a number");
                          break;
        case POINTERROR_LOWERZERO: $hint = gettext("Lower than zero");
                          break;
        case POINTERROR_NOTSPECIFIED: $hint = gettext("Not specified");
                          break;
        default: $hint = gettext("Invalid input");
                 break;
      };
      $template->setVariable("MAXPOINT_TITLE", $hint);
    }
    $template->setVariable("MAXPOINT_STYLE", "style='". $style . "'");
    $template->parse("BLOCK_MAXPOINT");
  }
}

if (!defined("PATH_TEMPLATES_UNITS_POINTLIST")) define( "PATH_TEMPLATES_UNITS_POINTLIST", PATH_EXTENSIONS . "units_pointlist/templates/" );

$content = new HTML_TEMPLATE_IT();

$content->loadTemplateFile( PATH_TEMPLATES_UNITS_POINTLIST . "units_pointlist_sheets_edit.template.html" );

// Load general data
$attributes = array(
  "UNIT_POINTLIST_COUNT",
  "UNIT_POINTLIST_PROXY"
);
$data = $unit->get_attributes( $attributes );

$count = $data["UNIT_POINTLIST_COUNT"];
$proxy = $data["UNIT_POINTLIST_PROXY"];

if ($_SERVER[ "REQUEST_METHOD" ] == "POST" && isset($_POST["values"])) {
  $values = $_POST[ "values" ];
  $maxpoints = $values["maxpoints"];
}
else {
  // Load points
  $tnr_maxpoints = $proxy->get_attribute("UNIT_POINTLIST_MAXPOINTS", TRUE);
  $result = $GLOBALS["STEAM"]->buffer_flush();
  $maxpoints = $result[$tnr_maxpoints];
}

// Check data integrity
if (!is_array($maxpoints)) {
  for ($i = 1; $i <= $count; $i++) {
    $maxpoints[$i] = 10;  // default value
  }
}

$count = $data["UNIT_POINTLIST_COUNT"];
if ($count < 0 || $count > 15) $count = 8;

// Fill template
$content->setVariable( "LABEL_SHEET_INFO", gettext("You may change the default max points for each sheet.") );
$content->setVariable( "VALUE_COUNT", $count );
$content->setCurrentBlock("BLOCK_SUBMIT");
$content->setVariable("LABEL_SUBMIT", gettext("Save changes"));
$content->setVariable("SUBMIT_COLSPAN", $count+2);
$backlink = $unit->get_url();
$content->setVariable( "SUBMIT_BACKLINK", "<a href=\"$backlink\">" . gettext( "back" ) . "</a>" );
$content->parse("BLOCK_SUBMIT");


// Table header
$content->setVariable("LABEL_SHEET", gettext("Sheet"));
$content->setVariable("LABEL_MAXPOINTS", gettext("Max points"));
for ($i = 1; $i <= $count; $i++) {
  $content->setCurrentBlock("BLOCK_TABLE_HEADER");
  $content->setVariable("TABLE_HEADER_LABEL", $i);
  $content->parse("BLOCK_TABLE_HEADER");
}

display_maxpoints($content, $count, $maxpoints);

$headline = $html_handler->get_headline();
$headline[] = array( "name" => gettext("Edit sheets"),  "link" => "sheets_edit");

$html_handler->set_html_left( $content->get() );
$portal->set_page_main( $headline, $html_handler->get_html(), "");
$portal->show_html(); 
exit;
?>
