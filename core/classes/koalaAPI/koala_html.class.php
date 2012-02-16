<?php

require_once( "HTML/Template/IT.php" );
require_once( PATH_LIB . "format_handling.inc.php" );

/**
 * Base class for html template based objects. You need to call
 * parent::__construct($template_path) in your constructor if you inherit
 * this class. This will set up the specified html template. You also need to
 * override the get_headline() method.
 * 
 * When inheriting this class, you will usually want to do the following:
 * * write your own __construct() and call parent::__construct($template_path)
 *   in it
 * * write your own get_headline() and return a (string) headline for the
 *   portal
 * * write your own get_menu() if you want a menu (tab bar) to be displayed
 *   (your html template must be prepared for this, see below)
 * * write your own get_context_menu() if you want a context menu (buttons) to
 *   be displayed (your html template must be prepared for this, see below)
 * 
 * Menu:
 * Override the get_menu() function and make sure your html template has code
 * like this in it (below a context menu block if you have one, see below):
 * <ul class="tabBar">
 *   <!-- BEGIN BLOCK_TABS -->    
 *   <li class="{TAB_STATE}">{TAB_ENTRY}</li>
 *   <!-- END BLOCK_TABS -->
 *   <li style="clear:left;"/>
 * </ul>
 * 
 * Context menu:
 * Override the get_context_menu() function and make sure your html template
 * has code like this in it (above the menu block if you have one, see above):
 * <!-- BEGIN BLOCK_CONTEXT_MENU -->
 * <div class="actionBar">
 *   <!-- BEGIN BLOCK_MENU_ENTRY -->
 *   <a class="button" href="{URL_MENU_ENTRY}">{LABEL_MENU_ENTRY}</a>
 *   <!-- END BLOCK_MENU_ENTRY -->
 * </div>
 * <!-- END BLOCK_CONTEXT_MENU -->
 *
 */
abstract class koala_html
{
	protected $template;
	private $context;
	private $context_params;

	/**
	 * You must call parent::__construct($template_path) in the constructor of
	 * your inherited class, so that the template is set up correctly.
	 *
	 * @param String $template_path path to the template file
	 */
	public function __construct ( $template_path )
	{
		$this->template = new HTML_TEMPLATE_IT();
		$this->template->loadTemplateFile( $template_path );
	}

	/**
	 * Override this function to return the headline for the portal.
	 *
	 * @return String headline
	 */
	public function get_headline()
	{
		return "";
	}

	/**
	 * Setup the context to display. This will set up the menu and context menu.
	 * You might need to pass additional parameters along with the context.
	 * Check the get_menu() and get_context_menu() functions in the inherited
	 * class you are using to see if they use any additional parameters.
	 * 
	 * Example:
	 * set_context( "documents", $container, $link_base );
	 *
	 * @param string $context the context to display
	 * @param array optional parameters array
	 */
	public function set_context ( $context, $params = array() )
	{
		$this->context = $context;
		$this->context_params = $params;
		$this->set_menu( $context, $params );
		$this->set_context_menu( $context, $params );
	}

	/**
	 * Returns the context if it has been set yet through the set_context()
	 * function.
	 *
	 * @return string the context (if it has been set)
	 */
	public function get_context ()
	{
		return $this->context;
	}

	/**
	 * Returns the context params if they have been set yet through the
	 * set_context() function.
	 *
	 * @return array the context params (if they have been set)
	 */
	public function get_context_params ()
	{
		return $this->context_params;
	}

	/**
	 * Override this function to return your menu for the portal.
	 * The menu must be an array:
	 * array( "the-context" => array("name"=>"...", "link"=>"...") ).
	 * The context strings will be the same as those passed to
	 * get_context_menu(). You can choose them arbitrarily, but make sure they
	 * match across get_context_menu().
	 * 
	 * You might receive additional parameters if set_context() was called with
	 * more parameters than just the context string.
	 * 
	 * Example:
	 * return array(
	 *   "start" => array( "name" => gettext("Start page"), "link" => PATH_URL . "desktop/" ),
	 *   "documents" => array( "name" => gettext( "Documents" ), "link" => PATH_URL . "/desktop/documents/" ),
	 * );
	 *
	 * @param Array optional parameters array
	 * @return Array an array with the menu for the portal
	 */
	protected function get_menu ( $params = array() )
	{
		return array();
	}

