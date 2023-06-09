<?php
return array(
    'id_st_easy_content_element' => array(
        'title' => $this->getTranslator()->trans('Id', array(), 'Admin.Theme.Panda'),
        'width' => 120,
        'type' => 'text',
        'search' => false,
        'orderby' => false,
    ),
    'id_st_easy_content_column' => array(
        'title' => $this->getTranslator()->trans('Products', array(), 'Admin.Theme.Panda'),
        'width' => 200,
        'type' => 'text',
        'search' => false,
        'orderby' => false,
        'callback' => 'displayElementProd',
        'callback_object' => 'StEasyContent',
    ),
    'position' => array(
        'title' => $this->getTranslator()->trans('Position', array(), 'Admin.Theme.Panda'),
        'width' => 40,
        'position' => 'position',
        'align' => 'center',
        'search' => false,
        'orderby' => false,
    ),
    'active' => array(
        'title' => $this->getTranslator()->trans('Status', array(), 'Admin.Theme.Panda'),
        'align' => 'center',
        'active' => 'status',
        'type' => 'bool',
        'width' => 25,
        'search' => false,
        'orderby' => false,
    ),
);