//bid 2 path fix
function bid2PathFix() {
    //lernstatt
    input = 'https://steam.lspb.de';
    output='http://bid.lspb.de';
    
    while(document.body.innerHTML.search(input)!=-1){
        document.body.innerHTML = document.body.innerHTML.replace(input,output);
    }
    
    input = 'http://steam.lspb.de';
    output='http://bid.lspb.de';
    
    while(document.body.innerHTML.search(input)!=-1){
        document.body.innerHTML = document.body.innerHTML.replace(input,output);
    }
    
    
    //schulen-gt
    input = 'http://www.schulen-gt.de/';
    output='http://www3.schulen-gt.de/';
    
    while(document.body.innerHTML.search(input)!=-1){
        document.body.innerHTML = document.body.innerHTML.replace(input,output);
    }
    
    
    
    inputAbort = 'http://www.bid-owl.de.localhost';
    while(document.body.innerHTML.search(inputAbort)!=-1){
        return false;
    }
    
    
    //bidowl
    input = 'http://www.bid-owl.de';
    output='http://www3.bid-owl.de';
    
    while(document.body.innerHTML.search(input)!=-1){
        document.body.innerHTML = document.body.innerHTML.replace(input,output);
    }
    
    return true;
}

function proofLoaded() {
   if (document.readyState == "complete") {
      bid2PathFix();
   } else {
      setTimeout('proofLoaded()',500);
   }
}

setTimeout('proofLoaded()',500);