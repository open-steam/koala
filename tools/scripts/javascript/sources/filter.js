 /*
   Filtering in html-tables
   
   Filters all table rows of the given htmlClass by the specified value.
   An empty value shows all rows.
 
 */
 
 function filter(htmlClass,value){
   var rows=document.getElementsByTagName('tr');;
    for (var i=0; i<rows['length'];i++){
     row=rows[i];
     if (row.className==htmlClass) {
        if (value=='' || row.innerHTML.toLowerCase().search(value.toLowerCase())!=-1){ 
           var cells=(row.getElementsByTagName('td'));
           for (var j=0; j<cells['length'];j++){
           	try {
           		cells[j].style.display='table-cell';
           	} catch (e) {
                cells[j].style.display='block';    //Internet Explorer
           	}
           }
        } else {
           var cells=(row.getElementsByTagName('td'));
           for (var j=0; j<cells['length'];j++){
             cells[j].style.display='none';
           }
        }
     }
   }
   }
