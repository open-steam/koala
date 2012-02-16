<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
        <title>Modul einfügen</title>
        <script type="text/javascript" src="../../tiny_mce_popup.js"></script>
        <script type="text/javascript" src="js/modules.js"></script>
        <script type="text/javascript" src="../../utils/mctabs.js"></script>
        <script type="text/javascript" src="../../utils/validate.js"></script>
        <script type="text/javascript" src="../../utils/form_utils.js"></script>
        <script type="text/javascript" src="../../utils/editable_selects.js"></script>
        <base target="_self" />
</head>
<body style="display: none">


<?php


echo 'test!';

die();



define("SET",1);
define("LOGIN",1);


$module_params = Array();



//get data for each module:

  $module_dir = "../../../../../modules";

  $md_h = opendir($module_dir);

  if ($md_h) {

    $modules = array();

    while ($md = readdir($md_h)) {

      if (is_dir($module_dir."/".$md) AND $md != '.' AND $md != '..') {

        $modules[] = $md;

      }

    }

    if (count($modules) > 0) {
    
      $j = 0;

              foreach ($modules as $module) {

                  include_once($module_dir."/".$module."/main.php");

                  $module_obj = new $module;

                  $module_params[$j] = $module_obj->selection("../../../../../modules/".$module);

                  $module_params[$j]['name'] = $module;

              $j++;
              }

    } else {

      echo '<p>Es wurden keine Module gefunden!</p>';

    }


  } else {
  
    echo  '<p>Fehler beim Laden der Module</p>';

  }




?>

    
    
                <div class="tabs">
                        <ul>


<?php

$i = 1;

  foreach ($module_params as $module) {

    echo '
    <li id="tab'.$i.'"'.(($i==1)?' class="current"':'').'><span><a href="javascript:mcTabs.displayTab(\'tab'.$i.'\',\'panel'.$i.'\');" onmousedown="return false;">'.$module['title'].'</a></span></li>
    ';

  $i++;
  }

?>

                        </ul>
                </div>

                <div class="panel_wrapper">
                
                
                
                
<?php

$i = 1;

  foreach ($module_params as $module) {

    echo '
    
<script type="text/javascript">

function modules_'.$i.'_submit() {

var display = "";

';

//replace %% by module_ID_ to avoid id-conflicts
echo str_replace('%%','module_'.$i.'_',$module['javascript']);

echo '

insertModule(\''.$i.'\',display, \''.$module['name'].'\');

}

</script>

<div id="panel'.$i.'" class="panel'.(($i==1)?' current':'').'">
<form onsubmit="modules_'.$i.'_submit();return false;" action="#" id="form'.$i.'">


        <fieldset>

';

//replace %% by module_ID_ to avoid id-conflicts
echo str_replace('%%','module_'.$i.'_',$module['html']);

echo '

        </fieldset>


                        <div class="mceActionPanel">
                                <div style="float: left">
                                        <input type="submit" id="insert" name="insert" value="{#insert}" />
                                </div>

                                <div style="float: right">
                                        <input type="button" id="cancel" name="cancel" value="{#cancel}" onclick="tinyMCEPopup.close();" />
                                </div>
                        </div>

</form>
</div>

    ';
    
  $i++;
  }

?>
                



                
                
                

</body>
</html>