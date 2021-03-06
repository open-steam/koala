<?php

// WE DON'T USE ERROR CODES LOWER 1000 TO AVOID INTERFERENCES
// WITH MYSQL EXCEPTIONS
define( "E_BACKEND_ERROR",   120);

// CRITICAL: NETWORK ERRORS
define( "E_CONNECTION", 	2000 );
define( "E_CONFIGURATION",	2100 );

// CRITICAL: PROGRAM ERRORS
define( "E_PARAMETER",		3000 );
define( "E_INITIALIZED",	3005 );
define( "E_INVOCATION",		3010 );
define( "E_SESSION",		3050 );
define( "E_USER_ACCESS_DENIED", 300 );
define( "E_USER_REGISTRATION",	3100 );
define( "E_USER_AUTHORIZATION",	3200 ); // USER NOT AUTHORIZED
define( "E_USER_RIGHTS",	3300 );
define( "E_USER_DISCLAIMER", 3400 );
define( "E_USER_CHANGE_PASSWORD", 3500 );
define( "E_USER_LOGIN",		4100 ); // LOGIN CREDENTIALS WRONG
define( "E_RUNTIME_ERROR", 4200);
define( "E_FATAL_ERROR", 4300);
define( "E_AJAX_ERROR", 4400);
define( "E_JS_ERROR", 4500);

// NOTIFICATION: PROGRAM NOTIFICATIONS
define( "E_USER_NO_NETWORKINGPROFILE", 5000);  // Networking Profile not initialized
define( "E_USER_NO_PRIVACYPROFILE", 5005);     // Could not create Privacy Profile if not initialized
define("E_OBJECT_NO_INVENTORY", 5010);         // Could not load the inventory of an object without an inventory

///// PAUL ERROR CODES \\\\\
define( "E_SOAP_SERVICE_ERROR", 6000);
define( "E_SOAP_INVALID_PASSWORD", 6005);
define( "E_SOAP_INVALID_INPUT", 6010);
define( "E_SOAP_INVALID_RESPONSE_DATA", 6015);

///// LDAP Error Codes
define( "E_LDAP_SERVICE_ERROR", 8000);
define( "E_LDAP_INVALID_MATRICULATION_NUMBER", 80100);
