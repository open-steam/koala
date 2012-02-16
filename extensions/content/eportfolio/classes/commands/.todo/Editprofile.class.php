<?php
namespace Portfolio\Commands;
class Editprofile extends \AbstractCommand implements \IFrameCommand {
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {

	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		
		
		$breadcrumb = new \Widgets\Breadcrumb();
	    $breadcrumb->setData(array(array("name"=>\Portfolio::getInstance()->getText("Profile"),"link"=>$this->getExtension()->getExtensionUrl() . "Profile/"),array("name"=>\Portfolio::getInstance()->getText("Edit Profile"), "link"=>$this->getExtension()->getExtensionUrl() . "Editprofile/")));
		
		
		
		$actionBar = new \Widgets\ActionBar();
	    $actionBar->setActions(array(array("name"=>\Portfolio::getInstance()->getText("Qualification"), "link"=>$this->getExtension()->getExtensionUrl() . "Qualificationprofile/"), array("name"=>\Portfolio::getInstance()->getText("Employment History"), "link"=>$this->getExtension()->getExtensionUrl()."Employmentprofile/")));
		
	    $input = new \Widgets\TextInput();
	    
		$grid = new \Widgets\Grid();
		$grid->setData(array(
								"headline" => array(
													array(
															"name"=>\Portfolio::getInstance()->getText("Edit Information"),
															"colspan" => "2"
													)
								),
								"rows" => array(
												array(
													array(
														"content" => \Portfolio::getInstance()->getText("First Name:"),
														"type" => "label"
													),
													array(
														"content" => $input,
														"type" => "value"
													)
												),
												array(
													array(
														"content" => \Portfolio::getInstance()->getText("Last Name:"),
														"type" => "label"
													),
													array(
														"content" => $input,
														"type" => "value"
													)
												),
												array(
													array(
														"content" => \Portfolio::getInstance()->getText("Profession:"),
														"type" => "label"
													),
													array(
														"content" => $input,
														"type" => "value"
													)
												),
												array(
													array(
														"content" => \Portfolio::getInstance()->getText("Email Id:"),
														"type" => "label"
													),
													array(
														"content" => $input,
														"type" => "value"
													)
												),
												array(
													array(
														"content" => \Portfolio::getInstance()->getText("Describe Yourself:"),
														"type" => "label"
													),
													array(
														"content" => $input,
														"type" => "value"
													)
												)
								)
		));
		
		
		$html = <<< end




<style type="text/css">


</style>

<div class="headline">
	<h1>Edit Profile</h1>
</div>

<div class="actionBar">
    
	<a href="#" class="button">Edit profile</a>
	
	<a href="../qualificationprofile/" class="button">Qualification icon</a>
	
	<a href="../employmentprofile/" class="button">Employment History</a>
	
</div>

<ul class="tabBar">
        
    <li class="tabIn"><a href="./..">Dashboard</a></li>
        
    <li class="tabOut"><a href="../profile/">Profile</a></li>
        
    <li class="tabIn"><a href="../myportfolio/">Portfolio</a></li>
        
    <li class="tabIn"><a href="../groups/">Groups</a></li>
    
    <li style="clear: left;">
</li></ul>
<br>

<table class="grid" cellspacing="0" cellpadding="5" width="100%">

	 <tr>
			<th class="group" colspan="2">Personal Information</th>
	 </tr>
	 <tr>
<td class="label">First name:</td>
	<td class="value">
		<input type="text" value="User_name" disabled="" size="30" >
</td>
<tr>
<td class="label">Last name:</td>
<td class="value">
<input type="text" value="Chopra" disabled="disabled" size="30" name="user_full_name">
</td>
</tr>
<tr>
<td class="label">Profession:</td>
<td class="value">
<select name="values[OBJ_DESC]">
<option value=""></option>
<option selected="selected" value="Management">Management</option>
<option value="Engineer">Engineer</option>
<option value="others">otherst</option>
</select>
</td>
</tr>
<tr>
<td class="label">Describe yourself:</td>
<td class="value">
<textarea wrap="virtual" rows="10" style="width: 95%;" name="values[USER_PROFILE_DSC]"></textarea>
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
	
	</td>
	</tr>
	 </table>

end;
		$frameResponseObject->setTitle("Editprofile");
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($html);
		$frameResponseObject->addWidget($breadcrumb);
		$frameResponseObject->addWidget($actionBar);
		$frameResponseObject->addWidget($grid);
		//$frameResponseObject->addWidget($rawHtml);
		return $frameResponseObject;
	}
}
?>