<?php
namespace PortletSubscription\Subscriptions;

abstract class AbstractSubscription {

    protected $portlet;
    protected $object;
    protected $private;
    protected $timestamp;
    protected $filter;
    protected $depth;

    public function __construct($portlet, $object, $private, $timestamp, $filter, $depth) {
        $this->portlet = $portlet;
        $this->object = $object;
        $this->private = $private;
        $this->timestamp = $timestamp;
        $this->filter = $filter;
        $this->depth = $depth;
    }

    abstract public function getUpdates();

    protected function getElementHtml($id, $divid, $private, $timestamp, $text, $linktext, $link, $additionalHTML = "") {

        //for deleted item it is not possible to give a exact date.
        if(date("Y", $timestamp)){
            //try to build a date out of the timestamp, then build the correct formatted date
            $dateString = date("d.m.Y H:i", $timestamp) . " Uhr";
        } else {
            //if it fails return a general phrase
            $dateString = "In letzter Zeit";
            $timestamp = -1;
        }

        $html = "<div id=\"subscription" . $this->portlet->get_id() . "_" . $divid . "\">
            <h2 class=\"subheadline\">" . $dateString;
        if ($private === TRUE) {
            //do not show the cross to hide each single changed element but only one button to hide all
            //$sendRequest = AbstractSubscription::getElementJS($this->portlet->get_id(), $id, $timestamp, $divid);
            //$html .= "<div class=\"subscription-close-button\" title=\"ausblenden\" onclick=\"".$sendRequest."\" style=\"float:right;\"></div>";
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
