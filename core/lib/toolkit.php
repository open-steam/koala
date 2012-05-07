<?php

function check_setup() {
    echo "<center><small><pre>";
    echo check_msg("PHP Version >= 5.3", true, "5.3.6");
    echo check_msg("Write access to folder PATH_CACHE (" . PATH_CACHE . ")", true, "777");
    echo check_msg("Write access to folder PATH_TEMP (" . PATH_TEMP . ")", true, "777");
    echo check_msg("Write access to file LOG_ERROR (" . LOG_ERROR . ")", true, "766");
    echo check_msg("Write access to file LOG_MESSAGES (" . LOG_MESSAGES . ")", true, "766");
    echo check_msg("Write access to file LOG_SECURITY (" . LOG_SECURITY . ")", true, "766");
    echo check_msg("PHP Module: mysql");
    echo check_msg("PHP Module: mcrypt");
    echo check_msg("PHP Module: ldap");
    echo check_msg("PHP Module: gd");
    echo check_msg("PHP Module: iimagick");
    echo check_msg("PHP Module: gettext");
    echo check_msg("PHP Module: curl");
    echo check_msg("PHP Module: zip");
    echo check_msg("PHP Option: TIMEZONE");
    echo check_msg("PHP Option: MAGIC_QUOTES_GPC");
    echo check_msg("PHP Option: MAGIC_QUOTES_RUNTIME");
    echo check_msg("PHP Option: MAGIC_QUOTES_SYBASE");
    echo check_msg("PHP Option: MEMORY_LIMIT");
    echo check_msg("PHP Option: SAFE_MODE");
    echo check_msg("sTeam: check connection");
    echo check_msg("sTeam: check root access");
    echo check_msg("sTeam: check guest access");
    echo check_msg("sTeam: version");
    echo check_msg("sTeam: koala_support");
    echo check_msg("sTeam: client_support");
    echo check_msg("mysql: check connection");
    echo check_msg("mysql: check access");
    echo "</pre></small></center>";
}

function check_msg($msg, $status = false, $value = "", $hint = null) {
    $length = 150;
    $html = "[ " . $msg . " ";
    for ($i = 0; $i < ($length - strlen($msg)); $i++) {
        $html .= ".";
    }
    $length = 20;
    $html .= " | " . $value;
    for ($i = 0; $i < ($length - strlen($value)); $i++) {
        $html .= ".";
    }
    if ($status) {
        $html .= " |  <span style=\"color:green\">ok</span>  ";
    } else {
        $html .= " | <span style=\"color:red\">fail</span> ";
    }
    $html .= " ]\n";
    return $html;
}

function strStartsWith($haystack, $needle, $case = true) {
    if ($case) {
        return (strcmp(substr($haystack, 0, strlen($needle)), $needle) === 0);
    }
    return (strcasecmp(substr($haystack, 0, strlen($needle)), $needle) === 0);
}

function strEndsWith($haystack, $needle, $case = true) {
    if ($case) {
        return (strcmp(substr($haystack, strlen($haystack) - strlen($needle)), $needle) === 0);
    }
    return (strcasecmp(substr($haystack, strlen($haystack) - strlen($needle)), $needle) === 0);
}

function array_trim($array) {
    array_walk($array, create_function('&$temp', '$temp = trim($temp);'));
    return $array;
}

function browserNoCache() {
    if (isset($_SERVER['HTTP_CACHE_CONTROL']) && $_SERVER['HTTP_CACHE_CONTROL'] == 'no-cache') {
        return true;
    } else {
        return false;
    }
}

function dropCache() {
    if (DEVELOPMENT_MODE && browserNoCache()) {
        return true;
    } else {
        return false;
    }
}

function emptyCacheFolder() {
    $strDir = PATH_CACHE;
    $oDir = dir($strDir);
    while (false !== ($strFile = $oDir->read())) {
        if ($strFile != '.' && $strFile != '..' && $strFile != '.cvsignore' && !is_link($strDir . $strFile) && is_file($strDir . $strFile)) {
            if (@unlink($strDir . $strFile)) {
                //print 'Gelöscht: ' . $strDir . $strFile . '<br />' . "\n";
            } else {
                //print 'Fehlschlag: ' . $strDir . $strFile . '<br />' . "\n";
            }
        }
    }
    $oDir->close();
}

