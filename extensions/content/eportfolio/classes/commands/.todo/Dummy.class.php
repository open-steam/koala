<?php
namespace Portfolio\Commands;
class ViewDiscuss extends \AbstractCommand implements \IFrameCommand {
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {

	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$html = <<< end


<style type="text/css">


</style>







<div id="headline">
<h1>
<a style="text-decoration: none;" href="">Discussions about my Portfolio</a>																															
</h1>
</div>
<ul class="tabBar">
        
    <li class="tabIn"><a href="./..">Dashboard</a></li>
        
    <li class="tabIn"><a href="../profile/">Profile</a></li>
        
    <li class="tabOut"><a href="">Portfolio</a></li>
        
    <li class="tabIn"><a href="../groups/">Groups</a></li>
    
    <li style="clear: left;">
</li></ul>

<br>

<table class="forum" width="100%">
<tbody>
<tr>
<td valign="top">
<h2>My Discussion View</h2>
<table class="topic" cellpadding="5">
<tbody>
<tr>
<td width="48" valign="top">
<a href="/">
<img width="48" height="56" src="https://koala.uni-paderborn.de/cached/get_document.php?id=&type=usericon&width=60&height=70">
</a>
</td>
<td>
<b>
<a href="">User's Name</a>
says:
</b>
<br>
<p>
Hi guys i have created a new Portfolio and i will like to have comments from all of you regarding it
<br>
<br>

<a href="">
<font color="green">
<font size = 5>
<b>PORTFOLIO_NAME.pdf</b>
</font size>
</a>

<br>
<br>

<small>

<font color="red">

Posted at TIME | DATE 

<div>
</td>
</tr>
</tbody>
</table>
<br>

<table class="forum" width="100%">
<tbody>

<tr>
<td valign="top">

<table class="topic" cellpadding="5">
<tbody>
<tr>
<td width="48" valign="top">
<a href="/">
<img width="48" height="56" src="https:">
</a>
</td>
<td>
<b>
<a href="">User's Friend_1</a>
says:
</b>
<br>
<p>
I really liked your new portfolio ................................................................
......................................................................

<br>
<br>

<small>
<font color="red">

Posted at TIME | DATE 
</small>
</td>
</tr>
</tbody>
</table>
<br>
<table class="forum" width="100%">
<tbody>

<tr>
<td valign="top">

<table class="topic" cellpadding="5">
<tbody>
<tr>
<td width="48" valign="top">
<a href="/">
<img width="48" height="56" src="https:">
</a>
</td>
<td>
<b>
<a href="">User's Friend_2</a>
says:
</b>
<br>
<p>
I really liked your new portfolio ................................................................
......................................................................

<br>
<br>

<small>
<font color="red">

Posted at TIME | DATE 
</small>
</td>
</tr>
</tbody>
</table>

<table class="forum" width="100%">
<tbody>

<tr>
<td valign="top">
<tr><h2>Post Your Comments</h2>

<table class="topic" cellpadding="5">
<tbody>



<td width="48" valign="top">

</td>

<textarea wrap="virtual" rows="10" style="width: 80%;" name="values[USER_PROFILE_DSC]"></textarea>
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
	 <p>
<input type="submit" value="Preview" name="values[preview]">
&nbsp;&nbsp;&nbsp;&nbsp;or&nbsp;&nbsp;&nbsp;&nbsp;
<input type="submit" value="Post now" name="values[save]">
</p>
<br>
<br>


end;
		$frameResponseObject->setTitle("Discuss View");
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($html);
		$frameResponseObject->addWidget($rawHtml);
		return $frameResponseObject;
	}
}
?>