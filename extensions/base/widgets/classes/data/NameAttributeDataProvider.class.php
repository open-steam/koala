<?php
namespace Widgets;

class NameAttributeDataProvider extends \Widgets\AttributeDataProvider {

    public function getUpdateCode($object, $elementId, $successMethode = "") {
        if (is_int($object)) {
            $objectId = $object;
        } else {
            $objectId = $object->get_id();
        }
        $function = ($successMethode != "") ? ", function(response){{$successMethode}({$elementId}, response);}" : ",''";
        $variableName = ($elementId)? $elementId:'value';
        return "sendRequest('databinding', {'id': {$objectId}, 'attribute': '{$this->getAttribute()}', 'value': {$variableName}}, '', 'data'{$function});";
    }
}
?>