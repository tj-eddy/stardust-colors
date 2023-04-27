<?php
class derniersproduits extends Module{

    protected static $cache_new_products;
    public function __construct() {
        $this->name = 'derniersproduits';
        if (version_compare(_PS_VERSION_, '1.4.0.0') >= 0)
            $this->tab = 'front_office_features';
        else
            $this->tab = 'lastproduct';
        $this->version = '2.2.0';
        $this->author = 'arobases.fr';

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('Selection du moment');
        $this->description = $this->l('Affichage produit sélectionné dans page Accueil');
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    public function install() {
        return parent::install() &&
        $this->registerHook('DisplayHome')&&
        $this->registerHook('displayHomeRight')&&
        $this->registerHook('DisplayTopColumn')&&
        Configuration::updateValue('IDPRODUCT','1242')&&
        Configuration::updateValue('SELECTSTATUS',true);
    }

    public function hookDisplayHomeRight($params)
    {
         $this->hookDisplayHome($params);
    }

    public function hookDisplayHome($params){
        $idlang = $this->context->language->id;
        $nbreproduit =Configuration::get('IDPRODUCT');
        $product = new Product($nbreproduit, true, $this->context->language->id, $this->context->shop->id);
        $images = Image::getImages((int)$this->context->language->id, (int)$nbreproduit);
        global $smarty;
        $smarty->assign('oproduits',$product);
        $smarty->assign('imgpro',$images);
        return $this->display(__FILE__, 'listproduct.tpl');
    }
    public function hookDisplayTopColumn($params) {
        return $this->hookDisplayHome($params);
    }

    public function getContent(){
        $output = null;
        if (Tools::isSubmit('submit'.$this->name))
        {
            $numberProduct = strval(Tools::getValue('IDPRODUCT'));
            if (!$numberProduct  || empty($numberProduct) || !Validate::isGenericName($numberProduct))
                $output .= $this->displayError( $this->l('Invalid Configuration value') );
            else
            {
                Configuration::updateValue('IDPRODUCT', $numberProduct);
                $output .= $this->displayConfirmation($this->l('Settings updated'));
            }
        }
        return $output.$this->displayForm();
    }

    public function displayForm()
    {
        // Get default Language
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Nbre produits  afficher'),
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('ID produit affiché'),
                    'name' => 'IDPRODUCT',
                    'size' => 20,
                    'required' => true
                )
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'btn btn-primary'
            )
        );

        $helper = new HelperForm();

        // Module, token and currentIndex
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

        // Language
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;

        // Title and toolbar
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;        // false -> remove toolbar
        $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
        $helper->submit_action = 'submit'.$this->name;
        $helper->toolbar_btn = array(
            'save' =>
                array(
                    'desc' => $this->l('Save'),
                    'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
                        '&token='.Tools::getAdminTokenLite('AdminModules'),
                )
        );

        // Load current value
        $helper->fields_value['IDPRODUCT'] = Configuration::get('IDPRODUCT');
        //$helper->fields_value['NOUVEAUXPRODUIT'] = Configuration::get('NOUVEAUXPRODUIT');

        return $helper->generateForm($fields_form);
    }


}