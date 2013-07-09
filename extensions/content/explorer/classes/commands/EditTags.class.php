<?php

namespace Explorer\Commands;

class EditTags extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $id;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        if ($requestObject instanceof \UrlRequestObject) {
            $this->params = $requestObject->getParams();
            isset($this->params[0]) ? $this->id = $this->params[0] : "";
        } else if ($requestObject instanceof \AjaxRequestObject) {
            $this->params = $requestObject->getParams();
            isset($this->params["id"]) ? $this->id = $this->params["id"] : "";
        }
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        $obj = \steam_factory::get_object($BLOGALS["STEAM"]->get_id(), $this->id);
        //get old tags 
        $tagsString = $obj->get_attribute("bid:tags");
        //transform old tags to array
        $bidtagsArray = array();
        if ($tagsString !== 0) {
            $bidtagsArray = explode(" ", $tagsString);
        }

        //get new tags
        $keywordsArray = array();
        $keywordsArray = $obj->get_attribute("OBJ_KEYWORDS");

        //combine old and new tags - ignore same tags
        foreach ($bidtagsArray as $oldString) {
            $contains = false;
            foreach ($keywordsArray as $newString) {
                if ($oldString === $newString) {
                    $contains = true;
                }
            }
            if (!$contains) {
                $keywordsArray[count($keywordsArray)] = $oldString;
            }
        }
        
        //get JavaScript
        $buttonId = "addTag234";
        $script = self::getScript(count($keywordsArray));
        
        //get all textfields
        $textInputHtml = array();
        foreach ($keywordsArray as $i => $kw) {
            $textInputHtml[i] = self::getTextInputHtml($kw);
        }


        $ajaxResponseObject->setStatus("ok");
        return $ajaxResponseObject;
    }

    private static function getTextInputHtml($value) {
        //TODO: Add corresponding class 
        return '<input type="text" class="" name="tags[]" value="'.$value.'">';
    }
    private static function getScript($count){
        //TODO: Add logic to button, add feedback 
        return '<script>
            var maxNumber=8;
            var tagNumber = '.$count.';
            function addTag(){
                $("#addTag234").remove();
                tagNumber++;
                if(tagNumber < maxNumber){
                    $("input")[tagNumber-1].appemd("<input type=\"text\" class=\"\" name=\"tags[]\"> +");
                }
            }
</script>';
    }

}

?>