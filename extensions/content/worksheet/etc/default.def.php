<?php
is_writable(Worksheet::getInstance()->getExtensionPath() . "templates_c/") or die("Not write access to folder " . Worksheet::getInstance()->getExtensionPath() . "templates_c/");
?>