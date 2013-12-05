<?php
namespace Webarena\Commands;
class Index extends \AbstractCommand implements \IFrameCommand
{
    private $params;
    private $id;

    public function validateData(\IRequestObject $requestObject)
    {
        return true;
    }

    public function processData(\IRequestObject $requestObject)
    {
        $this->params = $requestObject->getParams();
        isset($this->params[0]) ? $this->id = $this->params[0]: "";
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject)
    {
        $myExtension = \Webarena::getInstance();

        $obj = \steam_factory::get_object($GLOBALS[ "STEAM" ]->get_id(), $this->id);

        if ($obj->get_attribute("isWebarena") == 1) {

            $host = WEBARENA_HOST;
            $port = WEBARENA_PORT;
            $uid = uidv4();

            if ($host == "localhost") {
                $host = $_SERVER['HTTP_HOST'];
            }

            $fp = fsockopen($host, $port);

            if ($fp) {

                $data = http_build_query(
                    array(
                        "id" => $uid,
                        "username" => $_SESSION[ "LMS_USER" ]->get_login(),
                        "password" => $_SESSION[ "LMS_USER" ]->get_password()
                    )
                );

                // send the request headers:
                fputs($fp, "POST /pushSession HTTP/1.1\r\n");
                fputs($fp, "Host: ".$host."\r\n");

                fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
                fputs($fp, "Content-length: ". strlen($data) ."\r\n");
                fputs($fp, "Connection: close\r\n\r\n");
                fputs($fp, $data);

            } else {
                throw new Exception("unable to connect to webarena server");
            }

            fclose($fp);

            header("Location: http://".$host.":".$port."/room/".$this->id."#externalSession/".$_SESSION[ "LMS_USER" ]->get_login()."/".$uid);
        }

        return $frameResponseObject;

    }
}
