/***********************************************
 * lib/cookie.js                               *
 * by Ralf Kuhnert, August 2000                *
 * edited by Pieter Walsweer, January 2002     *
 * Copyright (c) Hyperworks Internet Solutions *
 ***********************************************/

// Arguments:
//	name		name of the Cookie
//	value		value of the Cookie
//	expire	Date object with explicit expiration date / or String "never"
function setCookie(name, value, expire){
  var cookie = getCookie(name);
  if (cookie != value) {
    var out = "";

    out += name + "=" + escape(value);

    if (expire != null) {
      if ((typeof expire == "object") && expire.constructor == Date) {
        // explicit expiration date
        out += "; expires=" + expire.toGMTString();
      }
      else {
        if (expire == "never") {
          // expire in 5 years
          today = new Date();
          expire = new Date(today.getFullYear()+5, today.getMonth());
          out += "; expires=" + expire.toGMTString();
        }
      }
    }

    out += ";path=/;"

    document.cookie = out;
    //alert("Content of Cookie:\n" + document.cookie);
  }
}


function getCookie(name){
   var search = name + "=";
   var cookie=null;
   if (document.cookie.length > 0) {
     offset = document.cookie.indexOf(search);
     if (offset != -1) {
       offset += search.length;
       end = document.cookie.indexOf(";", offset);
       if (end == -1)
          end = document.cookie.length;
       return unescape(document.cookie.substring(offset, end));
     }
   } else {
     return -1;
   }
   return cookie;
}
