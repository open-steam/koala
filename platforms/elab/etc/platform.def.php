<?php
defined("PATH_CURRENT_PLATFORM") or define("PATH_CURRENT_PLATFORM", dirname(dirname(__FILE__)) . "/");
defined("STYLE") or define("STYLE", "elab");

// short name and language variation
defined("PLATFORM_ID") or define("PLATFORM_ID", "elab");
// plattform name
defined("PLATFORM_NAME") or define("PLATFORM_NAME", "elab");
// plattform folder
defined("PLATFORM_FOLDER") or define("PLATFORM_FOLDER", PLATFORM_ID);

//displayed in browser title
defined("PLATFORM_TITLE") or define("PLATFORM_TITLE", "e-lab");

//copyright information
defined("COPYRIGHT_NAME") or define("COPYRIGHT_NAME", "Fachgruppe Kontextuelle Informatik");

define("STARTPAGE_IMAGE_TEXT_LONG", "\"zusammenwirken\", \"kommunizieren\", \"communicate\", \"communiquer\"");
define("STARTPAGE_IMAGE_TEXT_MEDIUM", "\"verbinden\",\"cooperate\",\"comunicar\",\"compartir\",\"colaborar\",\"apprendre\",\"partager\",\"aprender\",\"coopérer\",\"joindre\"");
define("STARTPAGE_IMAGE_TEXT_SHORT", "\"teilen\",\"lernen\",\"juntar\",\"share\",\"learn\",\"join\"");

defined("DISCLAIMER") or define("DISCLAIMER", FALSE);
defined("CHANGE_PASSWORD") or define("CHANGE_PASSWORD", FALSE);

define("MENU_YOU", FALSE);

define("YOUR_MAILBOX", false);
define("YOUR_CALENDER", false);
define("COURSES_MENU", false);
define("CONTACTS_MENU", false);
define("GROUPS_MENU", false);

define("SERVERMENU", false);
define("BLACKLISTED_EXTENSIONS", "PortfolioHome, ChangeLogHome, LastVisitedHome, SchoolHome");


$whitelistDevelopment = "Chronic, Clipboard, FileTree, Help, Home, Imprint, Startpage, Widgets, "
                        . "FolderObject, DocumentObject, DocumentHTMLObject, Portal, Forum, Gallery, DocumentPlainObject, ExitObject, LinkObject, WebLinkObject, Pyramiddiscussion, Rapidfeedback, Map, Questionary,"
                        . "Mplme, Wave, Bookmarks, BookmarksHome, Explorer, DocumentsHome, Favorite, PortalColumn, PortletAppointment, PortletHeadline, PortletMedia, PortletMsg, PortletPoll, PortletRss, PortletTermplan, PortletTopic, Profile, Worksheet,"
                        . "NotAccess, NotFound, Ajax, Rest, Webdav, Application, Download, Error, Frame, MainMenu, SignIn, Upload, Trashbin, AsciiSvgGenerator, Wiki, PortalOld, bid2PathCompatibility, Worksheet, Webarena, Spreadsheets, "
                        . "HomePortal, PortletUserPicture, PortletChronic, PortletBookmarks, Terms, Mokodesk";


$whitelistLiveSystems = "Chronic, Clipboard, FileTree, Help, Home, Imprint, Startpage, Widgets, "
                        . "FolderObject, DocumentObject, DocumentHTMLObject, Portal, Forum, Gallery, DocumentPlainObject, ExitObject, LinkObject, WebLinkObject, Rapidfeedback, Map, Questionary,"
                        . "Mplme, Wave, Bookmarks, BookmarksHome, Explorer, DocumentsHome, Favorite, PortalColumn, PortletAppointment, PortletHeadline, PortletMedia, PortletMsg, PortletPoll, PortletRss, PortletTermplan, PortletTopic, Profile,"
                        . "NotAccess, NotFound, Ajax, Rest, Webdav, Application, Download, Error, Frame, MainMenu, SignIn, Upload, Trashbin, AsciiSvgGenerator, Wiki, PortalOld, bid2PathCompatibility, Terms, Mokodesk";




//choose the extensions set
define("EXTENSIONS_WHITELIST", $whitelistLiveSystems);


// bid.lspb.de / develompent
$menusElab = array(
array("name" => "Übergreifendes", "link" => "/portal/index/99",
      "menu" => array(
                    array( "name" => "Einstiegsseite", "link" => "/explorer/index/99" ),
                    array( "name" => "Schulen", "link" => "/explorer/index/163621" ),
                    array( "name" => "Lernstatt intern", "link" => "/explorer/index/163622" )
      )
));


//choose menu set
$menus = $menusElab;


define("PLATFROM_MENUS", json_encode($menus));
define("YOUR_COURSES", false);
define("YOUR_GROUPS", false);
define("YOUR_CONTACTS",false);
define("YOUR_FAVORITES", true);
define("YOUR_SCHOOLBOOKMARKS", false);

