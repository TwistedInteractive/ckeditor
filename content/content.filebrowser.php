<?php
	require_once(TOOLKIT . '/class.htmlpage.php');
	require_once(TOOLKIT . '/class.sectionmanager.php');
	require_once(TOOLKIT . '/class.extensionmanager.php');

	Class contentExtensionCkeditorFilebrowser extends HTMLPage
	{

		public function build($context)
		{
			// Build the page:
			$this->setTitle('Symphony - File Browser for CKEditor');
            $this->addElementToHead(new XMLElement('meta', NULL, array('http-equiv' => 'Content-Type', 'content' => 'text/html; charset=UTF-8')), 0);
            $this->addElementToHead(new XMLElement('meta', NULL, array('http-equiv' => 'X-UA-Compatible', 'content' => 'IE=edge,chrome=1')), 1);
			$this->addStylesheetToHead(URL . '/symphony/assets/css/symphony.css', 'screen', 68);
			$this->addStylesheetToHead(URL . '/symphony/assets/css/admin.css', 'screen', 69);
			$this->addStylesheetToHead(URL . '/extensions/ckeditor/assets/filebrowser.css', 'screen', 70);
			$this->addScriptToHead(URL . '/symphony/assets/js/jquery.js', 50);
			$this->addScriptToHead(URL . '/extensions/ckeditor/assets/jquery.form.js', 51);
			$this->addScriptToHead(URL . '/extensions/ckeditor/assets/filebrowser.js', 52);
			$this->addHeaderToPage('Content-Type', 'text/html; charset=UTF-8');
            $this->Html->setDTD('<!DOCTYPE html>');
			
			// Check for unauthorized access:
			if(!Administration::instance()->isLoggedIn()){
				$this->_Parent->customError(E_USER_ERROR, __('Access Denied'), __('You are not authorised to access this page.'));
				exit();
			}
			
			$body = new XMLElement('div', '', array('id'=>'body'));

			$left = new XMLElement('div', '', array('class'=>'left'));
			$right = new XMLElement('div', '', array('class'=>'right'));
			$left->appendChild(new XMLElement('h3', __('Section')));
			
			// Get the sections:
			$sectionManager = new SectionManager($this);
			$sections = $sectionManager->fetch();
			
			// Check which sections are allowed:
			$data = Symphony::Configuration()->get('sections', 'ckeditor');
			$checkedSections = $data != false ? explode(',', $data) : array();
			
			if(count($checkedSections) > 0)
			{
				$list = new XMLElement('ul');
				foreach($sections as $section)
				{
					if(in_array($section->get('id'), $checkedSections))
					{
						$item = new XMLElement('li');
						$attributes = array('href'=>'#', 'id'=>$section->get('id'));
						$link = new XMLElement('a', $section->get('name'), $attributes);
						$item->appendChild($link);
						$list->appendChild($item);
					}
				}				
				$left->appendChild($list);
			} else {
				$left->appendChild(new XMLElement('p', __('There are no sections available. Please select which sections are permitted to use the CKEditor file upload feature in the Symphony System Preferences.')));
			}
			
			$body->appendChild($left);
			$body->appendChild($right);
			
			$this->Body->appendChild($body);
		}

	}
?>