<?php
namespace Portfolio\Commands;
class WizardBlogs extends \AbstractCommand implements \IFrameCommand {

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
<h1> Add Blogs</h1>
</div>

<div>
<ul class="tabBar">
<li class="tabIn">
<a href="../WizardCertificates/">EDUCATIONAL CERTIFICATES</a>
</li>
<li class="tabIn">
<a href="">ADDITIONAL CERTIFICATES</a>
</li>
<li class="tabIn">
<a href="../WizardLinks/">WEB LINKS</a>
</li>
<li class="tabOut">
<a href="">BLOGS</a>
</li>
<li class="tabIn">
<a href="../Editprofile/">PROFILE</a>
</li>

	<br>
	<br>
	
<h3><font color="red"> shows all previously written blogs and out of those user gets an option to select the desired ones</font></h3>	

end;
		$frameResponseObject->setTitle(" WizardBlogs ");
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($html);
		$frameResponseObject->addWidget($rawHtml);
		return $frameResponseObject;
	}
}
?>