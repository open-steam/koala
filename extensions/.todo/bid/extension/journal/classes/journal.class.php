<?php
	//TODO: Move this function to appropriate place
	function _stripslashes(&$item)
	{
	  if(is_array($item))
		array_walk($item, "_stripslashes");

	  else if(is_string($item) && $item != "")
		$item = stripslashes($item);
	}

	class journal {
		
		function get_content($id) {
			define("PATH_CURRENT_EXTENSION", PATH_PUBLIC . 
				"bid/extension/journal/");

			global $portal;

			$journal = steam_factory::get_object($GLOBALS['STEAM']->get_id(), $id);

			/* get permissions */
			$readable = 
				$journal->check_access_read($GLOBALS['STEAM']->get_current_steam_user(), 
				1);
			$writeable = 
				$journal->check_access_write($GLOBALS['STEAM']->get_current_steam_user(), 
				1);
			$result = $GLOBALS['STEAM']->buffer_flush();
			$readable = $result[$readable];
			$writeable = $result[$writeable];

			/* fetch columns */
			$columns = $journal->get_inventory(CLASS_CONTAINER, array(OBJ_TYPE, 
				"bid:journal:column:width"));
			$columnPortlets = array();

			/* now fetch portlets in all columns; buffering to be done */
			foreach($columns as $column) {
				if($column->get_attribute(OBJ_TYPE) != 
					"container_portalColumn_bid") continue;
				$columnPortlets [$column->get_id()] =
					$column->get_inventory("", array(OBJ_NAME,
								OBJ_DESC, "bid:portlet", 
								"bid:portlet:content"));
			}

			/*
			 * Fill template and display
			 */
			
			if(!$readable)
				die("Ansicht nicht m&ouml;glich!<br>");
			
			$content = new HTML_TEMPLATE_IT();
			$content->loadTemplateFile( PATH_CURRENT_EXTENSION . 
				"templates/journal.index.template.html" );

			$portal->set_page_title($journal->get_name());
			$portal->add_css_style_link( PATH_CURRENT_EXTENSION . 
				"css/journal.css");

			$portal_width = 0;
			foreach($columns as $column) {
				/* begin new column */
				$content->setCurrentBlock("portal_column");
				/* set column width */
				$column_width = $column->get_attribute("bid:portal:column:width");
				$portal_width += $column_width;
				$content->setVariable("PORTAL_COLUMN_WIDTH", $column_width);

				/* render editing area for column */
				if ($writeable && 
					$GLOBALS["STEAM"]->get_current_steam_user()->get_name() != "guest") 
				{
					$content->setVariable("PORTAL_COLUMN_ID", 
						$column->get_id());
				}

				/* render portlets */
				if (sizeof($columnPortlets[$column->get_id()]) > 0) {
					foreach ($columnPortlets[$column->get_id()] as $portlet) {
						if($portlet->get_attribute("bid:portlet")) {
							/* get the linked portlet if neccessary */
							if( $portlet instanceof steam_link )
								$portlet = $portlet->get_link_object();

							/* get content of portlet */
							$portlet_content = $portlet->get_attribute("bid:portlet:content");
							if(is_array($portlet_content) && count($portlet_content) > 0)
								array_walk($portlet_content, "_stripslashes");
							else
								$portlet_content = array();

							/* get portlet data in handy format */
							$portlet_name = $portlet->get_attribute(OBJ_DESC);
							if (trim($portlet_name) == "")
								$portlet_name = $portlet->get_attribute(OBJ_NAME);
							$portlet_type = $portlet->get_attribute("bid:portlet");

							/* produce portlet output and store in output buffer =>
							 * $portlet_content */
							ob_start();
							include(PATH_CURRENT_EXTENSION . "portlets/$portlet_type/view.php");
							$portlet_content = ob_get_contents();
							ob_end_clean();
						}
						else
							$portlet_content = "&nbsp;";

						$content->setVariable("PORTLET", $portlet_content);
					}		 
				}
				$content->parse("portal_column");
			}

			/* set portal width */
			$content->setVariable("PORTAL_WIDTH", $portal_width);

			/* return content */
			return $content->get();
		} /* function get_content */
	} /* class journal */
			
/*
//template stuff
$tpl = new Template("./templates/$language", "keep");
$tpl->set_file("content", "index.ihtml");
$tpl->set_block("content", "portlet_cell", "PORTLET_CELL");
$tpl->set_block("content", "empty_column", "DUMMY"); 
$tpl->set_block("content", "portal_column", "PORTAL_COLUMN");
$tpl->set_block("content", "edit_area", "DUMMY");
$tpl->set_var(array(
    "DUMMY" => "", 
    "DOC_ROOT" => $config_webserver_ip,
    "OBJECT_ID" => $portal->get_id(),
    "PORTAL_NAME" => $portal->get_name(),
    "EDIT_AREA" => "",
));
$portal_title = $portal->get_attribute(OBJ_DESC);

if ($portal_title != "") $tpl->set_var("PORTAL_NAME", $portal_title);


// $portal_width = 800;
$portal_width = 0;

foreach($columns as $column) {


		$column_width = $column->get_attribute("bid:portal:column:width");
		$portal_width += $column_width;

	$tpl->set_var(array(
      "PORTAL_COLUMN_ID" => $column->get_id(),
      "PORTAL_COLUMN_WIDTH" => $column->get_attribute("bid:portal:column:width")
	));

	//clear variable for next column
	$tpl->unset_var("PORTLET_CELL");

	//editing area for column
	if ($writeable && $steam->get_current_steam_user()->get_name() != "guest") {
		$tpl->parse("EDIT_AREA", "edit_area");
	}

	if (sizeof($columnPortlets[$column->get_id()]) > 0) {
		// column does contain portlets
		foreach ($columnPortlets [$column->get_id()] as $portlet) {
			if($portlet->get_attribute("bid:portlet")) {
				//get the linked portlet if neccessary
				if( $portlet instanceof steam_link )
					$portlet = $portlet->get_link_object();

				//get content of portlet
				$content = $portlet->get_attribute("bid:portlet:content");

				if(is_array($content) && count($content) > 0)
				array_walk($content, "_stripslashes");
				else
				$content = array();

				//get portlet data in handy format
				$portlet_name = $portlet->get_attribute(OBJ_DESC);
				if (trim($portlet_name) == "")
					$portlet_name = $portlet->get_attribute(OBJ_NAME);
				$portlet_type = $portlet->get_attribute("bid:portlet");

				//produce portlet output and store in output buffer => $content
				ob_start();
				include("./portlets/$portlet_type/view.php");
				$content = ob_get_contents();
				ob_end_clean();
			}
			else
				$content = "&nbsp;";

			$tpl->set_var(array(
        		"PORTLET" => $content,
			));
			$tpl->parse("PORTLET_CELL", "portlet_cell", true);
		}		 
	}
	else {
		// column does not contain any portlets yet
    $tpl->parse("PORTLET_CELL", "empty_column", true);
	}

	$tpl->parse("PORTAL_COLUMN", "portal_column", true);
}

$tpl->set_var(array(
   "PORTAL_WIDTH" => $portal_width . 'px'
));

//Logout & Disconnect
$steam->disconnect();

//parse all out
$tpl->parse("OUT", "content");
$tpl->p("OUT");
*/
?>
