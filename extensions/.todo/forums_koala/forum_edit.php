<?php
$env = $messageboard->get_environment();

$grp = $env->get_creator();

if ($grp->get_name() == "learners" && $grp->get_attribute(OBJ_TYPE) == "course_learners") {
  $grp = $grp->get_parent_group();
}

include_once("forum_new.php");
?>
