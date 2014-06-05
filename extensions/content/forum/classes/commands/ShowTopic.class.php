<?php

namespace Forum\Commands;

class ShowTopic extends \AbstractCommand implements \IFrameCommand {

    private $id;
    private $params;
    private $forumId;

    public function validateData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
        if (isset($this->params[0]) && isset($this->params[1])) {
            $this->id = $this->params[1];
            $this->forumId = $this->params[0];
            return true;
        } else {
            return false;
        }
    }

    public function processData(\IRequestObject $requestObject) {
        
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject) {
        $rawHtml = new \Widgets\RawHtml();
        $forumId = $this->forumId;
        $category_id = $this->id;
        
        
        
        $myExtension = \Forum::getInstance();
        $myExtension->addCSS("style_topics.css");


        //******************************************************
        //** sTeam Server Connection
        //******************************************************
        $steam = $GLOBALS["STEAM"];


        /** log-in user */
        $steamUser = \lms_steam::get_current_user();
        /** id of the log-in user */
        $steamUserId = $steamUser->get_id();
        /** the login user name */
        $steamUserLoginName = $steamUser->get_name();
        $steamUserName = $steamUser->get_full_name();

        /** the current category */
        $category = \steam_factory::get_object($steam->get_id(), $category_id);
        
        if(!($category instanceof \steam_document)){
            $invalidType = new \Widgets\RawHtml();
            $invalidType->setHtml("Sie versuchen ein nicht gültiges Thema anzuzeigen.");
            $frameResponseObject->addWidget($invalidType);
            return $frameResponseObject;
        }
        /** additional required attributes */
        $categoryAttributes = $category->get_attributes(array(OBJ_NAME, OBJ_DESC,
            OBJ_CREATION_TIME, "bid:description", "DOC_LAST_MODIFIED", "DOC_USER_MODIFIED"), 1);

        /** the content of the current category */
        $categoryContent = $category->get_content(1);

        /** the creater of the current category */
        $categoryCreator = $category->get_creator(1);
        /** the current forum */
        $forum = $category->get_annotating(1);
        $category_allowed_write = $category->check_access_write($steamUser, 1);
        $category_allowed_read = $category->check_access_read($steamUser, 1);
        $category_allowed_annotate = $category->check_access_annotate($steamUser, 1);

        // flush the buffer
        $result = $steam->buffer_flush();

        $categoryAttributes = $result[$categoryAttributes];
        $categoryContent = $result[$categoryContent];
        $categoryCreator = $result[$categoryCreator];
        $categoryCreatorId = $categoryCreator->get_id();
        $categoryAttributes["DOC_USER_MODIFIED"]->get_attributes(array(OBJ_NAME), 1);
        $forum = $result[$forum];
        $category_allowed_write = $result[$category_allowed_write];
        $category_allowed_read = $result[$category_allowed_read];
        $category_allowed_annotate = $result[$category_allowed_annotate];
        $category_allowed_sanction = $category->check_access(SANCTION_SANCTION);

        /** the environment of the forum object */
        if(!($forum instanceof \steam_messageboard)){
            $errorHtml = new \Widgets\RawHtml();
            $errorHtml->setHtml("Das gewünschte Thema kann nicht angezeigt werden. Bitte überprüfen Sie die angegebene Adresse.");
            $frameResponseObject->addWidget($errorHtml);
            return $frameResponseObject;     
        }
        if(!($category instanceof \steam_document)){
            $errorHtml = new \Widgets\RawHtml();
            $errorHtml->setHtml("Das gewünschte Thema kann nicht angezeigt werden. Bitte überprüfen Sie die angegebene Adresse.");
            $frameResponseObject->addWidget($errorHtml);
            return $frameResponseObject;     
        }
        $forumEnvironment = $forum->get_environment();
        /** additional required attributes */
        $forumEnvironmentAttributes = $forumEnvironment->get_attributes(array(OBJ_NAME, OBJ_DESC), 1);
        /** additional required attributes */
        $forumAttributes = $forum->get_attributes(array(OBJ_NAME, OBJ_DESC, "bid:description", "bid_forum_subtitle"), 1);
        /** the creator of the forum */
        $forumCreator = $forum->get_creator(1);
        /** attributes of the creator of the category */
        $categoryCreatorAttributes = $categoryCreator->get_attributes(array(OBJ_NAME, OBJ_DESC, OBJ_ICON), 1);

        $result = $steam->buffer_flush();
        $forumAttributes = $result[$forumAttributes];
        $forumCreator = $result[$forumCreator];
        $categoryCreatorAttributes = $result[$categoryCreatorAttributes];
        $forumEnvironmentAttributes = $result[$forumEnvironmentAttributes];

        /** attributes of the creator of the forum object */
        $forumCreatorAttributes = $forumCreator->get_attributes(array(OBJ_NAME, OBJ_DESC, OBJ_ICON), 1);


        $result = $steam->buffer_flush();
        $forumCreatorAttributes = $result[$forumCreatorAttributes];
        $forumCreatorId = $forumCreator->get_id();

        if ($category_allowed_read) {
            $messages = $category->get_annotations(false, 1);
            $result = $steam->buffer_flush();
            $messages = $result[$messages];
            sort($messages);

            if (count($messages) > 0) {
                foreach ($messages as $message) {
                    if (!empty($message)) {
                        $id = $message->get_id();
                        $messageAttributes[$id] = $message->get_attributes(array(OBJ_NAME, OBJ_DESC, OBJ_CREATION_TIME, "DOC_LAST_MODIFIED", "DOC_USER_MODIFIED"), 1);
                        $messageAccessWrite[$id] = $message->check_access_write($steamUser, 1);
                        $messageContent[$id] = $message->get_content(1);
                        $messageCreator[$id] = $message->get_creator(1);
                    }
                }

                $result = $steam->buffer_flush();

                foreach ($messages as $message) {
                    $id = $message->get_id();
                    $messageAttributes[$id] = $result[$messageAttributes[$id]];
                    $messageContent[$id] = $result[$messageContent[$id]];
                    $messageCreator[$id] = $result[$messageCreator[$id]];
                    $messageCreator[$id]->get_attributes(array(OBJ_NAME), 1);
                    $messageAttributes[$id]["DOC_USER_MODIFIED"]->get_attributes(array(OBJ_NAME), 1);
                    $messageAccessWrite[$id] = $result[$messageAccessWrite[$id]];
                }

                $result = $steam->buffer_flush();
            }
        }
        //START CHECK RIGHTS OF THE CURRENT USER
        $isForumCreator = false;
        $isTopicCreator = false;
        $canAnnotate = $category_allowed_write || $category_allowed_annotate;
        $canRead = $category_allowed_read;
        $hasSanctionRights = $category_allowed_sanction;
        if ($forumCreatorId == $steamUserId) {
            $isForumCreator = true;
            $isTopicCreator = true;
            $canAnnotate = true;
            $canRead = true;
        } elseif ($categoryCreatorId == $steamUserId) {
            $isTopicCreator = true;
            $canAnnotate = true;
            $canRead = true;
        } elseif ($canAnnotate) {
            $canRead = true;
        }

        //END CHECK RIGHTS OF THE CURRENT USER
        
        
        //chronic
        $entryTitle = $categoryAttributes[OBJ_DESC];
        $chronicPath = "/forum/showTopic/$forumId/$category_id";
        $chronicTitle = "Forum-Beitrag ($entryTitle)";
        \ExtensionMaster::getInstance()->getExtensionById("Chronic")->setCurrentPath($chronicPath,$chronicTitle);
        

        $content = \Forum::getInstance()->loadTemplate("forumShowTopic.template.html");

        $addIcon = \Forum::getInstance()->getAssetUrl() . "icons/new.gif";
        $editIcon = \Forum::getInstance()->getAssetUrl() . "icons/message_edit.gif";
        $deleteIcon = \Forum::getInstance()->getAssetUrl() . "icons/message_delete.gif";
        $backToTopic = '<h2>Zurück zur <a href="'.PATH_URL.'forum/index/'.$forumId.'">Übersicht</a></h2>';
        $content->setVariable("BACK_TO_TOPICLIST",$backToTopic);
        if ($category_allowed_read) {
            $content->setVariable("FORUM_NAME", $forumAttributes["OBJ_DESC"]);
            $content->setVariable("CATEGORIE_NAME", $categoryAttributes[OBJ_DESC]);
            $content->setVariable("FORUM_OWNER", $forumCreator->get_full_name());
            $content->setVariable("FORUM_OWNER_URL", PATH_URL . "profile/index/" . $forumCreator->get_name());
            $content->setVariable("CATEGORIE_DESCRIPTION", $categoryAttributes["bid:description"]);
            $content->setVariable("CATEGORIE_CREATOR", $categoryCreator->get_full_name());
            $content->setVariable("CATEGORIE_CREATION_TIME", date("d.m.Y G:i", $categoryAttributes['OBJ_CREATION_TIME']));
            $content->setVariable("CATEGORIE_CREATOR_URL", PATH_URL . "profile/index/" . $categoryCreator->get_name());
            $content->setVariable("CATEGORIE_CONTENT", $categoryContent);
            if ($canAnnotate) {
                $popupMenu = new \Widgets\PopupMenu();
                $popupMenu->setData($category);
                $popupMenu->setElementId("overlay_menu");
                $popupMenu->setParams(array(array("key" => "forum", "value" => $forumId), array("key" => "category", "value" => $category_id)));
                $content->setVariable("POPUP_MENU", $popupMenu->getHtml());
                $rawHtml->addWidget($popupMenu);
            }

            if ($categoryAttributes[OBJ_CREATION_TIME] != $categoryAttributes["DOC_LAST_MODIFIED"]) {
                if (strlen(trim($categoryContent)) > 0) {
                    $content->setVariable("AUTHOR_EDIT", $categoryAttributes["DOC_USER_MODIFIED"]->get_full_name());
                    $content->setVariable("TIMESTAMP_EDIT", date("d.m.Y G:i", $categoryAttributes["DOC_LAST_MODIFIED"]));
                } else {
                    $content->setVariable("AUTHOR_DELETE", $categoryAttributes["DOC_USER_MODIFIED"]->get_full_name());
                    $content->setVariable("TIMESTAMP_DELETE", date("d.m.Y G:i", $categoryAttributes["DOC_LAST_MODIFIED"]));
                }
            }
            $column_width = 763;
            if ($category->get_attribute("bid:forum:category:picture_id") !== 0) {

                $picture_width = (($category->get_attribute("bid:forum:category:picture_width") != 0) ? $category->get_attribute("bid:forum:category:picture_width") : "");
                if (extract_percentual_length($picture_width) == "") {
                    $bare_picture_width = extract_length($picture_width);
                    if ($bare_picture_width == "") {
                        $picture_width = "";
                    } else if ($bare_picture_width > $column_width - 25) {
                        $picture_width = $column_width - 25;
                    }
                }
                $align = $category->get_attribute("bid:forum:category:picture_alignment");
                if ($align !== "none" && $align != "0") {
                    $content->setVariable("MESSAGE_PICTURE_URL1", getDownloadUrlForObjectId($category->get_attribute("bid:forum:category:picture_id")));
                    $content->setVariable("MESSAGE_PICTURE_ALIGNMENT1", $category->get_attribute("bid:forum:category:picture_alignment"));
                    $content->setVariable("MESSAGE_PICTURE_WIDTH1", $picture_width);
                }else{
                    $content->setVariable("MESSAGE_PICTURE_URL_NONE", getDownloadUrlForObjectId($category->get_attribute("bid:forum:category:picture_id")));
                    $content->setVariable("MESSAGE_PICTURE_WIDTH_NONE", $picture_width);
               
                }
            }

            if (is_array($messages) && isset($messages)) {
                if (count($messages) > 0) {
                    $content->setVariable("EXISTS_REPLY", "Antworten");
                }

                foreach ($messages as $message) {
                    $id = $message->get_id();
                    $content->setCurrentBlock("message");
                    $content->setVariable("MESSAGE_CONTENT", $messageContent[$id]);
                    $content->setVariable("MESSAGE_CREATOR", $messageCreator[$id]->get_full_name());
                    $content->setVariable("MESSAGE_CREATOR_PROFILE", PATH_URL . "profile/index/" . $messageCreator[$id]->get_name());
                    $content->setVariable("MESSAGE_CREATION_TIME", date("d.m.Y G:i", $messageAttributes[$id][OBJ_CREATION_TIME]));
                    $content->setVariable("MESSAGE_NAME", $messageAttributes[$id][OBJ_DESC]);
                    $content->setVariable("MES_ID", "message_" . $id);
                    if ($messageAttributes[$id][OBJ_CREATION_TIME] != $messageAttributes[$id]["DOC_LAST_MODIFIED"]) {
                        if (strlen(trim($messageContent[$id])) > 0) {
                            $content->setVariable("AUTHOR_MES_EDIT", $messageAttributes[$id]["DOC_USER_MODIFIED"]->get_full_name());
                            $content->setVariable("TIMESTAMP_MES_EDIT", date("d.m.Y G:i", $messageAttributes[$id]["DOC_LAST_MODIFIED"]));
                        } else {
                            $content->setVariable("AUTHOR_MES_DELETE", $messageAttributes[$id]["DOC_USER_MODIFIED"]->get_full_name());
                            $content->setVariable("TIMESTAMP_MES_DELETE", date("d.m.Y G:i", $messageAttributes[$id]["DOC_LAST_MODIFIED"]));
                        }
                    }
                    if ($canAnnotate) {
                        $popupMenu2 = new \Widgets\PopupMenu();
                        $popupMenu2->setData($message);
                        $popupMenu2->setElementId("overlay_menu");
                        $popupMenu2->setParams(array(array("key" => "forum", "value" => $forumId), array("key" => "category", "value" => $category_id)));
                        $content->setVariable("POPUP_MENU2", $popupMenu2->getHtml());
                        $rawHtml->addWidget($popupMenu2);
                    }
                    if ($message->get_attribute("bid:forum:category:picture_id") !== 0) {
                        $content->setCurrentBlock("BLOCK_MESSAGE_PICTURE");
                        $picture_width = (($message->get_attribute("bid:forum:category:picture_width") != "0") ? trim($message->get_attribute("bid:forum:category:picture_width")) : "");
                        if (extract_percentual_length($picture_width) == "") {
                            $bare_picture_width = extract_length($picture_width);
                            if ($bare_picture_width == "") {
                                $picture_width = "";
                            } else if ($bare_picture_width > $column_width - 25) {
                                $picture_width = $column_width - 25;
                            }
                        }
                        $align = $message->get_attribute("bid:forum:category:picture_alignment");
                        if ($align !== "none" && $align != "0") {
                            $content->setVariable("MESSAGE_PICTURE_ALIGNMENT", $align);
                            $content->setVariable("MESSAGE_PICTURE_URL", getDownloadUrlForObjectId($message->get_attribute("bid:forum:category:picture_id")));
                            $content->setVariable("MESSAGE_PICTURE_WIDTH", $picture_width);
                        } else {
                            $content->setVariable("MESSAGE_PICTURE_URL_NONE", getDownloadUrlForObjectId($message->get_attribute("bid:forum:category:picture_id")));
                            $content->setVariable("MESSAGE_PICTURE_WIDTH_NONE", $picture_width);
                        }
                        
                    }
                    $content->parse("message");
                }
            }
        } else {
            $content->setVariable("NO_ACCESS", "Sie haben keine Berechtigung diesen Inhalt zu betrachten!
			Bitte wenden Sie sich an die Forumsverwaltung!");
        }

        $rawHtml->setHtml($content->get());

        $actions = array();
        if ($canAnnotate) {
            $actions[] = array("name" => "Antworten", "ajax" => array("onclick" => array("command" => "newReply", "params" => array("id" => $this->id, "forum" => $forumId), "requestType" => "popup")));
        }
        
        $frameResponseObject->setTitle( $forum->get_name()  . " - " . $categoryAttributes["OBJ_DESC"]);
        $parent = $forum->get_environment();
        if ($parent instanceof \steam_container) {
            $parentLink = PATH_URL . "explorer/Index/" . $parent->get_id();
        } else {
            $parentLink = "";
        }
        $title = $categoryAttributes[OBJ_DESC];
        
        $actionbar = new \Widgets\ActionBar();
        $actionbar->setActions($actions);
        $widget = new \Widgets\RawHtml();
        $html = '<div id="backToForum">Zurück zu ';
        $html1 = '<a href="' . PATH_URL . "forum/index/" . $forumId . '">';
        $html2 = $forum->get_name() . '</a></div>';
        $widget->setHtml($html . $html1 . $html2);
        $frameResponseObject->addWidget($actionbar);
        $frameResponseObject->addWidget($rawHtml);
        return $frameResponseObject;
    }

}

?>