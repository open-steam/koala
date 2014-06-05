<?php

namespace Forum\Commands;

class GetPopupMenu extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $id;
    private $selection;
    private $x, $y, $height, $width;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
        $this->id = $this->params["id"];
       
        $this->x = $this->params["x"];
        $this->y = $this->params["y"];
        $this->height = $this->params["height"];
        $this->width = $this->params["width"];
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {

        $path = $this->params["path"];
        $forumId = $this->params["forum"];

        if (isset($this->params["category"])) {
            $categoryId = $this->params["category"];
            $isOverview = false;
        } else {
            $categoryId = $this->id;
            $isOverview = true;
        }
        $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
        $objectId = $this->id;
        $objectCreatorId = $object->get_creator()->get_id();
        $popupMenu = new \Widgets\PopupMenu();

        $copyIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/copy.png";
        $cutIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/cut.png";
        $referIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/refer.png";
        $trashIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/trash.png";
        $hideIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/hide.png";
        $bookmarkIcon = \Bookmarks::getInstance()->getAssetUrl() . "icons/bookmark.png";
        $schoolBookmarkIcon = \School::getInstance()->getAssetUrl() . "icons/schoolbookmark.png";
        $upIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/up.png";
        $downIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/down.png";
        $topIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/top.png";
        $bottomIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/bottom.png";
        $renameIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/rename.png";
        $editIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/edit.png";
        $propertiesIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/properties.png";
        $rightsIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/rights.png";
        $blankIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/blank.png";
        $replyIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/reply.png";
        $addImageIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/add_image.png";
        $deleteIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/delete.png";

        $steamUser = \lms_steam::get_current_user();
        $steamUserId = $steamUser->get_id();
        $forum = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $forumId);
        $forumCreatorId = $forum->get_creator()->get_id();

        $category = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $categoryId);
        $categoryCreatorId = $category->get_creator()->get_id();
        $category_allowed_write = $category->check_access_write($steamUser);
        $category_allowed_annotate = $category->check_access_annotate($steamUser);
        $category_allowed_read = $category->check_access_read($steamUser);


        $isForumCreator = false;
        $isTopicCreator = false;
        
        $canAnnotate = $category_allowed_write || $category_allowed_annotate;
        $canRead = $category_allowed_read;
        $hasSanction = $category->check_access(SANCTION_SANCTION, $steamUser);
        if ($forumCreatorId == $steamUserId) {
            $isForumCreator = true;
            $isTopicCreator = true;
            $canAnnotate = true;
            $canRead = true;
        } elseif ($categoryCreatorId == $steamUserId) {
            $isTopicCreator = true;
            $canAnnotate = true;
            $canRead = true;
        } elseif ($hasSanction) {
            $canAnnotate = true;
            $canRead = true;
        } elseif ($canAnnotate) {
            $canRead = true;
        }

        $isEditable = $forum->get_attribute("bid:forum_is_editable");

        //TODO: NEED REPLY_ICON
        $items = array();
        if ($categoryId == $this->id) {
            if (!$isOverview) {
                if ($canAnnotate) {
                    $items[] = array("name" => "Antworten<img src=\"{$replyIcon}\">", "command" => "NewReply", "namespace" => "forum", "params" => "{'id':'{$this->id}','forum':'{$forumId}'}", "type" => "popup");
                }
            }
            if($isForumCreator){
                $items[] = array("name" => "Bearbeiten<img src=\"{$editIcon}\">", "command" => "EditTopic", "namespace" => "forum", "params" => "{'id':'{$this->params["category"]}','forum':'{$forumId}'}", "type" => "popup");
                $items[] = array("name" => "Löschen<img src=\"{$trashIcon}\">", "command" => "DeleteTopic", "namespace" => "forum", "params" => "{'id':'{$this->id}','forum':'{$forumId}'}");
                $items[] = array("name" => "Bild anfügen<img src=\"{$addImageIcon}\">", "command" => "EditMessageImage", "namespace" => "forum", "params" => "{'messageObjectId':'{$this->id}','forum':'{$forumId}'}", "type" => "popup");
            }
            else if ($isTopicCreator) {
                if ($isEditable) {
                    $items[] = array("name" => "Bearbeiten<img src=\"{$editIcon}\">", "command" => "EditTopic", "namespace" => "forum", "params" => "{'id':'{$this->params["category"]}','forum':'{$forumId}'}", "type" => "popup");
                    $items[] = array("name" => "Löschen<img src=\"{$trashIcon}\">", "command" => "DeleteTopic", "namespace" => "forum", "params" => "{'id':'{$this->id}','forum':'{$forumId}'}");
                }
                $items[] = array("name" => "Bild anfügen<img src=\"{$addImageIcon}\">", "command" => "EditMessageImage", "namespace" => "forum", "params" => "{'messageObjectId':'{$this->id}','forum':'{$forumId}'}", "type" => "popup");
            }
        } else {
            if ($canAnnotate) {
                $items[] = array("name" => "Antworten<img src=\"{$replyIcon}\">", "command" => "NewReply", "namespace" => "forum", "params" => "{'id':'{$this->id}','forum':'{$forumId}'}", "type" => "popup");
            }
            if (($steamUserId == $objectCreatorId) || $isForumCreator) {
                if ($isForumCreator || $isEditable) {
                    $items[] = array("name" => "Bearbeiten<img src=\"{$editIcon}\">", "command" => "EditReply", "namespace" => "forum", "params" => "{'id':'{$this->id}','forum':'{$forumId}'}", "type" => "popup");
                    $items[] = array("name" => "Löschen<img src=\"{$deleteIcon}\">", "command" => "DeleteReply", "namespace" => "forum", "params" => "{'id':'{$this->id}','forum':'{$forumId}'}");
                }
                $items[] = array("name" => "Bild anfügen<img src=\"{$addImageIcon}\">", "command" => "EditMessageImage", "namespace" => "forum", "params" => "{'messageObjectId':'{$this->id}','forum':'{$forumId}'}", "type" => "popup");
            }
        }

        $popupMenu->setItems($items);
        $popupMenu->setPosition(round($this->x + $this->width - 155) . "px", round($this->y + $this->height + 4) . "px");
        $popupMenu->setWidth("180px");

        $ajaxResponseObject->setStatus("ok");
        $ajaxResponseObject->addWidget($popupMenu);

        return $ajaxResponseObject;
    }

}

?>