function getReadableDate($timestamp) {
    $is_today = false;
    $is_yesterday = false;

    $now = time();

    $diff = $now - $timestamp;
    if ($diff < 60) {
        return "gerade";
    }

    if ($diff < 300) {
        return "weniger als 5 Minuten";
    }

    if ($diff < 1800) {
        return "weniger als 30 Minuten";
    }

    if ($diff < 3600) {
        return "in der letzten Stunde";
    }

    $today_day = date("d", $now);
    $date_day = date("d", $timestamp);
    $today_month = date("m", $now);
    $date_month = date("m", $timestamp);
    $today_year = date("Y", $now);
    $date_year = date("Y", $timestamp);
    if ($today_day == $date_day && $today_month == $date_month && $today_year == $date_year) {
        $is_today = true;
    } else if ((int) $today_day == (int) $date_day + 1 && $today_month == $date_month && $today_year == $date_year) {
        $is_yesterday = true;
    }

    if ($is_today) {
        return "heute um " . date("H:i", $timestamp);
    } else if ($is_yesterday) {
        return "gestern um " . date("H:i", $timestamp);
    } else {
        return date("d.m.Y, H:i ", $timestamp);
    }
}

function getReadableSize($size) {
    if ($size < 1024) {
        return $size . " Byte";
    } else if ($size < (1024 * 1024)) {
        return round($size / 1024) . " kByte";
    } else if ($size < (1024 * 1024 * 1024)) {
        return round($size / (1024 * 1024)) . " MByte";
    } else {
        return round($size / (1024 * 1024 * 1024)) . " GByte";
    }
}

function getFormatedDate($timestamp) {
    return date("d.m.Y, H:i ", $timestamp);
}

function getCleanName($object, $length = 30) {
    if (!($object instanceof steam_object)) {
        return "";
    }
    if ($object instanceof steam_user) {
        $title = $object->get_attribute(USER_FIRSTNAME) . " " . $object->get_attribute(USER_FULLNAME);
    } else {
        $user = isUserHome($object);
        if ($user) {
            $title = getCleanName($user);
        } else if ($object instanceof steam_trashbin) {
            $title = "Papierkorb";
        } else {
            $desc = $object->get_attribute(OBJ_DESC);
            if ($desc !== 0 && trim($desc) !== "") {
                $title = $desc;
            } else {
                $title = $object->get_name();
            }
            $title = str_replace("'s workarea", "", stripslashes($title));
            $title = str_replace(" workarea", "", stripslashes($title));
            $title = str_replace("s workroom.", "", $title);
            $title = str_replace("s workroom", "", $title);
            $title = preg_replace("/.*'s bookmarks/", "Lesezeichen", $title);
        }
    }
    if ($length != -1 && $length < strlen($title)) {
        $title = substr($title, 0, $length - 1) . "...";
    }
    return $title;
}

function isUserHome($container) {
    if ($container instanceof steam_room) {
        $pathArray = explode("/", $container->get_path());
        if (count($pathArray) == 3) {
            $user = $container->get_creator();
            if ($user instanceof steam_user) {
                if ($user->get_attribute(USER_WORKROOM)->get_id() === $container->get_id()) {
                    return $user;
                }
            }
        }
    }
    return null;
}

function isGroupWorkroom($container) {
    if ($container instanceof steam_room) {
        $pathArray = explode("/", $container->get_path());
        if (count($pathArray) == 3) {
            $group = $container->get_creator();
            if ($group instanceof steam_group) {
                if ($group->get_attribute(GROUP_WORKROOM)->get_id() === $container->get_id()) {
                    return $group;
                }
            }
        }
    }
    return null;
}

function getObjectType($object) {
    if ($object instanceof \steam_document) {
        $type = "document";
    } else if ($object instanceof \steam_messageboard) {
        $type = "forum";
    } else if ($object instanceof \steam_exit) {
        $type = "referenceFolder";
    } else if ($object instanceof \steam_link) {
        $type = "referenceFile";
    } else if ($object instanceof \steam_user) {
        $type = "user";
    } else if ($object instanceof \steam_group) {
        $type = "group";
    } else if ($object instanceof \steam_trashbin) {
        $type = "trashbin";
    } else if ($object instanceof \steam_docextern) {
        $type = "docextern";
    } else if ($object instanceof \steam_container) {
        $bidDocType = $object->get_attribute("bid:doctype");
        $bidType = $object->get_attribute("bid:collectiontype");
        $objType = $object->get_attribute("OBJ_TYPE");
        if ($bidDocType === "portal") {
            $type = "portal_old";
        } else if ($bidType === "gallery") {
            $type = "gallery";
        } else if ($objType === "container_portal_bid") {
            $type = "portal";
        } else if ($objType === "container_portalColumn_bid") {
            $type = "portalColumn";
        } else if ($objType === "container_portlet_bid") {
            $type = "portalPortlet";
        } else if (isUserHome($object)) {
            $type = "userHome";
        } else if (isGroupWorkroom($object)) {
            $type = "groupWorkroom";
        } else if ($object instanceof \steam_room) {
            $type = "room";
        } else {
            $type = "container";
        }
    } else {
        $type = "unknown";
    }
    return $type;
}

