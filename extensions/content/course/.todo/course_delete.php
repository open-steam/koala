<?php
include_once( "../etc/koala.conf.php" );
include_once( PATH_LIB . "url_handling.inc.php" );
include_once( PATH_LIB . "format_handling.inc.php" );

$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );
$user = lms_steam::get_current_user();
$course = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $_GET[ "course" ] );
$semester_name = $course->get_attribute("COURSE_SEMESTER");
$semester_object = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), "Courses." . $semester_name );
$course_name = $course->get_attribute("OBJ_DESC");
$admin_group = steam_factory::get_group( $GLOBALS["STEAM"]->get_id(), "Admin" );
$is_semester_admin = lms_steam::is_semester_admin($semester_object, $user);

//TODO: semester_admin check
//if ( !$admin_group->is_member( $user ) && !$is_semester_admin )
if ( !is_object($admin_group) || !$admin_group->is_member( $user ) )
{
	header("location:/");
	exit;
}

if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" )
{
	$values = $_POST[ "values" ];
	if ( $values[ "delete" ] )
	{
		$steam_group_course_admins = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), $course->get_groupname() . ".admins");
		$steam_group_course_learners = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), $course->get_groupname() . ".learners");
		$steam_group_course_staff = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), $course->get_groupname() . ".staff");

		$steam_group_course_admins->delete();
		$steam_group_course_learners->delete();
		$steam_group_course_staff->delete();
		$course->delete();
		
		//TODO: Update Cache
		
	    $_SESSION[ "confirmation" ] = str_replace("%NAME", $course_name, gettext( "The course '%NAME' has been deleted." ));
		header( "Location: " . PATH_URL . "semester/" . $semester_name . "/?mode=edit" );
	    exit;
	}
}

$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "object_delete.template.html" );
$backlink = PATH_URL . SEMESTER_URL . "/" . $semester_name . "/" . $_GET[ "course" ] . "/";

$content->setVariable( "LABEL_ARE_YOU_SURE", str_replace("%NAME", $course_name, gettext( "Are you sure you want to delete the course '%NAME' ?" )) );
$content->setVariable( "TEXT_INFORMATION", gettext("The course and all its data will be deleted.") );
$content->setVariable( "DELETE_BACK_LINK", $backlink);

$content->setCurrentBlock( "BLOCK_DELETE" );
	$content->setVariable( "FORM_ACTION", $_SERVER[ "REQUEST_URI" ] );
	$content->setVariable( "LABEL_DELETE_IT", gettext( "yes, delete it" ) );
	$content->setVariable( "BACK_LINK", $_SERVER[ "HTTP_REFERER" ] );
	$content->setVariable( "LABEL_RETURN", gettext( "back" ) );
$content->parse( "BLOCK_DELETE" );

$rootlink = lms_steam::get_link_to_root( $course );
$headline = array( $rootlink[0], $rootlink[1], array( "name" => gettext("Delete course") ) );

$portal->set_page_main(
	$headline,
	$content->get(),
	""
);
$portal->show_html();

?>