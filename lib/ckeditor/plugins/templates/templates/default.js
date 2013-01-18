/*
Copyright (c) 2003-2011, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.addTemplates('default',
	{
		imagesPath:CKEDITOR.getUrl(CKEDITOR.plugins.getPath('templates')+'templates/images/'),
		templates:
		[{
			title:'Container Blue',
			html:'<div class="alert alert-info"><p>Text</p></div>'
		},
		{
			title:'Container Red',
			html:'<div class="alert alert-error"><p>Text</p></div>'
		},
		{
			title:'Container Green',
			html:'<div class="alert alert-success"><p>Text</p></div>'
		},
		{
			title:'Container Yellow',
			html:'<div class="alert"><p>Text</p></div>'
		}]
	}
);
