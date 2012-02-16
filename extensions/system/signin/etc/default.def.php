<?php
defined("URL_SIGNIN") or define("URL_SIGNIN", PATH_URL . "signin/");
defined("URL_SIGNIN_REQUEST") or define("URL_SIGNIN_REQUEST", PATH_URL . "signin/request/");
defined("URL_SIGNOUT") or define("URL_SIGNOUT", PATH_URL . "signout/");

// ***** LDAP server *****************************************************

// LDAP DIENST UPB
defined("CHECK_LDAP_ACCESS") or define( "CHECK_LDAP_ACCESS",    FALSE );
defined("LDAP_SERVICE_LOCATION") or define( "LDAP_SERVICE_LOCATION", "" );
defined("LDAP_SERVICE_URL") or define( "LDAP_SERVICE_URL",      "" );
defined("LDAP_SERVICE_CERT") or define( "LDAP_SERVICE_CERT",     "" );

defined("LDAP_OU") or define( "LDAP_OU",      "ou=People" );
defined("LDAP_O") or define( "LDAP_O",       "o=upb" );
defined("LDAP_C") or define( "LDAP_C",       "c=de" );

defined("LDAP_ATTRIBUTE_FIRSTNAME") or define( "LDAP_ATTRIBUTE_FIRSTNAME",     "givenName" );
defined("LDAP_ATTRIBUTE_LASTNAME") or define( "LDAP_ATTRIBUTE_LASTNAME",      "sn" );
defined("LDAP_ATTRIBUTE_NAME") or define( "LDAP_ATTRIBUTE_NAME",          "cn" );
defined("LDAP_ATTRIBUTE_EMAI") or define( "LDAP_ATTRIBUTE_EMAIL",         "mail" );
defined("LDAP_ATTRIBUTE_STUDID") or define( "LDAP_ATTRIBUTE_STUDID",        "upbStudentID" );

defined("LDAP_OBJECTCLASS_STUDENT") or define( "LDAP_OBJECTCLASS_STUDENT",     "upbStudent" );

defined("LDAP_SERVER") or define( "LDAP_SERVER",          "" );
defined("LDAP_PORT") or define( "LDAP_PORT",            "" );
defined("LDAP_LOGIN") or define( "LDAP_LOGIN",           "" );
defined("LDAP_PASSWORD") or define( "LDAP_PASSWORD",        "" );
?>