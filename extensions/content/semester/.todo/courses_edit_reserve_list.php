<?php
if (!empty($_GET["rlid"])) {
  $rlid = (int)$_GET["rlid"];
} else $rlid = FALSE;
include_once("courses_add_reserve_list.php");
?>
