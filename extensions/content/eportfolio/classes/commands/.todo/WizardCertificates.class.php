<?php
namespace Portfolio\Commands;
class WizardCertificates extends \AbstractCommand implements \IFrameCommand {

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {

	}

	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$html = <<< end
<style type="text/css">



</style>

<div class="actionBar">
    
	<a href="../Fileupload/"class="button">Manual Upload</a>
	
	
</div>

<div>
<h1> Qualification Details</h1>
</div>

<div>
<ul class="tabBar">
<li class="tabOut">
<a href="">EDUCATIONAL CERTIFICATES</a>
</li>
<li class="tabIn">
<a href="">ADDITIONAL CERTIFICATES</a>
</li>
<li class="tabIn">
<a href="../WizardLinks/">WEB LINKS</a>
</li>
<li class="tabIn">
<a href="../WizardBlogs/">BLOGS</a>
</li>
<li class="tabIn">
<a href="../Editprofile/">PROFILE</a>
</li>

<br>
<br>

<h3><font color="red"> here user can see diffrent set of folders named school, bachelors, Masters,Phd,etc. by clicking on which user will be guided to upload the desired artifacts</font></h3>
	


end;
		$frameResponseObject->setTitle("Wizard Certificates");
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($html);
		$frameResponseObject->addWidget($rawHtml);
		return $frameResponseObject;
	}
}
?>