<?php
namespace Pyramiddiscussion\Commands;

class NewPyramiddiscussionForm extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $id;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        if ($requestObject instanceof \UrlRequestObject) {
            $this->params = $requestObject->getParams();
            isset($this->params[0]) ? $this->id = $this->params[0] : "";
        } else if ($requestObject instanceof \AjaxRequestObject) {
            $this->params = $requestObject->getParams();
            isset($this->params["id"]) ? $this->id = $this->params["id"] : "";
        }
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {

        $user = \lms_steam::get_current_user_no_guest();
        $groups = $user->get_groups();
        $options_basegroup = "";
        $options_admingroup = "<option value=\"0\">Nur ich</option> \n";
        $options_course = "";
        $buddies = array();
        if (PLATFORM_ID == "bid") {
            $buddies = $user->get_buddies();
            foreach ($buddies as $buddy) {
                if ($buddy instanceof \steam_group && !in_array($buddy, $groups) && $buddy->get_attribute("GROUP_INVISIBLE") !== 1 && $buddy->count_members() <= 64) {
                    $options_basegroup = $options_basegroup . "<option value=\"" . $buddy->get_id() . "\">" . $buddy->get_name() . "</option> \n";
                    $options_admingroup = $options_admingroup . "<option value=\"" . $buddy->get_id() . "\">" . $buddy->get_name() . "</option> \n";
                }
            }
        }
        foreach ($groups as $group) {
            if ($group->get_attribute("GROUP_INVISIBLE") !== 1 && $group->get_name() !== "sTeam" && $group->count_members() <= 64) {
                if (PLATFORM_ID == "bid") {
                    // bid owl    
                    $options_basegroup = $options_basegroup . "<option value=\"" . $group->get_id() . "\">" . $group->get_name() . "</option> \n";
                    $options_admingroup = $options_admingroup . "<option value=\"" . $group->get_id() . "\">" . $group->get_name() . "</option> \n";
                } else {
                    // koala
                    if ((strStartsWith($group->get_groupname(), "PrivGroup") || strStartsWith($group->get_groupname(), "PublicGroup"))) {
                        $options_basegroup = $options_basegroup . "<option value=\"" . $group->get_id() . "\">" . $group->get_name() . "</option> \n";
                        $options_admingroup = $options_admingroup . "<option value=\"" . $group->get_id() . "\">" . $group->get_name() . "</option> \n";
                    } else if (strStartsWith($group->get_groupname(), "Courses") && $group->get_name() == "staff") {
                        $group = $group->get_parent_group();
                        $name = $group->get_attribute("OBJ_DESC") . " (" . $group->get_name() . ")";
                        $options_course = $options_course . "<option value=\"" . $group->get_id() . "\">" . $name . "</option> \n";
                    }
                }
            }
        }

        $ajaxResponseObject->setStatus("ok");

        $ajaxForm = new \Widgets\AjaxForm();
        $ajaxForm->setSubmitCommand("Create");
        $ajaxForm->setSubmitNamespace("Pyramiddiscussion");

        if (PLATFORM_ID == "bid") {
            $ajaxForm->setHtml(<<<END
<style type="text/css">
.attribute {
  clear: left;
  padding: 5px 2px 5px 2px;
}

.attributeName {
  float: left;
  padding-right: 20px;
  text-align: right;
  width: 120px;
}

.attributeNameRequired {
  float: left;
  padding-right: 20px;
  text-align: right;
  font-weight: bold;
  width: 120px;
}

.attributeValue {
  float: left;
  width: 300px;
}

.attributeValue .text, .attributeValue textarea {
  wwidth: 100px;
}

.attributeValueColumn {
  float: left;
  position: relative;
  text-align: center;
}
</style>
<div class="attribute">
	<div class="attributeName">Titel:</div>
	<div class="attributeValue"><input type="text" class="text" value="" name="title"></div>
</div>
<div class="attribute">
	<div class="attributeName">Anzahl der Startfelder:</div>
	<div class="attributeValue">
		<select name="startElements" size="1" style="width: 50%">
			<option value="2">2</option>
			<option value="4">4</option>
			<option value="8">8</option>
			<option value="16">16</option>
			<option value="32">32</option>
			<option value="64">64</option>
		</select>
	</div>
</div>
<div class="attribute">
	<div class="attributeName">Eingabeeditor:</div>
	<div class="attributeValue">
		<select name="editor" size="1" style="width: 50%">
			<option value="text/plain">Einfacher Text</option>
			<option value="text/html">HTML Notation</option>
			<option value="text/wiki">Wiki Notation</option>
		</select>
	</div>
</div>
<input type="hidden" value="2" name="group">
<div class="attribute" id="basegroup">
	<div class="attributeName">Basisgruppe:</div>
	<div class="attributeValue">
		<select name="basegroup">
			{$options_basegroup}
		</select>
	</div>
</div>
<div class="attribute" id="admingroup">
	<div class="attributeName">Admingruppe:</div>
	<div class="attributeValue">
		<select name="admingroup">
			{$options_admingroup}
		</select>
	</div>
</div>
<input type="hidden" name="id" value="{$this->id}">
END
            );
        } else {
            $ajaxForm->setHtml(<<<END
<style type="text/css">
.attribute {
  clear: left;
  padding: 5px 2px 5px 2px;
}

.attributeName {
  float: left;
  padding-right: 20px;
  text-align: right;
  width: 120px;
}

.attributeNameRequired {
  float: left;
  padding-right: 20px;
  text-align: right;
  font-weight: bold;
  width: 120px;
}

.attributeValue {
  float: left;
  width: 300px;
}

.attributeValue .text, .attributeValue textarea {
  wwidth: 100px;
}

.attributeValueColumn {
  float: left;
  position: relative;
  text-align: center;
}
</style>
<div class="attribute">
	<div class="attributeName">Titel:</div>
	<div class="attributeValue"><input type="text" class="text" value="" name="title"></div>
</div>
<div class="attribute">
	<div class="attributeName">Anzahl der Startfelder:</div>
	<div class="attributeValue">
		<select name="startElements" size="1" style="width: 50%">
			<option value="2">2</option>
			<option value="4">4</option>
			<option value="8">8</option>
			<option value="16">16</option>
			<option value="32">32</option>
			<option value="64">64</option>
		</select>
	</div>
</div>
<div class="attribute">
	<div class="attributeName">Eingabeeditor:</div>
	<div class="attributeValue">
		<select name="editor" size="1" style="width: 50%">
			<option value="text/plain">Einfacher Text</option>
			<option value="text/html">HTML Notation</option>
			<option value="text/wiki">Wiki Notation</option>
		</select>
	</div>
</div>
<div class="attribute">
	<div class="attributeName">Erstellen in:</div>
	<div class="attributeValue">
		<input type="radio" value="1" name="group" onClick="document.getElementById('admingroup').style.display = 'none'; document.getElementById('basegroup').style.display = 'none'; document.getElementById('course').style.display = '';">Kurs
		<input type="radio" value="2" name="group" onClick="document.getElementById('admingroup').style.display = ''; document.getElementById('basegroup').style.display = ''; document.getElementById('course').style.display = 'none';">Gruppe
	</div>
</div>
<div class="attribute" id="course" style="display:none;">
	<div class="attributeName">Kurs:</div>
	<div class="attributeValue">
		<select name="course">
			{$options_course}
		</select>
	</div>
</div>
<div class="attribute" id="basegroup" style="display:none;">
	<div class="attributeName">Basisgruppe:</div>
	<div class="attributeValue">
		<select name="basegroup">
			{$options_basegroup}
		</select>
	</div>
</div>
<div class="attribute" id="admingroup" style="display:none;">
	<div class="attributeName">Admingruppe:</div>
	<div class="attributeValue">
		<select name="admingroup">
			{$options_admingroup}
		</select>
	</div>
</div>
<input type="hidden" name="id" value="{$this->id}">
END
            );
        }

        $ajaxForm->setPostJsCode('setTimeout(function(){$("input:text:visible:first").focus();}, 1300);');
        $ajaxResponseObject->addWidget($ajaxForm);
        return $ajaxResponseObject;
    }
}
?>