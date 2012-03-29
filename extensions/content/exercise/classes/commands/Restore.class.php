<?php
namespace Exercise\Commands;
class Restore extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {
	
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
	 * restoreCommand($id)
	 * 
	 * Restores an object previously marked for deletion, or multiple other objects
	 * depending on the provided mode string
	 * 
	 * @param int $id id of the file.
	 * @param string $mode mode.
	 * @return array result of the action.
	 */
	public static function restoreCommand($id, $mode) {

		//in case there was no file in the uploader:
		if ( $id == "noop" ) return array('success' => true);
		
		$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
		
		//in case all previously deleted, yet unconfirmed files have to be restored
		if ( $mode == "deleteFlagged" ) {
			
			$pick  = array(array( '-', 'attribute', 'DELETEFLAG', '!=', 'TRUE' ),
						   array( '+', 'class', CLASS_DOCUMENT ));
			$order = array(array( '<', 'attribute', 'OBJ_NAME' )); 
			
			$files = $object->get_inventory_filtered( $pick , $order, 0, 0 ); 
			foreach ( $files as $restoreme ) {
				
				$restoreme->set_name($restoreme->get_attribute("ORIG_NAME"));
				$restoreme->delete_attribute("ORIG_NAME");
				$restoreme->set_attribute("DELETEFLAG", "FALSE");
			}
			
			#unset session information about the process
			unset($_SESSION['EX_CREATE']);
			
			return array('success' => true);
		}
		
		//standard behaviour, if no mode is selected
		$object->set_name($object->get_attribute("ORIG_NAME"));
		$object->delete_attribute("ORIG_NAME");
		$object->set_attribute("DELETEFLAG", "FALSE");
		
		return array('success' => true);
	}
	
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		
		if (!isset($_GET["mode"])) $mode=null; else $mode=$_GET["mode"];
		$response = self::restoreCommand($_GET["steamid"], $mode);
		
		echo htmlspecialchars(json_encode($response), ENT_NOQUOTES);
		die;
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		
	}
}
?>