<?php
/** Fuktion zur Berechnung der zugeh�rigen L�sungen f�r eine 
 * �bergebene Aufgabenstellung und f�r den �bergebenen Benutzer
 * 
 * @param $item_task Aufgabenstellung f�r die die L�sungen zur�ckgegeben werden
 * @param $uuser Benutzer dessen L�sungen zur�ckgegeben werden
 * @param $iinventory Der Container, der durchsucht werden soll
 * @return gibt ein Array mit den zur Aufgabenstellung und Benutzer geh�rigen L�sungen
 */
function get_subitem($iinventory,$item_task,$uuser){
	$inven = $iinventory;
	foreach($inven as $sub_item)
		if($sub_item->get_attribute(HOMEWORK_TYPE)=="solution" && $sub_item->check_access_read($uuser)){
			if($sub_item->get_attribute(HOMEWORK_TASK)==$item_task){
				$sub_inv[]=$sub_item;
			}
		}
	return $sub_inv;	
}

include_once( PATH_KOALA . "etc/koala.conf.php" );
include_once( PATH_LIB . "format_handling.inc.php" );
require_once( PATH_LIB . "url_handling.inc.php");
if (!defined("PATH_TEMPLATES_UNITS_HOMEWORK")) define( "PATH_TEMPLATES_UNITS_HOMEWORK", PATH_EXTENSIONS. "units_homework/templates/" );
/*
 * The following variables *must* be set before including this file:
 *   $koala_container : a koala_container object that represents the container
 *     to be displayed here (you can use the koala_container functions to
 *     limit the inventory items to display here)
 *   $html_handler : a valid html_handler class, e.g. koala_html_user,
 *      koala_html_group, koala_html_course, ...
 *   $portal : a valid lms_portal instance
 * 
 * The following variables *may* be set before including this file:
 *   $container_icons : if set to FALSE, then no icons will be displayed for
 *      the inventory objects, otherwise the icons from the open-sTeam backend
 *      will be displayed.
 *   $path_offset : an offset in the link path from which to display the
 *     container's path. Default is 1, so that the user's or group's documents
 *     folder will not be displayed in the path.
 */

if ( !isset( $koala_container ) || !($koala_container instanceof koala_container) )
	throw new Exception( "No koala_container provided." );

if ( !isset( $portal ) || !is_object( $portal ) )
	throw new Exception( "No portal provided." );

if ( !isset( $html_handler ) || !is_object( $html_handler ) )
	throw new Exception( "No valid html_handler provided." );

$container = $koala_container->get_steam_object();
$path = url_parse_rewrite_path( $_GET[ "path" ] );
$id = $path[2];
$env = steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);

// check read permission:
if ( !$container->check_access_read( lms_steam::get_current_user() ) ) {
	$portal->set_problem_description( gettext( "You are not permitted to view this folder.'" ) );
	$portal->set_page_main( $html_handler->get_headline(), $html_handler->get_html(), "");
	$portal->show_html();
	exit;
}

$group_staff=$owner->get_group_staff();
$group_admins=$owner->get_group_admins();
$group_learners=$owner->get_group_learners();

if ( !isset( $container_icons ) ) $container_icons = TRUE;
if ( !isset( $path_offset ) ) $path_offset = 1;

$portal->set_environment( $container );

$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES_UNITS_HOMEWORK . "units_homework.template.html" );

if ( isset( $_REQUEST['nrshow'] ) ) $nr_show = (int)$_REQUEST['nrshow'];
else $nr_show = 1000000;
$start = $portal->get_paginator_start( $nr_show );

if ( isset( $_REQUEST['sort'] ) )
	$sort = $_REQUEST['sort'];
else $sort = FALSE;

$pagination_info = $koala_container->get_inventory_paginated( $start, $nr_show, $sort );
$inventory = $pagination_info['objects'];

if ( !isset( $link_path ) )
	$link_path = $koala_container->get_link_path();
$base_url = $link_path[0]["link"];
$link_path_index = array_search( $path_offset, array_keys( $link_path ) );
if ( $link_path_index !== FALSE ) {
	$link_path = array_slice( $link_path, $link_path_index );
	$content->setVariable( "CONTAINER_PATH", $koala_container->get_link_path_html( $link_path ) );
}

