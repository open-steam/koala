<?php

namespace Upload\Commands;

class To extends \AbstractCommand implements \IFrameCommand {

    private $params;
    private $id;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
        isset($this->params[0]) ? $this->id = $this->params[0] : "";
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject) {
        require_once( PATH_LIB . "format_handling.inc.php" );

        $env = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);

        if (!($env && $env instanceof \steam_container)) {
            throw new Exception("Environment unknown.");
        }

        $koala_env = \koala_object::get_koala_object($env);

        $backlink = ( empty($_POST["values"]["backlink"]) ) ? $_SERVER["HTTP_REFERER"] : $_POST["values"]["backlink"];

        $max_file_size = parse_filesize(ini_get('upload_max_filesize'));
        $max_post_size = parse_filesize(ini_get('post_max_size'));
        if ($max_post_size > 0 && $max_post_size < $max_file_size)
            $max_file_size = $max_post_size;

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $values = isset($_POST["values"]) ? $_POST["values"] : array();
            $problems = "";
            $hints = "";
            if (empty($_FILES) || (!empty($_FILES["material"]["error"]) && $_FILES["material"]["error"] > 0 )) {
                if (!empty($_FILES) && empty($_FILES["material"]["name"])) {
                    $problems = gettext("No file chosen.") . " ";
                    $hints = gettext("Please choose a local file to upload.") . " ";
                } else {
                    $problems = gettext("Could not upload document.") . " ";
                    $hints = str_replace(
                                    array("%SIZE", "%TIME"), array(readable_filesize($max_file_size), (string) ini_get('max_execution_time')), gettext("Maybe your document exceeded the allowed file size (max. %SIZE) or the upload might have taken too long (max. %TIME seconds).")
                            ) . " ";
                }
            }
            if (empty($problems)) {
                $content = file_get_contents($_FILES["material"]["tmp_name"]);
                /*
                  ob_start();
                  readfile( $_FILES["material"]["tmp_name"] );
                  $content = ob_get_contents();
                  ob_end_clean();
                 */
                if (defined("LOG_DEBUGLOG")) {
                    $time1 = microtime(TRUE);
                    \logging::write_log(LOG_DEBUGLOG, "upload" . " \t" . $GLOBALS["STEAM"]->get_login_user_name() . " \t" . $_FILES["material"]["name"] . " \t" . filesize($_FILES["material"]["tmp_name"]) . " Bytes \t... ");
                }
                $filename = str_replace(array("\\", "'"), array("", ""), $_FILES["material"]["name"]);
                $new_material = \steam_factory::create_document(
                                $GLOBALS["STEAM"]->get_id(), $filename, $content, $_FILES["material"]["type"], FALSE
                );
                if (defined("LOG_DEBUGLOG")) {
                    \logging::append_log(LOG_DEBUGLOG, " \t" . round((microtime(TRUE) - $time1) * 1000) . " ms");
                }
                //  Disabled for Testing issues
                // upload($new_material->get_content_id(), $content);
                if (isset($values["dsc"]))
                    $new_material->set_attribute("OBJ_DESC", $values["dsc"]);
                $new_material->move($env);

                $_SESSION["confirmation"] = str_replace(
                        "%DOCUMENT", h($filename), gettext("'%DOCUMENT' has been uploaded.")
                );

                header("Location: " . $backlink);
                die;
            }
            else {
                $frameResponseObject->setTitle(gettext("Upload document"));
                $frameResponseObject->setProblemDescription($problems);
                $frameResponseObject->setProblemSolution($hints);
                $frameResponseObject->addWidget(new \Widgets\Blank());
                return $frameResponseObject;
            }
        }


        $content = \Upload::getInstance()->loadTemplate("upload.template.html");
        $content->setVariable("LABEL_UPLOAD", gettext("Upload"));
        $content->setVariable("LABEL_FILE", gettext("Local file"));
        $content->setVariable("LABEL_DSC", gettext("Description"));
        $content->setVariable("BACKLINK", "<a href=\"$backlink\">" . gettext("back") . "</a>");
        $content->setVariable("BACK_LINK", $backlink);

        $content->setVariable("FORM_ACTION", PATH_URL . "upload/to/" . $this->id);

        if ($max_file_size > 0) {
            $content->setVariable("MAX_FILE_SIZE_INPUT", "<input type='hidden' name='MAX_FILE_SIZE' value='" . (string) $max_file_size . "'/>");
            $content->setVariable("MAX_FILE_SIZE_INFO", "<br />" . str_replace("%SIZE", readable_filesize($max_file_size), gettext("The maximum allowed file size is %SIZE.")));
        }

        $link_path = $koala_env->get_link_path();
        if (!is_array($link_path))
            $link_path = array();
        $link_path[] = array("name" => gettext("Upload document"));

        $rawHtml = new \Widgets\RawHtml();
        $rawHtml->setHtml($content->get());
        $frameResponseObject->setTitle(gettext("Upload document"));
        $frameResponseObject->setHeadline($link_path);
        $frameResponseObject->addWidget($rawHtml);
        return $frameResponseObject;
    }

}

?>