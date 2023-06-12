<?php

//on définis les champs correspondant à ceux utilisé dans la fonction "installSql" du fichier "moduletest.php"
class LessismoreTable extends ObjectModel {
    public $id;
    public $speed;
    public $collapsedHeight;
    public $page;
    public $selector;
    public $moreLink;
    public $lessLink;
    public static $definition = array(
        'table' => 'lessismore_table_conf',
        'primary' => 'id',
        'multilang' => false,
        'fields' => array(
            'page' => array(
                'type' => self::TYPE_STRING,
                'required' => true
            ),
            'selector' => array(
                'type' => self::TYPE_STRING,
                'required' => true
            ),
            'speed' => array(
                'type' => self::TYPE_INT,
                'required' => true
            ),
            'collapsedHeight' => array(
                'type' => self::TYPE_INT,
                'required' => true
            ),
            'moreLink' => array(
                'type' => self::TYPE_STRING,
                'required' => true
            ),
            'lessLink' => array(
                'type' => self::TYPE_STRING,
                'required' => true
            )
        )
    );
}