<?php
/**
 * Definition of all types of objects's attributes
 *
 * PHP versions 5
 * 
 * @package PHPsTeam
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author Henrik Beige <hebeige@gmx.de>, Alexander Roth <aroth@it-roth.de>, Dominik Niehus <nicke@upb.de>
 */

// BEGIN deprecated
  /**
   * 
   */
 /* define("EXIT_TO",                 401);
  define("LINK_TARGET",             900);*/
// END deprecated


  define("OBJ_OWNER",                 "OBJ_OWNER");
  define("OBJ_NAME",                  "OBJ_NAME");
  define("OBJ_DESC",                  "OBJ_DESC");
  define("OBJ_ICON",                  "OBJ_ICON");
  define("OBJ_KEYWORDS",              "OBJ_KEYWORDS");
  define("OBJ_COMMAND_MAP",           "OBJ_COMMAND_MAP"); // objects that can be executed with this
  define("OBJ_POSITION_X",            "OBJ_POSITION_X");
  define("OBJ_POSITION_Y",            "OBJ_POSITION_Y");
  define("OBJ_POSITION_Z",            "OBJ_POSITION_Z");
  define("OBJ_LAST_CHANGED",          "OBJ_LAST_CHANGED");
  define("OBJ_CREATION_TIME",         "OBJ_CREATION_TIME");
  define("OBJ_URL",                   "url");
  define("OBJ_LINK_ICON",             "obj:link_icon");
  define("OBJ_SCRIPT",                "obj_script");
  define("OBJ_ANNOTATIONS_CHANGED",   "obj_annotations_changed");
  define("OBJ_TYPE",                 "OBJ_TYPE");
  define("OBJ_PATH",                 "OBJ_PATH");
  define("OBJ_VERSIONOF",			 "OBJ_VERSIONOF");

  define("DOC_TYPE",                  "DOC_TYPE");
  define("DOC_SIZE",		      "DOC_SIZE");
  define("DOC_MIME_TYPE",             "DOC_MIME_TYPE");
  define("DOC_USER_MODIFIED",         "DOC_USER_MODIFIED");
  define("DOC_LAST_MODIFIED",         "DOC_LAST_MODIFIED");
  define("DOC_LAST_ACCESSED",         "DOC_LAST_ACCESSED");
  define("DOC_EXTERN_URL",            "DOC_EXTERN_URL");
  define("DOC_TIMES_READ",            "DOC_TIMES_READ");
  define("DOC_IMAGE_ROTATION",        "DOC_IMAGE_ROTATION");
  define("DOC_IMAGE_THUMBNAIL",       "DOC_IMAGE_THUMBNAIL");
  define("DOC_IMAGE_SIZEX",           "DOC_IMAGE_SIZEX");
  define("DOC_IMAGE_SIZEY",           "DOC_IMAGE_SIZEY");
  define("DOC_ENCODING",              "DOC_ENCODING");
  define("DOC_VERSION",				  "DOC_VERSION");
  define("DOC_VERSIONS",			  "DOC_VERSIONS");


  define("CONT_SIZE_X",               "CONT_SIZE_X");
  define("CONT_SIZE_Y",               "CONT_SIZE_Y");
  define("CONT_SIZE_Z",               "CONT_SIZE_Z");
  define("CONT_EXCHANGE_LINKS",       "CONT_EXCHANGE_LINKS");
  define("cont:monitor",              "CONT_MONITOR");
  define("cont_last_modified",        "CONT_LAST_MODIFIED");


  define("GROUP_MEMBERSHIP_REQS",     "GROUP_MEMBERSHIP_REQS");
  define("GROUP_EXITS",               "GROUP_EXITS");
  define("GROUP_MAXSIZE",             "GROUP_MAXSIZE");
  define("GROUP_MSG_ACCEPT",          "GROUP_MSG_ACCEPT");
  define("GROUP_MAXPENDING",          "GROUP_MAXPENDING");


  define("USER_ADRESS",               "USER_ADRESS");
  define("USER_FULLNAME",             "USER_FULLNAME");
  define("USER_MAILBOX",              "USER_MAILBOX");
  define("USER_WORKROOM",             "USER_WORKROOM");
  define("USER_CALENDAR",             "USER_CALENDAR");
  define("USER_LAST_LOGIN",           "USER_LAST_LOGIN");
  define("USER_EMAIL",                "USER_EMAIL");
  define("USER_UMASK",                "USER_UMASK");
  define("USER_MODE",                 "USER_MODE");
  define("USER_MODE_MSG",             "USER_MODE_MSG");
  define("USER_LOGOUT_PLACE",         "USER_LOGOUT_PLACE");
  define("USER_TRASHBIN",             "USER_TRASHBIN");
  define("USER_BOOKMARKROOM",         "USER_BOOKMARKROOM");
  define("USER_FORWARD_MSG",          "USER_FORWARD_MSG");
  define("USER_IRC_PASSWORD",         "USER_IRC_PASSWORD");
  define("USER_FIRSTNAME",            "USER_FIRSTNAME");
  define("USER_LANGUAGE",             "USER_LANGUAGE");
  define("USER_SELECTION",            "USER_SELECTION");
  define("USER_FAVOURITES",           "USER_FAVOURITES");


  define("DRAWING_TYPE",              "DRAWING_TYPE");
  define("DRAWING_WIDTH",             "DRAWING_WIDTH");
  define("DRAWING_HEIGHT",            "DRAWING_HEIGHT");
  define("DRAWING_COLOR",             "DRAWING_COLOR");
  define("DRAWING_THICKNESS",         "DRAWING_THICKNESS");
  define("DRAWING_FILLED",            "DRAWING_FILLED");


  define("GROUP_WORKROOM",            "GROUP_WORKROOM");
  define("GROUP_EXCLUSIVE_SUBGROUPS", "GROUP_EXCLUSIVE_SUBGROUPS");


  define("LAB_TUTOR",                 "LAB_TUTOR");
  define("LAB_SIZE",                  "LAB_SIZE");
  define("LAB_ROOM",                  "LAB_ROOM");
  define("LAB_APPTIME",               "LAB_APPTIME");
  define("MAIL_MIMEHEADERS",          "MAIL_MIMEHEADERS");
  define("MAIL_IMAPFLAGS",            "MAIL_IMAPFLAGS");



  define("CONTROL_ATTR_USER",         1);
  define("CONTROL_ATTR_CLIENT",       2);
  define("CONTROL_ATTR_SERVER",       3);

  define("DRAWING_LINE",              1);
  define("DRAWING_RECTANGLE",         2);
  define("DRAWING_TRIANGLE",          3);
  define("DRAWING_POLYGON",           4);
  define("DRAWING_CONNECTOR",         5);
  define("DRAWING_CIRCLE",            6);
  define("DRAWING_TEXT",              7);

  define("REGISTERED_TYPE",           0);
  define("REGISTERED_DESC",           1);
  define("REGISTERED_EVENT_READ",     2);
  define("REGISTERED_EVENT_WRITE",    3);
  define("REGISTERED_ACQUIRE",        4);
  define("REGISTERED_CONTROL",        5);
  define("REGISTERED_DEFAULT",        6);

  define("REG_ACQ_ENVIRONMENT",       1);
  define("CLASS_ANY",                 0); // for packages and registering attributes

  //login feature
  define("CLIENT_STATUS_CONNECTED",   1);
?>