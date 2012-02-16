<?php
namespace Portfolio\Commands;
class Qualificationprofile extends \AbstractCommand implements \IFrameCommand {

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {

	}

	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		
		
		
		$breadcrumb = new \Widgets\Breadcrumb();
	    $breadcrumb->setData(array(array("name"=>\Portfolio::getInstance()->getText("Profile/"),"link"=>$this->getExtension()->getExtensionUrl() . "Profile/"),array("name"=>\Portfolio::getInstance()->getText("Edit Profile/"), "link"=>$this->getExtension()->getExtensionUrl() . "Editprofile/"), array("name"=>\Portfolio::getInstance()->getText("Add Qualification Details"))));
		
		
		
		$actionBar = new \Widgets\ActionBar();
	    $actionBar->setActions(array(array("name"=>\Portfolio::getInstance()->getText("Edit Profile"), "link"=>$this->getExtension()->getExtensionUrl() . "Editprofile/"), array("name"=>\Portfolio::getInstance()->getText("Qualification"), "link"=>$this->getExtension()->getExtensionUrl() . "Qualificationprofile/"), array("name"=>\Portfolio::getInstance()->getText("Employment History"), "link"=>$this->getExtension()->getExtensionUrl()."Employmentprofile/")));
		
	    $input = new \Widgets\TextInput();
	    
	    
		$grid = new \Widgets\Grid();
		$grid->setData(array(
								"headline" => array(
													array(
															"name" => \Portfolio::getInstance()->getText("Education  History"),
															"colspan" => "2"
													)
								),
								"rows" => array(
												array(
													array(
														"content" => \Portfolio::getInstance()->getText("Start Date:"),
														"type" => "label"
													),
													array(
														"content" => $input,
														"type" => "value"
													)
												),
												array(
													array(
														"content" => \Portfolio::getInstance()->getText("End Date:"),
														"type" => "label"
													),
													array(
														"content" => $input,
														"type" => "value"
													)
												),
												array(
													array(
														"content" =>\Portfolio::getInstance()->getText("Course Name:"),
														"type" => "label"
													),
													array(
														"content" => $input,
														"type" => "value"
													)
												),
												array(
													array(
														"content" => \Portfolio::getInstance()->getText("Institute Name:"),
														"type" => "label"
													),
													array(
														"content" => $input,
														"type" => "value"
													)
												),
												array(
													array(
														"content" => \Portfolio::getInstance()->getText("Course Details:"),
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
	    $actionBar2->setActions(array(array("name"=>\Portfolio::getInstance()->getText("Add More"), "link"=>$this->getExtension()->getExtensionUrl() . ""), array("name"=>gettext("Save"), "link"=>$this->getExtension()->getExtensionUrl() . "")));
		
		
		$html = <<< end
<style type="text/css">

</style>

<div class="headline">
	<h1>Add Qualification</h1>
</div>

<div class="actionBar">
    
	<a href="../Editprofile/"class="button">Edit profile</a>
	
	<a href="#" class="button">Qualification icon</a>
	
	<a href="../employmentprofile/" class="button">Employment History</a>
	
</div>

<ul class="tabBar">
        
    <li class="tabIn"><a href="./..">Dashboard</a></li>
        
    <li class="tabOut"><a href="../profile/"">Profile</a></li>
        
    <li class="tabIn"><a href="../myportfolio/">Portfolio</a></li>
        
    <li class="tabIn"><a href="../groups/">Groups</a></li>
    
    <li style="clear: left;">
</li></ul>
<br>

<table class="grid" cellspacing="0" cellpadding="5" width="100%">

	 
	 <tr>
<td class="label">Start Date:</td>
	<td class="value">
		<input type="text" value="date of couse begning" " size="30" >
</td>
</tr>
<tr>
<td class="label">End Date:</td>
<td class="value">
<input type="text" value="Date when course ended" "size="30" name="user_full_name">
</td>
</tr>
<tr>
<td class="label">Qualification:</td>
<td class="value">
		<input type="text" value="Name of the Degree"" size="30" >
</td>
</tr>

<tr>
<td class="label">Institution Name :</td>
<td class="value">
		<input type="text" value="Name of the institution"" size="30" >
</td>
</tr>
<td class="label">Qualification Details:</td>
<td class="value">
<textarea wrap="virtual" rows="10" style="width: 70%;" name="values[USER_qualification_DSC]"></textarea>
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
	 </table><div class="buttons">
<a class="button">Save</a>
</div>
	 <br>
	 <div class="buttons">
<a class="button">Add More</a>
</div>



	

end;
		$frameResponseObject->setTitle("Qualificationprofile");
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($html);
		$frameResponseObject->addWidget($breadcrumb);
		//$frameResponseObject->addWidget($actionBar);
		$frameResponseObject->addWidget($grid);
		$frameResponseObject->addWidget($actionBar2);
		
		//$frameResponseObject->addWidget($rawHtml);
		return $frameResponseObject;
	}
}
?>