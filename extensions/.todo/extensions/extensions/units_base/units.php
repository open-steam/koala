<?php
define("PATH_TEMPLATES_UNITS_BASE", PATH_EXTENSIONS . "units_base/templates/");

require_once( 'classes/unitmanager.class.php' );
require_once( 'classes/koala_container_units.class.php' );

if (!isset($portal)) {
  $portal = lms_portal::get_instance();
  $portal->initialize( GUEST_ALLOWED );
} else $portal->set_guest_allowed( GUEST_ALLOWED );
$current_user = lms_steam::get_current_user();
$um = unitmanager::create_unitmanager($course);
$html_handler = new koala_html_course( $course );
$html_handler->set_context( "units" );
$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES_UNITS_BASE . "units.template.html" );
$container = $course->get_workroom();
$koala_container = new koala_container_units( $container, $course->get_url() . 'units/' );
$koala_container->set_owner( $course );
$is_admin = $course->is_admin( $current_user );
$units = array();
$units_tmp = $koala_container->get_inventory();

// Pre-load Atributes for all units to optimize requests
steam_factory::load_attributes($GLOBALS["STEAM"]->get_id(), $units_tmp, array(OBJ_TYPE, OBJ_NAME, OBJ_ICON, OBJ_DESC, "UNIT_TYPE", "UNIT_DISPLAY_TYPE"));

foreach ( $units_tmp as $unit ) {
	$koala_unit = koala_object::get_koala_object( $unit );
	if ( is_object( $koala_unit ) && method_exists( $koala_unit, 'get_unit' ) ) {
		$unit_class = $koala_unit->get_unit();
		if ( is_object( $unit_class ) && method_exists( $unit_class, 'is_enabled_for' ) && !$unit_class->is_enabled_for( $owner ) ) {
			continue;
		}
	}
	$units[] = $unit;
}

