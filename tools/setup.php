#!/usr/bin/php
<?php
//session_start();  // prevent warnings concerning previous output (session_start() is called in koala.conf.php, which is included later in the file)

define( "TIMEZONE", "Europe/Berlin");

function output( $msg ) { fputs( STDOUT, $msg ); }

// config file paths:
$config_path = "etc/koala.def.php";
$config_template_path = "etc/koala.def.php.example";
$htaccess_path = "public/.htaccess";
$htaccess_template_path = "public/.htaccess.example";


include_once( "version.php" );
include_once( "lib/config_handling.inc.php" );

output( "koaLA " . KOALA_VERSION . " setup\n\n" );

$success = TRUE;


// check file system permissions
$required_paths = array();
$required_paths[] = new RequiredPath( RequiredPath::$FILE, $config_path, 0644, $config_template_path );
$required_paths[] = new RequiredPath( RequiredPath::$FILE, $htaccess_path, 0644, $htaccess_template_path );
$required_paths[] = new RequiredPath( RequiredPath::$DIR, "cache", 0777 );
$required_paths[] = new RequiredPath( RequiredPath::$DIR, "log", 0777 );
$required_paths[] = new RequiredPath( RequiredPath::$DIR, "temp", 0777 );
$required_paths[] = new RequiredPath( RequiredPath::$FILE, "log/errors.log", 0666 );
$required_paths[] = new RequiredPath( RequiredPath::$FILE, "log/messages.log", 0666 );
$required_paths[] = new RequiredPath( RequiredPath::$FILE, "log/security.log", 0666 );
$required_paths[] = new RequiredPath( RequiredPath::$FILE, "log/debug.log", 0666 );
$required_paths[] = new RequiredPath( RequiredPath::$FILE, "log/paulsync.log", 0666 );
$required_paths[] = new RequiredPath( RequiredPath::$FILE, "log/paulsync_last.log", 0666 );
$required_paths_okay = TRUE;
output( "Checking files and directories for existance and permissions ...\n" );
foreach ( $required_paths as $path ) {
	if ( !$path->check( TRUE ) ) $required_paths_okay = FALSE;
}
if ( !$required_paths_okay ) {
	$ask_fix = new ConfigEntry( "fix", "YesNo", "Create missing files/directories and fix permissions?", NULL, "yes" );
	if ( $ask_fix->ask() == "no" ) $success = FALSE;
	else {
		$required_paths_okay = TRUE;
		foreach ( $required_paths as $path )
			if ( !$path->fix( TRUE ) ) $required_paths_okay = FALSE;
		if ( !$required_paths_okay ) {
			output( "Could not fix all files/directories.\n" );
			exit( 1 );
		}
	}
}
else output( "Files and directory permissions are okay.\n\n" );


$config = new Config( $config_path, $config_template_path );

// offer a random encryption key as default in the config:
if ( is_object( $config->get_entry( "ENCRYPTION_KEY" ) ) ) {
	$encryption_key = "";
	$encryption_charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789.-_!$";
	for ( $i=0; $i<20; $i++ )
		$encryption_key .= $encryption_charset[rand( 0, strlen($encryption_charset)-1 )];
	$config_encryption_key = $config->get_entry( 'ENCRYPTION_KEY' )->get_config_answer();
	if ( empty( $config_encryption_key ) ) {
		$config->get_entry( 'ENCRYPTION_KEY' )->set_answer( $encryption_key );
		$config->get_entry( "ENCRYPTION_KEY" )->set_default_answer( $encryption_key );
	}
}

// offer the current semester as a default in the config:
if ( is_object( $config->get_entry( "STEAM_CURRENT_SEMESTER" ) ) ) {
	$current_semester = "";
	if ( (int)date( "m" ) < 7 )  // next summer semester
		$current_semester = "SS" . date( "y" );
	else  // next winter semester
		$current_semester = "WS" . date( "y" ) . date( "y", time() + 356*24*60*60 );
	$config->get_entry( "STEAM_CURRENT_SEMESTER" )->set_default_answer( $current_semester );
}


