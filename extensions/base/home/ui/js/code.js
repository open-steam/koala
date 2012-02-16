// get box order from sTeam
/*new Ajax.Request(
	BackendConnector.getURL("get_boxes"),
	{
		asynchronous: false,
		evalScripts: true,
		method: "get",
		onSuccess: function(transport) {
			sortOrder = eval(transport.responseText);
			boxes = $('boxes')
			for(i in sortOrder) {
				boxes.appendChild($('boxes_'+ sortOrder[i]));
			}
		}
	}
);*

// make boxes sortable
/*Sortable.create("boxes",
	{
		onUpdate: function() // save box order to sTeam
		{
			new Ajax.Request(
				BackendConnector.getURL("set_boxes"),
				{
					asynchronous: true,
					evalScripts: true,
					method: "post",
					parameters: Sortable.serialize("boxes")
				}
			);
		}
	}
);*/