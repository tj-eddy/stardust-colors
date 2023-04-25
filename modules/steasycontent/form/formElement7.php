<?php
class FormElement7 extends StEasyContent
{
    public static $googlemap_zoom = array(
        array('id'=>1, 'name'=> '1'),
        array('id'=>2, 'name'=> '2'),
        array('id'=>3,'name'=> '3'),
        array('id'=>4, 'name'=> '4'),
        array('id'=>5, 'name'=> '5'),
        array('id'=>6, 'name'=> '6'),
        array('id'=>7, 'name'=> '7'),
        array('id'=>8, 'name'=> '8'),
        array('id'=>9, 'name'=> '9'),
        array('id'=>10, 'name'=> '10'),
        array('id'=>11, 'name'=> '11'),
        array('id'=>12, 'name'=> '12'),
        array('id'=>13, 'name'=> '13'),
        array('id'=>14, 'name'=> '14'),
        array('id'=>15, 'name'=> '15'),
        array('id'=>16, 'name'=> '16'),
        array('id'=>17, 'name'=> '17'),
        array('id'=>18, 'name'=> '18'),
    );
    public static $googlemap_type = array(
        array('value' => 'roadmap', 'id' => 'roadmap', 'label' => 'roadmap'),
        array('value' => 'hybrid', 'id' => 'hybrid', 'label' => 'hybrid'),
        array('value' => 'satellite', 'id' => 'satellite', 'label' => 'satellite'),
        array('value' => 'terrain', 'id' => 'terrain', 'label' => 'terrain'),
    );
    public function initFormElement()
    {
        $id_st_easy_content_element = Tools::getValue('id_st_easy_content_element');
        if (!$id_st_easy_content_element && !$this->id_st_easy_content_column) {
            Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules')); 
        }
        $easycontent_element = new StEasyContentElementClass($id_st_easy_content_element);
        
        $id_st_easy_content_column = $this->id_st_easy_content_column;
        $element = Tools::getValue('element');
        if ($easycontent_element->id) {
            $id_st_easy_content_column = $easycontent_element->id_st_easy_content_column;
            if (!$element) {
                $easycontent_column = new StEasyContentColumnClass($id_st_easy_content_column);
                $element = $easycontent_column->element;
            }
        }
        
        // No key ? redirect to the general setting.
        if (!$api_key = StEasyContentSettingClass::get($id_st_easy_content_column, 'st_gmap_api_key', 1)) {
            Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.
                '&id_st_easy_content_column='.$id_st_easy_content_column.'&element='.$element.
                '&setsteasycontentelement&token='.Tools::getAdminTokenLite('AdminModules'));
        }
        
        $gmap_url = 'https://maps.googleapis.com/maps/api/js?key='.$api_key.'&libraries=places&callback=initAutocomplete';
        Media::addJsDef(array('googlemap_url' => $gmap_url));
        
