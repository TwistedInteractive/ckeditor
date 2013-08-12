CKEDITOR.replaceClass = null;

jQuery(document).ready(function () {
    // Add custom styles:
    CKEDITOR.stylesSet.add('default', ckeditor_styles);

    // See if there are any ckeditor textareas:
    jQuery('textarea[class*="ckeditor"]').each(function(index) {
        var $this = jQuery(this);

        // Get the class name:
        var classNames = this.className;
        var myClassName = '';
        var a = classNames.split(' ');
        for(var i in a){
            if(a[i].toString().indexOf('ckeditor') != -1)
            {
                myClassName = a[i];
            }
        }

        // Set the configurationdata:
        var ck_configurationData = {};
        ck_configurationData.language = 'en';
        ck_configurationData.skin = 'moono';
        ck_configurationData.replaceByClassEnabled = false;
        ck_configurationData.forcePasteAsPlainText = true;
        ck_configurationData.format_tags = 'p;h1;h2;h3';
        ck_configurationData.entities_processNumerical = 'force';
        ck_configurationData.filebrowserBrowseUrl = Symphony.Context.get('root') + '/symphony/extension/ckeditor/filebrowser/';
        ck_configurationData.contentsCss = [Symphony.Context.get('root') + '/extensions/ckeditor/lib/ckeditor/contents.css', Symphony.Context.get('root') + '/symphony/extension/ckeditor/css/'];

        // Set the correct height and width:
        ck_configurationData.height = jQuery(this).height();
        ck_configurationData.width = '100%'; // add some width to make up for the margins

        var formatBlock = ckeditor_styles.length > 0 ? ['Format', 'Styles', 'RemoveFormat'] : ['Format'];

        // Set the correct preset:
        for(var i in ckeditor_presets)
        {
            if(ckeditor_presets[i].class == myClassName)
            {
                var info = ckeditor_presets[i];
                info.toolbar.unshift(formatBlock);
                ck_configurationData.toolbar = info.toolbar;
                ck_configurationData.resize_enabled = info.resize;
                ck_configurationData.startupOutlineBlocks = info.outline;
                ck_configurationData.extraPlugins = info.plugins;
            }
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
            // fix width issue in SBL+
            $this.siblings('span').css('width','');
        });

        //Stop CKEditor creating another instance
        jQuery(this).removeClass(myClassName);

        // Replace CKEditor instances:
        CKEDITOR.replace(objectName, ck_configurationData);
    });
});