$desc = $container->get_attribute( "OBJ_DESC" );
// don't show description for user clipboards (which would be the user description) or workrooms:
if ( is_string( $desc ) && (($container instanceof steam_user) || (is_object( $creator = $container->get_creator() ) && $creator->get_name() . "s workroom" == $desc)) )
	$desc = FALSE;
if ( is_string( $desc ) )
	$content->setVariable( "VALUE_CONTAINER_DESC", h( $desc ) );
$long_desc = $container->get_attribute( "OBJ_LONG_DESC" );
if ( is_string( $long_desc ) )
	$content->setVariable( "VALUE_CONTAINER_LONG_DESC", get_formatted_output( $long_desc ) );


$homework_date=strtotime("now");
//Testdatum
//echo strtotime("28.08.2008-16:23"), "\n";
//echo date("d.m.Y-H:i:s", $homework_date),"\n";

$can_write = $container->check_access_write( $user );

// don't show clipboard when viewing clipboard contents as a folder:
if ( ! $container->get_root_environment() instanceof steam_user ) {
	//clipboard:
	$koala_user = new koala_html_user( $user );
	$clipboard_menu = $koala_user->get_clipboard_menu( $koala_container );
	$content->setCurrentBlock( "BLOCK_CLIPBOARD" );
	$content->setVariable( "CLIPBOARD_HTML", $clipboard_menu->get_html() );
	$content->parse( "BLOCK_CLIPBOARD" );
}

