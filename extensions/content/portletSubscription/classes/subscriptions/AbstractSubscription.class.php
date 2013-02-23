<?php
namespace PortletSubscription\Subscriptions;

abstract class AbstractSubscription {
    
    protected $portlet;
    protected $object;
    protected $private;
    protected $timestamp;
    protected $filter;
    
    public function __construct($portlet, $object, $private, $timestamp, $filter) {
        $this->portlet = $portlet;
        $this->object = $object;
        $this->private = $private;
        $this->timestamp = $timestamp;
        $this->filter = $filter;
    }
    
    abstract public function getUpdates();
    
    protected function getElementHtml($id, $private, $timestamp, $text, $linktext, $link) {
        $html = "<div>
            <h2 class=\"subheadline\">" .  date("d.m.Y H:i", $timestamp) . " Uhr";
        if ($private === TRUE) {
            $params = "{ id : " . $this->portlet->get_id() . ", objectID : " . $id . ", timestamp : " . $timestamp . " }";
            $html .= "<a href=\"javascript:sendRequest('HideItem', " . $params . ", '" . $this->portlet->get_id() . "', 'reload', '', '', 'PortletSubscription');\" style=\"float:right;\">Ausblenden</a>";
        }
        
        $html .= "</h2>
            <h3>" . $text . " <a href=\"" . $link . "\">" . $linktext . "</a></h3>
            </div>";
        return $html;
    }
}
?>
