<?php
namespace Spreadsheets\Commands;

/**
 * This Command creates a new spreadsheet document with the given ID.
 * The Command will be called from the dialog in "NewSpreadsheetForm"
 */
class Create extends \AbstractCommand implements \IAjaxCommand
{
    private $params, $id;

    public function validateData(\IRequestObject $requestObject)
    {
        return true;
    }

    public function processData(\IRequestObject $requestObject)
    {
        if ($requestObject instanceof \AjaxRequestObject) {
            $this->params = $requestObject->getParams();
            isset($this->params["id"]) ? $this->id = $this->params["id"]: "";
        }
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject)
    {
        $this->newSpreadsheet($this->params["title"]);
        $ajaxResponseObject->setStatus("ok");

        //user will be forwarded to the explorer after creation of the document
        $path = PATH_URL;
        $jswrapper = new \Widgets\JSWrapper();
        $jswrapper->setJs(<<<END
        closeDialog();
        location.href = '{$path}explorer/Index/{$this->id}';
END
        );
        $ajaxResponseObject->addWidget($jswrapper);

        return $ajaxResponseObject;
    }

    private function newSpreadsheet($name)
    {
        //set default size for the spreadsheet if no size is specified
        isset($this->params["rows"]) ? $row_count = $this->params["rows"]: 10;
        isset($this->params["columns"]) ? $col_count = $this->params["columns"]: 5;

        if (empty($row_count)) {
            $row_count = 10;
        }

        if (empty($col_count)) {
            $col_count = 5;
        }

        //create new sTeam document in current container
        $spreadsheetExtension = \Spreadsheets::getInstance();
        $container = \steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $this->id);
        $document = \steam_factory::create_document($GLOBALS["STEAM"]->get_id(), $name, "", "application/json", $container);
        $document->set_attribute("OBJ_TYPE", "document_spreadsheet");
        $document->set_content($this->createJsonDocument(1, $row_count, $col_count));
    }

    private function createJsonDocument($sheet_count, $row_count, $col_count)
    {
        //creates a new spreadsheet document as a JSON-Object
        $sheets = array();
        $cols = array();
        $col_info = array();
        $rows = array();
        $users = array(''=>'');

        for ($col = 0; $col < $col_count; $col++) {
            $col_info[] = array('size' => 120);
            $cols[] = array('value' => "", 'style' =>  array('flags' => array('styleLeft' => true)));
        }

        for ($row = 0; $row < $row_count; $row++) {
            $rows[] = array('cells' => $cols, 'size' => 20);
        }

        for ($sheet = 0; $sheet < $sheet_count; $sheet++) {
            $sheets[] = array('rows' => $rows, 'columns' => $col_info);
        }

        return json_encode(array('sheets' => $sheets, 'users' => $users));
    }
}
