<?php
namespace School\Commands;
class AddSchoolBookmark extends \AbstractCommand implements \IAjaxCommand
{
    private $params;
    private $id;

    public function validateData(\IRequestObject $requestObject)
    {
        return true;
    }

    public function processData(\IRequestObject $requestObject)
    {
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
        $link->move($bookmarks);
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject)
    {
        $ajaxResponseObject->setStatus("ok");
        $rawHtml = new \Widgets\RawHtml();
        $rawHtml->setHtml(\Bookmarks\Model\Bookmark::getMarkerHtml($this->id));
        $ajaxResponseObject->addWidget($rawHtml);

        return $ajaxResponseObject;
    }
}