if ( !is_array( $inventory ) || count( $inventory ) == 0 ) {
	$content->setCurrentBlock( "BLOCK_EMPTY_INVENTORY" );
	$content->setVariable( "LABEL_NO_DOCUMENTS_FOUND", gettext( "There are no documents available yet." ) . "<br /><br />");
	$content->parse( "BLOCK_EMPTY_INVENTORY" );
}
else {
	$content->setCurrentBlock( "BLOCK_INVENTORY" );
	$page_option = '';
	$content->setVariable( "LABEL_HOMEWORK", gettext( "Tasks" ));
	$content->setVariable( "LABEL_ENDDATE", gettext( "Enddate" ));
	$content->setVariable( "LABEL_MODIFIED", '<a href="' . $label_date_link . $page_option . '">' . gettext( "Last modified" ) . '</a>' );
	$content->setVariable( "LABEL_WORKINGPEOPLE", gettext( "Participants" )  );
	$content->setVariable( "LABEL_POINTS", gettext( "Points").("/ <br>").gettext( "Feedback" )  );
	$content->setVariable( "LABEL_UPLOAD", gettext( "Upload" ));

	$paginator_text = gettext('%START - %END of %TOTAL');
	if ( $nr_show > 0 )
		$paginator_text .= ', <a href="?nrshow=0' . (is_string($sort) ? '&sort=' . $sort : '') . '">' . gettext( 'show all' ) . '</a>';
	else $nr_show = count( $inventory );
	$portal->set_paginator( $content, $nr_show, $pagination_info['total'], '(' . $paginator_text . ')', is_string($sort) ? '?sort=' . $sort : '' );
	
	$item_ids = array();
	$i = 0;
	foreach( $inventory as $item)
	{
		if($item->get_attribute(HOMEWORK_TYPE)=="task"){
			// Ignore hidden files starting with '.'
			if ( substr( $item->get_name(), 0, 1 ) == '.' ) continue;
			$attributes = array( OBJ_CREATION_TIME, DOC_LAST_MODIFIED, DOC_USER_MODIFIED, OBJ_DESC );
			$item_date=$item->get_attribute(HOMEWORK_ENDDATE);
			
			$content->setCurrentBlock( "BLOCK_ITEM" );
			$size = ( $item instanceof steam_document ) ? $item->get_content_size() : 0;
			if ( !($item instanceof steam_container) && !($item instanceof steam_room) )
				$content->setVariable( "LINK_ITEM", PATH_URL . "doc/" . $item->get_id() . "/" );
			else
				$content->setVariable( "LINK_ITEM", $base_url . $item->get_id() );
			
			$inven=get_subitem($inventory,$item->get_id(),$user);
			if (!empty($inven)){
				foreach($inven as $ivi){
					$content->setCurrentBlock( "SOLUTION" );
					$content->setVariable( "LINK_SOLUTION", PATH_URL . "doc/" . $ivi->get_id() . "/" );
					$content->setVariable( "NAME_SOLUTION", $ivi->get_name() );
					$content->parse( "SOLUTION" );
				}
			}
			
			//Abgabe-Button setzen wenn die Abgabe noch erlaubt ist
			if ($homework_date > $item_date){
				$content->setVariable( "HOMEWORK_INFO_TEXT", gettext("Your solution").(": "));
				//Enddate setzen
				$content->setVariable( "HOMEWORK_ENDDATE", gettext("time ist over"));
				$content->setVariable( "LABEL_USER_UPLOAD", "--------------");
			}
			else{
				$content->setVariable( "HOMEWORK_INFO_TEXT", gettext("Put in your solution").": "." <br> <input type=\"file\" name=\"user_upload\">");
				$content->setVariable( "LABEL_USER_UPLOAD", "<input type=\"submit\" value=\"". gettext( "submit" ) . " \" ><br>");
				//Enddate setzen
				$content->setVariable( "HOMEWORK_ENDDATE",date("d.m.Y", $item_date)."<br>".date("H:i", $item_date));
				//Hidden zur Zuordnung der Abgabe zu einer Aufgabenstellung
				$content->setVariable("HIDDEN_ITEM_TASK","<input type=\"hidden\" name=\"values[homework_task]\" value=\"".$item->get_id()."\">");
			}
			
			if ( $container_icons ) $attributes[] = OBJ_ICON;
			$attributes = $item->get_attributes( $attributes );
		
			if ( $container_icons && is_object( $icon = $attributes[ "OBJ_ICON" ] ) )
				$content->setVariable( "ICON_ITEM", "<div class='objecticon'><img src='" . PATH_URL . "cached/get_document.php?id=" . $icon->get_id() . "&type=objecticon&width=32&height=32' /></div>" );

			/* Mitwirkende setzen
			 * Anzahl der Mitwirkenden = Gruppenst�rke -1
			 * weil der "Uploader" auch mit z�hlt */
			$mit_i=1;
			$gs=$item->get_attribute(HOMEWORK_GROUPSIZE);
			if ($homework_date <= $item_date){
				$content->setCurrentBlock( "BLOCK_WORKING_PEOPLE" );
				while($mit_i<$gs){
					$content->setCurrentBlock( "WPEOPLE" );
					$content->setVariable( "WORKING_PEOPLE", gettext("participant "). $mit_i . "<br> <input type=\"text\" name=\"participant[]\" width=\"120\">");
					$content->parse( "WPEOPLE" );
					$mit_i++;		
				}
				$content->parse( "BLOCK_WORKING_PEOPLE" );
			}
			else{
				$content->setCurrentBlock( "BLOCK_WORKING_PEOPLE" );
				if (!empty($inven)){
					foreach( $inven as $sub){
						$homework_tmp_people=$sub->get_attribute(HOMEWORK_PARTICIPANTS);
						if (!empty($homework_tmp_people)){
							foreach( $homework_tmp_people as $tmp_wpeople){
								$content->setCurrentBlock( "WPEOPLE" );
								$content->setVariable( "WORKING_PEOPLE", gettext("participant").": ".$tmp_wpeople->get_name());
								$content->parse( "WPEOPLE" );		
							}
						}
						else{
							$content->setCurrentBlock( "WPEOPLE" );
							$content->setVariable( "WORKING_PEOPLE", gettext("no participants "));
							$content->parse( "WPEOPLE" );
						}
						
					}
				}
				else{
					$content->setCurrentBlock( "WPEOPLE" );
					$content->setVariable( "WORKING_PEOPLE", gettext("no participants "));
					$content->parse( "WPEOPLE" );
				}
				$content->parse( "BLOCK_WORKING_PEOPLE" );
				
			}
			//Ende Mitwirke setzen	
			
			/* Punkte bzw Bewertun setzen bzw angucken */
			if($group_learners->is_member($user)){
				//Studenten-Ansicht f�r die Bewertung
				if (!empty($inven)){
					foreach( $inven as $sub){
						$content->setCurrentBlock( "FEEDBACK" );
						$content->setVariable( "LINK_FEEDBACK", "feedback/". $sub->get_id() . "/" );
						$feba=$sub->get_attribute("HOMEWORK_FEEDBACK");
						if(isset($feba) && !$feba==0){//Bewertung wurde abgegeben
							$content->setVariable( "VALUE_FEEDBACK", gettext("show feedback for ")." ".$sub->get_name()."<br>");
						}
						else{//keine Bewertung wurde abgegeben
							$content->setVariable( "VALUE_FEEDBACK", gettext("no feedback"));
						}
							
						$content->setVariable( "LABEL_ITEM_POINTS", gettext("Points").(": ").$sub->get_attribute(HOMEWORK_POINTS)."<br><br>");
						$content->parse( "FEEDBACK" );					
					}
				}	
				else{
					$content->setVariable( "VALUE_FEEDBACK", gettext("no feedback"));
				}
			}
			else{
				//Tutoren Ansicht f�r die Bewertung
				if (!empty($inven)){
					foreach( $inven as $sub){
						$homework_tmp_people=$sub->get_attribute(HOMEWORK_PARTICIPANTS);
						if (!empty($homework_tmp_people)){
							foreach( $homework_tmp_people as $tmp_wpeople){
								$peop=$peop." ".$tmp_wpeople->get_name();		
							}
						}
						$fb=$sub->get_attribute("HOMEWORK_FEEDBACK");
						$content->setCurrentBlock( "FEEDBACK" );
						$content->setVariable( "LINK_FEEDBACK", "feedback/". $sub->get_id() . "/" );
						if(isset($fb) && !$fb==0){
							$content->setVariable( "COLOR_GIVE", "#7F99FF"); // andere Farbe f�r bewertete Abgaben
							$content->setVariable( "VALUE_FEEDBACK", gettext("edit feedback for ").("<br>").$peop."<br>");
						}
						else{
							$content->setVariable( "VALUE_FEEDBACK", gettext("give feedback for ").("<br>").$peop."<br>");
						}
						$content->parse( "FEEDBACK" );
						$peop=" ";						
					}
				}	
				else{
					$content->setVariable( "VALUE_FEEDBACK", gettext("no feedback"));
				}
				
			}
			/*Punkte bzw Bewertung verarbeitet*/
			
			
			
			
			$content->setVariable( "HOMEWORK_COMMENTS", PATH_URL . "doc/" . $item->get_id() . "/" );
			
			$content->setVariable( "NAME_ITEM", h( $item->get_name() ) );
			$item_desc = $attributes[ OBJ_DESC ];
			if (is_string($item_desc) && strlen($item_desc) > 0) {
				$content->setCurrentBlock("BLOCK_DESCRIPTION");
				$content->setVariable( "OBJ_DESC", h( $item_desc ) );
				$content->parse("BLOCK_DESCRIPTION");
				$content->setVariable("ITEM_STYLE", "style=\"margin-top: 3px;\"");
			} 
			else {
				$content->setVariable("ITEM_STYLE", "style=\"margin-top: 8px;\"");
			}
			$content->setVariable( "BOXES", "boxes_" . $i);
			$content->parse( "BLOCK_ITEM" );
			$item_ids[] = (string)$item->get_id();
			$i++;
		}
	}	
	$content->parse( "BLOCK_INVENTORY" );
	
}

