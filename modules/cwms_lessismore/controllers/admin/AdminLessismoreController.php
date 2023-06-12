<?php

//on appelle ici aussi notre classe "ObjectModel" que l'on va utiliser.
require_once _PS_MODULE_DIR_ . 'cwms_lessismore/classes/lessismoreTable.php';

class AdminLessismoreController extends ModuleAdminControllerCore {

    //configuration de l'objet a utilisé et des champ à affiché
    public function __construct() {
        $this->bootstrap = true; //Gestion de l'affichage en mode bootstrap
        $this->table = LessismoreTable::$definition['table']; //Table de l'objet
        $this->identifier = LessismoreTable::$definition['primary']; //Clé primaire de l'objet
        $this->className = LessismoreTable::class; //Classe de l'objet
        $this->lang = false; //Flag pour dire si utilisation de langues ou non
        $this->_defaultOrderBy = LessismoreTable::$definition['primary'];
        //Appel de la fonction parente
        parent::__construct();
        //Liste des champs de l'objet à afficher dans la liste
        $this->fields_list = array(
            'page' => array(
                'title' => $this->module->l('Page'),
                'align' => 'left',
            ),
            'selector' => array(
                'title' => $this->module->l('CSS selector'),
                'align' => 'left',
            ),
            'speed' => array(
                'title' => $this->module->l('Speed'),
                'align' => 'left',
            ),
            'collapsedHeight' => array(
                'title' => $this->module->l('Collapsed Height'),
                'align' => 'left',
            ),
            'moreLink' => array(
                'title' => $this->module->l('More Link'),
                'align' => 'left',
            ),
            'lessLink' => array(
                'title' => $this->module->l('Less Link'),
                'align' => 'left',
            )
        );
    }

    //configuration du formulaire d'ajout/edition d'une ligne de la tabler
    //utiliser l'URL de votre admin + "index.php?controller=AdminPatterns" pour a liste des champs disponibles
    public function renderForm() {
        // dump(Tools::getValue('page'));die;
        $options = array(
            array(
              'id_option' => 'index',       // The value of the 'value' attribute of the <option> tag.
              'name' => 'Accueil'    // The value of the text content of the  <option> tag.
            ),
            array(
              'id_option' => 'category',
              'name' => 'Categorie'
            ),
            array(
                'id_option' => 'product',
                'name' => 'Produit'
            ),
            array(
            'id_option' => 'manufacturer',
            'name' => 'Marque'
            ),
            array(
            'id_option' => 'supplier',
            'name' => 'Fournisseur'
            ),
            array(
            'id_option' => 'cart',
            'name' => 'Panier'
            )
          );
        $this->fields_form = [
            'legend' => [
                'title' => $this->l('Configuration du module Less is More'),
            ],
            'input' => [
                array(
                'type' => 'select',                              // This is a <select> tag.
                'label' => $this->l('Page'),         // The <label> for this <select> tag.
                'name' => 'page',                     // The content of the 'id' attribute of the <select> tag.
                'required' => true,                              // If set to true, this option must be set.
                'options' => array(
                    'query' => $options,                           // $options contains the data itself.
                    'id' => 'id_option',                           // The value of the 'id' key must be the same as the key for 'value' attribute of the <option> tag in each $options sub-array.
                    'name' => 'name'                               // The value of the 'name' key must be the same as the key for the text content of the <option> tag in each $options sub-array.
                )
                ),
                [
                    'type' => 'text',
                    'label' => $this->l('CSS selector'),
                    'name' => 'selector',
                    'required' => true,
                ],
                array(
                    'type' => 'text',
                    'label' => $this->l('Speed'),
                    'name' => 'speed',
                    'required' => true,
                    
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Collapsed Height'),
                    'name' => 'collapsedHeight',
                    'required' => true,
                ),
                [
                    'type' => 'text',
                    'label' => $this->l('More Link'),
                    'name' => 'moreLink',
                    'required' => true,
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Less Link'),
                    'name' => 'lessLink',
                    'required' => true,
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
            ],
        ];
        return parent::renderForm();
    }

    //permet d'ajouter le bouton de suppression pour chaque ligne
    public function renderList() {
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        return parent::renderList();
    }
}