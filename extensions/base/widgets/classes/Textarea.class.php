<?php
namespace Widgets;

class Textarea extends Widget {
    
    private $id;
    private $label;
    private $labelClass = ""; // texthidden or hidden
    private $data;
    private $contentProvider;
    private $leaveMessage = "Beim Verlassen der Seite gehen alle nicht gespeicherten Änderungen verloren.";
    
    private $width = "250px";
    private $height = "200px";
    private $textareaClass = ""; // plain or code html or mce-small or mce-full
    private $linebreaks = "<br><br>";
    
    public function setId($id){
        $this->id = "id_".$id."_textarea";
    }

    public function setLabel($label) {
        $this->label = $label;
    }

    /**
     * unused at the moment
     * @param type $data
     */
    public function setData($data) {
        $this->data = $data;
    }

    public function setContentProvider($contentProvider) {
        $this->contentProvider = $contentProvider;
    }
    
    public function setLeaveMessage($leaveMessage) {
        $this->leaveMessage = $leaveMessage;
    }

    /**
     * @param type $width width in pixels (without px at the end)
     */
    public function setWidth($width) {
        $this->width = $width . "px";
    }

    /**
     * @param type $height height in pixels (without px at the end)
     */
    public function setHeight($height) {
        $this->height = $height . "px";
    }

    public function setTextareaClass($textareaClass) {
        $this->textareaClass = $textareaClass;
    }

    public function setAutosave($autosave) {
        $last=next(debug_backtrace());
        \logging::write_log( LOG_MESSAGES, "The function setAutoSave is deprecated. Called in Class: ". $last['class']. " function: ". $last['function']);
        
    }
    
    public function setLinebreaks($linebreaks) {
        $this->linebreaks = $linebreaks;
    }

    public function getHtml() {
        if(!isset($this->id)){
            $this->setId(rand());
        }
        $this->getContent()->setVariable("ID", $this->id);
        $id2 = $this->id;
        $this->getContent()->setVariable("ID2", $id2);
        $this->getContent()->setVariable("PATH_URL", PATH_URL);

        //write sanction
        if ($this->contentProvider && !$this->contentProvider->isChangeable($this->data)) {
            $this->getContent()->setVariable("READONLY", "disabled");
            //this value is read in the tinyMCE init function
            $this->getContent()->setVariable("READONLY_JS", "var tinymceReadOnly=true");
        }else{
            $this->getContent()->setVariable("READONLY_JS", "var tinymceReadOnly=false");
        }

        

        if ($this->contentProvider) {
            $currentValue = rawurlencode($this->contentProvider->getData($this->data));
            
            
            $this->getContent()->setVariable("SAVE_FUNCTION", $this->contentProvider->getUpdateCode($this->data, $this->id));
        } else {
            $currentValue = "";
        }

        if (isset($this->label)) {
            $this->getContent()->setVariable("LABEL", $this->label . ":");
        } else {
            if ($this->labelClass === "") {
                $this->labelClass = "texthidden";
            }
        }

        $this->getContent()->setVariable("LINEBREAKS", $this->linebreaks);
        $this->getContent()->setVariable("VALUE", $currentValue);
        $this->getContent()->setVariable("LEAVE_MESSAGE", $this->leaveMessage);


        $this->getContent()->setVariable("ADDITIONAL_LABEL_CLASSES", $this->labelClass);
        $this->getContent()->setVariable("CUSTOM_TEXTAREA_STYLE", "width: {$this->width}; height: {$this->height}");
        $this->getContent()->setVariable("ADDITIONAL_TEXTAREA_CLASSES", $this->textareaClass);

        $this->setPostJsCode("$('#{$this->id}').textarea({  " // calls the init method
                                ."id : '{$this->id}',"
                                . "value : '{$currentValue}',"
                                . "sendFunction : function(value) { {$this->contentProvider->getUpdateCode($this->data, $this->id, "widgets_textarea_save_success")} } "
                           . "});");    
                 
        //create a PollingDummy to send some requests from time to time to avoid session expiring while typing long texts
        $pollingDummy = new PollingDummy();

        return $this->getContent()->get().$pollingDummy->getHtml();
    }
}
?>