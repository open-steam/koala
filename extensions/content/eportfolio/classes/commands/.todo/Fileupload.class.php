<?php
namespace Portfolio\Commands;
class Fileupload extends \AbstractCommand implements \IFrameCommand {
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {

	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		
		
	$breadcrumb = new \Widgets\Breadcrumb();
	    $breadcrumb->setData(array(array("name"=>\Portfolio::getInstance()->getText("Portfolio/"),"link"=>$this->getExtension()->getExtensionUrl() . "Myportfolio/"),array("name"=>\Portfolio::getInstance()->getText("File Upload"))));
	
	
	$tabBar = new \Widgets\TabBar();
	$tabBar->setTabs(array(array("name"=>\Portfolio::getInstance()->getText("Dashboard"), "link"=>$this->getextension()->getExtensionUrl()."/"), array("name"=>\Portfolio::getInstance()->getText("Portfolio"), "link"=>$this->getExtension()->getExtensionUrl() . "myportfolio/"), array("name"=>\Portfolio::getInstance()->getText("Shared Portfolios"), "link"=>$this->getExtension()->getExtensionUrl() . "SharedProfiles/")));
		
	$clearer = new \Widgets\Clearer();
	
	
	$upload = new\Widgets\UploadFile();
	$upload->setLabel(\Portfolio::getInstance()->getText("Upload File"));
	
	$menu = new\Widgets\DropdownBox();
	$menu->setLabel(\Portfolio::getInstance()->getText("type of group"));
	$menu->addOption(\Portfolio::getInstance()->getText("New Folder"));
	$menu->addOption(\Portfolio::getInstance()->getText("Folder 1"));
	$menu->addOption(\Portfolio::getInstance()->getText("Folder 2"));
	
	$actionBar2 = new \Widgets\ActionBar();
	    $actionBar2->setActions(array(array("name"=>\Portfolio::getInstance()->getText("Save"), "link"=>$this->getExtension()->getExtensionUrl() . ""), array("name"=>gettext("Cancel"), "link"=>$this->getExtension()->getExtensionUrl() . "")));
		
		
		
		
		
		$html = <<< end




<style type="text/css">

.uploadform td {
    font-size: 0.9167em;
    line-height: 1.3333em;
}

</style>

<div class="headline">
	<h1>File Upload</h1>
</div>





<ul class="tabBar">
        
    <li class="tabIn"><a href="./..">Dashboard</a></li>
        
    <li class="tabIn"><a href="../profile/">Profile</a></li>
        
    <li class="tabOut"><a href="../myportfolio/">Portfolio</a></li>
        
    <li class="tabIn"><a href="../groups/">Groups</a></li>
    
    <li style="clear: left;">
</li></ul>
<br>
Portfolio > file upload <br><br>
<td class="label" Upload File:</td>


<div>
<tr class="uploadform">
<th>
<p>
File:
<input type="file" size="45" name="filename1">
</p>

<p>
 Folder:
<td class="value">
<select name="values[OBJ_DESC]">
<option value=""></option>
<option value="New Folder" selected="selected">New Folder</option>
<option value="Folder 1">Folder 1</option>
<option value="Folder 2">Folder 2</option>
<option value="Folder 3">Folder 3</option>
</select>
</td>
<br>
<br>
<br>
<input type="submit" value="Submit" name=".submit">
</form>
end;
		$frameResponseObject->setTitle("Fileupload");
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($html);
		$frameResponseObject->addWidget($breadcrumb);
		//$frameResponseObject->addWidget($tabBar);
		$frameResponseObject->addWidget($clearer);
		$frameResponseObject->addWidget($upload);
		$frameResponseObject->addWidget($clearer);
		$frameResponseObject->addWidget($menu);
		$frameResponseObject->addWidget($actionBar2);
		
		//$frameResponseObject->addWidget($rawHtml);
		return $frameResponseObject;
	}
}
?>