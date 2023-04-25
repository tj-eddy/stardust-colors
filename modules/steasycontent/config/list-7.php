<?php
return array(
    'st_gmap_lat' => array(
        'title' => $this->getTranslator()->trans('Marker latitude', array(), 'Modules.Steasycontent.Admin'),
        'width' => 120,
        'type' => 'text',
        'search' => false,
        'orderby' => false
    ),
    'st_gmap_lng' => array(
        'title' => $this->getTranslator()->trans('Marker longitude', array(), 'Modules.Steasycontent.Admin'),
        'width' => 140,
        'type' => 'text',
        'search' => false,
        'orderby' => false
    ),
    'st_gmap_marker_text' => array(
        'title' => $this->getTranslator()->trans('Marker text', array(), 'Modules.Steasycontent.Admin'),
        'width' => 340,
        'type' => 'text',
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
    'parameters' => array(
        'new_label' => $this->getTranslator()->trans('Add a marker', array(), 'Modules.Steasycontent.Admin'),
    ),
);