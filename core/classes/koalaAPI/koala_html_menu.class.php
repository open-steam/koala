<?php

class koala_html_menu {

    public static function get_separator() {
        return array("name" => "SEPARATOR", "link" => "");
    }

    public static function get_burger_menu() {
        return array("name" => "BURGER_MENU", "link" => "");
    }

    private $tpl;

    public function __construct($menu = FALSE) {
        $this->tpl = new HTML_TEMPLATE_IT();
        $this->tpl->loadTemplateFile(PATH_EXTENSIONS . "base/widgets/ui/html/menu.template.html");
        if (is_array($menu)) {
            foreach ($menu as $item) {
                $this->add_menu_entry($item);
            }
        }
    }

    public function add_menu_entry($menu_entry = array()) {
        if (!is_array($menu_entry) || empty($menu_entry))
            return;
        $this->tpl->setCurrentBlock("BLOCK_MENU");
        // separator:
        if ($menu_entry["name"] === "SEPARATOR") {
            $this->tpl->touchBlock("BLOCK_SEPARATOR");
        } else if ($menu_entry["name"] === "BURGER_MENU") {
            $this->tpl->touchBlock("BLOCK_BURGER_MENU");
        } else {
            $this->tpl->setCurrentBlock("BLOCK_MENUITEM");
            if (isset($menu_entry["link"]) && !empty($menu_entry["link"])) {
                $this->tpl->setVariable("MENUITEM_LINK_START", "<a href=\"" . $menu_entry["link"] . "\">");
                $this->tpl->setVariable("MENUITEM_LINK_END", "</a>");
            } else {
                $this->tpl->setVariable("MENUITEM_LINK_START", "<span>");
                $this->tpl->setVariable("MENUITEM_LINK_END", "</span>");
            }
            if (isset($menu_entry["onclick"]) && !empty($menu_entry["onclick"])) {
                $this->tpl->setVariable("MENUITEM_ONCLICK", $menu_entry["onclick"]);
            }
            $this->tpl->setVariable("MENUITEM_NAME", ($menu_entry["name"]));
            if (isset($menu_entry["icon"]) && !empty($menu_entry["icon"])) {
                $this->tpl->setVariable("MENUITEM_ICON", $menu_entry["icon"]);
            }


            // submenu:
            if (isset($menu_entry["menu"]) && is_array($menu_entry["menu"])) {
                $this->tpl->setCurrentBlock("BLOCK_SUBMENU");
                $submenu = new koala_html_menu();
                foreach ($menu_entry["menu"] as $submenu_entry) {
                    $submenu->add_menu_entry($submenu_entry);
                }
                $this->tpl->setVariable("SUBMENU_HTML", $submenu->get_html());
                $this->tpl->parse("BLOCK_SUBMENU");
            }

            $this->tpl->parse("BLOCK_MENUITEM");
        }
        $this->tpl->parse("BLOCK_MENU");
    }

    public function get_html() {
        return $this->tpl->get();
    }

}

?>
