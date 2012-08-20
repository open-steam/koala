<?php
namespace Forum\Commands;

class Index extends \AbstractCommand implements \IFrameCommand {

	private $id;
	private $params;

	public function validateData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		if (isset($this->params[0])) {
			$this->id = $this->params[0];
			return true;
		} else {
			return false;
		}
	}

	public function processData(\IRequestObject $requestObject) {

	}

	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$rawHtml = new \Widgets\RawHtml();

		$steam=$GLOBALS["STEAM"];

		$objectId = $this->id;

		$myExtension = \Forum::getInstance();

		$forumObject = \steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $objectId );

		$steamUser = $GLOBALS["STEAM"]->get_current_steam_user();
		$lastSessionTime= $steamUser->get_attribute("bid:last_session_time");
		$lastSessionTime = is_array($lastSessionTime) ? intval($lastSessionTime[0]) : intval(time());
			
		/** check the rights of the log-in user */
		$forum_allowed_write = $forumObject->check_access(SANCTION_SANCTION, $steamUser);
		$forum_allowed_read = $forumObject->check_access_read($steamUser);
		$forum_allowed_annotate = $forumObject->check_access_annotate($steamUser);
		if(!$forum_allowed_read)
		throw new \Exception( "You have no permission to read this forum", E_USER_RIGHTS );
			
		$forumAttributes = $forumObject->get_attributes(array(
		OBJ_NAME,
		OBJ_DESC,
		OBJ_CREATION_TIME,
		  "bid:description",
		  "bid:forum_subscription"
		  ));

		  $categories = $forumObject->get_annotations();
		  $forumAnnotations=$categories;
		  foreach ($categories as $category) {
		  	$id = $category->get_id();
		  	$categoryAttributes[$id] = $category->get_attributes(array(OBJ_NAME, OBJ_DESC, OBJ_CREATION_TIME, "bid:description"), 1);
		  	$messages[$id] = $category->get_annotations(false, 1);
		  	$categoryCreator[$id] = $category->get_creator(1);
		  }

		  $result = $steam->buffer_flush();

		  foreach ($categories as $category) {
		  	$id = $category->get_id();
		  	$messages[$id] = $result[$messages[$id]];
		  	$categoryMessageCount[$id] = count($messages[$id]);
		  	sort($messages[$id]);
		  	if ($categoryMessageCount[$id] > 0) {
		  		$categoryLastMessageAttributes[$id] = end($messages[$id])->get_attributes(array(OBJ_NAME, OBJ_DESC, OBJ_CREATION_TIME), 1);
		  		$categoryLastMessageCreator[$id] = end($messages[$id])->get_creator(1);
		  	}
		  	$categoryAttributes[$id] = $result[$categoryAttributes[$id]];
		  	$categoryCreator[$id] = $result[$categoryCreator[$id]];
		  	$categoryCreator[$id]->get_attributes(array(OBJ_NAME), 1);
		  }

		  $result = $steam->buffer_flush();

		  foreach ($categories as $category) {
		  	$id = $category->get_id();
		  	if ($categoryMessageCount[$id] > 0) {
		  		$categoryLastMessageAttributes[$id] = $result[$categoryLastMessageAttributes[$id]];
		  		$categoryLastMessageCreator[$id] = $result[$categoryLastMessageCreator[$id]];
		  		$categoryLastMessageCreator[$id]->get_attributes(array(OBJ_NAME), 1);
		  	}
		  }
		  $result = $steam->buffer_flush();



		  $myExtension->addCSS();

		  $content = $myExtension->loadTemplate("forumIndex.template.html");

		  $content->setCurrentBlock('BLOCK_FORUM_HEAD');
		  $content->setVariable("FORUM_HEADING", urldecode($forumAttributes["OBJ_NAME"]));
		  $content->setVariable("FORUM_UNDERTITLE", ($forumAttributes["OBJ_DESC"] !== 0 ) ? $forumAttributes["OBJ_DESC"] : "");
		  $content->setVariable("FORUM_DESCRIPTION", ($forumAttributes["bid:description"] !== 0 ) ? $forumAttributes["bid:description"] : "");
		  $content->parse('BLOCK_FORUM_HEAD');


		  // sort all forum topics
		  usort($forumAnnotations, "sortTopicsByDate");

		  if (count($forumAnnotations) == 0) {
		  	$content->setVariable("NO_CONTENT", "Dieses Forum enthält keine Themen.");
		  } else {
		  	foreach($forumAnnotations as $annotation) {
		  		$content->setCurrentBlock('BLOCK_FORUM_CONTENT');
		  		$content->setVariable("TOPIC", $annotation->get_attribute("OBJ_DESC"));
		  		$content->setVariable("TOPIC_AUTHOR", getCleanName($annotation->get_attribute("DOC_USER_MODIFIED")));
		  		$content->setVariable("TOPIC_DATE", getReadableDate($annotation->get_attribute("OBJ_CREATION_TIME")));
		  		$content->setVariable("LINK_SHOW_TOPIC", PATH_URL."forum/showTopic/".$objectId."/".$annotation->get_id());
		  		$annotationsArray = $annotation->get_annotations();
                                $count = count($annotationsArray);
		  		$content->setVariable("REPLY_COUNT", $count);
		  		if ($count > 0) {
                                        $lastUser = $annotationsArray[0]->get_attribute("DOC_USER_MODIFIED");
                                        $content->setVariable("LAST_REPLY_TOPIC", $categoryLastMessageAttributes[$annotation->get_id() ][OBJ_DESC] );
		  			$content->setVariable("LAST_REPLY_DATE", date("d.m.Y G:i", $annotation->get_attribute("OBJ_LAST_CHANGED")));
		  			$content->setVariable("LAST_REPLY_USER", getCleanName($lastUser));
		  			$lastPostTime=$annotation->get_attribute("OBJ_CREATION_TIME");
		  			if($lastSessionTime < $lastPostTime){
		  				$imageUrl=\Forum::getInstance()->getAssetUrl()."icons/new_message_info.gif";
		  				//$content->setVariable("NEW_IMAGE", '<img src="'.$imageUrl.'" title="Neue Antworten.">');
		  			}
		  		}


		  		//	$popupMenu= new \Widgets\PopupMenu();
		  		//	$popupMenu->setData($annotation);
		  		//	$popupMenu->setElementId("overlay_menu");
		  		//	$popupMenu->setParams(array(array("key" => "forum", "value" => $objectId)));
		  		//	$content->setVariable("POPUP_MENU", $popupMenu->getHtml());
		  		//	$rawHtml->addWidget($popupMenu);
		  		$content->parse('BLOCK_FORUM_CONTENT');
		  	}
		  }

		  $actionBar = new \Widgets\ActionBar();
		  $actions = array();
		  if ($forum_allowed_annotate) {
		  	$actions[] = array("name" => "Neues Thema", "ajax"=>array("onclick"=>array("command"=>"newTopic", "params"=>array("id"=>$this->id), "requestType"=>"popup")));
		  }
		  if ($forum_allowed_write) {
		  	$actions[] = array("name"=>"Eigenschaften", "ajax"=>array("onclick"=>array("command"=>"Properties", "params"=>array("id"=>$this->id), "requestType"=>"popup", "namespace"=>"explorer")));
		  	$actions[] = array("name"=>"Rechte", "ajax"=>array("onclick"=>array("command"=>"Sanctions", "params"=>array("id"=>$this->id), "requestType"=>"popup", "namespace"=>"explorer")));
		  }
		  $actionBar->setActions($actions);

		  //$breadcrumb = new \Widgets\Breadcrumb();
		  //$breadcrumb->setData(array($currentRoom));
                  
		  $frameResponseObject->setTitle($forumObject->get_name());
		  $rawHtml->setHtml($content->get());
		  $frameResponseObject->addWidget($actionBar);
		  $frameResponseObject->addWidget($rawHtml);
		  return $frameResponseObject;
	}



}
?>