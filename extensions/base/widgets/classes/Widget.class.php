<?php

namespace Widgets;

abstract class Widget {

    private $content;
    private $widgets = array();
    private $postJsCode;

    public function __construct() {
        $myExtension = \Widgets::getInstance();
        $templateName = $this->right(get_class($this), "\\") . ".template.html";
        $this->content = $myExtension->loadTemplate($templateName);
    }

    public function getContent() {
        return $this->content;
    }

    public function getWidgets() {
        return $this->widgets;
    }

    private function right($string, $chars) {
        $vright = substr($string, strpos($string, $chars) + 1, strlen($string));
        return $vright;
    }

    abstract public function getHtml();

    public function getName() {
        $name = substr(get_class($this), strrpos(get_class($this), "\\") + 1);
        return $name;
    }

    public function addWidget($widget) {
        if (!$widget || !($widget instanceof \Widgets\Widget)) {
            throw new \Exception("Fehler in Widget.class.php");
        }
        if ($this === $widget) {
            //TODO
            throw new \Exception("---" . var_export($widget, true) . "-----" . var_export($this, true) . "---" . "Can't add widget on itself!");
        }
        if (!$this->widgets) {
            $this->widgets = array();
        }
        $this->widgets[] = $widget;
    }

    public function addWidgets($widgets) {
        if (is_array($widgets) || $widgets instanceof Traversable) {
            foreach ($widgets as $widget) {
                $this->addWidget($widget);
            }
        }
    }

    public function getPostJsCode() {
        $result = array();
        if (isset($this->postJsCode) && $this->postJsCode != "") {
            $result[] = $this->postJsCode;
        }
        if ($this->widgets) {
            foreach ($this->widgets as $widget) {
                $result = array_merge($result, $widget->getPostJsCode());
            }
        }
        return $result;
    }

    public function getJsCode() {
        $result = array();
        $fileName = $this->getName() . ".js";
        $jsCode = \Widgets::getInstance()->readJS($fileName);
        if ($jsCode && $jsCode != "") {
            $result[get_class($this)] = $jsCode;
        }
        if ($this->widgets) {
            foreach ($this->widgets as $widget) {
                $result = array_merge($result, $widget->getJsCode());
            }
        }
        return $result;
    }

    public function getCssStyle() {
        $result = array();
        $fileName = $this->getName() . ".css";
        $cssStyle = \Widgets::getInstance()->readCSS($fileName);
        if ($cssStyle && $cssStyle != "") {
            $result[get_class($this)] = $cssStyle;
        }
        if ($this->widgets) {
            foreach ($this->widgets as $widget) {
                $result = array_merge($result, $widget->getCssStyle());
            }
        }
        return $result;
    }

    public function setPostJsCode($postJsCode) {
        $this->postJsCode = $postJsCode;
    }

    public static function getData($widgets) {
        $result = array();
        $content = "";
        $cssStyle = "";
        $jsCode = "";
        $postJsCode = "";
        if ($widgets != null) {
            if (!is_array($widgets)) {
                throw new Exception("Widgets nicht im richtigen Format!");
            }
            foreach ($widgets as $widget) {
                $content .= $widget->getHtml();

                $stylesArray = $widget->getCssStyle();
                foreach ($stylesArray as $widgetClass => $style) {
                    $cssStyle .= $style . "\n";
                }
                $codesArray = $widget->getJsCode();
                foreach ($codesArray as $widgetClass => $code) {
                    $jsCode .= $code . "\n";
                }
                $postCodesArray = $widget->getPostJsCode();
                foreach ($postCodesArray as $widgetClass => $postCode) {
                    $postJsCode .= $postCode . "\n";
                }
            }
        } else {
            $content = "Kein Element für die Anzeige vorhanden(Fehler 471701).";
        }
        $result["js"] = str_replace("{PATH_URL}", PATH_URL, $jsCode);
        $result["css"] = str_replace("{PATH_URL}", PATH_URL, $cssStyle);
        $result["html"] = $content;
        $result["postjs"] = $postJsCode;
        return $result;
    }

}

?>