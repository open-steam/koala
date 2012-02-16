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
			isset($this->params[0]) ? $this->id = $this->params[0]: "";
		} else if ($requestObject instanceof \AjaxRequestObject) {
			$this->params = $requestObject->getParams();
			isset($this->params["id"]) ? $this->id = $this->params["id"]: "";
		}
	}
	
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		
		$user = $GLOBALS["STEAM"]->get_current_steam_user();
		$groups = $user->get_groups();
		$options_basegroup = "";
		$options_admingroup = "";
		$options_course = "";
		foreach ($groups as $group) {
			if ((strStartsWith($group->get_groupname(), "PrivGroup") || strStartsWith($group->get_groupname(), "PublicGroup")) && !strStartsWith($group->get_name(), "group_")) {
				if ($group->is_admin($user)) {
					$options_basegroup = $options_basegroup . "<option value=\"" . $group->get_id() . "\">" . $group->get_name() . "</option> \n";
				}
				$options_admingroup = $options_admingroup . "<option value=\"" . $group->get_id() . "\">" . $group->get_name() . "</option> \n";
			} else if (strStartsWith($group->get_groupname(), "Courses") && !strStartsWith($group->get_name(), "group_") && $group->get_name() == "staff") {
				$group = $group->get_parent_group();
				$name = $group->get_attribute("OBJ_DESC") . " (" . $group->get_name() . ")";
				$options_course = $options_course . "<option value=\"" . $group->get_id() . "\">" . $name . "</option> \n";
			}
		}
		
		$ajaxResponseObject->setStatus("ok");
		
		$ajaxForm = new \Widgets\AjaxForm();
		$ajaxForm->setSubmitCommand("Create");
		$ajaxForm->setSubmitNamespace("Pyramiddiscussion");
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
  width: 80px;
}

.attributeNameRequired {
  float: left;
  padding-right: 20px;
  text-align: right;
  font-weight: bold;
  width: 80px;
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
	<div class="attributeNameRequired">Titel*:</div>
	<div><input type="text" class="text" value="" name="title"></div>
</div>
<div class="attribute">
	<div class="attributeNameRequired">Anzahl der Startfelder*:</div>
	<div>
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
	<div class="attributeNameRequired">Eingabeeditor*:</div>
	<div>
		<select name="editor" size="1" style="width: 50%">
			<option value="text/plain">Einfacher Text</option>
			<option value="text/html">HTML Notation</option>
			<option value="text/wiki">Wiki Notation</option>
		</select>
	</div>
</div>
<div class="attribute">
	<div class="attributeNameRequired">Erstellen in*:</div>
	<div>
		<input type="radio" value="1" name="group" onClick="document.getElementById('admingroup').style.display = 'none'; document.getElementById('basegroup').style.display = 'none'; document.getElementById('course').style.display = '';">Kurs
		<input type="radio" value="2" name="group" onClick="document.getElementById('admingroup').style.display = ''; document.getElementById('basegroup').style.display = ''; document.getElementById('course').style.display = 'none';">Gruppe
	</div>
</div>
<div class="attribute" id="course" style="display:none;">
	<div class="attributeNameRequired">Kurs*:</div>
	<div>
		<select name="course">
			{$options_course}
		</select>
	</div>
</div>
<div class="attribute" id="basegroup" style="display:none;" title="Es werden nur Gruppen angezeigt, in denen Sie Administrator sind.">
	<div class="attributeNameRequired">Basisgruppe*:</div>
	<div>
		<select name="basegroup">
			{$options_basegroup}
		</select>
	</div>
</div>
<div class="attribute" id="admingroup" style="display:none;">
	<div class="attributeNameRequired">Admingruppe*:</div>
	<div>
		<select name="admingroup">
			{$options_admingroup}
		</select>
	</div>
</div>
END
);

		$ajaxResponseObject->addWidget($ajaxForm);
		return $ajaxResponseObject;
	}
}
?>