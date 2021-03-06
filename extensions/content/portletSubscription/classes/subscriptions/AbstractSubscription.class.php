<?php
namespace PortletSubscription\Subscriptions;

abstract class AbstractSubscription {

    protected $portlet;
    protected $object;
    protected $private;
    protected $timestamp;
    protected $filter;
    protected $depth;
    protected $updates;
    protected $count;
    protected $formerContent;
    protected $changedFormerContent;
    protected $content;

    public function __construct($portlet, $object, $private, $timestamp, $filter, $depth) {
        $this->portlet = $portlet;
        $this->object = $object;
        $this->private = $private;
        $this->timestamp = $timestamp;
        $this->filter = $filter;
        $this->depth = $depth;
        $this->updates = array();
        $this->count = 0;
        $this->formerContent = $this->portlet->get_attribute("PORTLET_SUBSCRIPTION_CONTENT");
        $this->changedFormerContent = false;
    }

    abstract public function getUpdates();

    protected function getElementHtml($id, $divid, $private, $timestamp, $text, $linktext, $link, $additionalHTML = "") {

        //for deleted item it is not possible to give a exact date.
        if(is_numeric($timestamp) && date("Y", $timestamp)){
            //try to build a date out of the timestamp, then build the correct formatted date
            $dateString = date("d.m.Y H:i:s", $timestamp) . " Uhr";
        } else {
            //if it fails return a general phrase
            $dateString = "In letzter Zeit";
            $timestamp = -1;
        }

        $html = "<div id=\"subscription" . $this->portlet->get_id() . "_" . $divid . "\">
            <h2 class=\"subheadline\">" . $dateString;
        if ($private === TRUE) {
            //do not show the cross to hide each single changed element but only one button to hide all
            $sendRequest = AbstractSubscription::getElementJS($this->portlet->get_id(), $id, $timestamp, $divid);
            $html .= "<div class=\"subscription-close-button blueeye\" title=\"Ausblenden\" onclick=\"".$sendRequest."\" style=\"float:right;\"><svg style='width:16px; height:16px;' ><use xlink:href='" . PATH_URL . "/widgets/asset/eye.svg#eye'/></svg></div>";
        }

        $html .= "</h2>
            <h3>" . $text . " <a href=\"" . $link . "\">" . $linktext . "</a>" . $additionalHTML . "</h3>
            </div>";
        return $html;
    }

    public static function getElementJS($portletId, $objectId, $timestamp, $divid){
        $params = "{ portletID : " . $portletId . ", objectID : " . $objectId . ", timestamp : " . $timestamp . ", hide : 'subscription" . $portletId . "_" . $divid . "' }";
        return "sendRequest('HideItem', " . $params . ", 'subscription" . $portletId . "_" . $divid . "', 'updater', '', '', 'PortletSubscription');";
    }
}
?>
