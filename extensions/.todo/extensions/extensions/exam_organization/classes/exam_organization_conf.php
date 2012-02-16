<?php
/*
 * exam management config file
 * 
 * @author Marcel Jakoblew
 */

//Required classes
require_once("bonus_import.class.php");
require_once("exam_office_file_handling.class.php");
require_once("exam_organization_database.class.php");
require_once("exam_organization_exam_object_data.class.php");
require_once("exam_organization_ldap_manager.class.php");

//Constants
defined("EXAM_ORGANIZATION_DEBUG") or define("EXAM_ORGANIZATION_DEBUG", TRUE);
defined("PATH_TEMPLATES_EXAM_ORGANIZATION") or define("PATH_TEMPLATES_EXAM_ORGANIZATION", "exam_organization/templates/");
defined("EXAM_ORGANIZATION_TEMP_DIR") or define("EXAM_ORGANIZATION_TEMP_DIR", PATH_TEMP);

//database connection
defined("EXAM_ORGANIZATION_DATABASE_URL") or define("EXAM_ORGANIZATION_DATABASE_URL",STEAM_SERVER);
defined("EXAM_ORGANIZATION_DATABASE_NAME") or define("EXAM_ORGANIZATION_DATABASE_NAME","examorganization");
defined("EXAM_ORGANIZATION_DATABASE_PORT") or define("EXAM_ORGANIZATION_DATABASE_PORT","3306");
defined("EXAM_ORGANIZATION_DATABASE_USERNAME") or define("EXAM_ORGANIZATION_DATABASE_USERNAME","eoroot");
defined("EXAM_ORGANIZATION_DATABASE_PASSWORD") or define("EXAM_ORGANIZATION_DATABASE_PASSWORD","eosteam");
?>