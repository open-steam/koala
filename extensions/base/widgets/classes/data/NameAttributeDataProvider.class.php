<?php
namespace Widgets;

class NameAttributeDataProvider extends \Widgets\AttributeDataProvider {

    public function getUpdateCode($object, $ownVariableName, $successMethod = "") {
        if (is_int($object)) {
            $objectId = $object;
        } else {
            $objectId = $object->get_id();
        }
        
        
        //if the user has an own successMethod, then call both methods, else just call the data-saveFunctionCallback method to wait until everything is saved
        $function = ($successMethod != "") ? ", function(response){dataSaveFunctionCallback(response); {$successMethod}({$ownVariableName}, response);}" : ", function(response){dataSaveFunctionCallback(response);}";
		
	//offer the possibility to use own variablenames
        //standard is 'value'
        $variableName = ($ownVariableName)? $ownVariableName:'value';
                
        //build the functioncall
        return "sendRequest('databinding', {'id': {$objectId}, 'attribute': '{$this->getAttribute()}', 'value': {$variableName}}, '', 'data'{$function});";
    }
}
?>