function deriveIcon($object) {
    if (!($object instanceof steam_object))
        return "";
    $worksheet_role = $object->get_attribute("worksheet_role");
    if ($worksheet_role === "build")
        return "worksheet.png";
    if ($worksheet_role === "view")
        return "worksheets.png";
    if ($worksheet_role === "edit")
        return "worksheet.png";
    //bidOWL:Collection Types
    $collectiontype = $object->get_attribute("bid:collectiontype");
    /* if($collectiontype === "sequence")
      return "sequence.gif";
      else if($collectiontype === "cluster")
      return "cluster.gif";
      else */ if ($collectiontype === "gallery")
        return "gallery.png";


    //bidOWL:Document Types
    $objtype = $object->get_attribute("OBJ_TYPE");
    $doctype = $object->get_attribute("bid:doctype");

    if ($doctype === "portal") {
        return "portal_old.png";
    }

    if ($objtype === "container_portal_bid")
        return "portal.png";
    else if ($objtype === "container_portlet_bid")
        return "portlet.png";
    /* else if($objtype === "LARS_DESKTOP")
      return "lars_desktop.gif";
      else if($doctype === "questionary")
      return "questionary.gif";
      else */ if ($objtype === "text_forumthread_bid")
        return "forumthread.png";

    //steam:Types
    if ($object instanceof steam_docextern)
        return "www.png";
    /* else if($object instanceof steam_trashbin)
      return "trashbin.gif"; */
    else if ($object instanceof steam_exit)
        return "reference_folder.png";
    /* else if($object instanceof steam_room && $object->get_attribute("bid:presentation") === "index")
      return "folder_closed_index.gif"; */
    else if ($object instanceof steam_room)
        return "folder.png";
    else if ($object instanceof steam_container)
        return "folder.png";
    /* else if($object instanceof steam_date)
      return "date.gif"; */
    /* else if($object instanceof steam_calendar)
      return "calendar.gif"; */
    else if ($object instanceof steam_messageboard)
        return "forum.png";
    else if ($object instanceof steam_link)
        return "reference_file.png";


    //mimetypes by object name
    $name = $object->get_name();
    $mimetype = $object->get_attribute("DOC_MIME_TYPE");
    $icons = array(
        "generic" => "generic.png",
        //"application/x-coreldraw" => "coreldraw.gif",
        //"application/mjet-mm" => "mindmap.gif",
        "application/msword" => "application_msword.png",
        "application/ms-excel" => "application_ms-excel.png",
        "application/ms-powerpoint" => "application_ms-powerpoint.png",
        "application/pdf" => "application_pdf.png",
        "application/vnd.oasis.opendocument.presentation" => "application_vnd.oasis.opendocument.presentation.png",
        "application/vnd.oasis.opendocument.spreadsheet" => "application_vnd.oasis.opendocument.spreadsheet.png",
        "application/vnd.oasis.opendocument.text" => "application_vnd.oasis.opendocument.text.png",
        //"application/vnd.sun.xml.calc" => "starcalc.gif",
        "application/vnd.sun.xml.impress" => "application_vnd.sun.xml.impress.png",
        //"application/vnd.sun.xml.writer" => "starwriter.gif",
        "application/vnd.visio" => "visio.gif",
        "application/vnd.openxmlformats-officedocument.wordprocessingml.document" => "application_vnd.openxmlformats-officedocument.wordprocessingml.document.png",
        //"application/vnd.openxmlformats-officedocument.presentationml.presentation" => "mspowerpoint.gif",
        "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" => "application_vnd.openxmlformats-officedocument.spreadsheetml.sheet.png",
        "application/x-robolab" => "application_x-robolab.png",
        //"application/x-shockwave-flash" => "shockwave.gif",
        "application/zip" => "application_zip.png",
        "audio/mpeg" => "audio.png",
        "audio/x-midi" => "audio.png",
        "audio/x-mp3" => "audio.png",
        "audio/x-wav" => "audio.png",
        "image/gif" => "image.png",
        "image/jpeg" => "image.png",
        "image/jpg" => "image.png",
        "image/x-ms-bmp" => "image.png",
        "image/png" => "image.png",
        "text/html" => "text.png",
        "text/plain" => "text.png",
        "text/xml" => "text.png",
        "video/x-flv" => "video.png",
        "video/mpeg" => "video.png",
        "video/quicktime" => "video.png",
        "video/x-msvideo" => "video.png",
        "video/3gpp" => "video.png",
        "video/x-m4v" => "video.png",
        "video/x-ms-wmv" => "video.png"
    );

    $mimetype_map = array(
        ".ai" => "application/postscript", //PostScript
        ".avi" => "video/x-msvideo", //Microsoft video
        ".bin" => "application/octet-stream", //Binary, UUencoded
        ".bmp" => "image/x-ms-bmp", //Microsoft Windows bitmap
        ".c" => "text/plain", //Plain text: documents; program listings
        ".cc" => "text/plain", //Plain text: documents; program listings
        ".cdr" => "application/x-coreldraw", //CorelDraw
        ".cpp" => "text/plain", //Plain text: documents; program listings
        ".css" => "text/css", //Cascading Stylesheets
        ".doc" => "application/msword", //Windows Word Documents
        ".docx" => "application/vnd.openxmlformats-officedocument.wordprocessingml.document", //Windows Word XML Documents
        ".emm" => "application/mjet-mm", //Mindmanager Mindjet
        ".eps" => "application/postscript", //PostScript
        ".exe" => "application/octet-stream", //PC executable
        ".flv" => "video/x-flv", //Flash Video Format
        ".gif" => "image/gif", //Comupserver GIF
        ".h" => "text/plain", //Plain text: documents; program listings
        ".htm" => "text/html", //HTML text data (RFC 1866)
        ".html" => "text/html", //HTML text data (RFC 1866)
        ".hqx" => "application/mac-binhex40", //Macintosh Binhexed archive
        ".jpg" => "image/jpg", //JPEG
        ".jpg" => "image/jpg", //JPEG
        ".jpe" => "image/jpg", //JPEG
        ".js" => "text/javascript", //Javascript program
        ".kml" => "application/vnd.google-earth.kml+xml", //Google KML file
        ".kmz" => "application/vnd.google-earth.kmz", //Google KMZ file
        ".latex" => "application/x-latex", //LaTeX document
        ".mail" => "application/x-mailfolder", //Mail folder
        ".mid" => "audio/x-midi", //MIDI music data
        ".mmp" => "application/mjet-mm", //Mindmanager Mindjet
        ".mov" => "video/quicktime", //Macintosh Quicktime
        ".mp2a" => "audio/x-mpeg-2", //MPEG-2 audio
        ".mp2v" => "video/mpeg-2", //MPEG-2 video
        ".mp3" => "audio/mpeg", //MP3 audio
        ".mp4" => "video/mpeg", //MPEG-4 video
        ".mpa" => "video/mpeg", //MPEG audio
        ".mpa2" => "audio/x-mpeg-2", //MPEG-2 audio
        ".mpe" => "video/mpeg", //MPEG video
        ".mpeg" => "video/mpeg", //MPEG video
        ".mpega" => "audio/x-mpeg", //MPEG audio
        ".mpg" => "video/mpeg", //MPEG video
        ".mpv2" => "video/mpeg", //MPEG-2 video
        ".odp" => "application/vnd.oasis.opendocument.presentation", //Open Document Presentation
        ".ods" => "application/vnd.oasis.opendocument.spreadsheet", //Open Document Spreadsheet
        ".odt" => "application/vnd.oasis.opendocument.text", //Open Document Text
        ".pdf" => "application/pdf", //Adobe Acrobat PDF
        ".png" => "image/png", //Portable Network Graphics
        ".ppt" => "application/ms-powerpoint", //PowerPoint (Microsoft)
        ".pptx" => "application/vnd.openxmlformats-officedocument.presentationml.presentation", //PowerPoint (Microsoft) XML
        ".pps" => "application/ms-powerpoint", //PowerPoint (Microsoft)
        ".ppz" => "application/ms-powerpoint", //PowerPoint presentation (Microsoft)
        ".ps" => "application/postscript", //PostScript
        ".qt" => "video/quicktime", //Macintosh Quicktime
        ".ram" => "application/x-pn-audioman", //Realaudio (Progressive Networks)
        ".rgb" => "image/rgb", //RGB
        ".rtf" => "application/rtf", //Microsoft Rich Text Format
        ".sit" => "application/x-stuffit", //Macintosh Stuffit Archive
        ".snd" => "audio/basic", //"basic"audio - 8-bit u-law PCM
        ".swf" => "application/x-shockwave-flash", //Shockwave Flash
        ".svg" => "image/svg+xml", //Scalable Vector Graphics
        ".sxc" => "application/vnd.sun.xml.calc", //Staroffice XML Calc
        ".sxd" => "application/vnd.sun.xml.draw", //Staroffice XML Calc
        ".sxi" => "application/vnd.sun.xml.impress", //Staroffice XML Impress
        ".sxw" => "application/vnd.sun.xml.writer", //Staroffice XML Writer
        ".tar" => "application/x-tar", //4.3BSD tar format
        ".tex" => "application/x-tex", //Tex/LaTeX document
        ".texi" => "application/x-texinfo", //GNU TexInfo document
        ".texinfo" => "application/x-texinfo", //GNU TexInfo document
        ".tif" => "image/tiff", //TIFF
        ".tiff" => "image/tiff", //TIFF
        ".txt" => "text/plain", //Plain text: documents; program listings
        ".vi" => "application/x-robolab", //Robolab files (Lego)
        ".vsd" => "application/vnd.visio", //Microsoft Visio
        ".vrml" => "x-world/x-vrml", //VRML data file
        ".wav" => "audio/x-wav", //Microsoft audio
        ".wmv" => "video/x-ms-wmv", //Microsoft Windows Media Video
        ".wrl" => "x-world/x-vrml", //VRML data file
        ".xls" => "application/ms-excel", //Microsoft Excel
        ".xlsx" > "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet", //Microsoft Excel XML
        ".xlt" => "application/ms-excel", //Microsoft Excel (Mustervorlage)
        ".xml" => "text/xml", //XML text data
        ".zip" => "application/zip"                     //Zipped archive
    );

    //follow the steam mimetype
    if (isset($icons[$mimetype]))
        return $icons[$mimetype];

    //derive mimetype out of filename
    $tail = strrchr($name, '.');
    if (isset($mimetype_map[$tail]) && isset($icons[$mimetype_map[$tail]]))
        return $icons[$mimetype_map[$tail]];

    //default icon
    return $icons["generic"];
}

