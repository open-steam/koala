<?php
$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "docextern_edit.template.html" );
if ( $_SERVER[ "REQUEST_METHOD" ] == "POST") {
	$values = $_POST[ "values" ];
	$problem = "";
	$hint    = "";
	if ( ! empty( $values[ "save" ] ) )
	{
		if ( empty( $values[ "url" ] ) )
		{
			$problem  = gettext( "The URL is missing." ) . " ";
			$hint     = gettext( "Please insert the URL, starting with 'http://'" ) . " ";
		}
		if ( empty( $values[ "name" ] ) )
		{
			$problem .= gettext( "The name is missing." );
			$hint    .= gettext( "How is the title of the webpage?" );
		} else {
      if ( strpos($values[ "name" ], "/" )) {
        if (!isset($problem)) $problem = "";
        $problem .= gettext("Please don't use the \"/\"-char in the name of the weblink.");
      }
    }

		if ( empty( $problem ) )
		{
      if (!is_object($docextern)) {
     		$docextern = steam_factory::create_docextern(
					$GLOBALS[ "STEAM" ]->get_id(),
					$values[ "name" ],
					$values[ "url" ],
					$env,
					$values[ "desc" ]
				);
      } else {
        if ( empty( $values[ "desc" ] ) )
        {
          $docextern->delete_attribute( "OBJ_DESC" );
        }
        else
        {
          $docextern->set_attribute( "OBJ_DESC", $values[ "desc" ] );
        }
        $docextern->set_name( $values[ "name" ] );
        $docextern->set_url( $values[ "url" ] );
      }
      header( "Location: " . $values[ "return_to" ] );
			exit;
		}
		else
		{
			$portal->set_problem_description( $problem, $hint );
			$content->setVariable( "VALUE_NAME", h($values[ "name" ]) );
			$content->setVariable( "VALUE_URL", h($values[ "url" ]) );
			$content->setVariable( "VALUE_DESC", h($values[ "desc" ]) );
		}
	}
}

if ( isset($docextern) && is_object($docextern)) { 
  $content->setVariable( "INFO_TEXT", str_replace( "%NAME", h($docextern->get_name()), gettext( "You are going to edit the weblink '<b>%NAME</b>'." ) ) );
  $content->setVariable( "LABEL_SAVE_CHANGES", gettext( "Save changes" ) );
  $pagetitle = gettext( "Edit a Weblink" );
  if (empty($values)) {
    $values = array();
    $values["name"] = $docextern->get_name();
    $values["dsc"] = $docextern->get_attribute(OBJ_DESC);
    $values["url"] = $docextern->get_attribute(DOC_EXTERN_URL);
  }
  $breadcrumbheader = gettext("Edit a Weblink");
}
else {
  $content->setVariable( "INFO_TEXT", str_replace( "%ENV", h($env->get_name()), gettext( "You are going to create a new weblink in '<b>%ENV</b>'." ) ) );
  $content->setVariable( "LABEL_SAVE_CHANGES", gettext( "Create Weblink" ) );
  $pagetitle = gettext( "Create Weblink" );
  $breadcrumbheader = gettext("Create a Weblink");
}

if (!empty($values) ) {
  if (!empty($values["name"])) $content->setVariable("VALUE_NAME", h($values["name"]));
  if (!empty($values["dsc"])) $content->setVariable("VALUE_DESC", h($values["dsc"]));
  if (!empty($values["url"])) $content->setVariable("VALUE_URL", h($values["url"]));
  else $content->setVariable("VALUE_URL", "http://");
  if (!empty($values["return_to"])) $backlink = $values["return_to"];
  else $backlink = $_SERVER[ "HTTP_REFERER" ];
} else {
  $content->setVariable("VALUE_URL", "http://");
  $backlink = $_SERVER[ "HTTP_REFERER" ];
}
$content->setVariable( "BACK_LINK", $backlink );
$content->setVariable( "LABEL_RETURN", gettext( "back" ) );

$koala_env = koala_object::get_koala_object( $env );

if (is_object($env) && is_object($koala_env)) $link_path = $koala_env->get_link_path();
if ( !is_array( $link_path ) ) $link_path = array();
$link_path[] = array( "name" => $pagetitle );


$portal->set_page_main(
	$link_path,
	$content->get(),
	""
);
$portal->show_html();

?>
