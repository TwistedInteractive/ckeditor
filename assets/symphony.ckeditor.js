if (typeof Symphony.ADMIN == "undefined") {
	Symphony.ADMIN = window.location.toString().match(/^(.+\/symphony)/)[1];
}

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
            filebrowserBrowseUrl: Symphony.ADMIN + '/extension/ckeditor/filebrowser/'
        };
        CKEDITOR.replaceByClassEnabled = false;
        CKEDITOR.replace(objectName, configurationData);
    });
});