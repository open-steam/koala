/**
 * lib/reload.js 
 * by Ralf Kuhnert, August 2000
 * Copyright (c) [Hyperworks Internet Solutions]
 */


function reloadIfNeccessary(){
  if (getCookie("RELOAD_NECCESSARY") == "yes") {
    setCookie("RELOAD_NECCESSARY", "no", null);
    window.location.reload();
  }
}