// ask user for config values:
$config->ask();
$path_koala = $config->get_entry( "PATH_KOALA" )->get_answer();
if ( is_string( $path_koala ) ) {
	$path_koala = rtrim( $path_koala, "/" ) . "/";
	$config->get_entry( "PATH_KOALA" )->set_answer( $path_koala );
}
$path_server = $config->get_entry( "PATH_SERVER" )->get_answer();
if ( is_string( $path_server ) ) {
	$path_server = rtrim( $path_server, "/" );
	$config->get_entry( "PATH_SERVER" )->set_answer( $path_server );
}

$undocumented_configs = array();
// ignore unspecified settings that are installation-specific:
$ignore_unspecified = array( "STEAM_FACULTIES_GROUP", "STEAM_COURSES_GROUP", "STEAM_PUBLIC_GROUP", "STEAM_PRIVATE_GROUP" );
foreach ( $config->get_undocumented_entries() as $entry ) {
	if ( in_array( $entry->get_config_key(), $ignore_unspecified ) ) continue;
	if ( is_string( $entry->get_config_answer() ) && $entry->get_config_answer() == $entry->get_default_answer() ) continue;
	$undocumented_configs[] = $entry;
}
if ( count( $undocumented_configs ) > 0 ) {
	output( "\nThe following settings are missing or differing in your config:\n(Missing values are marked with an asterisk '*')\n" );
	foreach ( $undocumented_configs as $entry ) {
		output( (is_string( $entry->get_config_answer()) ? "  " : "* ") . $entry->get_config_key() . " = " . $entry->get_config_answer() . " (default: " . $entry->get_default_answer() . ")\n" );
	}
	$ask_continue = new ConfigEntry( "continue", "YesNo", "\nDo you want to insert or change any of these?" );
	if ( $ask_continue->ask() == "yes" ) {
		foreach ( $undocumented_configs as $entry ) {
			$entry = $config->add_entry( $entry->get_config_key(), "Any", $entry->get_config_key() );
			$entry->set_is_optional( TRUE );
			// if the user didn't set a value for an undocumented entry, make it undocumented again:
			if ( !is_string( $entry->ask() ) ) {
				$config->remove_entry( $entry->get_config_key() );
				$config->add_undocumented_entry( $entry->get_config_key(), $entry->get_default_answer() );
			}
		}
	}
}

// show config and changes to user:
output( "\nYour chosen settings:\n(Changed values are marked with an asterisk '*')\n" );
foreach ( $config->get_entries() as $entry )
	output( ($entry->has_changed() ? "* " : "  ") . $entry->get_config_key() . " = " . $entry->get_answer() . "\n" );

// write config to file (if necessary):
if ( sizeof( $config->get_changes() ) > 0 ) {
	$ask_continue = new ConfigEntry( "continue", "YesNo", "\nWrite these settings to the config file?" );
	if ( $ask_continue->ask() == "no" )
		exit( 1 );
	if ( !$config->write_config() ) {
		output( "Error: could not write config file: " . $config_path . "\n" );
		exit( 1 );
	}
	else output( "Wrote config to " . $config_path . "\n" );
}
else
	output( "\nYour config settings haven't changed.\n" );


