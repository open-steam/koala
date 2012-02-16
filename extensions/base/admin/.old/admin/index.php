<?php
include_once( "../../etc/koala.conf.php" );
include_once( "version.php" );

$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );
$user = lms_steam::get_current_user();
$min_mb = 8;
if( !lms_steam::is_koala_admin($user) )
{
	header("location:/");
	exit;
}

$version = KOALA_VERSION;
$admin_version = KOALA_ADMIN_VERION;
$error_reporting = error_reporting();
$html = <<< END
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>koaLA Admin</title>
<style type="text/css">
* {margin:0; padding:0; outline:0}
body {font:11px Verdana,Arial; margin:25px; background:#fff url(images/bg.gif) repeat-x}
#tablewrapper {width:550px; margin:0 auto}
#tableheader {height:55px}
.search {float:left; padding:6px; border:1px solid #c6d5e1; background:#fff}
#tableheader select {float:left; font-size:12px; width:125px; padding:2px 4px 4px}
#tableheader input {float:left; font-size:12px; width:225px; padding:2px 4px 4px; margin-left:4px}
.details {float:right; padding-top:12px}
.details div {float:left; margin-left:15px; font-size:12px}
.tinytable {width:549px; border-left:1px solid #c6d5e1; border-top:1px solid #c6d5e1; border-bottom:none}
.tinytable th {background:url(images/header-bg.gif); text-align:left; color:#cfdce7; border:1px solid #fff; border-right:none}
.tinytable th h3 {font-size:10px; padding:6px 8px 8px}
.tinytable td {padding:4px 6px 6px; border-bottom:1px solid #c6d5e1; border-right:1px solid #c6d5e1}
.tinytable .head h3 {background:url(images/sort.gif) 7px center no-repeat; cursor:pointer; padding-left:18px}
.tinytable .desc, .sortable .asc {background:url(images/header-selected-bg.gif)}
.tinytable .desc h3 {background:url(images/desc.gif) 7px center no-repeat; cursor:pointer; padding-left:18px}
.tinytable .asc h3 {background:url(images/asc.gif) 7px  center no-repeat; cursor:pointer; padding-left:18px}
.tinytable .head:hover, .tinytable .desc:hover, .tinytable .asc:hover {color:#fff}
.tinytable .evenrow td {background:#fff}
.tinytable .oddrow td {background:#ecf2f6}
.tinytable td.evenselected {background:#ecf2f6}
.tinytable td.oddselected {background:#dce6ee}
.tinytable tfoot {background:#fff; font-weight:bold}
.tinytable tfoot td {padding:6px 8px 8px}
#tablefooter {height:15px; margin-top:20px}
#tablenav {float:left}
#tablenav img {cursor:pointer}
#tablenav div {float:left; margin-right:15px}
#tablelocation {float:right; font-size:12px}
#tablelocation select {margin-right:3px}
#tablelocation div {float:left; margin-left:15px}
.page {margin-top:2px; font-style:italic}
#selectedrow td {background:#c6d5e1}
</style>
</head>
<body>
	
	<div id="tablewrapper">
	<h1>koaLA-Admin <small>(v$admin_version)</small></h1>
        <table cellpadding="0" cellspacing="0" border="0" id="table" class="tinytable">
            <thead>
                <tr>
                    <th><h3>Server Informationen</h3></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <tr><td>Koala-Version</td><td>$version</td></tr>
            </tbody>
         </table><br>
	<h2>Werkzeuge</h2>
        <table cellpadding="0" cellspacing="0" border="0" id="table" class="tinytable">
            <thead>
                <tr>
                    <th><h3>Data Access</h3></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <tr><td><b>Suche nach großen Dateien</b><br>Skript zum Finden großer Dateien. Notwendige Konstanten: STEAM_DATABASE_HOST, STEAM_DATABASE_USER, STEAM_DATABASE_PASS. Das Suchlimit beträgt 8MB.<br><small><div style="color:red;display:inline">Achtung:</div> diese Skript erzeugt eine sehr hohe Last auf der Datenbank und kann je nach Größe der Datenbank lange dauern</small></td><td><a href="./big_files.php">Los!</a></td></tr>
            <tr><td><b>Suche Kurse mit Extension</b></td><td><a href="./find_course_with_extension.php">Los!</a></td></tr>
            <tr><td><b>Suche suspended Benutzer</b></td><td><a href="./suspend_users.php">Los!</a></td></tr>
            </tbody>
         </table>
END;

$cache_check = new cache_check();
$html .= $cache_check->get_html();

$html .= <<< END
         <table cellpadding="0" cellspacing="0" border="0" id="table" class="tinytable">
            <thead>
                <tr>
                    <th><h3>Error Handling</h3></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <tr><td>error_reporting</td><td style="background-color:yellow">$error_reporting</td></tr>
            </tbody>
         </table>
       <div>
 </body>
END;

echo $html;

?>