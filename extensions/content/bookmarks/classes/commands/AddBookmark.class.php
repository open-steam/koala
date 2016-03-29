<?php
namespace Bookmarks\Commands;
class AddBookmark extends \AbstractCommand implements \IAjaxCommand{

    private $params;
    private $id;
    private $oldName;
    private $newName;

    public function validateData(\IRequestObject $requestObject){
        return true;
    }

    public function processData(\IRequestObject $requestObject){
        $this->params = $requestObject->getParams();

        $this->id = $this->params["id"];
        $bookmarks = $GLOBALS["STEAM"]->get_current_steam_user()->get_attribute(USER_BOOKMARKROOM);

        $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);

        if ($object instanceof \steam_link) {
            $link = \steam_factory::create_link($GLOBALS["STEAM"]->get_id(), $object->get_link_object());
        } elseif ($object instanceof \steam_docextern) {
            $link = $object->copy();
        } elseif ($object instanceof \steam_exit) {
            $link = \steam_factory::create_link($GLOBALS["STEAM"]->get_id(), $object->get_exit());
        } else {
            $link = \steam_factory::create_link($GLOBALS["STEAM"]->get_id(), $object);
        }
        $link->set_attribute(OBJ_DESC,  $object->get_attribute(OBJ_DESC));
        $link->set_attribute(DOC_MIME_TYPE,  $object->get_attribute(DOC_MIME_TYPE));
        $this->oldName = $link->get_name();
        $info = $link->move($bookmarks);
        $this->newName = $link->get_name();
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject){
        $ajaxResponseObject->setStatus("ok");
        $rawHtml = new \Widgets\RawHtml();
        $rawHtml->setHtml(\Bookmarks\Model\Bookmark::getMarkerHtml($this->id));
        $ajaxResponseObject->addWidget($rawHtml);
        $path= $this->params["path"];

        if($this->oldName != $this->newName){
          $informSlider = new \Widgets\InformSlider();
          $informSlider->setTitle("Information");
          $informSlider->setPostJsCode("createInformSlider()");
          $informSlider->setContent('Es existiert bereits ein Lesezeichen mit diesem Namen. Das neue Lesezeichen wird daher als "' . $this->newName . '" bezeichnet.');
          $ajaxResponseObject->addWidget($informSlider);
        }

        return $ajaxResponseObject;
    }
}
