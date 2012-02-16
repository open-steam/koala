<?php
namespace Portfolio\Commands;
class Blog extends \AbstractCommand implements \IFrameCommand {

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {

	}

	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		
	
		$actionBar = new \Widgets\ActionBar();
	    $actionBar->setActions(array(array("name"=>\Portfolio::getInstance()->getText("Profile"), "link"=>$this->getExtension()->getExtensionUrl() . "profile/"), array("name"=>gettext("Groups"), "link"=>$this->getExtension()->getExtensionUrl() . "groups/"), array("name"=>gettext("File Uploads"), "link"=>$this->getExtension()->getExtensionUrl()."Fileupload/"),array("name"=>gettext("Blogs"), "link"=>$this->getExtension()->getExtensionUrl() . "Blog/")));
		
	   $breadcrumb = new \Widgets\Breadcrumb();
	    $breadcrumb->setData(array(array("name"=>\Portfolio::getInstance()->getText("Portfolio/"),"link"=>$this->getExtension()->getExtensionUrl() . "Myportfolio/"),array("name"=>gettext("Blog"))));
	    
	    $tabBar = new \Widgets\TabBar();
	    $tabBar->setTabs(array(array("name"=>\Portfolio::getInstance()->getText("Dashboard"), "link"=>$this->getextension()->getExtensionUrl()."/"), array("name"=>gettext("Portfolio"), "link"=>$this->getExtension()->getExtensionUrl() . "myportfolio/"), array("name"=>gettext("Shared Portfolios"), "link"=>$this->getExtension()->getExtensionUrl() . "SharedProfiles/")));
	    
	    $clearer = new \Widgets\Clearer();
	    
	    
	    $input = new \Widgets\TextInput();
	    
		
	    
		$grid = new \Widgets\Grid();
		$grid->setData(array(
								"headline" => array(
													array(
															"name"=>\Portfolio::getInstance()->getText("BLOG Number"),
															"colspan" => "2"
													)
								),
								"rows" => array(
												array(
													array(
														"content"=>\Portfolio::getInstance()->getText("Blog Name:"),
														"type" => "label"
													),
													array(
														"content" => $input,
														"type" => "value"
													)
												),
												array(
													array(
														"content"=>\Portfolio::getInstance()->getText("Body"),
														"type" => "label"
													),
													array(
														"content" => $input,
														"type" => "value"
													)
													
												)
												
							
		)));
		
		
		
		$actionBar2 = new \Widgets\ActionBar();
	    $actionBar2->setActions(array(array("name"=>\Portfolio::getInstance()->getText("Attach Files"), "link"=>$this->getExtension()->getExtensionUrl() . ""), array("name"=>gettext("Save"), "link"=>$this->getExtension()->getExtensionUrl() . "")));
		
																					
	    $html = <<< end
	    


<style type="text/css">


</style>


<div class="actionBar">
    
	<a href="../Editview/"class="button">File Uploads</a>
	
	<a href="" class="button">Blog</a>
	
</div>
<div class="headline">
	<h1>User's Blog </h1>
</div>

<ul class="tabBar">
        
    <li class="tabIn"><a href="./..">Dashboard</a></li>
        
    <li class="tabIn"><a href="../profile/">Profile</a></li>
        
    <li class="tabOut"><a href="">Portfolio</a></li>
        
    <li class="tabIn"><a href="../groups/">Groups</a></li>
    
    <li style="clear: left;">
</li></ul>

<br>



<table class="grid" cellspacing="0" cellpadding="5" width="100%">

	 <tr>
			<th class="group" colspan="2">Blog...(NUMBER)</th>
	 </tr>
	 <tr>
<td class="label">Title</td>
	<td class="value">
		<input type="text" value="blogs_title" disabled="" size="30" >
</td>
<tr>

<td class="label">Body</td>
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
	 <div class="actionBar">
    
	<a href=""class="button">Attach Files</a>
	<a href=""class="button">Connect Web</a>
</div>
<div class="buttons">
<a class="button">Save</a>
</div>
       

end;
		$frameResponseObject->setTitle("Blog");
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($html);
		//$frameResponseObject->addWidget($actionBar);
		$frameResponseObject->addWidget($breadcrumb);
		//$frameResponseObject->addWidget($tabBar);
		$frameResponseObject->addWidget($clearer);
		$frameResponseObject->addWidget($grid);
	    $frameResponseObject->addWidget($actionBar2);
	    //$frameResponseObject->addWidget($content);
		
		//$frameResponseObject->addWidget($rawHtml);
		return $frameResponseObject;
	}
}
?>