// check public/.htaccess file:
output( "\n" );
if ( file_exists( $htaccess_path ) ) {
	$ask_rewrite = new ConfigEntry( "apply.htaccess", "AbsolutePath", "The RewriteBase for " . $htaccess_path, NULL, "/" );
	if ( file_exists( $htaccess_path ) ) {
		foreach ( file( $htaccess_path ) as $line ) {
			$match = array();
			if ( preg_match( "#^([ \t]*RewriteBase[ \t]*)([^ \t\n]*)[ \t]*$#", $line, $match ) > 0 ) {
				$ask_rewrite->set_config_answer( $match[2] );
				$ask_rewrite->set_answer( $match[2] );
				break;
			}
		}
	}
	$htaccess_changed = FALSE;
	$htaccess = file( $htaccess_path );
	$htaccess_template = file( $htaccess_template_path );
	if ( count( $htaccess) != count( $htaccess_template) )
		$htaccess_changed = TRUE;
	else for ( $i=0; $i<count( $htaccess_template ); $i++ ) {
		if ( strpos( $htaccess[$i], "RewriteBase" ) !== FALSE && strpos( $htaccess_template[$i], "RewriteBase" ) !== FALSE ) continue;
		if ( $htaccess[$i] != $htaccess_template[$i] ) {
			$htaccess_changed = TRUE;
			break;
		}
	}
	if ( $htaccess_changed )
		output( "Your " . $htaccess_path . " needs to be updated.\n" );
	$ask_rewrite->ask();
	$rewrite_base = $ask_rewrite->get_answer();
	if ( is_string( $rewrite_base ) ) {
		$rewrite_base = rtrim( $rewrite_base, "/" ) . "/";
		$ask_rewrite->set_answer( $rewrite_base );
	}
	$ask_update = new ConfigEntry( "update.htaccess", "YesNo", "Update " . $htaccess_path . " ?", NULL, "yes" );
	if ( $htaccess_changed || $ask_rewrite->get_answer() !== $ask_rewrite->get_config_answer() )
		$ask_update->ask();
	if ( $ask_update->get_answer() === "yes" ) {
		if ( !copy( $htaccess_template_path, $htaccess_path ) ) {
			output( "Error: could not update " . $htaccess_path . "\n" );
			exit( 1 );
		}
		$htaccess = file( $htaccess_path );
		for ( $i=count($htaccess)-1; $i>=0; $i-- ) {
			$match = array();
			if ( preg_match( "#^([ \t]*RewriteBase[ \t]*)([^ \t]*)[ \t]*$#", $htaccess[$i], $match ) < 1 ) continue;
			$htaccess[$i] = $match[1] . $rewrite_base . "\n";
			break;
		}
		$file = fopen( $htaccess_path, "wb" );
		$written = fwrite( $file, implode( "", $htaccess ) );
		fclose( $file );
		if ( $written == 0 ) {
			output( "Error: could not write " . $htaccess_path . "\n" );
			exit( 1 );
		}
		output( "Wrote RewriteBase to " . $htaccess_path . "\n" );
	}
}


