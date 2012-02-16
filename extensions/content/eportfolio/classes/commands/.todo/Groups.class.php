<?php
namespace Portfolio\Commands;
class Groups extends \AbstractCommand implements \IFrameCommand {
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {

	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		
		$breadcrumb = new \Widgets\Breadcrumb();
	    $breadcrumb->setData(array(array("name"=>\Portfolio::getInstance()->getText("Portfolio/"),"link"=>$this->getExtension()->getExtensionUrl() . "Myportfolio/"),array("name"=>\Portfolio::getInstance()->getText("My Friends and Groups"))));
		
	
	$actionBar = new \Widgets\ActionBar();
	$actionBar->setActions(array(array("name"=>\Portfolio::getInstance()->getText("Search"), "link"=>$this->getExtension()->getExtensionUrl()."Searchgroups/"), array("name"=>\Portfolio::getInstance()->getText("Create New"), "link"=>$this->getExtension()->getExtensionUrl() . "Creategroups/")));
		
	$menu = new\Widgets\DropdownBox();
	$menu->setLabel(\Portfolio::getInstance()->getText("Type Of Group"));
	$menu->addOption(\Portfolio::getInstance()->getText("All Groups"));
	$menu->addOption(\Portfolio::getInstance()->getText("Groups Made by me"));
	$menu->addOption(\Portfolio::getInstance()->getText("Groups i am Connected"));
	
	$boxGroup = new \Widgets\PortfolioViewBox();
	$boxGroup->setTitle(\Portfolio::getInstance()->getText("Group One"));
	$boxGroup->setContent(" Shows the details about the group.number of members connected,etc.");
	$boxGroup->setButtons(array(array("name"=>"Dlete Group", "link"=>"#")));
	
	$clearer = new \Widgets\Clearer();
	
	$boxGroup2 = new \Widgets\PortfolioViewBox();
	$boxGroup2->setTitle(\Portfolio::getInstance()->getText("Group two name"));
	$boxGroup2->setContent(" Shows the details about the group.number of members connected,etc.");
	$boxGroup2->setButtons(array(array("name"=>"Dlete Group", "link"=>"#")));
		
		$html = <<< end



<style type="text/css">

select {
    border: 1px solid #D1D1D1;
}

select {
    margin-right: 0.25em;
    padding: 0.16em;
    }
    
   .box_view {
		border: 1px solid #ccc;
		margin: 5px 10px 15px 10px;
		background-color: #ccffcc;
		width: 85%;
		padding:10px;
		float: left;
}

a, a:link, a:active, a:visited {
    color: #3092CE;
    text-decoration: none;
    }
</style>



<div class="headline">
	<h1>My Friends and Groups</h1>
</div>

<div class="actionBar">
    
	<a href="../Searchgroups/" class="button">Search</a>
	
	<a href="../Creategroups/" class="button">Create New</a>
	
	<a href="" class="button">Add Friend</a>
	
</div>



<select tabindex="1" name="options" id="filter_options" class="select autofocus">
	<option selected="selected" value="all">All Groups</option>
	<option value="admin">Groups made by me</option>
	<option value="member">Groups i am connected to </option>
	<option value="invite">Groups I'm Invited To(shows pending group requests)</option>
</select>

<br>


<td><div class="box_view">

     <h2><a href="">Group name 1</a></h3>
     <p>Shows the details about the group.number of members connected,etc.</p>
     
     <div class="buttons">

<a class="button" ">Delete Group</a>
</div>
     
<td>
</div>
<td><div class="box_view">

     <h2><a href="">Group name 2</a></h3>
     <p>Shows the details about the group.number of members connected,etc.</p>
      
     <div class="buttons">

<a class="button" ">Delete Group</a>
</div>
     
<td>


</div>

end;
		$frameResponseObject->setTitle("Groups");
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($html);
		$frameResponseObject->addWidget($breadcrumb);
		$frameResponseObject->addWidget($actionBar);
		$frameResponseObject->addWidget($clearer);
		$frameResponseObject->addWidget($menu);
		$frameResponseObject->addWidget($clearer);
		$frameResponseObject->addWidget($boxGroup);
		$frameResponseObject->addWidget($boxGroup2);
		//$frameResponseObject->addWidget($rawHtml);
		
		return $frameResponseObject;
	}
}
?>