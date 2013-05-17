function setCookie(c_name,value,1){
    document.cookie = c_name + "=" +escape(value);
}

function deleteCookie(c_name) {
    createCookie(c_name,"",-1);
}