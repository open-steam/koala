/***********************************************
 * focus.js                                    *
 * by Pieter Walsweer, October 2002            *
 * Copyright (c) Hyperworks Internet Solutions *
 * Version 1.0                                 *
 ***********************************************/

function setFocus() {
	for (var j=0; j < document.forms.length; j++) {
		for (var i=0; i < document.forms[j].elements.length; i++) {
			if (document.forms[j].elements[i].type == 'text' || document.forms[j].elements[i].type == 'textarea' || document.forms[j].elements[i].type == 'password') {
				if (document.forms[j].elements[i].enabled == true) {
				  document.forms[j].elements[i].focus();
				  return;
				}
			}
		}
	}
}