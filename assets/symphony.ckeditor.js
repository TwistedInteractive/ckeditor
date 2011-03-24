if (typeof Symphony.ADMIN == "undefined") {
	Symphony.ADMIN = window.location.toString().match(/^(.+\/symphony)/)[1];
}

jQuery(document).ready(function () {
	
	// See if there are any ckeditor textareas:
    jQuery('label > textarea.ckeditor').each(function(index) {
		
		// Disable replacing by class:
		CKEDITOR.replaceByClassEnabled = false;
		
		// Set the objectname:
        var objectName = jQuery(this).attr('name');
		
		// Fix for IE:
		var objectWidth = jQuery(this).width();
		
		// Set the configurationdata:
        var configurationData = {
			// width : objectWidth,			
            language : 'en',
            skin:'chris',
            height : this.offsetHeight,
            removePlugins : 'font,styles',
            extraPlugins : 'xmlentities',
            // uiColor : '#d6d6c7',
            startupOutlineBlocks : true,
            replaceByClassEnabled : false,
            xmlentities : true,
            toolbar : 
            [
                ['Format'],
                ['Bold', 'Italic', 'Strike', '-', 'Subscript', 'Superscript'],
                ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', 'Blockquote'],
                ['Image'],['Link', 'Unlink'],
                ['HorizontalRule'],
                ['Source', 'Maximize']
            ],
            forcePasteAsPlainText: true,
            format_tags: 'p;h1;h2;h3',
            entities_processNumerical: 'force',
            filebrowserBrowseUrl: Symphony.ADMIN + '/extension/ckeditor/filebrowser/'
        };
		
		// Do not add linebreaks and spaces after opening and before closing tags.
		CKEDITOR.on('instanceReady', function(ev){
			var tags = ['p', 'ol', 'ul', 'li']; // etc.
			for (var key in tags) {
				ev.editor.dataProcessor.writer.setRules(tags[key],
					{
						indent : false,
						breakBeforeOpen : true,
						breakAfterOpen : false,
						breakBeforeClose : false,
						breakAfterClose : true
					});
			}
		});
		
		// Replace CKEditor instances:
        CKEDITOR.replace(objectName, configurationData);
    });
});