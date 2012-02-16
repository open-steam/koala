<?php require_once "../../../etc/koala.def.php"; ?>

BackendConnector = new Object();

BackendConnector.URL = "<?php echo PATH_URL; ?>ajax_backend.php";

BackendConnector.getURL = function(action, attribute)
{   
	if ( typeof(attribute) == "undefined" ) {
		return (BackendConnector.URL + "?action=" + action);
	}
	else {
		return (BackendConnector.URL + "?action=" + action + "&attribute=" + escape(attribute));
	}
}