function getObjectReadableSize($object) {
    $type = getObjectType($object);
    try {
        if ($type == "document") {
            $html = getReadableSize($object->get_content_size());
        } else if ($type == "container" || $type == "room" || $type == "groupWorkroom" || $type == "userHome") {
            $html = count($object->get_inventory()) . " Objekte";
        } else if ($type == "portal") {
            $html = count($object->get_inventory()) . " Spalten";
        } else if ($type == "gallery") {
            $html = count($object->get_inventory()) . " Bilder";
        } else if ($type == "forum") {
            $html = count($object->get_annotations()) . " Themen";
        } else if ($type == "referenceFile") {
            $linkObject = $object->get_link_object();
            $html = getObjectReadableSize($linkObject);
        } else if ($type == "referenceFolder") {
            $exitObject = $object->get_exit();
            $html = getObjectReadableSize($exitObject);
        } else {
            $html = "";
        }
    } catch (steam_exception $e) {
        $html = "keine Berechtigung";
    }
    return $html;
}

//old bid-owl function lib
function check_width_string($column_width, $pc_min, $pc_max, $px_min, $px_max, $default_value) {
    if (preg_match('/([0-9]+)(px|%){0,1}$/', trim($column_width), $substring)) {
        if ($substring[2] == "") {
            $substring[2] = "px";
        }
        if ($substring[2] == "%") {
            if ($substring[1] <= $pc_max && $substring[1] >= $pc_min) {
                return $substring[1] . $substring[2];
            }
        } else if ($substring[2] == "px") {
            if ($substring[1] <= $px_max && $substring[1] >= $px_min) {
                return $substring[1] . $substring[2];
            }
        }
    }
    return $default_value;
}