defined("ENABLED_STATUS") or define("ENABLED_STATUS", false);
defined("ENABLED_EMAIL") or define("ENABLED_EMAIL", false);
defined("ENABLED_GENDER") or define("ENABLED_GENDER", false);
defined("ENABLED_FACULTY") or define("ENABLED_FACULTY", false);
defined("ENABLED_WANTS") or define("ENABLED_WANTS", false);
defined("ENABLED_HAVES") or define("ENABLED_HAVES", false);
defined("ENABLED_ORGANIZATIONS") or define("ENABLED_ORGANIZATIONS", false);
defined("ENABLED_HOMETOWN") or define("ENABLED_HOMETOWN", false);
defined("ENABLED_MAIN_FOCUS") or define("ENABLED_MAIN_FOCUS", false);
defined("ENABLED_OTHER_INTERESTS") or define("ENABLED_OTHER_INTERESTS", false);
defined("ENABLED_CONTACTS") or define("ENABLED_CONTACTS", false);
defined("ENABLED_LANGUAGES") or define("ENABLED_LANGUAGES", false);
defined("ENABLED_GROUPS") or define("ENABLED_GROUPS", false);
defined("ENABLED_ADDRESS") or define("ENABLED_ADDRESS", false);
defined("ENABLED_TELEPHONE") or define("ENABLED_TELEPHONE", false);
defined("ENABLED_PHONE_MOBILE") or define("ENABLED_PHONE_MOBILE", false);
defined("ENABLED_WEBSITE") or define("ENABLED_WEBSITE", false);
defined("ENABLED_ICQ_NUMBER") or define("ENABLED_ICQ_NUMBER", false);
defined("ENABLED_MSN_IDENTIFICATION") or define("ENABLED_MSN_IDENTIFICATION", false);
defined("ENABLED_AIM_ALIAS") or define("ENABLED_AIM_ALIAS", false);
defined("ENABLED_YAHOO_ID") or define("ENABLED_YAHOO_ID", false);
defined("ENABLED_SKYPE_NAME") or define("ENABLED_SKYPE_NAME", false);

defined("ENABLED_DEGREE") or define("ENABLED_DEGREE", false);
defined("ENABLED_USER_DESC") or define("ENABLED_USER_DESC", false);
defined("ENABLED_FIRST_NAME") or define("ENABLED_FIRST_NAME", false);
defined("ENABLED_FULL_NAME") or define ("ENABLED_FULL_NAME", false);

defined("ENABLED_PROFILE_TITLE") or define("ENABLED_PROFILE_TITLE",false);
defined("ENABLED_CONTACTS_TITLE") or define("ENABLED_CONTACTS_TITLE",false);
defined("ENABLED_CONTACTS_GROUPS_TITLE") or define("ENABLED_CONTACTS_GROUPS_TITLE",false);

// bid profile
defined("ENABLED_BID_DESCIPTION") or define("ENABLED_BID_DESCIPTION", true);
defined("ENABLED_BID_ADRESS") or define("ENABLED_BID_ADRESS", true);
defined("ENABLED_BID_EMAIL") or define("ENABLED_BID_EMAIL", true);
defined("ENABLED_BID_PHONE") or define("ENABLED_BID_PHONE", true);
defined("ENABLED_BID_IM") or define("ENABLED_BID_IM", true);
defined("ENABLED_BID_LANGUAGE") or define("ENABLED_BID_LANGUAGE", false);
defined("ENABLED_BID_NAME") or define("ENABLED_BID_NAME", true);

defined("VIEW_FAVORITES") or define("VIEW_FAVORITES", true);
defined("SEARCH_FAVORITES") or define("SEARCH_FAVORITES", true);
defined("PROFILE_SEND_MAIL") or define("PROFILE_SEND_MAIL", false);
defined("PROFILE_INTRODUCE_PERSON") or define("PROFILE_INTRODUCE_PERSON", false);
defined("PROFILE_MANAGE_CONTACT") or define("PROFILE_MANAGE_CONTACT", false);

// delete group home exits in user workrooms in explorer index command
defined("DELETE_GROUP_HOME_EXITS") or define("DELETE_GROUP_HOME_EXITS", true);

// enable automatic image scaling on uploads, needs imagick library
defined("ENABLE_AUTOMATIC_IMAGE_SCALING") or define("ENABLE_AUTOMATIC_IMAGE_SCALING", false);

//your school link in main menu
defined("YOUR_SCHOOL") or define("YOUR_SCHOOL", true);

//url to terms of use
defined("PLATFORM_USERMANAGEMENT_URL") or define("PLATFORM_USERMANAGEMENT_URL", "http://www.bid-owl.de/benutzerverwaltung/");

//logo link
defined("LOGO_PATH_URL") or define("LOGO_PATH_URL", "http://www.hni.uni-paderborn.de/koi/");

//option for objects only root can create
defined("CREATE_RESTRICTED_TO_ROOT") or define("CREATE_RESTRICTED_TO_ROOT", "Webarena");

//show tags an tag search field at explorer
defined("EXPLORER_TAGS_VISIBLE") or define("EXPLORER_TAGS_VISIBLE", false);
?>