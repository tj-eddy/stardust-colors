<?php
/**
* NOTICE OF LICENSE
*
* This source file is subject to a commercial license from SARL Ether Création
* Use, copy, modification or distribution of this source file without written
* license agreement from the SARL Ether Création is strictly forbidden.
* In order to obtain a license, please contact us: contact@ethercreation.com
* ...........................................................................
* INFORMATION SUR LA LICENCE D'UTILISATION
*
* L'utilisation de ce fichier source est soumise à une licence commerciale
* concedée par la sociéte Ether Création
* Toute utilisation, reproduction, modification ou distribution du present
* fichier source sans contrat de licence écrit de la part de la SARL Ether Création est
* expressement interdite.
* Pour obtenir une licence, veuillez contacter la SARL Ether Création a l'adresse: contact@ethercreation.com
* ...........................................................................
*
*    @package ecreduction
*    @author Fiona Godard
*    @copyright Copyright (c) 2010-2014 S.A.R.L Ether Création (http://www.ethercreation.com)
*    @license Commercial license
*/

if (!defined('_PS_VERSION_')) {
    exit;
}
  
class Ecsocialsharing extends Module
{

    protected static $networks = array(
                                    'Facebook',
                                    'Twitter',
                                    'Google',
                                    'Pinterest',
                                    'Whatsapp',
                                    'Linkedin',
                                    'Skype',
                                    'Tumblr'
                                 );
    protected $html = '';

    public function __construct()
    {
        $this->name = 'ecsocialsharing';
        $this->author = 'Ether Creation';
        $this->tab = 'advertising_marketing';
        $this->need_instance = 0;
        $this->version = '2.1.1';
        $this->bootstrap = true;
        $this->module_key = '3767aa93644481502309b0a0fb4b5c0f';
        $this->_directory = dirname(__FILE__);

        parent::__construct();
        
        $this->displayName = $this->l('Ether Creation - Social Sharing');
        $this->description = $this->l('Displays social sharing buttons (Twitter, Facebook, Google+, Pinterest, WhatsApp, Skype, LinkedIn and Tumblr) on every product page.');
        $this->templateFile = 'module:ecsocialsharing/views/templates/hook/ec_socialsharing.tpl';
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
    }
  
    public function install()
    {
        // Activate every option by default
        Configuration::updateValue('EC_TWITTER', 1);
        Configuration::updateValue('EC_FACEBOOK', 1);
        Configuration::updateValue('EC_GOOGLE', 1);
        Configuration::updateValue('EC_PINTEREST', 1);
        Configuration::updateValue('EC_WHATSAPP', 1);
        Configuration::updateValue('EC_LINKEDIN', 1);
        Configuration::updateValue('EC_SKYPE', 1);
        Configuration::updateValue('EC_TUMBLR', 1);
        
        // The module will add a meta in the product page header
        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('displayProductButtons');
    }
  
    public function getConfigFieldsValues()
    {
        $values = array();
        foreach (self::$networks as $network) {
            $values['EC_'.Tools::strtoupper($network)] = (int)Tools::getValue('EC_'.Tools::strtoupper($network), Configuration::get('EC_'.Tools::strtoupper($network)));
        }
        return $values;
    }

    public function getContent()
    {
        if (Tools::isSubmit('submitECSocialSharing')) {
            foreach (self::$networks as $network) {
                echo 'EC_'.Tools::strtoupper($network).' : '.Tools::getValue('EC_'.Tools::strtoupper($network)).'<br />';
                Configuration::updateValue('EC_'.Tools::strtoupper($network), (int)Tools::getValue('EC_'.Tools::strtoupper($network)));
                $this->html .= $this->displayConfirmation($this->l('Settings updated'));
                Tools::clearCache(Context::getContext()->smarty, $this->getTemplatePath('ec_socialsharing.tpl'));
            }
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=6&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name);
        }

        $helper = new HelperForm();
        $helper->submit_action = 'submitECSocialSharing';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array('fields_value' => $this->getConfigFieldsValues());

        $fields = array();
        foreach (self::$networks as $network) {
            $fields[] = array(
              'type' => 'switch',
              'label' => $network,
              'name' => 'EC_'.Tools::strtoupper($network),
              'values' => array(
                array(
                  'id' => Tools::strtolower($network).'_active_on',
                  'value' => 1,
                  'label' => $this->l('Enabled')
                ),
                array(
                  'id' => Tools::strtolower($network).'_active_off',
                  'value' => 0,
                  'label' => $this->l('Disabled')
                )
              )
            );
        }

