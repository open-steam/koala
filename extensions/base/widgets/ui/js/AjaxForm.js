function formToObject(formId) {
	var object = {};
	jQuery('#' + formId).find(':input:text, :input:hidden, :input:radio:checked').each(function() {
        var self = jQuery(this);
        var name = self.attr('name');
        var type = self.attr('type');
        object[name] = self.val();
    });
	
	jQuery('#' + formId).find('option:selected').each(function() {
		var self = jQuery(this);
		var name = self.parent().attr('name');
		var type = 'select';
		object[name] = self.val();
	});
	
	if (window.tinyMCE && tinyMCE.activeEditor) {
		object[tinyMCE.activeEditor.editorId] = tinyMCE.activeEditor.getContent();
	}
			
    return object;

}