if ( $is_admin ) {
	//clipboard:
	$koala_user = new koala_html_user( new koala_user( $current_user ) );
	$clipboard_menu = $koala_user->get_clipboard_menu( $koala_container );
	$content->setCurrentBlock( "BLOCK_CLIPBOARD" );
	$content->setVariable( "CLIPBOARD_HTML", $clipboard_menu->get_html() );
	$content->parse( "BLOCK_CLIPBOARD" );
}
if(count($units)>0)
{
	if(AUTOLOAD_FIRST_UNIT) {
		header("Location: " . $backlink  . $units[0]->get_id() . "/");
		exit;
	}
	
	$content->setCurrentBlock( "BLOCK_UNIT_LIST" );
	$paginator_text = gettext('%START - %END of %TOTAL');
	if ( isset( $_REQUEST['nrshow'] ) ) {
		$nr_show = (int)$_REQUEST['nrshow'];
		if ( $nr_show < 1 ) $nr_show = count( $units );
	}
	else $nr_show = 10;
	if ( $nr_show > 0 )
		$paginator_text .= ', <a href="' . $course->get_url() . 'units/?nrshow=0">' . gettext( 'show all' ) . '</a>';
	$start = $portal->set_paginator( $content, $nr_show, count($units), '(' . $paginator_text . ')' );
	$end   = ( $start + $nr_show >  count($units)) ? count($units) : $start + $nr_show;

	$content->setVariable( "LABEL_UNITS", gettext( "Units for the course" ) . " '" . h($course->get_attribute("OBJ_DESC")) . "'" );

	$content->setVariable( "LABEL_NAME_DESCRIPTION", gettext( "Name, description" ) );
	$content->setVariable( "LABEL_TYPE", gettext( "Unit type" ) );
	$content->setVariable( "LABEL_ACTIONS", gettext("Actions"));
	
	$item_ids = array();
  
  // initialize unitmanager for admin view
  if ($is_admin) $unitmanager = new unitmanager();

  $tnr_read = array();
  // Use buffer to determine read access
	for( $i = $start; $i < $end; $i++ ) {
		$unit = $units[ $i ];
    if (is_object($unit)) $tnr_read[$unit->get_id()] = $unit->check_access_read($current_user, TRUE);
  }
  $result_read = $GLOBALS["STEAM"]->buffer_flush();
  
	for( $i = $start; $i < $end; $i++ ) {
		$unit = $units[ $i ];
    if ( is_object($unit) && $result_read[$tnr_read[$unit->get_id()]]) {
      $content->setCurrentBlock( "BLOCK_UNIT" );

    if (get_class($unit) === "steam_container")
    {
		$content->setVariable( "INVENTORY_SIZE", count( $unit->get_inventory() ) . " " . gettext("object(s)") );
    }

      $content->setVariable( "UNIT_LINK", $backlink  . $unit->get_id() . "/" );
      $content->setVariable( "UNIT_NAME", h($unit->get_attribute( OBJ_NAME )) );
			$item_desc = $unit->get_attribute( OBJ_DESC );
			if (is_string($item_desc) && strlen($item_desc) > 0) {
				$content->setCurrentBlock("BLOCK_DESCRIPTION");
				$content->setVariable( "UNIT_DESC", h( $item_desc ) );
				$content->parse("BLOCK_DESCRIPTION");
				$content->setVariable("ITEM_STYLE", "style=\"margin-top: 3px;\"");
			} else {
				$content->setVariable("ITEM_STYLE", "style=\"margin-top: 8px;\"");
			}
      //$content->setVariable( "GROUPLINK", "<td><big><b>" . h($unit->get_attribute( "OBJ_NAME" )) . "</b></big><br/><small>" . h($unit->get_attribute( "OBJ_DESC" )) . "</small></td>");
      $unit_icon = $unit->get_attribute( OBJ_ICON );
      $unit_icon = ( is_object( $unit_icon ) ) ? PATH_URL . "cached/get_document.php?id=" . $unit_icon->get_id() . "&type=objecticon&height=64" : PATH_STYLE . "images/anonymous.jpg";
      $content->setVariable( "UNIT_ICON", $unit_icon );
      $unit_type = $unit->get_attribute("UNIT_DISPLAY_TYPE");
      if ( is_string( $unit_type ) && !empty( $unit_type ) ) {
        $unit_valid = TRUE;
        $unit_type = h( $unit_type );
      }
      else {
        $unit_valid = FALSE;
        $unit_type = gettext( 'Unit' );
      }
      $content->setVariable( "UNIT_TYPE", $unit_type );
      $content->setVariable( "BOXES", "boxes_" . $i);

      if ( $is_admin && $unit_valid ) {
        $akt_unit = $unitmanager->get_unittype($unit->get_attribute("UNIT_TYPE"));
        if (is_object($akt_unit)) {
          $permissions = $akt_unit->get_action_permissions();
          $content->setCurrentBlock( "BLOCK_UNIT_ACTIONS" );
          // copy unit:
          if ($permissions & PERMISSION_ACTION_COPY) {
            $content->setCurrentBlock("BLOCK_UNIT_ACTION");
            $content->setVariable( "ACTION_LINK", PATH_URL . "clipboard/take-copy/" . $unit->get_id() . "/from/" . $container->get_id() );
            $content->setVariable( "ACTION_LABEL", gettext( "pick up a copy" ) );
            $content->setVariable( "ACTION_ICONPATH", PATH_STYLE . "images/copy.png" );
            $content->parse("BLOCK_UNIT_ACTION");
          }
          // pick up unit:
          if ($permissions & PERMISSION_ACTION_CUT) {
            $content->setCurrentBlock("BLOCK_UNIT_ACTION");
            $content->setVariable( "ACTION_LINK", PATH_URL . "clipboard/take/" . $unit->get_id() . "/from/" . $container->get_id() );
            $content->setVariable( "ACTION_LABEL", gettext( "pick up unit" ) );
            $content->setVariable( "ACTION_ICONPATH", PATH_STYLE . "images/cut.png" );
            $content->parse("BLOCK_UNIT_ACTION");
          }
          // edit unit
          if ($permissions & PERMISSION_ACTION_EDIT) {
            $content->setCurrentBlock("BLOCK_UNIT_ACTION");
            $content->setVariable( "ACTION_LINK", $course->get_url() . "units/" . $unit->get_id() . "/edit" );
            $content->setVariable( "ACTION_LABEL", gettext( "edit unit" ) );
            $content->setVariable( "ACTION_ICONPATH", PATH_STYLE . "images/edit.png" );
            $content->parse("BLOCK_UNIT_ACTION");
          }
          // delete unit
          if ($permissions & PERMISSION_ACTION_DELETE) {
            $content->setCurrentBlock("BLOCK_UNIT_ACTION");
            $content->setVariable( "ACTION_LABEL", gettext( "delete unit" ) );
            $content->setVariable( "ACTION_ICONPATH", PATH_STYLE . "images/delete.png" );
            $content->setVariable( "ACTION_LINK", $course->get_url() . "units/" . $unit->get_id() . "/delete" );
            $content->parse("BLOCK_UNIT_ACTION");
          }
          $content->parse( "BLOCK_UNIT_ACTIONS" );
        }
      }
      $content->parse( "BLOCK_UNIT" );
      $item_ids[] = (string)$unit->get_id();
    }
	}
	$content->parse( "BLOCK_UNIT_LIST" );
	if ( $course->get_workroom()->check_access_write( $current_user ) ) {
		$content->setCurrentBlock( "BLOCK_STAFF" );
		$portal->add_javascript_code( 'units', 'containerStart=' . $start . '; containerEnd=' . $end . '; itemIds=Array(' . implode(',', $item_ids) . ');' );
		$content->setVariable( "CONTAINER_ID", $course->get_workroom()->get_id() );
		$content->setVariable( 'KOALA_VERSION', KOALA_VERSION );
		$content->setVariable( "PATH_JAVASCRIPT", PATH_JAVASCRIPT );
		$infotext = gettext( "Units can be sorted by dragging and dropping them." ) . "<br/>";
		
		$koala_container = new koala_container( $course->get_workroom() );
		$webdav_url = $koala_container->get_webdav_url();
		if ( !empty( $webdav_url ) )
			$infotext .= gettext( "This folder is available as a web folder" ) . ": " . $webdav_url;
		$content->setVariable( "INFO_TEXT", $infotext );
		$content->parse( "BLOCK_STAFF" );
	}
}
else
{
	$content->setVariable( 'LABEL_UNITS', gettext( 'No units available. Either no units have been created in this context, or you are not allowed to see them.' ) );
}
$html_handler->set_html_left( $content->get());
$portal->set_page_main( $html_handler->get_headline(), $html_handler->get_html() , "" );
$portal->show_html();
?>
