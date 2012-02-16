<?php
/**
 * Type definitions for PHPsTeam
 * 
 * all types for COAL protocol
 * all types of CLASS definition
 * all types of Serialization
 *
 * PHP versions 5
 *
 * @package     PHPsTeam
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License   
 * @author Henrik Beige <hebeige@gmx.de>
 */

/**
 * 
 */
  //define COAL command
  define("COAL_QUERY_COMMANDS", "\x00");
  define("COAL_COMMAND",        "\x01");
  define("COAL_EVENT",          "\x02");
  define("COAL_LOGIN",          "\x03");
  define("COAL_LOGOUT",         "\x04");
  define("COAL_FILE_DOWNLOAD",  "\x05");
  define("COAL_FILE_UPLOAD",    "\x06");
  define("COAL_QUERY_PROGRAMS", "\x07");
  define("COAL_ERROR",          "\x08");
  define("COAL_SET_CLIENT",     "\x09");
  define("COAL_RELOGIN",        "\x15");
  define("COAL_UPLOAD_START",   "\x0a");
  define("COAL_UPLOAD_PACKAGE", "\x0b");
  define("COAL_CRYPT",          "\x0c");
  


  //define CLASS ids
  define("CLASS_OBJECT",       0x00000001);
  define("CLASS_CONTAINER",    0x00000002);
  define("CLASS_ROOM",         0x00000004);
  define("CLASS_USER",         0x00000008);
  define("CLASS_DOCUMENT",     0x00000010);
  define("CLASS_LINK",         0x00000020);
  define("CLASS_GROUP",        0x00000040);
  define("CLASS_EXIT",         0x00000080);
  define("CLASS_DOCEXTERN",    0x00000100);
  define("CLASS_DOCLPC",       0x00000200);
  define("CLASS_SCRIPT",       0x00000400);
  define("CLASS_DOCHTML",      0x00000800);
  define("CLASS_DATE",         0x00001000);
  define("CLASS_FACTORY",      0x00002000);
  define("CLASS_MODULE",       0x00004000);
  define("CLASS_DATABASE",     0x00008000);
  define("CLASS_PACKAGE",      0x00010000);
  define("CLASS_IMAGE",        0x00020000);
  define("CLASS_MESSAGEBOARD", 0x00040000);
  define("CLASS_GHOST",        0x00080000);
  define("CLASS_SERVERGATE",   0x00100000);
  define("CLASS_TRASHBIN",     0x00200000);
  define("CLASS_DOCXML",       0x00400000);
  define("CLASS_DOCXSL",       0x00800000);
  define("CLASS_LAB",          0x01000000);
  define("CLASS_DOCWIKI",      0x02000000);
  define("CLASS_BUG",          0x04000000);
  define("CLASS_CALENDAR",     0x08000000);
  define("CLASS_SCORM",        0x10000000);
  define("CLASS_DRAWING",      0x20000000);


  //define serialization types
  define("CMD_TYPE_UNKNOWN",   "\x00");
  define("CMD_TYPE_INT",       "\x01");
  define("CMD_TYPE_FLOAT",     "\x02");
  define("CMD_TYPE_STRING",    "\x03");
  define("CMD_TYPE_OBJECT",    "\x04");
  define("CMD_TYPE_ARRAY",     "\x05");
  define("CMD_TYPE_MAPPING",   "\x06");
  define("CMD_TYPE_MAP_ENTRY", "\x07");
  define("CMD_TYPE_PROGRAM",   "\x08");
  define("CMD_TYPE_TIME",      "\x09");
  define("CMD_TYPE_FUNCTION",  "\x0a");


  //define access types
  define("FAILURE",             0xffffffff);
  define("ACCESS_DENIED",       0x00000000);
  define("ACCESS_GRANTED",      0x00000001);
  define("ACCESS_BLOCKED",      0x00000002);
  define("SANCTION_READ",       0x00000001);
  define("SANCTION_EXECUTE",    0x00000002);
  define("SANCTION_MOVE",       0x00000004);
  define("SANCTION_WRITE",      0x00000008);
  define("SANCTION_INSERT",     0x00000010);
  define("SANCTION_ANNOTATE",   0x00000020);
  define("SANCTION_SANCTION",   0x00000100);
  define("SANCTION_LOCAL",      0x00000200);
  define("SANCTION_ALL",        0x0000ffff);
  define("SANCTION_SHIFT_DENY", 0x00000010);
  define("SANCTION_COMPLETE",   0xffffffff);
  define("SANCTION_POSITIVE",   0xffff0000);
  define("SANCTION_NEGATIVE",   0x0000ffff);
  define("SANCTION_USER",       0x00010000);
?>