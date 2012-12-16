//bid 2 path fix
function bid2PathFix() {
    
    inputAbort = false;
    if (document.body.innerHTML.search('http://www.bid-owl.de.localhost')!=-1){
       inputAbort = true;
    }
   
    // lernstatt
    inputLSPBS = 'https://steam.lspb.de';
    outputLSPBS ='http://bid.lspb.de';
    inputLSPB = 'http://steam.lspb.de';
    outputLSPB ='http://bid.lspb.de';
    
    // schulen-gt
    inputGT = 'http://www.schulen-gt.de/';
    outputGT= 'http://www3.schulen-gt.de/';
    
    // bid-owl
    inputBid = 'http://www.bid-owl.de';
    outputBid ='http://www3.bid-owl.de';
    
    var aList = document.body.getElementsByTagName("a");
    for (i = 0; i < aList.length; i++) {
        aList[i].href = aList[i].href.replace(inputLSPBS, outputLSPBS);
        aList[i].href = aList[i].href.replace(inputLSPB, outputLSPB);
        aList[i].href = aList[i].href.replace(inputGT, outputGT);
        if (!inputAbort) {
            aList[i].href = aList[i].href.replace(inputBid, outputBid);
        }
    }
    
    var imgList = document.body.getElementsByTagName("img");
    for (i = 0; i < imgList.length; i++) {
        imgList[i].src = imgList[i].src.replace(inputLSPBS, outputLSPBS);
        imgList[i].src = imgList[i].src.replace(inputLSPB, outputLSPB);
        imgList[i].src = imgList[i].src.replace(inputGT, outputGT);
        if (!inputAbort) {
            imgList[i].src = imgList[i].src.replace(inputBid, outputBid);
        }
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