// check sTeam structures and create them if necessary:
include_once( "etc/koala.conf.php" );
set_exception_handler( NULL );
include_once( "lib/steam_handling.inc.php" );
try {
	steam_connect( STEAM_SERVER, STEAM_PORT, STEAM_ROOT_LOGIN, STEAM_ROOT_PW );
	if ( !$STEAM->get_login_status() )
		throw new Exception( "Could not connect/login." );
	$steam_group = steam_factory::get_group( $STEAM->get_id(), "sTeam" );
	if ( !is_object( $steam_group ) ) {
		output( "Error: could not get 'sTeam' group from open-sTeam backend.\n" );
		exit( 1 );
	}
	$admin_group = steam_factory::get_group( $STEAM->get_id(), "Admin" );
	if ( !is_object( $admin_group ) ) {
		output( "Error: could not get 'Admin' group from open-sTeam backend.\n" );
		exit( 1 );
	}
	$config->add_entry( "STEAM_FACULTIES_GROUP", "Number", "open-sTeam faculties group object-id" );
	$config->add_entry( "STEAM_COURSES_GROUP", "Number", "open-sTeam courses group object-id" );
	$config->add_entry( "STEAM_PUBLIC_GROUP", "Number", "open-sTeam public group object-id" );
	$config->add_entry( "STEAM_PRIVATE_GROUP", "Number", "open-sTeam private group object-id" );
	
	$faculties_access = array( $steam_group->get_id() => SANCTION_READ );
	$courses_access = array( $steam_group->get_id() => SANCTION_READ );
	$publicgroups_access = array( $steam_group->get_id() => SANCTION_READ|SANCTION_INSERT );
	$publicgroups_workroom_access = array( $steam_group->get_id() => SANCTION_READ|SANCTION_INSERT );
	$privgroups_access = array( $steam_group->get_id() => SANCTION_READ|SANCTION_INSERT );
	$semester_access = array( $steam_group->get_id() => SANCTION_READ );  // will be modified below
	$semester_admins_access = array( $steam_group->get_id() => SANCTION_READ );
	$admin_access = array( $admin_group->get_id() => SANCTION_ALL );
	
	$steam_status = "";
	if ( !is_object( $g = check_steam_group( "Faculties" ) ) )
		$steam_status .= "* create group 'Faculties'\n";
	else {
		$config->get_entry( "STEAM_FACULTIES_GROUP" )->set_answer( (string)($g->get_id()) );
		if ( !check_steam_access( $g, $faculties_access ) )
			$steam_status .= "* set permissions on group 'Faculties'.\n";
	}
	if ( !is_object( $g = check_steam_group( "Courses" ) ) )
		$steam_status .= "* create group 'Courses'\n";
	else {
		$config->get_entry( "STEAM_COURSES_GROUP" )->set_answer( (string)($g->get_id()) );
		if ( !check_steam_access( $g, $courses_access ) )
			$steam_status .= "* set permissions on group 'Courses'.\n";
	}
	if ( !is_object( $g = check_steam_group( "PublicGroups" ) ) )
		$steam_status .= "* create group 'PublicGroups'\n";
	else {
		$config->get_entry( "STEAM_PUBLIC_GROUP" )->set_answer( (string)($g->get_id()) );
		if ( !check_steam_access( $g, $publicgroups_access ) || !check_steam_access( $g->get_workroom(), $publicgroups_workroom_access ) )
			$steam_status .= "* set permissions on group 'PublicGroups'.\n";
	}
	if ( !is_object( $g = check_steam_group( "PrivGroups" ) ) )
		$steam_status .= "* create group 'PrivGroups'\n";
	else {
		$config->get_entry( "STEAM_PRIVATE_GROUP" )->set_answer( (string)($g->get_id()) );
		if ( !check_steam_access( $g, $privgroups_access ) )
			$steam_status .= "* set permissions on group 'PrivGroups'.\n";
	}
	$current_semester = $config->get_entry( "STEAM_CURRENT_SEMESTER" )->get_answer();
	if ( !is_object( $g = check_steam_group( $current_semester, "Courses" ) ) )
		$steam_status .= "* create group 'Courses." . $current_semester . " (current/default koaLA semester)'\n";
	else {
		$admins_group = check_steam_group( "admins", "Courses." . $current_semester );
		if ( is_object( $admins_group ) )
			$semester_access[ $admins_group->get_id() ] = SANCTION_READ|SANCTION_INSERT;
		if ( !check_steam_access( $g, $semester_access ) )
			$steam_status .= "* set permissions on group 'Courses." . $current_semester . "'.\n";
	}
	if ( !is_object( $g = check_steam_group( "admins", "Courses." . $current_semester ) ) )
		$steam_status .= "* create group 'Courses." . $current_semester . ".admins (current/default koaLA semester admins)'\n";
	else if ( !check_steam_access( $g, $semester_admins_access ) )
		$steam_status .= "* set permissions on group 'Courses." . $current_semester . ".admins'.\n";

	if ( !is_object( $g = check_steam_container( "/config/koala" ) ) )
		$steam_status .= "* create container /config/koala (koaLA config folder)\n";
	if ( !is_object( $g = check_steam_container( "/config/koala/extensions" ) ) )
		$steam_status .= "* create container /config/koala/extensions (koaLA extensions config folder)\n";
		
	if ( !empty( $steam_status ) ) {
		output( "\nThe following changes in your open-sTeam backend need to be performed:\n" . $steam_status );
		$ask_continue = new ConfigEntry( "continue", "YesNo", "\nPerform these changes in your open-sTeam backend?" );
		if ( $ask_continue->ask() == "no" )
			exit( 1 );
		
		// Faculties group
		if ( is_object( $g = check_steam_group( "Faculties", NULL, "koaLA faculties", TRUE ) ) ) {
			$config->get_entry( "STEAM_FACULTIES_GROUP" )->set_answer( (string)($g->get_id()) );
			if ( !check_steam_access( $g, $faculties_access, TRUE ) ) {
				output( "Warning: could not set permissions on group 'Faculties'.\n" );
				$success = FALSE;
			}
		}
		else $success = FALSE;
		
		// Courses group
		if ( is_object( $g = check_steam_group( "Courses", NULL, "koaLA courses", TRUE ) ) ) {
			$config->get_entry( "STEAM_COURSES_GROUP" )->set_answer( (string)($g->get_id()) );
			if ( !check_steam_access( $g, $courses_access, TRUE ) ) {
				output( "Warning: could not set permissions on group 'Courses'.\n" );
				$success = FALSE;
			}
		}
		else $success = FALSE;
		
		// PublicGroups group
		if ( is_object( $g = check_steam_group( "PublicGroups", NULL, "The group to create public groups in.", TRUE ) ) ) {
			$config->get_entry( "STEAM_PUBLIC_GROUP" )->set_answer( (string)($g->get_id()) );
			if ( !check_steam_access( $g, $publicgroups_access, TRUE ) ) {
				output( "Warning: could not set permissions on group 'PublicGroups'.\n" );
				$success = FALSE;
			}
			if ( is_object( $w = $g->get_workroom() ) && !check_steam_access( $w, $publicgroups_workroom_access, TRUE ) ) {
				 output( "Error: could not set permissions on 'PublicGroups' workroom.\n" );
				 $success = FALSE;
			}
		}
		else $success = FALSE;
		
		// PrivGroups group
		if ( is_object( $g = check_steam_group( "PrivGroups", NULL, "The group to create private groups in.", TRUE ) ) ) {
			$config->get_entry( "STEAM_PRIVATE_GROUP" )->set_answer( (string)($g->get_id()) );
			if ( !check_steam_access( $g, $privgroups_access, TRUE ) ) {
				output( "Warning: could not set permissions on group 'PrivGroups'.\n" );
				$success = FALSE;
			}
		}
		else $success = FALSE;
		
		// current semester group and admins group
		$current_semester_desc = NULL;
		$match = array();
		if ( preg_match( "#^WS([0-9][0-9])([0-9][0-9])$#", $current_semester, $match ) > 0 )
			$current_semester_desc = "Wintersemester " . $match[1] . "/" . $match[2];
		else if ( preg_match( "#^SS([0-9][0-9])$#", $current_semester, $match ) > 0 )
			$current_semester_desc = "Sommersemester " . $match[1];
		if ( is_object( $current_semester_group = check_steam_group( $current_semester, "Courses", $current_semester_desc, TRUE ) ) ) {
			$current_semester_admins_desc = $current_semester_desc;
			if ( is_string( $current_semester_admins_desc ) )
				$current_semester_admins_desc .= " admins";
			else
				$current_semester_admins_desc = $current_semester . " admins";
			if ( is_object( $g = check_steam_group( "admins", "Courses." . $current_semester, $current_semester_admins_desc, TRUE ) ) ) {
				if ( !check_steam_access( $g, $semester_admins_access, TRUE ) ) {
					output( "Warning: could not set permissions on group 'Courses." . $current_semester . ".admins'.\n" );
					$success = FALSE;
				}
				$semester_access[ $g->get_id() ] = SANCTION_READ|SANCTION_INSERT;
			}
			if ( !check_steam_access( $current_semester_group, $semester_access, TRUE ) ) {
				output( "Warning: could not set permissions on group 'Courses." . $current_semester . "'.\n" );
				$success = FALSE;
			}
		}
		else $success = FALSE;
		
		// koaLA config folder and extensions:
		if ( !is_object( check_steam_container( "/config/koala", "koaLA configuration settings", TRUE ) ) ) {
			output( "Warning: missing container in open-sTeam backend: /config/koala\n" );
			$success = FALSE;
		}
		if ( !is_object( check_steam_container( "/config/koala/extensions", "koaLA extensions configuration settings", TRUE ) ) ) {
			output( "Warning: missing container in open-sTeam backend: /config/koala/extensions\n" );
			$success = FALSE;
		}
	}
	
	// show changes and write config to file (if necessary):
	if ( sizeof( $config->get_changes() ) > 0 ) {
		output( "\nThe open-sTeam structures need to be registered in the config file.\nThis will result in the following config settings:\n" );
		foreach ( $config->get_changes() as $entry )
			output( "* " . $entry->get_config_key() . " = " . $entry->get_answer() . "\n" );
		$ask_continue = new ConfigEntry( "continue", "YesNo", "\nWrite these settings to the config file?" );
		if ( $ask_continue->ask() == "no" )
			exit( 1 );
		if ( !$config->write_config() ) {
			output( "Error: could not write config file: " . $config_path . "\n" );
			exit( 1 );
		}
		else output( "Wrote config to " . $config_path . "\n" );
	}
}
catch ( Exception $e ) {
	output( "Error while accessing the open-sTeam backend " . STEAM_SERVER . ":" . STEAM_PORT . " as '" . STEAM_ROOT_LOGIN . "'.\n" );
	output( $e->getMessage() . "\n" . $e->getTraceAsString() . "\n" );
	exit( 1 );
}