/**
 * If the given value ends with a percent sign then this sign is removed
 * from the output. Otherwise an empty string is returned.
 */
if (!function_exists("extract_percentual_length")) {

    function extract_percentual_length($value) {
        if (preg_match('/([0-9]+)(%)$/', trim($value), $substring)) {
            return $substring[1];
        }
        return "";
    }

}

/**
 * Remove any trailing % or pt signs from the given length value.
 * Return the empty string if the given value is not a length.
 */
function extract_length($value) {
    if (preg_match('/([0-9]+)(px|%){0,1}$/', trim($value), $substring)) {
        return $substring[1];
    }
    return "";
}

/**
 * Check if the given length is a relative length and convert
 * it to an absolute length using the given base value.
 */
function calculate_absolute_length($length, $base_value) {
    if (($relative_length = extract_percentual_length($length)) != "") {
        return floor($base_value * $relative_length / 100);
    } else {
        return extract_length($length);
    }
}

function derive_url($url, $path = "") {
    global $config_webserver_ip;

    $url = trim($url);
    $http = strpos(strtolower($url), "http://");
    $https = strpos(strtolower($url), "https://");
    $ftp = strpos(strtolower($url), "ftp://");
    $mailto = strpos(strtolower($url), "mailto:");
    $skype = strpos(strtolower($url), "skype:");
    $worldwind = strpos(strtolower($url), "worldwind://");
    $relative = strpos($url, ".");

    if ($http === 0 || $https === 0 || $ftp === 0 || $mailto === 0 || $skype === 0 || $worldwind === 0)
        return $url;
    else if ($relative === 0)
        return $config_webserver_ip . $path . $url;
    else
        return "http://" . $url;
}

