<?php
/** Fuktion zum Pr�fen ob das eingegebene Datum der Vorlage entspricht
 * 
 * @param $datum  Das Datum was auf Korektheit �berpr�ft werden soll
 * @param $uhrzeit  Das Uhrzeit welche auf Korektheit �berpr�ft werden soll
 * @param return  gibt true bei richtigem DAtum zur�ck und Falsch bei falscher Eingabe
 */
function test_date($datum, $uhrzeit){
	$datu=explode(".", $datum);
	$uhr=explode(":", $uhrzeit);
	if(strlen($datu[0])!=2 || !is_numeric($datu[0])) return false;
	if(strlen($datu[1])!=2 || !is_numeric($datu[1])) return false;
	if(strlen($datu[2])!=4 || !is_numeric($datu[2])) return false;
	if(strlen($uhr[0])!=2 || !is_numeric($uhr[0])) return false;
	if(strlen($uhr[1])!=2 || !is_numeric($uhr[1])) return false;
	return true;	
}


if (!defined("PATH_TEMPLATES_UNITS_HOMEWORK")) define( "PATH_TEMPLATES_UNITS_HOMEWORK", PATH_EXTENSIONS. "units_homework/templates/" );

require_once( "../etc/koala.conf.php" );
require_once( PATH_LIB . "format_handling.inc.php" );
require_once( PATH_LIB . "url_handling.inc.php");
//require_once( PATH_LIB . "upload_handling.inc.php" );

$path = url_parse_rewrite_path( $_GET[ "path" ] );
$id = $path[2];
$env = steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);

$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );

if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" )
{
				$values = isset( $_POST[ "values" ] ) ? $_POST[ "values" ] : array();
				$problems = "";
				$hints    = "";
				

				if ( empty( $_FILES ) ){
					$problems = gettext( "Could not upload document." ) . " ";
					$hints = str_replace(
						array( "%SIZE", "%TIME" ),
						array( readable_filesize( $max_file_size ), (string)ini_get( 'max_execution_time' ) ),
						gettext( "Maybe your document exceeded the allowed file size (max. %SIZE) or the upload might have taken too long (max. %TIME seconds)." )
					) . " ";
				}
				
				//�berpr�ft, ob ein pdf hochgeladen wurde
				$path_part = pathinfo($_FILES[ "material" ]["name"]);
				if ( $path_part["extension"] != "pdf" ){
					$problems = gettext( "file-type not allowed" ) . " (".$path_part["extension"].")";
					$hints = gettext( "Please choose a pdf-document" ) . " ";
				}
				
				//�berpr�ft, ob ein Datum richtig ist
				if ( isset( $values[ "enddate_d" ] ) && isset( $values[ "enddate_h" ] ) ){
					if (!test_date($values[ "enddate_d" ],$values[ "enddate_h" ])){
						$problems = gettext( "Wrong Date" ) . " ";
						$hints = gettext( "Please enter a valid date." ) . " ";
					}	
				}
				
				if ( !isset( $values[ "groupsize" ]) ){
					$problems = gettext( "No Groupsize" ) . " ";
					$hints = gettext( "Please enter a groupsize." ) . " ";	
				}
				
				if ( !empty( $_FILES["material"]["error"] ) && $_FILES["material"]["error"] > 0 ){
					$problems = gettext( "No file chosen." ) . " ";
					$hints = gettext( "Please choose a local file to upload." ) . " ";
				}
				if ( empty( $problems ) )
				{
								$content = file_get_contents( $_FILES["material"]["tmp_name"] );
								$filename = str_replace( array( "\\", "'" ), array( "", "" ), $_FILES[ "material" ][ "name" ]  );
								$new_material = steam_factory::create_document(
									$GLOBALS[ "STEAM" ]->get_id(), 
									$filename,
									$content,
									$_FILES[ "material" ][ "type" ],
									FALSE
								);
								
								if ( isset( $values[ "dsc" ] ) )
									$new_material->set_attribute( "OBJ_DESC", $values[ "dsc" ] );
								if ( isset( $values[ "groupsize" ] ) )
									$new_material->set_attribute( "HOMEWORK_GROUPSIZE", $values[ "groupsize" ] );
									//Wenn keine Gruppenstaerke angegeben wird, wird sie auf 10 gesetzt
								else $new_material->set_attribute( "HOMEWORK_GROUPSIZE", 10 );
								
								if ( isset( $values[ "enddate_d" ] ) && isset( $values[ "enddate_h" ] ) ){
									$ende=$values[ "enddate_d" ]."-".$values[ "enddate_h" ];
									$end_time=strtotime($ende)-7200;
									$new_material->set_attribute( "HOMEWORK_ENDDATE", $end_time);	
								}
								else{
									$problems = gettext( "no enddate");
									$hints = gettext( "give an enddate");
								}
								
								//Die Variable HOMEWORK_TYPE gibt an ob Aufgabenstellung oder Loesung
								$new_material->set_attribute( "HOMEWORK_TYPE", task );
								
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

$content = new HTML_TEMPLATE_IT( PATH_TEMPLATES_UNITS_HOMEWORK );
$content->loadTemplateFile( "units_homework_new_homework.template.html" );
$content->setVariable( "LABEL_UPLOAD", gettext( "Upload" ) );
$content->setVariable( "LABEL_FILE", gettext( "Local file" ) );
$content->setVariable( "LABEL_DSC", gettext( "Description" ) );
$content->setVariable( "LABEL_GROUPSIZE", gettext( "Groupsize" ) );
$content->setVariable( "LABEL_ENDDATE", gettext( "End of Upload" ) );
$content->setVariable( "BACKLINK", "<a href=\"$backlink\">" . gettext( "back" ) . "</a>" );
$content->setVariable( "BACK_LINK", $backlink );
$content->setVariable( "LABEL_ENDDATEINFO_D", gettext( "DD.MM.YYYY" ) );
$content->setVariable( "LABEL_ENDDATEINFO_H", gettext( "HH:MM" ) );

    
$content->setVariable( "FORM_ACTION", "new_homework");

if ( $max_file_size > 0 ) {
	$content->setVariable( "MAX_FILE_SIZE_INPUT", "<input type='hidden' name='MAX_FILE_SIZE' value='" . (string)$max_file_size . "'/>" );
	$content->setVariable( "MAX_FILE_SIZE_INFO", "<br />" . str_replace( "%SIZE", readable_filesize( $max_file_size ), gettext( "The maximum allowed file size is %SIZE." ) ) );
}

$link_path = $koala_container->get_link_path();
if ( !is_array( $link_path ) ) $link_path = array();
$link_path[] = array( "name" => gettext( "Upload homework" ) );
$portal->set_page_main( $link_path, $content->get() );
$portal->set_page_title( gettext( "Upload document" ) );
$portal->show_html(); 
?>