// check koala_icons package on open-sTeam server:
output( "\nChecking koala_support package ...\n" );
$koala_support = $STEAM->get_module( "package:koala_support" );
if ( is_object($koala_support) )
	$koala_support_installed_version = $STEAM->predefined_command( $koala_support, "get_version", array(), 0 );
$opensteam = new steam_connector( "steam.uni-paderborn.de", 1900, "guest", "guest" );
$koala_support_folder = steam_factory::get_object_by_name( $opensteam->get_id(), "/home/web.steamware/Packages/koala_support/download" );
$koala_support_server_version = "0_0";
foreach ( $koala_support_folder->get_inventory() as $item ) {
	$match = array();
	if ( preg_match( "#koala_support-([0-9]+_[0-9]+)\.spm#", $item->get_name(), $match ) <= 0 )
		continue;
	if ( isset( $match[1] ) && is_string( $match[1]) && !empty( $match[1] ) ) {
		$match[1] = str_replace( "_", ".", $match[1] );
		if ( strcmp( $match[1], $koala_support_server_version ) ) {
			$koala_support_spm = $item;
			$koala_support_server_version = $match[1];
		}
	}
}
if ( !isset( $koala_support_installed_version ) ) {
	output( "No koala_support found" );
	$koala_support_installed_version = "0.0";
	$ask_text = "Install koala_support version ";
}
else {
	output( "Found koala_support $koala_support_installed_version" );
	$ask_text = "Update koala_support to version ";
}
output( " (newest available version is $koala_support_server_version).\n" );
if ( strcmp( $koala_support_installed_version, $koala_support_server_version ) < 0 ) {
	$ask_update = new ConfigEntry( "update.koala_support", "YesNo", $ask_text . $koala_support_server_version . " ?", NULL, "yes" );
	if ( $ask_update->ask() === "yes" ) {
		$package_container = steam_factory::get_object_by_name( $STEAM->get_id(), "/packages" );
		if ( !is_object( $package_container ) ) {
			output( "Could not find /packages on your open-sTeam server.\n" );
			exit( 1 );
		}
		$package = steam_factory::create_document( $STEAM->get_id(), $koala_support_spm->get_name(), $koala_support_spm->get_content(), $koala_support_spm->get_attribute( DOC_MIME_TYPE ), $package_container );
		if ( !is_object( $package ) ) {
			output( "Could not upload " . $koala_support_spm->get_name() . " to your open-sTeam server.\n" );
			exit( 1 );
		}
		try {
			if ( !$STEAM->install_package( $package ) )
				throw new Exception( "Installation failed." );
			output( "Installed koala_support $koala_support_server_version on your open-sTeam server.\n" );
		}
		catch ( Exception $ex ) {
			output( "Could not install koala_support $koala_support_server_version: " . $ex->getMessage() . "\n" );
			output( $ex->getTraceAsString() . "\n" );
		}
		$package->delete();
	}
}