        $this->fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->getTranslator()->trans(($id_st_easy_content_element?'Edit':'Create').' a marker:', array(), 'Modules.Steasycontent.Admin'),
                'icon' => 'icon-cogs',                
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('Marker address:', array(), 'Modules.Steasycontent.Admin'),
                    'name' => 'st_gmap_address',
                    'validation' => 'isAnything',
                    'desc' => $this->getTranslator()->trans('Auto complelete, type some address charactars fristly.%1% Can\'t find the address on google map, please check your address! %2%', array('%1%'=>'<div style="color:#f00;font-weight:bold;" class="st_map_not_found hidden">','%2%'=>'</div>'), 'Modules.Steasycontent.Admin'),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('Marker latitude:', array(), 'Modules.Steasycontent.Admin'),
                    'name' => 'st_gmap_lat',
                    'class' => 'fixed-width-xxl',
                    'validation' => 'isFloat',
                    'required'  => true,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('Marker longitude:', array(), 'Modules.Steasycontent.Admin'),
                    'name' => 'st_gmap_lng',
                    'class' => 'fixed-width-xxl',
                    'validation' => 'isFloat',
                    'required'  => true,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('Marker title:', array(), 'Modules.Steasycontent.Admin'),
                    'name' => 'st_gmap_marker_title',
                    'class' => 'fixed-width-xxl',
                    'lang' => true,
                    'validation' => 'isGenericName',
                    'desc' => $this->getTranslator()->trans('This shows out when mouseover the marker.', array(), 'Modules.Steasycontent.Admin')
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->getTranslator()->trans('Info window content:', array(), 'Modules.Steasycontent.Admin'),
                    'name' => 'st_gmap_marker_text',
                    'lang' => true,
                    'cols' => 60,
                    'rows' => 2,
                    'desc' => $this->getTranslator()->trans('Text and HTML tag are accept. This content would show out when clicking on the marker.', array(), 'Modules.Steasycontent.Admin')
                ),
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('Info window max width:', array(), 'Modules.Steasycontent.Admin'),
                    'name' => 'st_gmap_info_width',
                    'prefix' => 'px',
                    'class' => 'fixed-width-lg',
                    'default_value' => '',
                    'validation' => 'isNullOrUnsignedId',
                    'desc' => $this->getTranslator()->trans('Specifies the maximum width of the info window in pixels. By default, an info window expands to fit its content, and auto-wraps text if the info window fills the map.', array(), 'Modules.Steasycontent.Admin'),                        
                ),
                array(
                    'type' => 'file',
                    'label' => $this->getTranslator()->trans('Custom marker image:', array(), 'Modules.Steasycontent.Admin'),
                    'name' => 'st_gmap_marker_img',
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->getTranslator()->trans('Status:', array(), 'Admin.Theme.Panda'),
                    'name' => 'active',
                    'is_bool' => true,
                    'default_value' => 1,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->getTranslator()->trans('Enabled', array(), 'Admin.Theme.Panda'),
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->getTranslator()->trans('Disabled', array(), 'Admin.Theme.Panda'),
                        )
                    ),
                    'validation' => 'isBool',
                ),
            ),
            'buttons' => array(
                array(
                    'type' => 'submit',
                    'title' => $this->getTranslator()->trans('Save', array(), 'Admin.Actions'),
                    'icon' => 'process-icon-save',
                    'class'=> 'pull-right',
                ),
            ),
            'submit' => array(
				'title' => $this->getTranslator()->trans('Save and stay', array(), 'Admin.Actions'),
                'stay' => true,
			),
        );
        
        if (!$easycontent_element->id) {
            $this->fields_form[0]['form']['input'][] = array('type' => 'hidden', 'name' => 'id_st_easy_content_column');
            $easycontent_element->id_st_easy_content_column = $this->id_st_easy_content_column;
            if ($element = Tools::getValue('element')) {
                $this->fields_form[0]['form']['input'][] = array('type' => 'hidden', 'name' => 'element');
                $easycontent_element->element = $element;
            }
            
            $query_string = '&id_st_easy_content_column='.$this->id_st_easy_content_column.'&viewsteasycontentcolumn';
        } else {
            $query_string = '&id_st_easy_content_column='.$easycontent_element->id_st_easy_content_column.'&viewsteasycontentcolumn';
        }
        
        $this->fields_form[0]['form']['input'][] = array(
			'type' => 'html',
            'id' => 'a_cancel',
			'label' => '',
			'name' => '<a class="btn btn-default btn-block fixed-width-md" href="'.AdminController::$currentIndex.'&configure='.$this->name.$query_string.'&token='.Tools::getAdminTokenLite('AdminModules').'"><i class="icon-arrow-left"></i> '.$this->getTranslator()->trans('Back to list', array(), 'Admin.Theme.Panda').'</a>',                  
		);
        
        
        // Fetch settings to object.
        if ($easycontent_element->id) {
            $this->LoadSettingsToObject($easycontent_element, 2);    
            $this->loadImageFieldsDesc($this->fields_form[0]['form']['input'], $easycontent_element);
        }
        
        return $this->loadFormHelper('st_easy_content_element', 'element', $easycontent_element);
    }
    
    public function initFormElementSetting()
    {
        $element = Tools::getValue('element');
        $easycontent_column = new StEasyContentColumnClass($this->id_st_easy_content_column);
        if (!$element || !$easycontent_column->id) {
            Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules')); 
        }
        
        if (!$api_key = StEasyContentSettingClass::get($easycontent_column->id, 'st_gmap_api_key', 1)) {
            $this->fields_form[0]['form'] = array(
                'legend' => array(
                    'title' => $this->getTranslator()->trans('General settings:', array(), 'Modules.Steasycontent.Admin'),
                    'icon' => 'icon-cogs',                
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->getTranslator()->trans('Fill in an API key first:', array(), 'Modules.Steasycontent.Admin'),
                        'name' => 'st_gmap_api_key',
                        'required' => true,
                        'validation' => 'isGenericName',
                        'class' => 'fixed-width-xxl',
                        'desc' => array(
                                $this->getTranslator()->trans('%1%You have to fill in an API key, and then you can add Google maps to your site.', array('%1%'=>'<span class="alert alert-info span-block">'), 'Modules.Steasycontent.Admin'),
                                $this->getTranslator()->trans('How to get API key: click %1% here %2%, and enable the following APIs for the API key:', array('%1%'=>'<a target="_blank" href="https://developers.google.com/maps/documentation/javascript/get-api-key">', '%2%'=>'</a>'), 'Modules.Steasycontent.Admin'),
                                $this->getTranslator()->trans('%1% Google Maps Geocoding API %2%,%3% Google Maps JavaScript API %4% and %5% Google Places API Web Service %6%%7%.', array('%1%'=>'<b>', '%2%'=>'</b>', '%3%'=>'<b>', '%4%'=>'</b>', '%5%'=>'<b>', '%6%'=>'</b>', '%7%'=>'</span>'), 'Modules.Steasycontent.Admin'),
                            ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->getTranslator()->trans('Save', array(), 'Admin.Actions'),
                    'stay' => true
                ),
            );
        } else {
            $gmap_url = 'https://maps.googleapis.com/maps/api/js?key='.$api_key.'&libraries=places&callback=initAutocomplete';
            Media::addJsDef(array('googlemap_url' => $gmap_url));
            
            $this->fields_form[0]['form'] = array(
                'legend' => array(
                    'title' => $this->getTranslator()->trans('General settings:', array(), 'Admin.Theme.Panda'),
                    'icon' => 'icon-cogs',                
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->getTranslator()->trans('API key:', array(), 'Modules.Steasycontent.Admin'),
                        'name' => 'st_gmap_api_key',
                        'required' => true,
                        'validation' => 'isGenericName',
                        'class' => 'fixed-width-xxl',
                        'desc' => array(
                                $this->getTranslator()->trans('You have to fill in an API key, and then you can add Google maps to your site.', array(), 'Modules.Steasycontent.Admin'),
                                $this->getTranslator()->trans('Click on the first green "GET A KEY" button on %1% this page %2% to get a key:', array('%1%'=>'<a target="_blank" href="https://developers.google.com/maps/documentation/javascript/get-api-key">','%2%'=>'</a>'), 'Modules.Steasycontent.Admin'),
                            ),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->getTranslator()->trans('Map height:', array(), 'Modules.Steasycontent.Admin'),
                        'name' => 'st_gmap_height',
                        'prefix' => 'px',
                        'class' => 'fixed-width-lg',
                        'default_value' => '',
                        'validation' => 'isUnsignedInt',
                        'desc' => $this->getTranslator()->trans('Default value 200px.', array(), 'Modules.Steasycontent.Admin'),
                    ),
                    array(
                        'type' => 'radio',
                        'label' => $this->getTranslator()->trans('Map type:', array(), 'Modules.Steasycontent.Admin'),
                        'name' => 'st_gmap_type',
                        'default_value' => 'roadmap',
                        'values' => self::$googlemap_type,
                        'validation' => 'isUnsignedInt',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->getTranslator()->trans('Address:', array(), 'Modules.Steasycontent.Admin'),
                        'name' => 'st_gmap_address',
                        'validation' => 'isAnything',
                        'desc' => $this->getTranslator()->trans('Auto complelete, type some address charactars fristly.%1% Can\'t find the address on google map, please check your address! %2%',
                            array('%1%'=>'<div style="color:#f00;font-weight:bold;" class="st_map_not_found hidden">', '%2%'=>'</div>'), 'Modules.Steasycontent.Admin'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->getTranslator()->trans('Map latitude:', array(), 'Modules.Steasycontent.Admin'),
                        'name' => 'st_gmap_lat',
                        'class' => 'fixed-width-xxl',
                        'validation' => 'isFloat',
                        'required'  => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->getTranslator()->trans('Map longitude:', array(), 'Modules.Steasycontent.Admin'),
                        'name' => 'st_gmap_lng',
                        'class' => 'fixed-width-xxl',
                        'validation' => 'isFloat',
                        'required'  => true,
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->getTranslator()->trans('Zoom level:', array(), 'Modules.Steasycontent.Admin'),
                        'name' => 'st_gmap_zoom',
                        'options' => array(
                            'query' => self::$googlemap_zoom,
                            'id' => 'id',
                            'name' => 'name',
                            'default' => array(
                                'value' => '14',
                                'label' => $this->getTranslator()->trans('14', array(), 'Modules.Steasycontent.Admin')
                            )
                        ),
                    ),
                    /*array(
                        'type' => 'switch',
                        'label' => $this->getTranslator()->trans('Show marker:', array(), 'Modules.Steasycontent.Admin'),
                        'name' => 'st_gmap_marker',
                        'default_value' => 1,
                        'values' => array(
                            array(
                                'id' => 'st_gmap_marker_on',
                                'value' => 1,
                                'label' => $this->getTranslator()->trans('Yes', array(), 'Modules.Steasycontent.Admin'),
                            ),
                            array(
                                'id' => 'st_gmap_marker_off',
                                'value' => 0,
                                'label' => $this->getTranslator()->trans('No', array(), 'Modules.Steasycontent.Admin'),
                            )
                        ),
                        'validation' => 'isBool',
                    ),*/
                    array(
                        'type' => 'switch',
                        'label' => $this->getTranslator()->trans('Show marker animation:', array(), 'Modules.Steasycontent.Admin'),
                        'name' => 'st_gmap_marker_animation',
                        'is_bool' => true,
                        'default_value' => 1,
                        'values' => array(
                            array(
                                'id' => 'st_gmap_marker_animation_on',
                                'value' => 1,
                                'label' => $this->getTranslator()->trans('Yes', array(), 'Admin.Theme.Panda'),
                            ),
                            array(
                                'id' => 'st_gmap_marker_animation_off',
                                'value' => 0,
                                'label' => $this->getTranslator()->trans('No', array(), 'Admin.Theme.Panda'),
                            )
                        ),
                        'validation' => 'isBool',
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->getTranslator()->trans('Hide map controls:', array(), 'Modules.Steasycontent.Admin'),
                        'name' => 'st_gmap_hide_control',
                        'is_bool' => true,
                        'default_value' => 0,
                        'values' => array(
                            array(
                                'id' => 'st_gmap_hide_control_on',
                                'value' => 1,
                                'label' => $this->getTranslator()->trans('Yes', array(), 'Admin.Theme.Panda'),
                            ),
                            array(
                                'id' => 'st_gmap_hide_control_off',
                                'value' => 0,
                                'label' => $this->getTranslator()->trans('No', array(), 'Admin.Theme.Panda'),
                            )
                        ),
                        'validation' => 'isBool',
                    ),  
                    array(
                        'type' => 'switch',
                        'label' => $this->getTranslator()->trans('Disable map zoom on mouse wheel scroll:', array(), 'Admin.Theme.Panda'),
                        'name' => 'st_gmap_zoom_scroll_off',
                        'is_bool' => true,
                        'default_value' => 0,
                        'values' => array(
                            array(
                                'id' => 'st_gmap_zoom_scroll_on',
                                'value' => 1,
                                'label' => $this->getTranslator()->trans('Yes', array(), 'Admin.Theme.Panda'),
                            ),
                            array(
                                'id' => 'st_gmap_zoom_scroll_off',
                                'value' => 0,
                                'label' => $this->getTranslator()->trans('No', array(), 'Admin.Theme.Panda'),
                            )
                        ),
                        'validation' => 'isBool',
                    ),  
                    array(
                        'type' => 'switch',
                        'label' => $this->getTranslator()->trans('Disable dragging on touch screens:', array(), 'Modules.Steasycontent.Admin'),
                        'name' => 'st_gmap_dragging_off',
                        'is_bool' => true,
                        'default_value' => 0,
                        'values' => array(
                            array(
                                'id' => 'st_gmap_dragging_off_on',
                                'value' => 1,
                                'label' => $this->getTranslator()->trans('Yes', array(), 'Admin.Theme.Panda'),
                            ),
                            array(
                                'id' => 'st_gmap_dragging_off_off',
                                'value' => 0,
                                'label' => $this->getTranslator()->trans('No', array(), 'Admin.Theme.Panda'),
                            )
                        ),
                        'validation' => 'isBool',
                    ),
                    array(
    					'type' => 'textarea',
    					'label' => $this->getTranslator()->trans('Map style:', array(), 'Modules.Steasycontent.Admin'),
    					'name' => 'st_gmap_style',
                        'cols' => 80,
    					'rows' => 12,
                        'desc' => $this->getTranslator()->trans('You can pasted map style code from %s%', array('%s%'=>'<a href="http://snazzymaps.com/" target="_blank">snazzymaps.com/</a>'), 'Modules.Steasycontent.Admin')
    				),
                    array(
                        'type' => 'text',
                        'label' => $this->getTranslator()->trans('Top padding:', array(), 'Admin.Theme.Panda'),
                        'name' => 'st_element_top_padding',
                        'default_value' => '',
                        'validation' => 'isNullOrUnsignedId',
                        'prefix' => 'px',
                        'class' => 'fixed-width-lg',
                        'desc' => $this->getTranslator()->trans('Leave it empty to use the default value.', array(), 'Admin.Theme.Panda'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->getTranslator()->trans('Bottom padding:', array(), 'Admin.Theme.Panda'),
                        'name' => 'st_element_bottom_padding',
                        'default_value' => '',
                        'validation' => 'isNullOrUnsignedId',
                        'prefix' => 'px',
                        'class' => 'fixed-width-lg',
                        'desc' => $this->getTranslator()->trans('Leave it empty to use the default value.', array(), 'Admin.Theme.Panda'),
                    ),
                    array(
                        'type' => 'color',
                        'label' => $this->getTranslator()->trans('Block Background:', array(), 'Admin.Theme.Panda'),
                        'name' => 'st_element_bg',
                        'size' => 33,
                        'default_value' => '',
                        'validation' => 'isColor',
                    ),          
                    array(
                        'type' => 'radio',
                        'label' => $this->getTranslator()->trans('Hide on mobile:', array(), 'Admin.Theme.Panda'),
                        'name' => 'st_el_hide_on_mobile',
                        'default_value' => 0,
                        'values' => array(
                            array(
                                'id' => 'st_el_hide_on_mobile_0',
                                'value' => 0,
                                'label' => $this->getTranslator()->trans('No', array(), 'Admin.Theme.Panda')),
                            array(
                                'id' => 'st_el_hide_on_mobile_1',
                                'value' => 1,
                                'label' => $this->getTranslator()->trans('Hide on mobile (screen width < 992px)', array(), 'Admin.Theme.Panda')),
                            array(
                                'id' => 'st_el_hide_on_mobile_2',
                                'value' => 2,
                                'label' => $this->getTranslator()->trans('Hide on PC (screen width > 992px)', array(), 'Admin.Theme.Panda')),
                        ),
                        'validation' => 'isUnsignedInt',
                    ),
                ),
                'buttons' => array(
                    array(
                        'type' => 'submit',
                        'title' => $this->getTranslator()->trans('Save', array(), 'Admin.Actions'),
                        'icon' => 'process-icon-save',
                        'class'=> 'pull-right'
                    ),
                ),
                'submit' => array(
                    'title' => $this->getTranslator()->trans('Save and stay', array(), 'Admin.Actions'),
                    'stay' => true
                ),
            );
        }

        $this->fields_form[0]['form']['input'][] = array('type' => 'hidden', 'name' => 'element');           
        $query_string = '&id_st_easy_content_column='.$this->id_st_easy_content_column.'&viewsteasycontentcolumn&element='.$element;
        
        $this->fields_form[0]['form']['input'][] = array(
			'type' => 'html',
            'id' => 'a_cancel',
			'label' => '',
			'name' => '<a class="btn btn-default btn-block fixed-width-md" href="'.AdminController::$currentIndex.'&configure='.$this->name.$query_string.'&token='.Tools::getAdminTokenLite('AdminModules').'"><i class="icon-arrow-left"></i> '.$this->getTranslator()->trans('Back to list', array(), 'Admin.Theme.Panda').'</a>',                  
		);
        if (!$easycontent_column->element) {
            $easycontent_column->element = $element;
        }
        
        // Fetch settings to object.
        $this->LoadSettingsToObject($easycontent_column, 1);
        
        return $this->loadFormHelper('st_easy_content_column', 'elementsetting', $easycontent_column);
    }
}