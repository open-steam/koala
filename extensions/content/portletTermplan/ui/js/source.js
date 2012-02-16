<script language="javascript" type="text/javascript">
function send_termvote () {
  var options = this.document.vote_{PORTLET_ID}.termitem;
  var i;
  var encodedChoices = "termChoices";
  for (i = 0; i < options.length; i++) { 
    if (options[i].checked) {
      //break;
      encodedChoices+=":"+i;
    } 
  }
  window.open('{PORTLET_ROOT}/add_vote.php?portlet={PORTLET_ID}&action=' + encodedChoices, 'addVote', 'resizable,scrollbars,width=500,height=200');
}
function termPlanSumMod(voteNumber,checkboxField,portletId){
	sumField = document.getElementById(portletId+"TermSum"+voteNumber);
	//alert(sumField.innerHTML);
	if(checkboxField.checked){
		sumField.innerHTML = parseInt(sumField.innerHTML)+1;
	}else{
		sumField.innerHTML = parseInt(sumField.innerHTML)-1;
	}
	
}

function greenSwitcher(item){
	if(item.checked){
		item.parentNode.style.backgroundColor = "#99EE99";
	}else{
		item.parentNode.style.backgroundColor = "";
    	}
    	
    }
    
</script>