output( "\n" );
exit( $success ? 0 : 1 );



// utility class for checking required files and directories:

class RequiredPath {
	public static $FILE = "File";
	public static $DIR = "Directory";
	public $path;
	public $template_path;
	public $mode;
	public $type;
	function RequiredPath ( $type, $path, $mode, $template_path = FALSE ) {
		$this->type = $type;
		$this->path = $path;
		$this->mode = $mode;
		$this->template_path = $template_path;
	}
	function check ( $verbose = FALSE ) {
		$okay = TRUE;
		if ( !$this->check_exists( $verbose ) ) $okay = FALSE;
		if ( !$this->check_mode( $verbose ) ) $okay = FALSE;
		return $okay;
	}
	function check_exists ( $verbose = FALSE ) {
		if ( $this->type == self::$DIR && is_dir( $this->path ) ) return TRUE;
		if ( $this->type == self::$FILE && file_exists( $this->path ) ) return TRUE;
		if ( $verbose ) output( "  " . $this->type . " " . $this->path . " is missing.\n" );
		return FALSE;
	}
	function check_mode ( $verbose = FALSE ) {
		if ( !file_exists( $this->path ) ) return FALSE;
		$stat = stat( $this->path );
		$mode = $stat[ "mode" ] & 0777;
		if ( $mode == $this->mode ) return TRUE;
		if ( $verbose ) output( "  " . $this->type . " " . $this->path . " has wrong permissions: " . sprintf( "%o", $mode ) . ".\n" );
		return FALSE;
	}
	function fix ( $verbose = FALSE ) {
		if ( !$this->check_exists( FALSE ) ) {
			if ( $this->type == self::$FILE ) {
				if ( is_string( $this->template_path ) ) {
					if ( !copy( $this->template_path, $this->path ) ) {
						if ( $verbose ) output( "Could not copy " . $this->template_path . " to " . $this->path . "\n" );
						return FALSE;
					}
					if ( $verbose ) output( "Copied " . $this->template_path . " to " . $this->path . "\n" );
				}
				else {
					if ( !touch( $this->path ) ) {
						if ( $verbose ) output( "Could not create file " . $this->path . "\n" );
						return FALSE;
					}
					if ( $verbose ) output( "Created file " . $this->path . "\n" );
				}
			}
			else if ( $this->type == self::$DIR ) {
				if ( !mkdir( $this->path, $this->mode ) ) {
					if ( $verbose ) output( "Could not create directory " . $this->path . "\n" );
					return FALSE;
				}
				if ( $verbose ) output( "Created directory " . $this->path . "\n" );
			}
		}
		if ( !$this->check_mode( FALSE ) ) {
			if ( !chmod( $this->path, $this->mode ) ) {
				if ( $verbose ) output( "Could not set permissions of " . $this->path . " to " . sprintf( "%o", $this->mode ) . "\n" );
				return FALSE;
			}
			if ( $verbose ) output( "Changed permissions of " . $this->path . " to " . sprintf( "%o", $this->mode ) . "\n" );
		}
		return TRUE;
	}
}


