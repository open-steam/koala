<?php
namespace Widgets;

class TextareaCode extends Widget {
	private $label;
	private $data;
	private $contentProvider;
	private $id;
	private $objectId;
	private $width = "250px";
	private $rows = 10;
	
	public function setLabel($label) {
		$this->label = $label;
	}
	
	public function setData($data) {
		$this->data = $data;
		if (is_int($data)) {
			$this->objectId = $data;
		} else {
			$this->objectId = $data->get_id();
		}
	}
	
	public function setContentProvider($contentProvider) {
		$this->contentProvider = $contentProvider;
	}
	
	public function setWidth($width) {
		$this->width = $width . "px";
	}
	
	public function setRows($rows) {
		$this->rows = $rows;
	}
	
	public function disableTMCE() {
		$this->disableTMCE = true;
	}
	
	public function getHtml() {
		$this->id = rand();
		if (isset($this->label)) {
			$this->getContent()->setVariable("LABEL", $this->label . ":");
		}
		$this->getContent()->setVariable("ID", $this->id);
		$this->getContent()->setVariable("WIDTH", $this->width);
		$this->getContent()->setVariable("ROWS", $this->rows);
		if ($this->contentProvider) {
			$this->getContent()->setVariable("VALUE", $this->contentProvider->getData($this->data));
		} else {
			$this->getContent()->setVariable("VALUE", " ");
		}
		$this->getContent()->setVariable("SAVE_FUNCTION", "onClick=\"value = editor.getValue(); widgets_textareacode_save({$this->id});{$this->contentProvider->getUpdateCode($this->data, $this->id, "widgets_textareacode_save_success")}\"");
		$this->getContent()->setVariable("UNDO_FUNCTION", "onClick=\"value = jQuery('#{$this->id}').attr('oldValue'); widgets_textareacode_save({$this->id});{$this->contentProvider->getUpdateCode($this->data, $this->id, "widgets_textareacode_undo_success")}\"");
		$this->setPostJsCode(<<<END
var delay;
// Initialize CodeMirror editor with a nice html5 canvas demo.
var editor = CodeMirror.fromTextArea(document.getElementById('{$this->id}'), {
	mode: 'text/html',
    tabMode: 'indent',
    lineNumbers: true,
    onCursorActivity: function() {
      editor.setLineClass(hlLine, null);
      hlLine = editor.setLineClass(editor.getCursor().line, "activeline");
    },
    onChange: function() {
      widgets_textareacode_changed({$this->id});
      clearTimeout(delay);
      delay = setTimeout(updatePreview, 300);
    }
});
  
var hlLine = editor.setLineClass(0, "activeline");
  
function updatePreview() {
    frames.preview.document.getElementsByTagName('body')[0].innerHTML = editor.getValue();
}
setTimeout(updatePreview, 300);
END
);
		return $this->getContent()->get();
	}
}
?>