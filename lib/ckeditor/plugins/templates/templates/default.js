/*
Copyright (c) 2003-2011, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.addTemplates('default',
	{
		imagesPath:CKEDITOR.getUrl(CKEDITOR.plugins.getPath('templates')+'templates/images/'),
		templates:
		[{
			title:'Container Blauw',
			html:'<div class="alert alert-info"><p>Tekst</p></div>'
		},
		{
			title:'Container Rood',
			html:'<div class="alert alert-error"><p>Tekst</p></div>'
		},
		{
			title:'Container Groen',
			html:'<div class="alert alert-success"><p>Tekst</p></div>'
		},
		{
			title:'Container Geel',
			html:'<div class="alert"><p>Tekst</p></div>'
		}]
	}
);
