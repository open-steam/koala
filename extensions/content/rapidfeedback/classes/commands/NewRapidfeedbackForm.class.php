<?php
namespace Rapidfeedback\Commands;
class NewRapidfeedbackForm extends \AbstractCommand implements \IAjaxCommand {
	
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
		$options_group = "";
		$options_course = "";
		foreach ($groups as $group) {
			if ((strStartsWith($group->get_groupname(), "PrivGroup") || strStartsWith($group->get_groupname(), "PublicGroup")) && !strStartsWith($group->get_name(), "group_")) {
				$options_group = $options_group . "<option value=\"" . $group->get_id() . "\">" . $group->get_name() . "</option> \n";
			} else if (strStartsWith($group->get_groupname(), "Courses") && !strStartsWith($group->get_name(), "group_") && $group->get_name() == "staff") {
				$group = $group->get_parent_group();
				$name = $group->get_attribute("OBJ_DESC") . " (" . $group->get_name() . ")";
				$options_course = $options_course . "<option value=\"" . $group->get_id() . "\">" . $name . "</option> \n";
			}
		}
		$ajaxResponseObject->setStatus("ok");
		$ajaxForm = new \Widgets\AjaxForm();
		$ajaxForm->setSubmitCommand("Create");
		$ajaxForm->setSubmitNamespace("Rapidfeedback");
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
	<div class="attributeNameRequired">Beschreibung*:</div>
	<div><textarea name="desc"></textarea></div>
</div>
<div class="attribute">
	<div class="attributeNameRequired">Erstellen in*:</div>
	<div>
		<input type="radio" value="1" name="group_course" onClick="document.getElementById('group').style.display = 'none'; document.getElementById('group_admin').style.display = 'none'; document.getElementById('course').style.display = '';">Kurs
		<input type="radio" value="2" name="group_course" onClick="document.getElementById('group').style.display = ''; document.getElementById('group_admin').style.display = ''; document.getElementById('course').style.display = 'none';">Gruppe
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
<div class="attribute" id="group" style="display:none;" title="">
	<div class="attributeNameRequired">Gruppe*:</div>
	<div>
		<select name="group">
			{$options_group}
		</select>
	</div>
</div>
<div class="attribute" id="group_admin" style="display:none;" title="Wenn keine Admingruppe ausgewählt wird, sind Sie der einzige Administrator der Rapid Feedback Instanz">
	<div class="attributeNameRequired">Admingruppe*:</div>
	<div>
		<select name="group_admin">
			<option value="0">Keine</option>
			{$options_group}
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