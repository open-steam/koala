<?php
$environment = $container->get_environment();

$group = $environment->get_creator();

if ($group->get_name() == "learners" && $group->get_attribute(OBJ_TYPE) == "course_learners")
	$group = $group->get_parent_group();

include_once("container_new.php");
?>
