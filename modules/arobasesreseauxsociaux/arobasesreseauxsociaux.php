<?php

/**
 * Created by PhpStorm.
 * User: integraLenovo
 * Date: 27/06/2016
 * Time: 15:58
 */
class arobasesreseauxsociaux extends Module{
    public function __construct()
    {
        $this->name = 'arobasesreseauxsociaux';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'arobases';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('reseaux solciaux');
        $this->description = $this->l('Module pour les reseaux sociaux');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        if (!Configuration::get('AROBASESRESEAUXSOCIAUX'))
            $this->warning = $this->l('No name provided');
    }
    public function install()
    {
        if (Shop::isFeatureActive())
            Shop::setContext(Shop::CONTEXT_ALL);

        if (!parent::install() ||
            !$this->registerHook('leftColumn') ||
            !$this->registerHook('displayHome') ||
            !$this->registerHook('displayFooter') ||
            !$this->registerHook('header') ||
            !$this->registerHook('customSocialNetwork') ||
            !Configuration::updateValue('AROBASESRESEAUXSOCIAUX', 'arobasesreseauxsociaux')
        )
            return false;

        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall() ||
            !Configuration::deleteByName('AROBASESRESEAUXSOCIAUX')
        )
            return false;

        return true;
    }

    public function getContent()
    {
        $output = null;

        if (Tools::isSubmit('submit'.$this->name))
        {
            $my_title = strval(Tools::getValue('TITLEMODULES'));
            $description = strval(Tools::getValue('DESCRIPTIONRS'));
            $my_facebook = strval(Tools::getValue('LINKFACEBOOK'));
            $my_twiiter = strval(Tools::getValue('LINKTWITTER'));
            $my_googleplus = strval(Tools::getValue('LINKGOOGLEPLUS'));
            $my_instagramm = strval(Tools::getValue('LINKINSTAGRAM'));

                Configuration::updateValue('TITLEMODULES', $my_title);
                Configuration::updateValue('DESCRIPTIONRS', $description);
                Configuration::updateValue('LINKFACEBOOK', $my_facebook);
                Configuration::updateValue('LINKTWITTER', $my_twiiter);
                Configuration::updateValue('LINKGOOGLEPLUS', $my_googleplus);
                Configuration::updateValue('LINKGOOGLEPLUS', $my_googleplus);
                Configuration::updateValue('LINKINSTAGRAM', $my_instagramm);
                $output .= $this->displayConfirmation($this->l('Mise à jour réussi'));

        }
        return $output.$this->displayForm();
    }

    public function displayForm()
    {
        // Get default Language
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

        // Init Fields form array
        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Lien des pages des reseaux sociaux'),
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Titre'),
                    'name' => 'TITLEMODULES',
                    'size' => 255
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Description'),
                    'name' => 'DESCRIPTIONRS',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Facebook'),
                    'name' => 'LINKFACEBOOK',
                    'size' => 100
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Twitter'),
                    'name' => 'LINKTWITTER',
                    'size' => 100
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Google +'),
                    'name' => 'LINKGOOGLEPLUS',
                    'size' => 100
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Instagramm'),
                    'name' => 'LINKINSTAGRAM',
                    'size' => 100
                )
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right'
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
                ),
            'back' => array(
                'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list')
            )
        );

        // Load current value
        $helper->fields_value['TITLEMODULES'] = Configuration::get('TITLEMODULES');
        $helper->fields_value['DESCRIPTIONRS'] = Configuration::get('DESCRIPTIONRS');
        $helper->fields_value['LINKFACEBOOK'] = Configuration::get('LINKFACEBOOK');
        $helper->fields_value['LINKTWITTER'] = Configuration::get('LINKTWITTER');
        $helper->fields_value['LINKGOOGLEPLUS'] = Configuration::get('LINKGOOGLEPLUS');
        $helper->fields_value['LINKINSTAGRAM'] = Configuration::get('LINKINSTAGRAM');

        return $helper->generateForm($fields_form);
    }
    public function hookDisplayHome($params){
        global $smarty;
        $my_title = Configuration::get('TITLEMODULES');
        $description = Configuration::get('DESCRIPTIONRS');
        $facebook = Configuration::get('LINKFACEBOOK');
        $twitter = Configuration::get('LINKTWITTER');
        $googleplus = Configuration::get('LINKGOOGLEPLUS');
        $instagram = Configuration::get('LINKINSTAGRAM');
        $smarty->assign(
            array(
                'titremodules'=>$my_title,
                'secondetitre'=>$description,
                'facebooklink'=>$facebook,
                'twitter'=>$twitter,
                'googleplus'=>$googleplus,
                'instagram'=>$instagram,
            )

        );
        return $this->display(__FILE__, 'reseauxsociaux.tpl');
    }
    public function hookHeader(){
        $this->context->controller->addJS($this->_path.'js/jQueryRotate.js');
        $this->context->controller->addJS($this->_path.'js/script.js');
    }
    public function hookDisplayFooter($params){
        return $this->hookDisplayHome($params);
    }

    /**
     * @param $params
     * @return false|string
     */
    public function hookCustomSocialNetwork($params)
    {
        return $this->hookDisplayHome($params);
    }

}