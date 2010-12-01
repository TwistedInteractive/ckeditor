jQuery(document).ready(function () {
    var count = 0;
    
    jQuery('label > textarea.ckeditor').each(function(index) {
        var objectName = jQuery(this).attr('name');    
        var configurationData = {
            language : 'en',            
            height : this.offsetHeight,
            removePlugins : 'font,entities,styles',
            extraPlugins : 'xmlentities',
            uiColor : '#d6d6c7',
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
            filebrowserBrowseUrl: '/symphony/extension/ckeditor/filebrowser/'
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
		
        CKEDITOR.replaceByClassEnabled = false;
        CKEDITOR.replace(objectName, configurationData);
    });
});