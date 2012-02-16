<?php



class ajax_backend {

	private $portal, $user, $cache, $request, $post;

	function __construct() {
		$this->portal = lms_portal::get_instance();
		$this->portal->initialize(GUEST_NOT_ALLOWED);
		$this->user = lms_steam::get_current_user();
		$this->cache = get_cache_function($this->user->get_name(), 86400);
		$this->request = $_REQUEST;
		$this->post = $_POST;
		switch($this->request['action']) {
			case "sort_inventory":
				$this->sort_inventory();
				break;
			case "get_user_attribute":
				$this->get_user_attribute();
				break;
			case "set_user_attribute":
				$this->set_user_attribute();
				break;
			case "set_boxes":
				$this->set_boxes();
				break;
			case "get_boxes":
				$this->get_boxes();
				break;
			case "get_current_user":
				$this->get_current_user();
				break;
			case "set_courses_units_boxes":
				$this->set_courses_units_boxes();
				break;
			case "get_courses_units_boxes":
				$this->get_courses_units_boxes();
				break;
			case "set_list_inventory_boxes":
				$this->set_list_inventory_boxes();
				break;
			case "get_list_inventory_boxes":
				$this->get_list_inventory_boxes();
				break;
		}
	}

	function __destruct() {
		$cache = get_cache_function( $this->user->get_name() );
		$cache->drop( "lms_steam::user_get_profile", $this->user->get_name() );
	}

	function set_user_attribute() {
		//TODO check permission
		$this->user->set_attribute($this->request['attribute'], $this->request['value']);
		$this->get_user_attribute();
	}

	function get_user_attribute() {
		echo $this->user->get_attribute($this->request['attribute']);
	}

	function set_boxes() {
		$this->user->set_attribute("KOALA_DESKTOP_WEBPARTS", serialize($this->post['boxes']));
	}

	function get_boxes() {
		$webparts = $this->user->get_attribute("KOALA_DESKTOP_WEBPARTS");
		if ( is_string($webparts) )
		echo json_encode(unserialize($webparts));
	}

	function set_courses_units_boxes() {
		$room_path = $this->request['attribute'];
		logging::write_log(LOG_MESSAGES, $room_path);
		$room = steam_factory::path_to_object( $GLOBALS[ "STEAM" ]->get_id(), $room_path, 0 );
		$room->set_attribute( "box_sort_order", serialize($this->request['boxes']));
	}

	function get_courses_units_boxes() {
		$room_path = $this->request['attribute'];
		$room = steam_factory::path_to_object( $GLOBALS[ "STEAM" ]->get_id(), $room_path, 0 );
		$webparts = $room->get_attribute("box_sort_order");
		if ( is_string($webparts) )
		echo json_encode(unserialize($webparts));
	}

	function sort_inventory() {
		$container_id = $this->request['attribute'];
		if ( !is_numeric( $container_id ) ) return;
		$container = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $container_id, CLASS_CONTAINER);
    try {
      $container->order_inventory_objects( $this->request['order'] );
    } catch (Exception $ex) {
      // Do Nothing
    }
	}

	function set_list_inventory_boxes() {
		$container_id = $this->request['attribute'];
		if ( !is_numeric( $container_id ) ) return;
		$container = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $container_id, CLASS_CONTAINER);
    try {
    	$container->set_attribute( "box_sort_order", serialize($this->request['boxes']));
    } catch (Exception $ex) {
      // Do Nothing
    }
	}

	function get_list_inventory_boxes() {
		$container_id = $this->request['attribute'];
		if ( !is_numeric( $container_id ) ) return;
		$container = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $container_id, CLASS_CONTAINER );
    try {
        $webparts = $container->get_attribute("box_sort_order");
    } catch (Exception $ex) {
      // Do Nothing
    }
		if ( !is_string( $webparts ) ) return;
		echo json_encode(unserialize($webparts));
	}

	function get_current_user() {
		echo json_encode($this->user->get_name());
	}
}

?>
