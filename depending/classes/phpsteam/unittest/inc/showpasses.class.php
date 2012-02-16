<?php
class showpasses extends HtmlReporter {
    
    function paintPass($message) {
        parent::paintPass($message);
        print "<span class=\"pass\">Pass</span>: ";
        $breadcrumb = $this->getTestList();
        array_shift($breadcrumb);
        //print implode("->", $breadcrumb);
        print "&#x2192; $message<br />\n";
    }
    
}
?>