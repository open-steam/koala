<?php
	defined('_VALID_KOALA') or die('Direct Access to this location is not allowed.');

	// short name and language variation
	define("PLATFORM_ID", "bid");
	// plattform name
	define("PLATFORM_NAME", "Bildung im Dialog");
	//displayed in browser title
	define("PLATFORM_TITLE", "bid");
	//copyright information
	define("COPYRIGHT_NAME", "University of Paderborn");
	
	// The message to display in the error dialog during the Maintainance.
	// If MAINTAINANCE_MESSAGE is empty, a generic error message will be displayed
	define( "MAINTAINANCE_MESSAGE", "Das System wird zur Zeit gewartet, dies wird voraussichtlich 20 Minuten in Anspruch nehmen.<br /> Bitte haben Sie etwas Geduld und versuchen es später noch einmal.");
	
	// Define the start time for the backup process in 24 hour format
	// for a more precise error message during the backup process
	define("BACKUP_TIME_START", 4);
	// Define the ending time for the backup process in 24 hour format
	//  for a more precise error message during the backup process
	define("BACKUP_TIME_END", 5);
	
	define("STARTPAGE_IMAGE_TEXT_LONG", "\"zusammenwirken\", \"kommunizieren\", \"communicate\", \"communiquer\"");
	define("STARTPAGE_IMAGE_TEXT_MEDIUM", "\"verbinden\",\"cooperate\",\"comunicar\",\"compartir\",\"colaborar\",\"apprendre\",\"partager\",\"aprender\",\"coopérer\",\"joindre\"");
	define("STARTPAGE_IMAGE_TEXT_SHORT", "\"teilen\",\"lernen\",\"juntar\",\"share\",\"learn\",\"join\"");

	// you need to update .htaccess too
	define("SEMESTER_URL", "semester");
	define("AUTOLOAD_FIRST_UNIT", FALSE);
	
	/*Functions that can be used in koaLA/kopp. FALSE == off | TRUE == on 
	 *MENU/TABS
	 *	SUBMENU
	 *		BUTTON
	*/
	//YOU
	define("YOU_MENU", TRUE); //the hole you menu.
		define("YOUR_DESKTOP", TRUE);
		define("YOUR_CERTS", FALSE);
		define("YOUR_PORTFOLIO", FALSE);
		define("YOUR_NEWS", TRUE);
			define("YOUR_SUBSCRIPTION", TRUE);
		define("YOUR_MAILBOX", TRUE);
			define("WRITE_MESSAGES", TRUE);
			define("MAILBOX_KONFIGURATION", TRUE);
			define("DELETE_MAILS", TRUE);
		define("MAILBOX_SHOW_UNREAD_ON_STARTPAGE",TRUE);
		define("YOUR_CALENDER", TRUE);
			define("ADD_MEETINGS", TRUE);
		define("YOUR_DOCUMENTS", TRUE);
			define("CREATE_FOLDER", TRUE);
			define("UPLOAD_DOCUMENT", TRUE);
			define("ADD_WEBLINK", TRUE);
			define("CLIPBOARD", TRUE);
		define("YOUR_PROFILE", TRUE);
			define("CHANGE_PROFILE_DATA", TRUE);
			define("CHANGE_PROFILE_PICTURE", TRUE);
			define("CHANGE_PROFILE_PRIVACY", TRUE);
	//COURSES
	define("COURSES_MENU", TRUE);
		define("YOUR_COURSES", TRUE);
			define("ADD_COURSE", TRUE);
			define("IMPORT_COURSE_FROM_PAUL", TRUE);
			define("MANAGE_SEMESTER", TRUE);
			define("ADD_SEMESTER", TRUE);
			define("COURSE_PARTICIPANTS", TRUE);
			define("COURSE_UNITS", TRUE);
			define("COURSE_COMMUNICATION", TRUE);
			define("COURSE_EXAM", FALSE);
			define("COURSE_LEAVE", TRUE);
			define("COURSE_SHOW_MAXSIZE", TRUE);
			define("COURSE_SHOW_ONLY_EXTERN_MAIL", FALSE);
		define("ALL_COURSES", TRUE);
		define("ADMIN_ONLY_ALL_COURSES", FALSE);
		define("COURSE_STAFF_ADD_MEMBER", TRUE);
		define("COURSE_STAFF_EXCEL_LIST", TRUE);
		define("COURSE_STAFF_CIRCULAR", TRUE);
		define("COURSE_STAFFLIST_MANAGE", TRUE);
		define("COURSE_STAFFLIST_HIDE", TRUE);
		define("COURSE_STAFF", FALSE);
		define("COURSE_PARTICIPANTS_ADD_MEMBER", TRUE);
		define("COURSE_PARTICIPANTS_EXCEL_LIST", TRUE);
		define("COURSE_PARTICIPANTS_CIRCULAR", TRUE);
		define("COURSE_PARTICIPANTSLIST_MANAGE", TRUE);
		define("COURSE_PREFERENCES", TRUE);
		define("COURSE_START_ADMIN_PROFILE_ANKER", TRUE);
		define("COURSE_START_SEND_MESSAGE", FALSE);
		define("COURSE_SEM_APP_ENABLED", TRUE);
		define("COURSE_KOALAADMIN_ONLY", FALSE);
		define("COURSE_PARTICIPANTS_STAFF_ONLY", FALSE);
		define("COURSE_STAFF_FACULTY_AND_FOCUS", TRUE);
		define("COURSE_STAFF_EXTENSIONS", FALSE);
		define("COURSE_PARTICIPANTS_FACULTY_AND_FOCUS", TRUE);
		define("COURSE_PARTICIPANTS_COMMUNICATION", TRUE);
		define("COURSE_PARTICIPANTS_ATTENDANCE_LIST", TRUE);
		define("COURSE_PARTICIPANTS_MANAGE_MEMBERSHIP_REQUESTS", TRUE);
		define("COURSE_PARTICIPANTS_EXTENSIONS", FALSE);
		define("SHOW_SEMESTER_IN_HEADLINE", TRUE);
		define("USER_LIST_NO_PAGEING", FALSE);
			//the buttons here are redundant with the buttons above
	//CONTACTS
	define("CONTACTS_MENU", TRUE);
		define("YOUR_CONTACTS", TRUE);
		define("PROFILE_VISITORS", FALSE);
		define("USER_SEARCH", FALSE);
	//GROUPS
	define("GROUPS_MENU", TRUE);
		define("YOUR_GROUPS", TRUE);
			define("SHOW_ALL_PUBLIC_GROUPS", TRUE);
			define("CREATE_GROUPS", TRUE);
		define("BROWSE_GROUPS", TRUE);
			define("MANAGE_GROUPS_MEMBERSHIP", TRUE);	
		define("CREATE_GROUP", TRUE);
			define("CREATE_PUBLIC_GROUP", TRUE);
			define("CREATE_PRIVATE_GROUP", TRUE);
			
	//PROFILE
	define("PROFILE_EDIT", TRUE);
	define("PROFILE_PRIVACY", TRUE);
	define("PROFILE_PICTURE", TRUE);
	define("PROFILE_STATUS", TRUE);
	define("PROFILE_EMAIL", FALSE);
	define("PROFILE_GENDER", TRUE);
	define("PROFILE_GENERAL", TRUE);
	define("PROFILE_LANGUAGE", TRUE);
	define("PROFIL_CONTACT", TRUE);	
	define("PROFILE_MANAGE_CONTACT", TRUE);
	define("PROFILE_INTRODUCE_PERSON", TRUE);
	define("PROFILE_SEND_MAIL", TRUE);
		
	define("SERVERMENU", FALSE);
	define("SERVERMONITOR", FALSE);
	define("KOALAADMINTOOLS", TRUE);
	define("USERMANAGEMENT", FALSE);
		define("USERMANAGEMENT_CUSTOMERS", FALSE);
		define("USERMANAGEMENT_CONFIGURATION", FALSE);
		define("USERMANAGEMENT_SYSTEMADMIN", FALSE);
	define("DISCLAIMER", FALSE);
	define("CHANGE_PASSWORD", FALSE);		
	
	define("SUPPORT_EMAIL", "hase@uni-paderborn.de");
	
	define("SYSTEMCONFIG_FUNCTION_MAIL", FALSE); // FALSE disables mail function for the whole system 
	
?>