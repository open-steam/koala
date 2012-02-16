<?php
namespace Portfolio\Commands;
class WizardLinks extends \AbstractCommand implements \IFrameCommand {

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
<h1> Add Weblink</h1>
</div>

<div>
<ul class="tabBar">
<li class="tabIn">
<li class="tabIn">
<a href="../WizardCertificates/">EDUCATIONAL CERTIFICATES</a>
</li>
<li class="tabIn">
<a href="">ADDITIONAL CERTIFICATES</a>
</li>
<li class="tabOut">
<a href="">WEB LINKS</a>
</li>
<li class="tabIn">
<a href="../WizardBlogs/">BLOGS</a>
</li>
<li class="tabIn">
<a href="../Editprofile/">PROFILE</a>
</li>
<br>
<br>

<h3><font color="red"> In this page user get LINKS to youtube or other needfull site via which user can select the deired weblinks which should be seen on his/her E-portfolio
</font></h3>

	

end;
		$frameResponseObject->setTitle("Wizard Links");
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($html);
		$frameResponseObject->addWidget($rawHtml);
		return $frameResponseObject;
	}
}
?>