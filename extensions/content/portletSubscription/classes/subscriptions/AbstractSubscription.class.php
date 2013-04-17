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
        $html = "<div id=\"subscription" . $this->portlet->get_id() . "_" . $divid . "\">
            <h2 class=\"subheadline\">" .  date("d.m.Y H:i", $timestamp) . " Uhr";
        if ($private === TRUE) {
            $params = "{ id : " . $this->portlet->get_id() . ", objectID : " . $id . ", timestamp : " . $timestamp . ", hide : 'subscription" . $this->portlet->get_id() . "_" . $divid . "' }";
            $html .= "<a href=\"javascript:sendRequest('HideItem', " . $params . ", 'subscription" . $this->portlet->get_id() . "_" . $divid . "', 'updater', '', '', 'PortletSubscription');\" style=\"float:right;\">Ausblenden</a>";
        }
        
        $html .= "</h2>
            <h3>" . $text . " <a href=\"" . $link . "\">" . $linktext . "</a>" . $additionalHTML . "</h3>
            </div>";
        return $html;
    }
}
?>
