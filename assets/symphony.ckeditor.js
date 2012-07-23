jQuery(document).ready(function () {

	// See if there are any ckeditor textareas:
    jQuery('textarea.ckeditor, textarea.ckeditor_compact').each(function(index) {
    
    	var ck_configurationData = {};
    	ck_configurationData.language = 'en';
    	ck_configurationData.skin = 'chris';
    	ck_configurationData.replaceByClassEnabled = false;
    	ck_configurationData.forcePasteAsPlainText = true;
    	ck_configurationData.format_tags = 'p;h1;h2;h3';
    	ck_configurationData.entities_processNumerical = 'force';
    	ck_configurationData.filebrowserBrowseUrl = Symphony.Context.get('root') + '/symphony/extension/ckeditor/filebrowser/';
    	
        // Set the configurationdata:
        // Set the correct height and width:
	    ck_configurationData.height = jQuery(this).height();
	    ck_configurationData.width = jQuery(this).width();

        // Check if this is the compact CKEditor:
        if(jQuery(this).hasClass("ckeditor_compact"))
        {
            jQuery(this).parent().addClass("ck_compact");
	        ck_configurationData.toolbar =
            [
                ['Bold', 'Italic', 'Strike', '-', 'Subscript', 'Superscript'],
                ['Link', 'Unlink'],
                ['Source']
            ];
	        ck_configurationData.resize_enabled = false;
	        ck_configurationData.removePlugins = 'font,styles,elementspath';
	        ck_configurationData.startupOutlineBlocks = false;
        } else {
	        jQuery(this).parent().addClass("ck_full");
	        ck_configurationData.toolbar =
            [
                ['Format'],
                ['Bold', 'Italic', 'Strike', '-', 'Subscript', 'Superscript'],
                ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', 'Blockquote'],
                ['Image'],['Link', 'Unlink'],
                ['HorizontalRule'],
                ['Source', 'Maximize']
            ];
	        ck_configurationData.resize_enabled = true;
	        ck_configurationData.removePlugins = 'font,styles';
	        ck_configurationData.startupOutlineBlocks = true;
        }

		// Set the objectname:
        var objectName = jQuery(this).attr('name');

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
            // Add a border:
            jQuery("label.ck_compact td.cke_contents").css({borderBottom: "1px solid #aaa"});
		});

		//Stop CKEditor creating another instance
		jQuery(this).removeClass('ckeditor ckeditor_compact');

		// Replace CKEditor instances:
        CKEDITOR.replace(objectName, ck_configurationData);
    });
});