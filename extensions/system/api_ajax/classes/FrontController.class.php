<?php

class FrontController {

    public function handleRequest(AjaxRequestObject $ajaxRequestObject, AjaxResponseObject $ajaxResponseObject) {
        $response = new HttpResponse();
        $extension = ExtensionMaster::getInstance()->getExtensionForNamespace($ajaxRequestObject->getNamespace());
        if (isset($extension)) {
            if ($extension instanceof ICommandAdapter) {
                if ($ajaxRequestObject->getCommand() != "") {
                    if ($ajaxRequestObject->getCommand() == "databinding") {
                        $command = new Databinding();
                    } else if ($ajaxRequestObject->getCommand() == "DatabindingURLEncodeName") {
                        $command = new DatabindingURLEncodeName();
                    } else {
                        if ($extension->isValidCommand($ajaxRequestObject->getCommand())) {
                            $command = $extension->getCommand($ajaxRequestObject->getCommand());
                        }
                    }
                    if ($command instanceof IAjaxCommand && $command->validateData($ajaxRequestObject)) {
                        try {
                            $command->processData($ajaxRequestObject);
                            $ajaxResponseObject = $command->ajaxResponse($ajaxResponseObject);
                        } catch (Exception $e) {
                            if(!$e instanceof AjaxLogOutException) {
                                $response->setStatus("400 Bad Request");
                                $response->write("Command processing error\n");
                                $response->write($e->getMessage() . "\n" . $e->getTraceAsString());
                                $response->flush();
                            } else {
                                throw $e; //bypass the AjaxLogOutException exception that it can be catched by the AJAX handler
                            }
                            if (DEVELOPMENT_MODE) {
                                throw $e;
                            }
                            die;
                        }
                        if ($ajaxResponseObject instanceof AjaxResponseObject) {
                            $data = \Widgets\Widget::getData($ajaxResponseObject->getWidgets());
                            $stat = "";
                            if ($_SESSION["STATISTICS_LEVEL"] > 0) {
                                $stat = "console.log('Serveranfragen: " . $GLOBALS["STEAM"]->get_request_count() . " / " . $GLOBALS["STEAM"]->get_globalrequest_count() . "');";
                            }
                            if ($_SESSION["STATISTICS_LEVEL"] > 1 && isset($GLOBALS["page_time_start"])) {
                                $requestMap = $GLOBALS["STEAM"]->get_globalrequest_map();
                                $requestTime = $GLOBALS["STEAM"]->get_globalrequest_time();
                                $requestString = "";
                                foreach ($requestMap as $method => $count) {
                                    $requestString .= "console.log('Methode {$method} -> {$count} mal in " . round($requestTime[$method] * 1000) . " ms');";
                                }
                                $stat .= "console.log('Zeit: " . round((microtime(TRUE) - $GLOBALS["page_time_start"]) * 1000) . " ms');" . $requestString;
                            }
                            header("Content-type: text/plain");
                            $response->write(json_encode(array("status" => $ajaxResponseObject->getStatus(), "html" => $data["html"], "data" => $ajaxResponseObject->getData(), "css" => $data["css"], "js" => $data["js"] . $stat, "postjs" => $data["postjs"])));
                            $response->flush();
                            die;
                        } else {
                            $response->setStatus("400 Bad Request");
                            $response->write("Wrong response type for \"{$ajaxRequestObject->getCommand()}\"");
                            $response->flush();
                            die;
                        }
                    } else {
                        $response->setStatus("400 Bad Request");
                        $response->write("Command \"{$ajaxRequestObject->getCommand()}\" not valid.");
                        $response->flush();
                        die;
                    }
                } else {
                    $response->setStatus("400 Bad Request");
                    $response->write("Command parameter missing.");
                    $response->flush();
                    die;
                }
            } else {
                $response->setStatus("400 Bad Request");
                $response->write("Extension doesn't support commands.");
                $response->flush();
                die;
            }
        } else {
            $response->setStatus("400 Bad Request");
            $response->write("Not extension found for url");
            $response->flush();
            die;
        }
    }

}

?>