// utility functions for checking open-sTeam structures:

function check_steam_group ( $group_name, $parent_group_name = NULL, $description = NULL, $fix = FALSE ) {
	$parent_group = NULL;
	if ( is_string( $parent_group_name ) ) {
		$parent_group = steam_factory::get_group( $GLOBALS[ "STEAM" ]->get_id(), $parent_group_name );
		if ( !is_object( $parent_group ) ) {
			output( "Error: could not find parent group '" . $parent_group_name . "' for group '" . $group_name . "'.\n" );
			return FALSE;
		}
	}
	$group_fullname = ( is_string( $parent_group_name ) ? $parent_group_name . "." : "" ) . $group_name;
	$group = steam_factory::get_group( $GLOBALS[ "STEAM" ]->get_id(), $group_fullname );
	if ( is_object( $group ) )
		return $group;
	if ( !$fix ) return FALSE;
	$group = steam_factory::create_group( $GLOBALS[ "STEAM" ]->get_id(), $group_name, $parent_group, NULL, $description );
	if ( is_object( $group ) )
		output( "Created group '" . $group_fullname . "'.\n" );
	else
		output( "Error: could not create group '" . $group_fullname . "'.\n" );
	return $group;
}

function check_steam_container ( $container_path, $description = "", $fix = FALSE ) {
	$container = steam_factory::get_object_by_name( $GLOBALS[ "STEAM" ]->get_id(), $container_path );
	if ( !is_object( $container ) ) {
		if ( ! $fix ) return FALSE;
		if ( !is_object( $container_environment = steam_factory::get_object_by_name( $GLOBALS[ "STEAM" ]->get_id(), dirname( $container_path ) ) ) ) {
			output( "Error: could not get parent directory for container: '" . dirname( $container_path ) . "'\n" );
			return FALSE;
		}
		if ( !is_object( $container = steam_factory::create_container( $GLOBALS[ "STEAM" ]->get_id(), basename( $container_path ), $container_environment, $description ) ) ) {
			output( "Error: could not create container: '" . $container_path . "'.\n" );
			return FALSE;
		}
		output( "Created container: '" . $container_path . "'.\n" );
	}
	return $container;
}

function check_steam_access ( $steam_object, $permissions, $fix = FALSE ) {
	if ( !is_object( $steam_object ) ) return NULL;
	$okay = TRUE;
	foreach ( $permissions as $who_id => $what ) {
		$who = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $who_id );
		if ( $steam_object->query_sanction( $who ) != $what ) {
			if ( !$fix ) $okay = FALSE;
			else {
				$steam_object->set_sanction( $who, $what );
				if ( $steam_object->query_sanction( $who ) == $what )
					output( "Fixed permissions for '" . $steam_object->get_name() . "'.\n" );
				else {
					$okay = FALSE;
					output( "Error: could not fix permissions for '" . $steam_object->get_name() . "'.\n" );
				}
			}
		}
	}
	return $okay;
}

?>
