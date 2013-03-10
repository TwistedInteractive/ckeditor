<?php
    require_once(TOOLKIT . '/class.sectionmanager.php');

    Class extension_ckeditor extends Extension
    {
        protected $addedCKEditorHeaders = false;
        protected $sections;

        /**
         * Add callback functions to backend delegates
         * @return array
         */
        public function getSubscribedDelegates(){
            return array(
                array('page'        =>  '/backend/',
                      'delegate'    =>  'ModifyTextareaFieldPublishWidget',
                      'callback'    =>  'applyCKEditor'),

                array('page'        =>  '/backend/',
                      'delegate'    =>  'ModifyTextBoxFullFieldPublishWidget',
                      'callback'    =>  'applyCKEditor'),

                array('page'        => '/system/preferences/',
                      'delegate'    => 'AddCustomPreferenceFieldsets',
                      'callback'    => 'appendPresets'),

                array('page'        => '/system/preferences/',
                      'delegate'    => 'Save',
                      'callback'    => 'savePresets')
            );
        }

        /**
         * Append presets
         * @param $context
         */
        public function appendPresets($context)
        {
            Symphony::Engine()->Page->addScriptToHead(URL . '/extensions/ckeditor/assets/preferences.js', 4676);

            $wrapper = $context['wrapper'];

            $fieldset = new XMLElement('fieldset', '', array('class'=>'settings'));
            $fieldset->appendChild(new XMLElement('legend', __('CKEditor File Browser')));

            $sectionManager = new SectionManager($this);
            $sections = $sectionManager->fetch();

            // Check which sections are allowed:
            $data = Symphony::Configuration()->get('sections', 'ckeditor');
            $checkedSections = $data != false ? explode(',', $data) : array();

            // If there are no sections found:
            if($sections)
            {
                $options = array();
                foreach($sections as $section)
                {
                    $options[] = array($section->get('id'), in_array($section->get('id'), $checkedSections), $section->get('name'));
                }
                $label = Widget::Label(__('Permitted sections for the file browser:'));
                $label->appendChild(Widget::Select('ckeditor_sections[]', $options, array('multiple'=>'multiple')));
                $fieldset->appendChild($label);
            }

            // Link templates for CKEditor:
            $sections = SectionManager::fetch();
            $dbpages    = PageManager::fetch();

            $pages = array();

            // Filter out the ck_hide:
            foreach ($dbpages as $page) {
                $types = PageManager::fetchPageTypes($page['id']);
                if(!in_array('ck_hide', $types))
                {
                    $pages[] = $page;
                }
            }

            // Adjust page title:
            foreach ($pages as &$_page) {
                $p = $_page;
                $title = $_page['title'];
                while (!is_null($p['parent'])) {
                    $p = PageManager::fetch(false, array(), array('id' => $p['parent']));
                    $title = $p['title'] . ' : ' . $title;
                }
                $_page['title'] = $title;
            }

            // Sort the array:
            $titles = array();
            foreach ($pages as $key => $row) {
                $titles[$key] = strtolower($row['title']);
            }
            array_multisort($titles, SORT_ASC, $pages);

            $this->sections = array();
            foreach($sections as $s)
            {
                $a = array('id'=>$s->get('id'), 'name'=>$s->get('name'), 'fields'=>array());
                $fields = FieldManager::fetch(null, $s->get('id'));
                foreach($fields as $field)
                {
                    // For now, only allow fields of the type 'input' to be used as a handle:
                    if($field->get('type') == 'input')
                    {
                        $a['fields'][] = array('id'=>$field->get('id'), 'label'=>$field->get('label'), 'element_name'=>$field->get('element_name'));
                    }
                }
                $this->sections[] = $a;
            }

            $fieldset->appendChild(new XMLElement('p', __('Link templates:'), array('class' => 'label')));
            $ol = new XMLElement('ol');
            $ol->setAttribute('class', 'ckeditor-duplicator');

            $templates = Symphony::Database()->fetch('SELECT * FROM `tbl_ckeditor_link_templates`;');
            if(!is_array($pages)) $pages = array($pages);

            foreach($pages as $page)
            {
                foreach($templates as $template) {
                    if($template['page_id'] != $page['id']) continue;
                    $duplicator = $this->__buildDuplicatorItem($page, $template);
                    $ol->appendChild($duplicator);
                }

                $duplicator = $this->__buildDuplicatorItem($page, NULL);
                $ol->appendChild($duplicator);
            }

            $fieldset->appendChild($ol);

            // Plugin presets:
            $fieldset->appendChild(new XMLElement('p', __('Plugin presets:'), array('class' => 'label')));
            $ol = new XMLElement('ol');
            $ol->setAttribute('class', 'ckeditor-duplicator');

            // Create template:
            $template = new XMLElement('li', null, array('class' => 'template'));
            $template->appendChild(new XMLElement('header', '<h3>'.__('New Preset').'</h3>'));
            $template->appendChild(Widget::Label(__('Name'), Widget::Input('ckeditor_presets[-1][name]')));
            $template->appendChild(Widget::Label(__('Toolbar'), Widget::Textarea('ckeditor_presets[-1][toolbar]', 5, 50)));
            $template->appendChild(Widget::Label(__('Plugins'), Widget::Textarea('ckeditor_presets[-1][plugins]', 5, 50)));
            $template->appendChild(Widget::Label(__('%s Enable resizing', array(Widget::Input('ckeditor_presets[-1][resize]', 'yes', 'checkbox')->generate()))));
            $template->appendChild(Widget::Label(__('%s Show outline blocks', array(Widget::Input('ckeditor_presets[-1][outline]', 'yes', 'checkbox')->generate()))));

            $ol->appendChild($template);

            // Append all the fields:
            $presets = Symphony::Database()->fetch('SELECT * FROM `tbl_ckeditor_presets');
            $index   = 0;
            foreach($presets as $preset)
            {
                $template = new XMLElement('li');
                $template->appendChild(new XMLElement('header', '<h3>'.$preset['name'].'</h3>'));
                $template->appendChild(Widget::Label(__('Name'), Widget::Input('ckeditor_presets['.$index.'][name]', $preset['name'])));
                $template->appendChild(Widget::Label(__('Toolbar'),
                    Widget::Textarea('ckeditor_presets['.$index.'][toolbar]', 5, 50, $preset['toolbar'])));
                $template->appendChild(Widget::Label(__('Plugins'),
                    Widget::Textarea('ckeditor_presets['.$index.'][plugins]', 5, 50, $preset['plugins'])));
                $template->appendChild(Widget::Label(__('%s Enable resizing',
                    array(Widget::Input('ckeditor_presets['.$index.'][resize]', '1', 'checkbox',
                        ($preset['resize'] == 1 ? array('checked'=>'checked') : null)
                    )->generate()))));
                $template->appendChild(Widget::Label(__('%s Show outline blocks',
                    array(Widget::Input('ckeditor_presets['.$index.'][outline]', '1', 'checkbox',
                        ($preset['outline'] == 1 ? array('checked'=>'checked') : null)
                    )->generate()))));
                $ol->appendChild($template);
                $index++;
            }

            $fieldset->appendChild($ol);

            // Styles:
            $fieldset->appendChild(new XMLElement('p', __('Styles: (one style per line: <code>h3.example { color: #f00; background: #0f0; }</code>) Class name is converted to name (h3.hello-world = Hello World).'), array('class'=>'label')));
            $textarea = Widget::Textarea('ckeditor[styles]', 5, 50, Symphony::Configuration()->get('styles', 'ckeditor'));
            $fieldset->appendChild($textarea);

            $wrapper->appendChild($fieldset);
        }

        /**
         * @param $page
         * @param null $template
         * @return XMLElement
         */
        private function __buildDuplicatorItem($page, $template=NULL) {
            // value of -1 signifies a duplicator "template"
            $index = ($template == NULL) ? '-1' : $template['id'];

            $wrapper = new XMLElement('li');
            $wrapper->setAttribute('class', ($template == NULL) ? 'template' : '');

            $header = new XMLElement('header', null, array('data-name' => $page['title']));
            $header->appendChild(new XMLElement('h4', $page['title']));
            $wrapper->appendChild($header);

            $divgroup = new XMLElement('div');

            $label = Widget::Label(__('Link template') . '<i>' . __('Use {$id} for the entry ID, and {$fieldname} for field-placeholders. If the field has a handle, this is automatically used.') . '</i>');
            $label->appendChild(Widget::Input(
                "ckeditor_link_templates[" . $index . "][link]",
                General::sanitize($template['link']
            )));
            $divgroup->appendChild($label);
            $wrapper->appendChild($divgroup);

            $divgroup = new XMLElement('div', null, array('class'=>'group'));

            $label = Widget::Label(__('Section to get the entries from'));
            $options = array();
            foreach($this->sections as $section)
            {
                $options[] = array($section['id'], $template['section_id'] == $section['id'], $section['name']);
            }
/*            $label->appendChild(Widget::Select('ckeditor_link_templates[' . $index . '][section]', $options, array('onchange'=>
                "jQuery('optgroup[label!=' + jQuery(this).val() + '], optgroup[label!=' + jQuery(this).val() + '] option', jQuery(this).parent().parent()).hide()")));*/
            $label->appendChild(Widget::Select('ckeditor_link_templates[' . $index . '][section_id]', $options));
            $divgroup->appendChild($label);

            $label = Widget::Label(__('Field to display as name'));
            $options = array(array('', false, 0));
            foreach($this->sections as $section)
            {
                $fields = array();
                foreach($section['fields'] as $field)
                {
                    $fields[] = array($field['id'], $template['field_id'] == $field['id'], $field['label']);
                }
                $options[] = array('label'=>$section['name'], 'options'=>$fields);
            }
            $label->appendChild(Widget::Select('ckeditor_link_templates[' . $index . '][field_id]', $options));
            $divgroup->appendChild($label);

            $wrapper->appendChild(new XMLElement('input', NULL, array(
                'type' => 'hidden',
                'name' => 'ckeditor_link_templates[' . $index . '][page_id]',
                'value' => $page['id']
            )));

            $wrapper->appendChild($divgroup);

            return $wrapper;
        }

        /**
         * Save the presets
         * @param $context
         */
        public function savePresets($context)
        {
            if(isset($_POST['ckeditor_sections'])) {
                // Save the sections to the config-file
                $sectionStr = implode(',', $_POST['ckeditor_sections']);
                Symphony::Configuration()->set('sections', $sectionStr, 'ckeditor');
                if(version_compare(Administration::Configuration()->get('version', 'symphony'), '2.2.5', '>'))
                {
                    // 2.3 and up:
                    Symphony::Configuration()->write();
                } else {
                    // Earlier versions:
                    Symphony::Configuration()->write();
                }

            } else {
                // If no sections are selected, delete the file:
                Symphony::Configuration()->remove('sections', 'ckeditor');
                Symphony::Configuration()->write();
            }
            if(isset($_POST['ckeditor_link_templates'])) {
                // Save the link templates to the database:
                Symphony::Database()->query("DELETE FROM `tbl_ckeditor_link_templates`");

                $shortcuts = $_POST['ckeditor_link_templates'];
                unset($_POST['ckeditor_link_templates']);

                if(!empty($shortcuts))
                {
                    foreach($shortcuts as $i => $shortcut) {
                        Symphony::Database()->insert($shortcut, "tbl_ckeditor_link_templates");
                    }
                }
            }
            if(isset($_POST['ckeditor']['styles']))
            {
                Symphony::Configuration()->set('styles', General::sanitize($_POST['ckeditor']['styles']), 'ckeditor');
                Symphony::Configuration()->write();
            } else {
                Symphony::Configuration()->remove('styles', 'ckeditor');
                Symphony::Configuration()->write();
            }
            // Presets:
            if(isset($_POST['ckeditor_presets']))
            {
                // Delete formatter references from DB:
                Symphony::Database()->query("DELETE FROM `tbl_ckeditor_presets`");

                // Delete formatter files:
                $formatters = glob(EXTENSIONS.'/ckeditor/text-formatters/formatter.*.php');
                foreach($formatters as $formatter) { unlink($formatter); }

                // Create it all new:
                foreach($_POST['ckeditor_presets'] as $preset)
                {
                    Symphony::Database()->insert($preset, 'tbl_ckeditor_presets');
                    // Create text formatter file:
                    $str = file_get_contents(EXTENSIONS.'/ckeditor/text-formatters/template.ckeditor.php');
                    $handle = 'ckeditor_'.General::createHandle($preset['name'], 255, '_');
                    $str = str_replace(array('{{NAME}}', '{{HANDLE}}'), array($preset['name'], $handle), $str);
                    file_put_contents(EXTENSIONS.'/ckeditor/text-formatters/formatter.'.$handle.'.php', $str);
                }
            }
        }

        /**
         * Install CKEditor
         * @return void
         */
        public function install()
        {
            Symphony::Database()->query("
                CREATE TABLE IF NOT EXISTS `tbl_ckeditor_link_templates` (
                `id` int(11) NOT NULL auto_increment,
                `link` varchar(255) NOT NULL,
                `field_id` int(11) NOT NULL,
                `section_id` int(11) NOT NULL,
                `page_id` int(11) NOT NULL,
                `sort_order` int(11) NOT NULL,
                PRIMARY KEY (`id`)
                )
            ");

            Symphony::Database()->query("
                CREATE TABLE IF NOT EXISTS `tbl_ckeditor_presets` (
                `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                `name` VARCHAR( 255 ) NOT NULL ,
                `toolbar` TEXT NULL ,
                `plugins` TEXT NULL ,
                `resize` INT( 1 ) NULL ,
                `outline` INT( 1 ) NULL
                ) ENGINE = MYISAM ;
            ");

            // Fill default presets:
            Symphony::Database()->query("
            INSERT INTO `tbl_ckeditor_presets` (`name`, `toolbar`, `plugins`, `resize`, `outline`) VALUES
('Minimal', '[''Bold'', ''Italic'', ''Strike'', ''-'', ''Subscript'', ''Superscript''],\r\n[''Link'', ''Unlink''],\r\n[''Source'']', NULL, NULL, NULL),
('Normal', '[''Bold'', ''Italic'', ''Strike'', ''-'', ''Subscript'', ''Superscript''],\r\n[''NumberedList'', ''BulletedList'', ''-'', ''Outdent'', ''Indent'', ''Blockquote''],\r\n[''Image'', ''oembed''],[''Link'', ''Unlink''],\r\n[''HorizontalRule''],\r\n[''Source'', ''Maximize'']', NULL, 1, 1),
('Full', '{ name: ''document'',    items : [ ''Source'',''-'',''Save'',''NewPage'',''DocProps'',''Preview'',''Print'',''-'',''Templates'' ] },\r\n    { name: ''clipboard'',   items : [ ''Cut'',''Copy'',''Paste'',''PasteText'',''PasteFromWord'',''-'',''Undo'',''Redo'' ] },\r\n    { name: ''editing'',     items : [ ''Find'',''Replace'',''-'',''SelectAll'',''-'',''SpellChecker'', ''Scayt'' ] },\r\n    { name: ''forms'',       items : [ ''Form'', ''Checkbox'', ''Radio'', ''TextField'', ''Textarea'', ''Select'', ''Button'', ''ImageButton'', ''HiddenField'' ] },\r\n    ''/'',\r\n    { name: ''basicstyles'', items : [ ''Bold'',''Italic'',''Underline'',''Strike'',''Subscript'',''Superscript'',''-'',''RemoveFormat'' ] },\r\n    { name: ''paragraph'',   items : [ ''NumberedList'',''BulletedList'',''-'',''Outdent'',''Indent'',''-'',''Blockquote'',''CreateDiv'',''-'',''JustifyLeft'',''JustifyCenter'',''JustifyRight'',''JustifyBlock'',''-'',''BidiLtr'',''BidiRtl'' ] },\r\n    { name: ''links'',       items : [ ''Link'',''Unlink'',''Anchor'' ] },\r\n    { name: ''insert'',      items : [ ''Image'',''Flash'',''Table'',''HorizontalRule'',''Smiley'',''SpecialChar'',''PageBreak'' ] },\r\n    ''/'',\r\n    { name: ''styles'',      items : [ ''Styles'',''Format'',''Font'',''FontSize'' ] },\r\n    { name: ''colors'',      items : [ ''TextColor'',''BGColor'' ] },\r\n    { name: ''tools'',       items : [ ''Maximize'', ''ShowBlocks'',''-'',''About'' ] }', NULL, 1, 1);
            ");

            // Delete formatter files:
            $formatters = glob(EXTENSIONS.'/ckeditor/text-formatters/formatter.*.php');
            foreach($formatters as $formatter) { unlink($formatter); }

            // Create it all new:
            $presets = Symphony::Database()->fetch('SELECT * FROM `tbl_ckeditor_presets`;');
            foreach($presets as $preset)
            {
                unset($preset['id']);
                Symphony::Database()->insert($preset, 'tbl_ckeditor_presets');
                // Create text formatter file:
                $str = file_get_contents(EXTENSIONS.'/ckeditor/text-formatters/template.ckeditor.php');
                $handle = 'ckeditor_'.General::createHandle($preset['name'], 255, '_');
                $str = str_replace(array('{{NAME}}', '{{HANDLE}}'), array($preset['name'], $handle), $str);
                file_put_contents(EXTENSIONS.'/ckeditor/text-formatters/formatter.'.$handle.'.php', $str);
            }
        }

        /**
         * Update CKEditor
         * @param bool|string $prevVersion
         */
        public function update($prevVersion)
        {
            if(version_compare($prevVersion, '1.4', '<'))
            {
                $this->install();
            }
        }

        /**
         * On uninstall, delete the ckeditor_sections-file
         */
        public function uninstall()
        {
            Symphony::Configuration()->remove('sections', 'ckeditor');
            Administration::instance()->saveConfig();
            Symphony::Database()->query("DROP TABLE `tbl_ckeditor_link_templates`");
            Symphony::Database()->query("DROP TABLE `tbl_ckeditor_presets`");
        }

        /**
         * Load and apply CKEditor
         * @param $context
         * @return mixed
         */
        public function applyCKEditor($context) {

/*          $format = $context['field']->get('text_formatter') == TRUE ? 'text_formatter' : 'formatter';

            if(($context['field']->get($format) != 'ckeditor' && $context['field']->get($format) != 'ckeditor_compact')) return;*/

            if(!$this->addedCKEditorHeaders){
                Administration::instance()->Page->addScriptToHead(URL . '/extensions/ckeditor/lib/ckeditor/ckeditor.js', 200, false);
                Administration::instance()->Page->addScriptToHead(URL . '/symphony/extension/ckeditor/js/', 209, false);
                Administration::instance()->Page->addScriptToHead(URL . '/extensions/ckeditor/assets/symphony.ckeditor.js', 210, false);
                Administration::instance()->Page->addStylesheetToHead(URL . '/extensions/ckeditor/assets/symphony.ckeditor.css', 'screen', 30);

                $js = 'var ckeditor_presets = [];';
                $presets = Symphony::Database()->fetch('SELECT * FROM `tbl_ckeditor_presets`;');
                foreach($presets as $preset)
                {
                    $js .= 'ckeditor_presets.push({name:"'.$preset['name'].'", class: "ckeditor_'.
                        General::createHandle($preset['name'], 255, '_').'", toolbar: ['.$preset['toolbar'].'], plugins: "'.
                        $preset['plugins'].'", resize: '.($preset['resize'] == 1 ? 'true' : 'false').', outline: '.
                        ($preset['outline'] == 1 ? 'true' : 'false').'});'."\n";
                }
                $script = new XMLElement('script', $js, array('type'=>'text/javascript'));
                Administration::instance()->Page->addElementToHead($script);


                $this->addedCKEditorHeaders = true;
            }
        }

    }
