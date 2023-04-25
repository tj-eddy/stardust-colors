<?php
return array(
    'id_st_easy_content_element' => array(
        'title' => $this->getTranslator()->trans('Id', array(), 'Admin.Theme.Panda'),
        'width' => 120,
        'type' => 'text',
        'search' => false,
        'orderby' => false
    ),
    'st_el_icon' => array(
        'title' => $this->getTranslator()->trans('Icon name', array(), 'Admin.Theme.Panda'),
        'width' => 140,
        'type' => 'text',
        'search' => false,
        'orderby' => false
    ),
    'st_el_url' => array(
        'title' => $this->getTranslator()->trans('Url', array(), 'Admin.Theme.Panda'),
        'width' => 140,
        'type' => 'text',
        'search' => false,
        'orderby' => false
    ),
    'position' => array(
        'title' => $this->getTranslator()->trans('Position', array(), 'Admin.Theme.Panda'),
        'width' => 40,
        'position' => 'position',
        'align' => 'center',
        'search' => false,
        'orderby' => false
    ),
    'active' => array(
        'title' => $this->getTranslator()->trans('Status', array(), 'Admin.Theme.Panda'),
        'align' => 'center',
        'active' => 'status',
        'type' => 'bool',
        'width' => 25,
        'search' => false,
        'orderby' => false
    ),
);