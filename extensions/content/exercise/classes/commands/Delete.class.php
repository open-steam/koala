<?php
namespace Exercise\Commands;
class Delete extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {
	
	private $params;
	private $id;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {
		if ($requestObject instanceof \UrlRequestObject) {
			$this->params = $requestObject->getParams();
			isset($this->params[0]) ? $this->id = $this->params[0]: "";
		} else if ($requestObject instanceof \AjaxRequestObject) {
			$this->params = $requestObject->getParams();
			isset($this->params["id"]) ? $this->id = $this->params["id"]: "";
		}
	}
	
	/**
	 * deleteCommand($id, $mode)
	 * 
	 * Deletes the object identifiable by its id, or multiple other objects
	 * depending on the provided mode string
	 * 
	 * @param int $id id of the file.
	 * @param string $mode mode.
	 * @return array result of the action.
	 */
	public static function deleteCommand($id, $mode) {
		
		//in case there was no file in the uploader:
		if ( $id == "noop" ) return array('success' => true);
		
		$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
		
		//in case all previously uploaded, yet unconfirmed files have to be deleted
		if ( $mode == "unsaved" ) {
			
			$pick  = array(array( '-', 'attribute', 'IS_NEW', '!=', 'TRUE' ),
						   array( '+', 'class', CLASS_DOCUMENT ));
			$order = array(array( '<', 'attribute', 'OBJ_NAME' )); 
			
			$newFiles = $object->get_inventory_filtered( $pick , $order, 0, 0 ); 
			foreach ( $newFiles as $removeme ) {
				
				$removeme->delete();
			}
			
			return array('success' => true);
		}
		
		//delete all files that are flagged as deletable
		if ( $mode == "flagged" ) {
			
			$pick  = array(array( '-', 'attribute', 'DELETEFLAG', '!=', 'TRUE' ),
						   array( '+', 'class', CLASS_DOCUMENT ));
			$order = array(array( '<', 'attribute', 'OBJ_NAME' ));
			
			$files = $object->get_inventory_filtered( $pick, $order, 0, 0 );
			foreach ( $files as $removeme ) {
				
				$removeme->delete();
			}
			
			return array('success' => true);
		}
		
		//standard behaviour, if no mode is selected
		// delete exercise and the corresponding containers for solutions/reviews
		$ex_cont = $object->get_environment();
		$parent = $ex_cont->get_environment();
		$parent_path = $parent->get_path();
		$sl_path = $parent_path."/solutions/".$id;
		$rv_path = $parent_path."/reviews/".$id;
		$solution_container = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $sl_path );
		$review_container = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $rv_path );
		
		$object->delete();
		$review_container->delete();
		$solution_container->delete();
		
		return array('success' => true);
	}
	
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		
		if (!isset($_GET["mode"])) $mode=null; else $mode=$_GET["mode"];
		$response = self::deleteCommand($_GET["steamid"], $mode);
		
		echo htmlspecialchars(json_encode($response), ENT_NOQUOTES);
		die;
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		
	}
}
?>