//old bid-owl function lib - end


function getDownloadUrlForObjectId($id) {
    return PATH_URL . "download/document/" . $id;
}

function isAjaxRequest() {
    return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));
}

function isPhpCli() {
    return php_sapi_name() == "cli";
}

function isErrorPage() {
    return stristr($_SERVER['REQUEST_URI'], "/error/report/");
}

function buffer_flush() {

    echo str_pad('', 1024);
    echo '<!-- -->';

    if (ob_get_length()) {

        @ob_flush();
        @flush();
        @ob_end_flush();
    }

    @ob_start();
}

function displayStartupUserInfo() {
    if (!isPhpCli() && !isAjaxRequest()) {
        @ob_start();
        $version = KOALA_VERSION;
        echo <<< END
<style type="text/css">
body
{
	text-align: center;
	font-family: "Lucida Sans Unicode", "Lucida Grande", Verdana, Arial, Helvetica, sans-serif;
	font-size: 17px;
	font-weight: bold;
}

div#container
{
	position:relative;
	top:30%;
}
</style>
<div id="container">
<h1>Initializing System. Please wait ...</h1>
<small style="font-weight: normal;">koala framework v$version</small>
</div>
END;
        buffer_flush();
    } else if (isPhpCli()) {
        echo "Initializing System. Please wait ...\n";
    }
}

function cleanHTML($dirtyHTML) {
    define("HTMLPURIFIER_PREFIX", PATH_DEPENDING . "classes/htmlpurifier/library");
    $config = HTMLPurifier_Config::createDefault();
    $config->set('Cache.DefinitionImpl', null);
    
    $config->set('HTML.SafeEmbed', true);
    $config->set('HTML.SafeObject', true);
    $config->set('HTML.SafeIframe', true);
    
    $config->set('URI.SafeIframeRegexp', '%^http://(www.youtube.com/embed/|player.vimeo.com/video/)%');
    
    $def = $config->getHTMLDefinition(true);
    $def->addAttribute('a', 'target', new HTMLPurifier_AttrDef_Enum(
                    array('_blank', '_self', '_target', '_top')
    ));
    
    $purifier = new HTMLPurifier($config);
    $dirtyHTML = $purifier->purify($dirtyHTML);
    //$tidy = tidy_parse_string($dirtyHTML);
    //$tidy->cleanrepair();
    //$dirtyHTML = $tidy;
    //$dirtyHTML = strip_tags($dirtyHTML,"<h1><h2><h3><h4><h5><p><a><div><br><b><i><u><strong><img><hr><table><tr><th><td><ul><ol><li><dl><dd><dt><sup><sub><span>");
    return $dirtyHTML;
}