        return $this->html.$helper->generateForm(array(
          array(
            'form' => array(
              'legend' => array(
                'title' => $this->displayName,
                'icon' => 'icon-share'
              ),
              'input' => $fields,
              'submit' => array(
                'title' => $this->l('Save')
              )
            )
          )
        ));
    }
    public function hookHeader()
    {
        $this->context->controller->addCSS($this->_path.'/views/css/ec_socialsharing.css');
        $this->context->controller->addCSS($this->_path.'views/fonts/font-awesome/css/font-awesome.min.css');
    }
    
    public function hookDisplayProductButtons()
    {
        if (!isset($this->context->controller) || !method_exists($this->context->controller, 'getProduct')) {
            return;
        }

        $product = $this->context->controller->getProduct();

        $this->smarty->assign($this->getWidgetVariables());
        return $this->display(__FILE__, 'ec_socialsharing.tpl', $this->getCacheId('ec_socialsharing|'.(isset($product->id) && $product->id ? (int)$product->id : '')));
    }

    public function getWidgetVariables()
    {
        if (!isset($this->context->controller->php_self) || $this->context->controller->php_self != 'product') {
            return;
        }

        $product = $this->context->controller->getProduct();

        if (!Validate::isLoadedObject($product)) {
            return;
        }

        $social_share_links = array();
        $sharing_url = addcslashes($this->context->link->getProductLink($product), "'");
        $sharing_name = addcslashes($product->name, "'");

        $image_cover_id = $product->getCover($product->id);
        if (is_array($image_cover_id) && isset($image_cover_id['id_image'])) {
            $image_cover_id = (int)$image_cover_id['id_image'];
        } else {
            $image_cover_id = 0;
        }

        $sharing_img = addcslashes($this->context->link->getImageLink($product->link_rewrite, $image_cover_id), "'");

        if (Configuration::get('EC_FACEBOOK')) {
            $social_share_links['facebook'] = array(
                'label' => $this->l('facebook'),
                'class' => 'facebook',
                'id' => 'btn-facebook',
                'url' => 'http://www.facebook.com/sharer.php?u='.$sharing_url,
            );
        }

        if (Configuration::get('EC_TWITTER')) {
            $social_share_links['twitter'] = array(
                'label' => $this->l('Tweet'),
                'class' => 'twitter',
                'id' => 'btn-twitter',
                'url' => 'https://twitter.com/intent/tweet?text='.$sharing_name.' '.$sharing_url,
            );
        }

        if (Configuration::get('EC_GOOGLE')) {
            $social_share_links['googleplus'] = array(
                'label' => $this->l('Google+'),
                'class' => 'googleplus',
                'id' => 'btn-google-plus',
                'url' => 'https://plus.google.com/share?url='.$sharing_url,
            );
        }

        if (Configuration::get('EC_PINTEREST')) {
            $social_share_links['pinterest'] = array(
                'label' => $this->l('Pinterest'),
                'class' => 'pinterest',
                'id' => 'btn-pinterest',
                'url' => 'http://www.pinterest.com/pin/create/button/?media='.$sharing_img.'&url='.$sharing_url,
            );
        }
        if (Configuration::get('EC_WHATSAPP')) {
            $social_share_links['whatsapp'] = array(
                'label' => $this->l('Whatsapp'),
                'class' => 'whatsapp',
                'id' => 'btn-whatsapp',
                'url' => 'whatsapp://send?text='.$sharing_name.' '.$sharing_url,
            );
        }
        if (Configuration::get('EC_LINKEDIN')) {
            $social_share_links['linkedin'] = array(
                'label' => $this->l('Linkedin'),
                'class' => 'linkedin',
                'id' => 'btn-linkedin',
                'url' => 'https://www.linkedin.com/cws/share?url='.$sharing_url,
            );
        }
        if (Configuration::get('EC_SKYPE')) {
            $social_share_links['skype'] = array(
                'label' => $this->l('Skype'),
                'class' => 'skype',
                'id' => 'btn-skype',
                'url' => 'https://web.skype.com/share?url='.$sharing_url,
                //'url' => '#'
            );
        }
        if (Configuration::get('EC_TUMBLR')) {
            $social_share_links['tumblr'] = array(
                'label' => $this->l('Tumblr'),
                'class' => 'tumblr',
                'id' => 'btn-tumblr',
                'url' => 'http://www.tumblr.com/share?v=3&u='.$sharing_url,
            );
        }
        //var_export($social_share_links);
        //exit();
        return array(
            'mobile' => Context::getContext()->isMobile(),
            'social_share_links' => $social_share_links,
        );
    }
}
