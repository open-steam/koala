<?php
namespace Widgets;

class Textarea extends Widget {
	private $label;
	private $labelClass = ""; // texthidden or hidden
	private $data;
	private $contentProvider;
	private $id;
	private $width = "250px";
	private $height = "200px";
	private $textareaClass = "plain"; // plain or code html or mce-small or mce-full
	private $autosave = false;
	
	public function setLabel($label) {
		$this->label = $label;
	}
	
	public function setData($data) {
		$this->data = $data;
	}
	
	public function setContentProvider($contentProvider) {
		$this->contentProvider = $contentProvider;
	}
	
	public function setWidth($width) {
		$this->width = $width . "px";
	}
	
	public function setHeight($height) {
		$this->height = $height . "px";
	}
	
	public function setTextareaClass($textareaClass) {
		$this->textareaClass = $textareaClass;
	}
	
	public function setAutosave($autosave) {
		$this->autosave = $autosave;
	}
	
	public function getHtml() {
		$this->id = rand();
                $this->getContent()->setVariable("PATH_URL", PATH_URL);
		$this->getContent()->setVariable("ID", $this->id);
		
		$additinalWidgetClasses = "";
		if ($this->autosave) {
			$additinalWidgetClasses .= "autosave";
		}
		
		$this->getContent()->setVariable("ADDITINAL_WIDGET_CLASSES", $additinalWidgetClasses);
		
		if ($this->contentProvider) {
			$currentValue = rawurlencode($this->contentProvider->getData($this->data));
		} else {
			$currentValue = "";
		}
		//$this->getContent()->setVariable("WIDGET_VALUE", $currentValue);
		//$this->getContent()->setVariable("WIDGET_OLD_VALUE", $currentValue);
		//$this->getContent()->setVariable("VALUE", $currentValue);
		
		if (isset($this->label)) {
			$this->getContent()->setVariable("LABEL", $this->label . ":");
		} else {
			if ($this->labelClass === "") {
				$this->labelClass = "texthidden";
			}
		}
		
                //write sanction
                if ($this->contentProvider && !$this->contentProvider->isChangeable($this->data)) {
                        $this->getContent()->setVariable("READONLY", "disabled");
                }
                
                $this->getContent()->setVariable("ADDITINAL_LABEL_CLASSES", $this->labelClass);
		$this->getContent()->setVariable("CUSTOM_TEXTAREA_STYLE", "width: {$this->width}; height: {$this->height}");
		$this->getContent()->setVariable("ADDITINAL_LABEL_CLASSES", $this->textareaClass);
		
		$this->setPostJsCode(<<<END
								
			$('#{$this->id}').textarea({  // calls the init method
				value : '{$currentValue}',
				sendFunction : function(value) { {$this->contentProvider->getUpdateCode($this->data, $this->id, "widgets_textarea_save_success")} } 
			});
			$('#{$this->id}').find('.button.save').bind('click', function() { $('#{$this->id}').textarea('save'); });
			$('#{$this->id}').find('.button.undo').bind('click', function() { $('#{$this->id}').textarea('undo'); });

END
				);	
		return $this->getContent()->get();
	}
}
?>