function return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val) - 1]);
    switch ($last) {
        // The 'G' modifier is available since PHP 5.1.0
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }

    return $val;
}

/*
 * returns a valid path for a relative path
 * 
 * @savedPath could be a relative or absolute path to an object
 * @currentPath path of the current environment or current portal
 * 
 * @return a valid path
 */

function revealPath($savedPath, $currentPath = "") {
    //path is an absolute path
    if (strtolower(substr($savedPath, 0, 4)) == "http") {
        return $savedPath;
    }
    if (strtolower(substr($savedPath, 0, 3)) == "www") {
        return "http://" . $savedPath;
    }
    if (strtolower(substr($savedPath, 0, 7)) == "mailto:") {
        return $savedPath;
    }


    //case relative path in portal-portlet
    $constructedPath1 = $currentPath . "/../../../" . $savedPath;  //redirect from a portlet
    $steamObject = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), trim($constructedPath1));
    if (gettype($steamObject) == "object") {
        $objectId = $steamObject->get_id();
        $newUrl = PATH_URL . "explorer/Index/" . $objectId . "/";
        return $newUrl;
    }


    //case relative path 5
    $constructedPath2 = $currentPath . "/../../../../" . $savedPath;  //redirect from a portlet
    $steamObject = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), trim($constructedPath2));
    if (gettype($steamObject) == "object") {
        $objectId = $steamObject->get_id();
        $newUrl = PATH_URL . "explorer/Index/" . $objectId . "/";
        return $newUrl;
    }


    //case relative path 2
    $constructedPath2 = $currentPath . "/../../" . $savedPath;  //redirect from a portlet
    $steamObject = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), trim($constructedPath2));
    if (gettype($steamObject) == "object") {
        $objectId = $steamObject->get_id();
        $newUrl = PATH_URL . "explorer/Index/" . $objectId . "/";
        return $newUrl;
    }


    //case relative path 3
    $constructedPath2 = $currentPath . "/../" . $savedPath;  //redirect from a portlet
    $steamObject = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), trim($constructedPath2));
    if (gettype($steamObject) == "object") {
        $objectId = $steamObject->get_id();
        $newUrl = PATH_URL . "explorer/Index/" . $objectId . "/";
        return $newUrl;
    }

    //case relative path 4
    $constructedPath2 = $currentPath . "/" . $savedPath;  //redirect from a portlet
    $steamObject = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), trim($constructedPath2));
    if (gettype($steamObject) == "object") {
        $objectId = $steamObject->get_id();
        $newUrl = PATH_URL . "explorer/Index/" . $objectId . "/";
        return $newUrl;
    }



    return $savedPath; //return not modified
}

function detectMimeType($pathString) {
    $path = pathinfo($pathString);
    $extension = $path['extension'];
    $mimeArray = array();

    if (file_exists(PATH_TEMP . "mime.types")) {
        $handle = @fopen(PATH_TEMP . "mime.types", "r");
        $existing = true;
    } else {
        $handle = @fopen("http://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types", "r");
        $handleWrite = @fopen(PATH_TEMP . "mime.types", "w");
        $existing = false;
    }

    while (($line = fgets($handle, 4096)) !== false) {
        if (substr($line, 0, 1) === "#")
            continue;
        $parts = preg_split("/[\s]+/", $line);
        for ($i = 1; $i < count($parts); $i++) {
            $mimeArray [$parts[$i]] = $parts[0];
        }
        if (!$existing)
            fwrite($handleWrite, $line . "\n");
    }
    return $mimeArray[$extension];
}

function getMimeTypeExtension($mimeType) {
    $mimeArray = array();

    if (file_exists(PATH_TEMP . "mime.types")) {
        $handle = @fopen(PATH_TEMP . "mime.types", "r");
        $existing = true;
    } else {
        $handle = @fopen("http://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types", "r");
        $handleWrite = @fopen(PATH_TEMP . "mime.types", "w");
        $existing = false;
    }

    while (($line = fgets($handle, 4096)) !== false) {
        if (substr($line, 0, 1) === "#")
            continue;
        $parts = preg_split("/[\s]+/", $line);
        //first file extension wins
        $mimeArray [$parts[0]] = $parts[1];
        if (!$existing)
            fwrite($handleWrite, $line);
    }
    $mimeArray["application/x-unknown-content-type"] = "";
    return $mimeArray[$mimeType];
}

?>