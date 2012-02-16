<?php
namespace Portfolio\Commands;
class Creategroups extends \AbstractCommand implements \IFrameCommand {
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {

	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		
	$breadcrumb = new \Widgets\Breadcrumb();
	    $breadcrumb->setData(array(array("name"=>\Portfolio::getInstance()->getText("Portfolio/"),"link"=>$this->getExtension()->getExtensionUrl() . "Myportfolio/"),array("name"=>gettext("My Friends and Groups/"),"link"=>$this->getExtension()->getExtensionUrl() . "Groups/"),array("name"=>gettext("Create Group"))));
	
	
	$tabBar = new \Widgets\TabBar();
	$tabBar->setTabs(array(array("name"=>\Portfolio::getInstance()->getText("Dashboard"), "link"=>$this->getextension()->getExtensionUrl()."/"), array("name"=>gettext("Portfolio"), "link"=>$this->getExtension()->getExtensionUrl() . "myportfolio/"), array("name"=>gettext("Shared Portfolios"), "link"=>$this->getExtension()->getExtensionUrl() . "SharedProfiles/")));
		
	$menu = new\Widgets\DropdownBox();
	$menu->setLabel(\Portfolio::getInstance()->getText("Group Category"));
	$menu->addOption(\Portfolio::getInstance()->getText("Educational"));
	$menu->addOption(\Portfolio::getInstance()->getTex("Office"));
	$menu->addOption(\Portfolio::getInstance()->getText(" Hobbies"));
		
	
	$input = new \Widgets\TextInput();
	    
	    
		$grid = new \Widgets\Grid();
		$grid->setData(array(
								"headline" => array(
													array(
															"name"=>\Portfolio::getInstance()->getText("Group Dscription"),
															"colspan" => "2"
													)
								),
								"rows" => array(
												array(
													array(
														"content" => \Portfolio::getInstance()->getText("Group Name:"),
														"type" => "label"
													),
													array(
														"content" => $input,
														"type" => "value"
													)
												),
												array(
													array(
														"content" => \Portfolio::getInstance()->getText("Group Details:"),
														"type" => "label"
													),
													array(
														"content" => $input,
														"type" => "value"
													)
												
												)
								)
		));
		
		
		$actionBar2 = new \Widgets\ActionBar();
	    $actionBar2->setActions(array(array("name"=>\Portfolio::getInstance()->getText("Save"), "link"=>$this->getExtension()->getExtensionUrl() . ""), array("name"=>gettext("Cancel"), "link"=>$this->getExtension()->getExtensionUrl() . "")));
		$html = <<< end




<style type="text/css">


</style>

<div class="headline">
	<h1>Create New Group</h1>
</div>



<ul class="tabBar">
        
    <li class="tabIn"><a href="./..">Dashboard</a></li>
        
    <li class="tabIn"><a href="../profile/">Profile</a></li>
        
    <li class="tabIn"><a href="../myPortfolio/">Portfolio</a></li>
        
    <li class="tabOut"><a href="../groups/">Groups</a></li>
    
    <li style="clear: left;">
</li></ul>
<br>

<table class="grid" cellspacing="0" cellpadding="5" width="100%">

	 <tr>
			<th class="group" colspan="2">Group Details</th>
	 </tr>
	 <tr>
<td class="label">Group name:</td>
	<td class="value">
		<input type="text" "size="30" >
</td>
<tr>
<td class="label">Group Type:</td>
<td class="value">
<select name="values[OBJ_DESC]">
<option value=""></option>
<option selected="selected" value="Educational">Educational</option>
<option value="Professional">Profesional</option>
<option value="General">General</option>
</select>
</td>
</tr>
<td class="label">Group Description:</td>
<td class="value">
<textarea wrap="virtual" rows="10" style="width: 70%;" name="values[USER_PROFILE_DSC]"></textarea>
<br>
<a class="textformat_button" title="boldface" href="javascript:insert('[b]', '[/b]', 'formular', 'values[USER_PROFILE_DSC]')">
<b>B</b>
</a>
<a class="textformat_button" title="italic" href="javascript:insert('[i]', '[/i]', 'formular', 'values[USER_PROFILE_DSC]')">
<i>I</i>
</a>
<a class="textformat_button" title="underline" href="javascript:insert('[u]', '[/u]', 'formular', 'values[USER_PROFILE_DSC]')">
<u>U</u>
</a>
<a class="textformat_button" title="strikethrough" style="text-decoration: line-through;" href="javascript:insert('[s]', '[/s]', 'formular', 'values[USER_PROFILE_DSC]')">S</a>
<a class="textformat_button" title="image" href="javascript:insert('[img]http://', '[/img]', 'formular', 'values[USER_PROFILE_DSC]')">IMG</a>
<a class="textformat_button" title="web link" href="javascript:insert('[url=http://]', '[/url]', 'formular', 'values[USER_PROFILE_DSC]')">URL</a>
<a class="textformat_button" title="email link" href="javascript:insert('[mail=@]', '[/mail]', 'formular', 'values[USER_PROFILE_DSC]')">MAIL</a>
</td>
</tr>
	 </table>
	 <div class="buttons">
<a class="button">Save</a>
</div>
	

end;
		$frameResponseObject->setTitle("Creategroup");
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($html);
		$frameResponseObject->addWidget($breadcrumb);
		//$frameResponseObject->addWidget($tabBar);
		$frameResponseObject->addWidget($menu);
		$frameResponseObject->addWidget($grid);
		$frameResponseObject->addWidget($actionBar2);
		//$frameResponseObject->addWidget($rawHtml);
		return $frameResponseObject;
	}
}
?>