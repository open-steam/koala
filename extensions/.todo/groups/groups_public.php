<?php

$public = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), STEAM_PUBLIC_GROUP, CLASS_GROUP );
$user = lms_steam::get_current_user();
$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "groups_public.template.html" );

if(MANAGE_GROUPS_MEMBERSHIP || CREATE_GROUPS){
	$content->setCurrentBlock( "BLOCK_ACTION_BAR_GROUPS" );
	if(MANAGE_GROUPS_MEMBERSHIP){
		$content->setCurrentBlock( "BLOCK_MANAGE_GROUPS_MEMBERSHIP" );
		$content->setVariable( "LINK_MANAGE_SUBSCRIPTIONS", PATH_URL . "user/" . $user->get_name(). "/groups/" );
		$content->setVariable( "LABEL_MANAGE_SUBSCRIPTIONS", gettext( "Manage subscriptions"  ) );
		$content->parse( "BLOCK_MANAGE_GROUPS_MEMBERSHIP" );
	}
	if(CREATE_GROUPS){
		$content->setCurrentBlock( "BLOCK_CREATE_GROUPS" );
		$content->setVariable( "LINK_CREATE_NEW_GROUP", PATH_URL . "groups_create_dsc.php?parent=" . (isset($_GET["cat"])?$_GET["cat"]:$public->get_id()) );
		$content->setVariable( "LABEL_CREATE_NEW_GROUP", gettext( "Create new group" ) );
		$content->parse( "BLOCK_CREATE_GROUPS" );
	}
	$content->parse( "BLOCK_ACTION_BAR_GROUPS" );
}
$content->setVariable( "LABEL_NAME", gettext( "Name" ) );
$content->setVariable( "LABEL_DESC", gettext( "Description" ) );

if ( ! empty( $_GET[ "cat" ] ) )
{
				// EINE KATEGORIE ANZEIGEN
				if ( ! $category = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $_GET[ "cat" ] ) )
				{
								include( "bad_link.php" );
								exit;
				}
				if ( ! $category instanceof steam_container )
				{
								include( "bad_link.php" );
								exit;
				}
				$subgroups = $category->get_inventory( CLASS_GROUP );
				$content->setVariable( "LABEL_ALL_CATEGORIES", str_replace( array( "%i", "%NAME" ), array( count( $subgroups ), $category->get_name()), gettext( "%i groups in %NAME" ) ) );
				$content->setVariable( "LABEL_SUBGROUPS_MEMBERS", gettext( "Members" ) );
        
        $tnr = array();
        $attributes = array(OBJ_NAME, OBJ_DESC);
        
        $tnr = array();
        $attributes = array(OBJ_NAME, OBJ_DESC);
        foreach( $subgroups as $subgroup ) {
          $tnr[$subgroup->get_id()] = array();
          $tnr[$subgroup->get_id()][OBJ_NAME] = $subgroup->get_attribute(OBJ_NAME, TRUE);
          $tnr[$subgroup->get_id()][OBJ_DESC] = $subgroup->get_attribute(OBJ_DESC, TRUE);
          $tnr[$subgroup->get_id()]["membercount"] = $subgroup->count_members(TRUE);
        }
        $result = $GLOBALS["STEAM"]->buffer_flush();
        foreach( $subgroups as $subgroup ) {
          $subgroup->set_value(OBJ_NAME, $result[$tnr[$subgroup->get_id()][OBJ_NAME]]);
          $subgroup->set_value(OBJ_DESC, $result[$tnr[$subgroup->get_id()][OBJ_DESC]]);
        }

        usort( $subgroups, "sort_objects_new" );

				foreach( $subgroups as $subgroup )
				{
					$content->setCurrentBlock( "BLOCK_CATEGORY_GROUP" );
					$content->setVariable( "VALUE_LINK", PATH_URL . "groups/" . $subgroup->get_id() . "/" );
					$content->setVariable( "VALUE_NAME", h($subgroup->get_name()) );
					$content->setVariable( "VALUE_SUBGROUPS_MEMBERS", $result[$subgroup->get_id()]["membercount"] );
					$content->setVariable( "VALUE_DESC", h($subgroup->get_attribute( "OBJ_DESC" )) );
					$content->parse( "BLOCK_CATEGORY_GROUP" );
				}
				$headline = array( array( "link" => PATH_URL . "groups/", "name" => gettext( "Public Groups" ) ), array( "link" => "", "name" => h($category->get_name()) ) );

}
else
{
				// KATEGORIEN ANZEIGEN
				$categories = $public->get_workroom()->get_inventory( CLASS_ROOM | CLASS_CONTAINER );
				$content->setVariable( "LABEL_ALL_CATEGORIES", str_replace( "%i", count( $categories ), gettext( "%i categories in Public Groups" ) ) );
				$content->setVariable( "LABEL_SUBGROUPS_MEMBERS", gettext( "Groups" ) );
        
				if ( count( $categories ) > 0 )
				{
          $tnr = array();
          $attributes = array(OBJ_NAME, OBJ_DESC);
          foreach( $categories as $category ) {
            $tnr[$category->get_id()] = array();
            $tnr[$category->get_id()]["attributes"] = $category->get_attributes($attributes, TRUE);
            $tnr[$category->get_id()]["inventory"] = $category->get_inventory_raw(CLASS_GROUP, TRUE);
          }
          $result = $GLOBALS["STEAM"]->buffer_flush();
          foreach( $categories as $category ) {
            $category->set_value(OBJ_NAME, $result[$tnr[$category->get_id()]["attributes"]][OBJ_NAME]);
            $category->set_value(OBJ_DESC, $result[$tnr[$category->get_id()]["attributes"]][OBJ_DESC]);
          }
          
          usort( $categories, "sort_objects_new" );
          
          foreach( $categories as $category ) {
            $content->setCurrentBlock( "BLOCK_CATEGORY_GROUP" );
            $content->setVariable( "VALUE_LINK", PATH_URL . "groups/?cat=" . $category->get_id() );
            $content->setVariable( "VALUE_NAME", h($category->get_name()) );
            $content->setVariable( "VALUE_DESC", h($category->get_attribute( OBJ_DESC )) );
            $subgroups = $result[$tnr[$category->get_id()]["inventory"]];
            $no_subgroups = count( $subgroups );
            if ( $no_subgroups == 0 )
            {
              $content->setVariable( "VALUE_SUBGROUPS_MEMBERS", "-" );
            }
            else
            {
              $content->setVariable( "VALUE_SUBGROUPS_MEMBERS", $no_subgroups );
            }
            $content->parse( "BLOCK_CATEGORY_GROUP" );
          }
				}
				$headline = gettext( "Public Groups" );
}
$portal->set_page_main( $headline, $content->get(), "" );
$portal->show_html();
?>