if ( $can_write ) {
	$content->setCurrentBlock( 'BLOCK_STAFF' );

	$infotext = '';
	if ( is_array( $inventory ) && count( $inventory ) > 0 ) {
		$content->setCurrentBlock( 'BLOCK_DRAG_DROP' );
		$portal->add_javascript_code( 'units_homework', 'containerStart=0; containerEnd=' . count( $inventory ) . '; itemIds=Array(' . implode(',', $item_ids) . ');' );
		$content->setVariable( 'CONTAINER_ID', $container->get_id() );
		$content->setVariable( 'KOALA_VERSION', KOALA_VERSION );
		$content->setVariable( 'PATH_JAVASCRIPT', PATH_JAVASCRIPT );
		$infotext = gettext( 'Folders and documents can be sorted by dragging and dropping them.' ) . '<br/>';
		$content->parse( 'BLOCK_DRAG_DROP' );
	}
	
	$webdav_url = $koala_container->get_webdav_url();
	if ( !empty( $webdav_url ) )
		$infotext .= gettext( 'This folder is available as a web folder' ) . ': ' . $webdav_url;
	$content->setVariable( 'INFO_TEXT', $infotext );
	$content->parse( 'BLOCK_STAFF' );
}

//Hier ist der TEil f�r die Studentenabgabe
if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" )
{
				$values = isset( $_POST[ "values" ] ) ? $_POST[ "values" ] : array();
				$participant = isset( $_POST[ "participant" ] ) ? $_POST[ "participant" ] : array();
				$problems = "";
				$hints    = "";

				if(!isset($group_staff)){
					$group_staff=$owner->get_group_staff();
				}
				if(!isset($group_admins)){
					$group_admins=$owner->get_group_admins();
				}
				if(!isset($group_learners)){
					$group_learners=$owner->get_group_learners();
				}

				if ( empty( $_FILES ) )
				{
					$problems = gettext( "Could not upload document." ) . " ";
					$hints = str_replace(
						array( "%SIZE", "%TIME" ),
						array( readable_filesize( $max_file_size ), (string)ini_get( 'max_execution_time' ) ),
						gettext( "Maybe your document exceeded the allowed file size (max. %SIZE) or the upload might have taken too long (max. %TIME seconds)." )
					) . " ";
				}
				
				//teste ob angegebene Mitwirkende auch im Kurs sind und ob es sie gibt
				foreach($participant as $pati){
					if ( isset( $pati ) && !empty($pati) ){
						$u=steam_factory::get_user($GLOBALS["STEAM"]->get_id(),$pati);
						if(is_object($u) && $group_learners->is_member($u) && $u instanceof steam_user){
							$partici[]=$u;
						}
						else{
							$problems.= gettext("Can not find User: ").$pati."<br>";
						}
					}
				}
				
				//�berpr�fen ob die Abgegeben Datei ein PDF ist
				$path_part = pathinfo($_FILES[ "user_upload" ]["name"]);
				if ( $path_part["extension"] != "pdf" )
				{
					$problems = gettext( "file-type not allowed" ) . " (".$path_part["extension"].")";
					$hints = gettext( "Please choose a pdf-document" ) . " ";
				}
				
				if ( !empty( $_FILES["user_upload"]["error"] ) && $_FILES["user_upload"]["error"] > 0 )
				{
					$problems = gettext( "No file chosen." ) . " ";
					$hints = gettext( "Please choose a local file to upload." ) . " ";
				}
				if ( empty( $problems ) )
				{
								$content = file_get_contents( $_FILES["user_upload"]["tmp_name"] );
								$filename = str_replace( array( "\\", "'" ), array( "", "" ), $_FILES[ "user_upload" ][ "name" ]  );
								$new_material = steam_factory::create_document(
									$GLOBALS[ "STEAM" ]->get_id(), 
									$user->get_name(). "_".$filename ,
									$content,
									$_FILES[ "user_upload" ][ "type" ],
									FALSE
								);
								
								//Rechte richtig setzen
								$partici[]=$user;						
								$new_material->set_sanction($group_staff, SANCTION_ALL, true);
								$new_material->set_sanction($group_admin, SANCTION_ALL, true);
								foreach($partici as $pati){
									$new_material->set_sanction($pati, SANCTION_ALL, true);
								}
								
								$new_material->set_acquire(false,true);
								$GLOBALS["STEAM"]->buffer_flush();
								
								$new_material->set_attribute( "HOMEWORK_PARTICIPANTS", $partici );
								
								$new_material->set_attribute( "OBJ_DESC", "Solution by Student: ".$user->get_name());
								
								// die zugeh�rige Aufgabenstellung zur L�sung setzen
								if ( isset( $values[ "homework_task" ] ) )
									$new_material->set_attribute( "HOMEWORK_TASK", $values[ "homework_task" ] );
								
								//Die Variable HOMEWORK_TYPE gibt an ob Aufgabenstellung oder Loesung
								$new_material->set_attribute( "HOMEWORK_TYPE", solution );
								
								$new_material->move($env);
								
								$_SESSION[ "confirmation" ] = str_replace(
									"%DOCUMENT",
									h($filename),
									gettext( "'%DOCUMENT' has been uploaded." )
								);
				
								header( "Location: " . $backlink );
								exit;
				}
				else
				{
								$portal->set_problem_description( $problems, $hints );
				}
}




$html_handler->set_html_left( $content->get() );
$portal->set_page_main( $html_handler->get_headline(), $html_handler->get_html(), "");
$portal->show_html(); 
?>
