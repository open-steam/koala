function getRandom(min, max) {
    if (min > max) {
        return (-1);
    }
    if (min == max) {
        return (min);
    }

    return (min + parseInt(Math.random() * (max - min + 1)));
}

jQuery(document).ready(function(){
    var longtext = new Array({STARTPAGE_IMAGE_TEXT_LONG}); 	//"zusammenwirken", "kommunizieren", "communicate", "communiquer"
    var mediumtext = new Array({STARTPAGE_IMAGE_TEXT_MEDIUM}); //"verbinden","cooperate","comunicar","compartir","colaborar","apprendre","partager","aprender","coopérer","joindre"
    var shorttext= new Array({STARTPAGE_IMAGE_TEXT_SHORT}); 	//"teilen","lernen","juntar","share","learn","join"
		 	   
    var longtext=longtext[getRandom(0,longtext.length-1)];
    var mediumtext=mediumtext[getRandom(0,mediumtext.length-1)];
    var short1=shorttext[getRandom(0,shorttext.length-1)];
		   
    var short2=short1;
    var short3=short1;
		   
    while (short2==short1 || short3==short2 || short3==short1){
        short2=shorttext[getRandom(0,shorttext.length-1)];
        short3=shorttext[getRandom(0,shorttext.length-1)];
    }
		   
    document.getElementById('long').innerHTML=longtext;
    document.getElementById('medium').innerHTML=mediumtext;
    document.getElementById('short1').innerHTML=short1;
    document.getElementById('short2').innerHTML=short2;
    document.getElementById('short3').innerHTML=short3;
    if($("#loginDiv").css("display") == "block"){
        document.getElementById('first_field').focus();
    }
	
});