	/**
	 * Override this function to return your context menu for a given context.
	 * The context menu must be an array:
	 * array( array( "name"=>"...", "link":"..." ) ).
	 * 
	 * You might receive additional parameters if set_context() was called with
	 * more parameters than just the context string.
	 *
	 * Example:
	 * if ( $context == "documents" ) return array(
	 *   array( "name" => gettext("Create folder"), "link" => PATH_URL . $something ),
	 *   array( "name" => gettext("Upload file"), "link" => PATH_URL . $something_else ),
	 * );
	 *
	 * @param Array optional parameters array
	 * @param String $context the context for which to return the context menu
	 * @return Array an array with the context menu for the portal
	 */
	protected function get_context_menu( $context, $params = array() )
	{
		return array();
	}
	
	public function set_menu( $context = "start", $params = array() )
	{
		$menu = $this->get_menu( $params );
		if ( !is_array( $menu ) || empty( $menu ) ) return;
		if ( !isset( $menu[ $context ] ) || !is_array( $menu[ $context ] ) ) {
      error_log( 'koala_html_class.php->set_menu(): Menu does not contain item ' . $context );
			//throw new Exception( 'Menu does not contain item ' . $context , E_PARAMETER );
    }

		foreach ( $menu as $key => $item ) {
			$this->template->setCurrentBlock( "BLOCK_TABS" );
			if ( $key == $context ) {
				if ( is_string( $item[ "link" ] ) )
					$this->template->setVariable( "TAB_ENTRY", "<a href=\"" . $item[ "link" ] . "\">" . h( $item[ "name" ] ) . "</a>" );
				else
					$this->template->setVariable( "TAB_ENTRY", h( $item[ "name" ] ) );	
				$this->template->setVariable( "TAB_STATE", "tabOut" );
			}
			else {
				$this->template->setVariable( "TAB_ENTRY", "<a href=\"" . $item[ "link" ] . "\">" . h( $item[ "name" ] ) . "</a>" );
			    $this->template->setVariable( "TAB_STATE", "tabIn" );
			}
			$this->template->parse( "BLOCK_TABS" );
		}
	}

	public function set_context_menu( $context, $params = array() )
	{
		$context_menu = $this->get_context_menu( $context, $params );
		if ( !is_array( $context_menu ) || empty( $context_menu ) ) return;
		foreach ($context_menu as $key => $item) {
			if (!is_array($item)) {
				unset($context_menu[$key]);
			}
		}	
		$this->template->setCurrentBlock( "BLOCK_CONTEXT_MENU" );
		$popup_count = 0;
		foreach ( $context_menu as $key => $item ) {
			$this->template->setCurrentBlock( "BLOCK_MENU_ENTRY" );
				if ( isset( $item[ "menu" ] ) ) {
					$popup_count++;
					$html_menu = new koala_html_menu();
					$submenu = array();
					$subsubmenu = array();
					foreach ( $item["menu"] as $menu_item ) {
						$submenu_item = array( "name" => $menu_item[ "name" ] );
						if ( isset( $menu_item[ "link" ] ) )
							$submenu_item[ "link" ] = $menu_item[ "link" ];
						if ( isset( $menu_item[ "icon" ] ) )
							$submenu_item[ "icon" ] = $menu_item[ "icon" ];
						$submenu[] = $submenu_item;
						if ( isset( $menu_item[ "menu" ] ) )
							$subsubmenu[] = $menu_item[ "menu" ];
						else
							$subsubmenu[] = array();							
					}
					if ( isset( $item["link"] ) )
						$html_menu->add_menu_entry( array( "name" => $item["name"], "link" => $item["link"]), $submenu, $subsubmenu );
					else
						$html_menu->add_menu_entry( array( "name" => $item["name"] ), $submenu, $subsubmenu );
					$menu_html = "<div id='popupmenu_" . $popup_count . "' class='button'>";
					$menu_html .= $html_menu->get_html();
					$menu_html .= "</div>";
					$this->template->setVariable( "LINK_MENU_ENTRY", $menu_html );
				}else if ( isset( $item[ "link" ] ) ){
					if (count($context_menu) > 1 && $key == 0) {
						$position = "left";
					} else if (count($context_menu) > 1 && (($key + 1) == count($context_menu))) {
						$position = "right";
					} else {
						$position = "middle";
					}
					$this->template->setVariable( "LINK_MENU_ENTRY", "<a class='{$position} pill button' href='" . $item[ "link" ] . "'>" . h( $item[ "name" ] ) . "</a>" );
				}else if ( isset( $item[ "name" ] )){
					$this->template->setVariable( "LINK_MENU_ENTRY", h( $item[ "name" ] ) );
				}
			$this->template->parse( "BLOCK_MENU_ENTRY" );
		}
		$this->template->parse( "BLOCK_CONTEXT_MENU" );
	}

	public function get_html()
	{
		return $this->template->get();
	}
	
	public function get_template() {
		return $this->template;
	}
}

?>
