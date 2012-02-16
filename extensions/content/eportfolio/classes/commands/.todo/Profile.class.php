<?php
namespace Portfolio\Commands;
class Profile extends \AbstractCommand implements \IFrameCommand {

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {

	}

	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		
		$breadcrumb = new \Widgets\Breadcrumb();
	    $breadcrumb->setData(array(array("name"=>\Portfolio::getInstance()->getText("Portfolio/"),"link"=>$this->getExtension()->getExtensionUrl() . "Myportfolio/"),array("name"=>\Portfolio::getInstance()->getText("Profile"))));
		
		
		$actionBar = new \Widgets\ActionBar();
	    $actionBar->setActions(array(array("name"=>\Portfolio::getInstance()->getText("Edit Profile"), "link"=>$this->getExtension()->getExtensionUrl() . "Editprofile/"), array("name"=>\Portfolio::getInstance()->getText("Qualification"), "link"=>$this->getExtension()->getExtensionUrl() . "Qualificationprofile/"), array("name"=>\Portfolio::getInstance()->getText("Employment History"), "link"=>$this->getExtension()->getExtensionUrl()."Employmentprofile/")));
		
	    $captionImage = new \Widgets\CaptionImage();
		$captionImage->setLink(PATH_URL . "user/index/" .  \lms_steam::get_current_user()->get_name() . "/");
		$captionImage->setLinkText(\Portfolio::getInstance()->getText("To your profile"));
		$captionImage->setImageSrc(\lms_user::get_user_image_url(140,185));
		$captionImage->setImageAlt(\Portfolio::getInstance()->getText("Profile Image"));
		$captionImage->setImageTitle(\Portfolio::getInstance()->getText("Complete your Profile"));
	    
		
		$input = new \Widgets\TextInput();
		
		$grid = new \Widgets\Grid();
		$grid->setData(array(
								"headline" => array(
													array(
															"name" => \Portfolio::getInstance()->getText("Personal Information"),
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
														"content" => "l.name",
														"type" => "value"
													)
												),
												array(
													array(
														"content" => \Portfolio::getInstance()->getText("Email Id:"),
														"type" => "label"
													),
													array(
														"content" => "email",
														"type" => "value"
													),
													array(
													array(
														"content" => \Portfolio::getInstance()->getText("Address:"),
														"type" => "label"
													),
													array(
														"content" => "Address",
														"type" => "value"
													)
												))
								)
		));
		
		$html = <<< end
<style type="text/css">


</style>

<div class="headline">
	<h1>My Profile</h1>
</div>

<div class="actionBar">
    
	<a href="../Editprofile/"class="button">Edit profile</a>
	
	<a href="../Qualificationprofile/" class="button">Qualification icon</a>
	
	<a href="../Employmentprofile/" class="button">Employment History</a>
	
</div>



<tbody>
 <tr>
    <td class="info" width="155" valign="top">
       <table style="width: 146px; height: 191px; background-color: rgb(238, 238, 238); margin-bottom: 3px;">
       </table>
<tbody>
<tr>
<td>
<img class="border" alt=" Ashish Chopra" type=usericon&width=140&height=185">
</td>
</tr>

</tbody>

<table cellspacing="0" cellpadding="5" width="100%" class="grid">
	<tr>
		<th class="group" colspan="2">Personal Information</th>
	</tr>
	<tr>
		<td class="label">First Name:</td>
		<td class="value">f.name</td>
	</tr>
	<tr>
		<td class="label">Last Name:</td>
		<td class="value">L.name</td>
	</tr>	
	<tr>
		<td class="label">Email Id:</td>
		<td class="value">Email address</td>
	</tr>
	<tr>
		<td class="label">Contact Details:</td>
		<td class="value">Number and Address</td>
	</tr>
	<tr>
		<td class="label">Introduction:</td>
	
	</tr>
 </table>
 
 

</div>
end;
		$frameResponseObject->setTitle("Profile");
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($html);
		$frameResponseObject->addWidget($breadcrumb);
		$frameResponseObject->addWidget($actionBar);
		$frameResponseObject->addWidget($captionImage);
		$frameResponseObject->addWidget($grid);
		
		//$frameResponseObject->addWidget($rawHtml);
		return $frameResponseObject;
	}
}
?>