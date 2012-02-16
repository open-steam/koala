<?php
namespace Portfolio\Commands;
class Searchgroups extends \AbstractCommand implements \IFrameCommand {
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {

	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		
		$breadcrumb = new \Widgets\Breadcrumb();
	    $breadcrumb->setData(array(array("name"=>\Portfolio::getInstance()->getText("Portfolio/"),"link"=>$this->getExtension()->getExtensionUrl() . "Myportfolio/"),array("name"=>\Portfolio::getInstance()->getText("My Friends and Groups/"),"link"=>$this->getExtension()->getExtensionUrl() . "Groups/"),array("name"=>\Portfolio::getInstance()->getText("My Search Group"))));
		
	
	
	$tabBar = new \Widgets\TabBar();
	$tabBar->setTabs(array(array("name"=>\Portfolio::getInstance()->getText("Dashboard"), "link"=>$this->getextension()->getExtensionUrl()."/"), array("name"=>\Portfolio::getInstance()->getText("Portfolio"), "link"=>$this->getExtension()->getExtensionUrl() . "myportfolio/"), array("name"=>\Portfolio::getInstance()->getText("Shared Portfolios"), "link"=>$this->getExtension()->getExtensionUrl() . "SharedProfiles/")));
		
	$clearer = new \Widgets\Clearer();
	
	$boxSearch = new \Widgets\PortfolioViewBox();
	$boxSearch->setTitle(\Portfolio::getInstance()->getText("Group searched 1"));
	$boxSearch->setContent(" Shows the details about the group.number of members connected,etc.");
	$boxSearch->setButtons(array(array("name"=>"Get Connected", "link"=>"#")));
	
	$boxSimilar = new \Widgets\PortfolioViewBox();
	$boxSimilar->setTitle(\Portfolio::getInstance()->getText("Group Similar"));
	$boxSimilar->setContent(" Shows the details about the group.number of members connected,etc.");
	$boxSimilar->setButtons(array(array("name"=>"Get Connected", "link"=>"#")));
	
	
		$html = <<< end



<style type="text/css">

}

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
	<h1>My Search Groups</h1>
</div>




<ul class="tabBar">
        
    <li class="tabIn"><a href="./..">Dashboard</a></li>
        
    <li class="tabIn"><a href="../profile/">Profile</a></li>
        
    <li class="tabIn"><a href="../myportfolio">Portfolio</a></li>
        
    <li class="tabOut"><a href="../groups/">Groups</a></li>
    
    <li style="clear: left;">
</li></ul>
<br>
<h2>Find Groups</h2>

<div class="text">
<input>
</div>
<div>
<input type="submit" value="Search"  name="search"  class="submit">
</div>


<br>


<td><div class="box_view">

     <h2><a href="">Group searched</a></h3>
     <p>Shows the details about the group.number of members connected,etc.</p>
     
     <div class="buttons">

<a class="button" ">get connected</a>
</div>
     
<td>
</div>
<td><div class="box_view">

     <h2><a href="">similar group 1 2</a></h3>
     <p>Shows the details about the group.number of members connected,etc.</p>
      
     <div class="buttons">

<a class="button" ">get connected</a>
</div>
     
<td>


</div>

end;
		$frameResponseObject->setTitle("Searchgroups");
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($html);
		$frameResponseObject->addWidget($breadcrumb);
		$frameResponseObject->addWidget($tabBar);
		$frameResponseObject->addWidget($clearer);
		$frameResponseObject->addWidget($boxSearch);
		$frameResponseObject->addWidget($boxSimilar);
		$frameResponseObject->addWidget($rawHtml);
		return $frameResponseObject;
	}
}
?>