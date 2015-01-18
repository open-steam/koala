<?php
namespace Widgets;

interface IDataProvider {	
	public function getData($object);
	
	public function getUpdateCode($object, $elementId, $successMethod = null);
        
	public function isChangeable($object);
}	
?>