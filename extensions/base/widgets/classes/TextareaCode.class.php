<?php
namespace Widgets;

class TextareaCode extends Widget {

    
    private $id;
    private $label;
    private $data;
    private $contentProvider;
    private $width = "250px";
    private $rows = 10;

    
    public function setId($id){
        $this->id = "id_".$id."_textarea_code";
    }
    
    public function getId(){
        if(!isset($this->id)){
           $this->setId(rand());
        }
        return $this->id;
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

    /**
     * @param type $width width in pixels (without px at the end)
     */
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
        
        if(!isset($this->id)){
            $this->setId(rand());
        }
        
        $this->getContent()->setVariable("ID", $this->id);

        if (isset($this->label)) {
                $this->getContent()->setVariable("LABEL", $this->label . ":");
        }

        $this->getContent()->setVariable("WIDTH", $this->width);
        $this->getContent()->setVariable("ROWS", $this->rows);

        if ($this->contentProvider) {   
            $this->getContent()->setVariable("SAVE_FUNCTION", $this->contentProvider->getUpdateCode($this->data, $this->id));
            $this->getContent()->setVariable("VALUE", htmlentities($this->contentProvider->getData($this->data)));
        } else {
            $this->getContent()->setVariable("VALUE", " ");
        }

        $this->setPostJsCode("var delay;
                                  // Initialize CodeMirror editor with a nice html5 canvas demo.
                                  var editor = CodeMirror.fromTextArea(document.getElementById('{$this->id}'), {
                                      mode: 'text/html',
                                      tabMode: 'indent',
                                      lineNumbers: true,
                                      onCursorActivity: function() {
                                          editor.setLineClass(hlLine, null);
                                          hlLine = editor.setLineClass(editor.getCursor().line, 'activeline');
                                      },
                                      onChange: function() {
                                          //mark the field as changed and save the current value in the corresponring variable
                                          $('#{$this->id}').addClass('changed');
                                          {$this->id} = editor.getValue();
                                          clearTimeout(delay);
                                          delay = setTimeout(updatePreview, 300);
                                      }
                                  });

                                  var hlLine = editor.setLineClass(0, 'activeline');

                                  function updatePreview() {
                                      frames.preview.document.getElementsByTagName('body')[0].innerHTML = editor.getValue();
                                  }
                                  setTimeout(updatePreview, 300);
                                  ");

        //create a PollingDummy to send some requests from time to time to avoid session expiring while typing long texts
        $pollingDummy = new PollingDummy();

        return $this->getContent()->get().$pollingDummy->getHtml();
    }
}
?>