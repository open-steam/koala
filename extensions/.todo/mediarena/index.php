<?php
include_once( "../../etc/koala.conf.php" );

$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );
$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_PUBLIC . "/mediarena/templates/mediarena.template.html" );

/*
# A hex number that may represent 'abyz'
$abyz = 0x6162797A;

# Convert $abyz to a binary string containing 32 bits
# Do the conversion the way that the system architecture wants to
switch (pack ('L', $abyz)) {

    # Compare the value to the same value converted in a Little-Endian fashion
    case pack ('V', $abyz):
        echo 'Your system is Little-Endian.';
        break;

    # Compare the value to the same value converted in a Big-Endian fashion
    case pack ('V', $abyz):
        echo 'Your system is Big-Endian.';
        break;

    default:
        $endian = "Your system 'endian' is unknown."
            . "It may be some perverse Middle-Endian architecture.";
}
die;*/

/*$in_str =   pack("f*", 118.0);
$hex_ary = array();
foreach (str_split($in_str) as $chr) 
	$hex_ary[] = sprintf("%02X", ord($chr));
echo implode(' ',$hex_ary);

die;*/

$whiteboardsupport = $GLOBALS['STEAM']->get_module("package:whiteboardsupport");
if (is_object($whiteboardsupport)) {
      $objects = $GLOBALS['STEAM']->predefined_command( $whiteboardsupport, "query_inventory_data", array( $GLOBALS['STEAM']->get_current_steam_user()->get_workroom() ), false);
      //var_dump($objects);
} else {
	echo "package:whiteboardsupport nicht installiert";
}

$attributes =  $objects["attributes"];

$keys = array_keys($attributes);

foreach($keys as $key) {
	$content->setVariable("CURRENT_ROOM", $key);
	$content->setCurrentBlock("BLOCK_ICON");
	$attribute_map = $attributes[$key];
	$content->setVariable("ICON_ID", $key);
	$content->setVariable("ICON_LABEL", $attribute_map[OBJ_NAME]);
	$content->setVariable("ICON_SRC", PATH_SERVER . "/download/" . $attribute_map[OBJ_ICON]->get_id() . "/" . $attribute_map[OBJ_ICON]->get_name());
	$content->setVariable("POSITION_X", intval($attribute_map[OBJ_POSITION_X])+20);
	$content->setVariable("POSITION_Y", intval($attribute_map[OBJ_POSITION_Y])+50);
	//echo $attribute_map[OBJ_NAME];
	//echo $attribute_map[OBJ_POSITION_X];
	//echo "Get Attribute: " . steam_factory::get_object($GLOBALS['STEAM']->get_id(), $key)->get_attribute("OBJ_POSITION_X");
	//die;
	$content->parse("BLOCK_ICON");
	$content->setVariable("SCRIPT", "<script type=\"text/javascript\">new Draggable('icon_" . $key . "', {onEnd: arrangeObject});</script>");
}

$portal->add_javascript_onload("Mediarena", "setTimeout('loadRoom()',1000)");



$portal->set_page_main(
	"HTML Medi@rena Composer",
	$content->get(),
	""
);

$portal->show_html();
?>