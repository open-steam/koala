function padding(laenge) {
result = '';
for (i = 0; i < laenge; i++)
result = result + '___';
return result;
}

function print_r(das_array, ebene) {
var result = '';
for (var wert in das_array)
if (typeof das_array[wert] == "object")
result = result + ' ' + padding(ebene) + wert + "\n" + print_r(das_array[wert], ebene + 1);
else
result = result + ' ' + padding(ebene) + wert + ' = ' + das_array[wert] + "\n";

return result;
}
















function insertModule(module_id,module_title,module_name) {


var f = document.getElementById("form"+module_id);
var params = Array();
var display = "";
var param_tags = '';

for (var i = 0; i <= f.length; i++) {

  if (f[i]) {
  if (f[i].name) {

    var new_param = Array();
	var value = "";

	if (f[i].type == "radio") {
		
		value = "radiobuttons are not supported at this time!";
		
	} else if (f[i].type == "checkbox") {
		
		if (f[i].checked) {
			value = true;
		} else {
			value = false;
		}
		
	} else {
		
		value = f[i].value;
		
	}
	
    
    new_param.push(f[i].name);
    new_param.push(value);

    params.push(new_param);
    
    param_tags = param_tags+'&param_'+escape(f[i].name)+'="'+escape(value)+'"';
    
  }
  }

}

    //add module name as first parameter
    param_tags = 'simplex_module_name="'+module_name+'"'+param_tags;


//alert(module_title);

//alert(print_r(params, 0));


//tinyMCE.execCommand('mceInsertContent', false, '<!-- simplexmodule['+escape(module_title)+']['+escape(param_tags)+'] -->');
tinyMCE.execCommand('mceInsertContent', false, '<img src="libs/jscript/tiny_mce/plugins/simplexmodule/img/trans.gif" class="mceSimplexModule mceItemNoResize" title="'+escape(module_title)+'" alt="'+escape(param_tags)+'" style="background-image: url(inc/core/simplexmodule_display.php?display='+escape(module_title)+');" />');

tinyMCEPopup.close();

}