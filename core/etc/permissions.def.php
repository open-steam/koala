<?php
define( "PERMISSION_UNDEFINED", 0 );
define( "PERMISSION_PUBLIC", 1 );
define( "PERMISSION_PUBLIC_READONLY", 2 );
define( "PERMISSION_PRIVATE", 3 );
define( "PERMISSION_PRIVATE_READONLY", 4 );
define( "PERMISSION_PUBLIC_READONLY_STAFF", 5 );
define( "PERMISSION_PRIVATE_STAFF", 6 );

define("PERMISSION_COURSE_UNDEFINED", 0);
define("PERMISSION_COURSE_PUBLIC", 1);
define("PERMISSION_COURSE_PASSWORD", 2);
define("PERMISSION_COURSE_CONFIRMATION", 3);
define("PERMISSION_COURSE_HISLSF", 4);
define("PERMISSION_COURSE_PAUL_SYNC", 5);

define("PERMISSION_GROUP_UNDEFINED", 0);
define("PERMISSION_GROUP_PRIVATE", 1);
define("PERMISSION_GROUP_PUBLIC_FREEENTRY", 2);
define("PERMISSION_GROUP_PUBLIC_PASSWORD", 3);
define("PERMISSION_GROUP_PUBLIC_CONFIRMATION", 4);

// These values are representations of attribute keys to store the
// meta information of access settings. Changes to these will
// result in severe disfunction of the whole access management in koaLA
define("KOALA_ACCESS", "KOALA_ACCESS");
define("KOALA_GROUP_ACCESS", "KOALA_GROUP_ACCESS");

// Bitmask to enable/disable member and document tabs on public groups for non-members
define("PERMISSION_GROUP_PRIVACY_DENY_PARTICIPANTS", 0x00000001);
define("PERMISSION_GROUP_PRIVACY_DENY_DOCUMENTS", 0x00000002);

//define actions for units
define("PERMISSION_ACTION_NONE",     0x00000000);
define("PERMISSION_ACTION_CUT",      0x00000001);
define("PERMISSION_ACTION_COPY",     0x00000002);
define("PERMISSION_ACTION_EDIT",     0x00000004);
define("PERMISSION_ACTION_DELETE",   0x00000008);
// Note: the next possible value for an action permission is:  0x00000010

//Profile Privacy Permissions
define("PROFILE_DENY_ALLUSERS",      0x00000001);
define("PROFILE_DENY_CONTACTS",     0x00000002);
define("PROFILE_DENY_COURSEMATES",  0x00000004);
define("PROFILE_DENY_GROUPMATES",   0x00000008);
?>