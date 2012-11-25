<?php

namespace Portal\Commands;

require_once 'ical/When.php';
require_once 'ical/When_Iterator.php';
require_once 'ical/reader.php';

class Test extends \AbstractCommand implements \IFrameCommand {

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
        isset($this->params[0]) ? $this->id = $this->params[0] : "";
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject) {


        $data_to_send = '<?xml version="1.0" encoding="utf-8" ?>
<C:free-busy-query xmlns:C="urn:ietf:params:xml:ns:caldav">
<C:time-range start="20060104T140000Z" end="20060105T220000Z"/>
</C:free-busy-query>';
        $fp = fsockopen("www.google.com", 80);
        if (!$fp) {
            echo "Keine Verbindung möglich!";
        } else {
            echo "Verbindung hergestellt!";
            fputs($fp, "POST /calendar/dav/christoph.sens@gmail.com/user HTTP/1.1\r\n");
            fputs($fp, "Host: www.google.com\r\n");
            fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
            fputs($fp, "Content-length: " . strlen($data_to_send) . "\r\n");
            fputs($fp, "Connection: close\r\n\r\n");
            fputs($fp, $data_to_send);
            $res = "";
            while (!feof($fp)) {
                $res .= fgets($fp, 128);
            }
            printf("Done!\n");
            fclose($fp);

            echo $res;
        }
        die;



        return $frameResponseObject;
    }

}

