

function treeFrameResized(event)
{
  var isIE = (navigator.appName.indexOf("Microsoft") != -1);
  var isNN = (navigator.appName.indexOf("Netscape") != -1);

  if(isIE)
  {
    // THIS DOES NOT WORK HERE:
    // var event = top.event;   // ie grabs the event from the global event object!
    // var width = event.clientX;

    // Workaround:
    // The value of event.clientX is of no use, since it always contains the
    // width BEFORE the resize.
    // Even worse: when the size of the top level window was changed, event contains
    // the values of that window and NOT of the frame as expected - although the
    // event/handler is triggered by the frame.
    // So we get the width from the frame DIRECTLY (which already does contain the
    // new value ...)
    var width = top.document.all("frameset1").all("frameset2").all("treeFrame").width;

    //top.shiftMenubar();		//adjust menubar to new Frame size
    if (navigator.appName == "Netscape" && navigator.appVersion == "4.7")
      location.reload();
  }

  if(isNN)
  {
    var width = top.frames[1].innerWidth;

    //top.shiftMenubar();		//adjust menubar to new Frame size
  }

  if(width)
  {
    // save the new width in a (persistent) Cookie
    today = new Date();
    expire = new Date(today.getFullYear()+5, today.getMonth());
    setCookie("treeFrame_width", width, "never");
    //alert("Set Cookie \'treeFrame_width\' to: " + getCookie("treeFrame_width"));
    top.frames[0].location.reload();
  }

  return false;
}