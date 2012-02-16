function showArrow(element){
	divs = element.getElementsByTagName("div");
	for (var num in divs) {
		div = divs[num];
		if (div.className == "explorer-item-menuicon") {
			div.style.display = "block";
		}
	}
}

function hideArrow(element){
	divs = element.getElementsByTagName("div");
	for (var num in divs) {
		div = divs[num];
		if (div.className == "explorer-item-menuicon") {
			div.style.display = "none";
		} else if (div.className == "explorer-item-menu") {
			div.style.display = "none";
		}
	}
}

function toggleHighlight(element) {
	line = element.parentNode.parentNode;
	if (element.checked) {
		line.style.background = "#ccc";
	} else {
		line.style.background = "transparent";
	}
}

function showMenu(element) {
	sendUpdater("GetMenu", "", "menu-wrapper");
//	divs = element.parentNode.getElementsByTagName("div");
//	for (var num in divs) {
//		div = divs[num];
//		if (div.className == "explorer-item-menu") {
//			div.style.display = "block";
//		}
//	}
}