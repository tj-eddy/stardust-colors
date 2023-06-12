<?php

//pour éviter un accès direct à ce fichier.
if (!defined('_PS_VERSION_')) {
    exit;
}

//on appelle le fichier de ce module "/classes/ModuleTestTableTest.php" que l'on va créer dans la partie suivante.
require_once dirname(__FILE__) . '/classes/lessismoreTable.php';


class Cwms_lessismore extends Module
{

    public function __construct()
    {
        $this->name = 'cwms_lessismore';
        $this->tab = 'others';
        $this->version = '0.1.0';
        $this->author = 'CrownMakers';
        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('Less IS More');
        $this->description = $this->l('Read More/Less in paragraph');
    }

    public function install()
    {
        include(dirname(__FILE__) . '/sql/install.php');
        return parent::install() &&
            $this->registerHook('actionFrontControllerSetMedia') &&
            $this->registerHook('header');
    }


    public function uninstall()
    {
        include(dirname(__FILE__) . '/sql/uninstall.php');
        return parent::uninstall();
    }

    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminLessismore'));
    }

    public function hookHeader()
    {
        $conf = \Db::getInstance()->executeS("SELECT selector, speed, collapsedHeight, moreLink, lessLink FROM " . _DB_PREFIX_ . "lessismore_table_conf");
        $this->context->smarty->assign(array(
            'conf' => json_encode($conf),
        ));
        return $this->context->smarty->fetch(dirname(__FILE__) . '/views/hookHeader.tpl');
    }


    public function hookActionFrontControllerSetMedia($params)
    {
        $page = \Db::getInstance()->executeS("SELECT `page` FROM " . _DB_PREFIX_ . "lessismore_table_conf GROUP BY page");
        $page_list = array();
        foreach ($page as $value) {
            $page_list[] = $value['page'];
        }

        // dump(in_array($this->context->controller->php_self, $page_list)); die;
        if (in_array($this->context->controller->php_self, $page_list)) {
            $this->context->controller->registerJavascript(
                'readmore-js',
                'modules/' . $this->name . '/js/readmore.min.js',
                [
                    'priority' => 199,
                    'attribute' => 'async',
                ]
            );

            $this->context->controller->registerJavascript(
                'lessismoreTable',
                'modules/' . $this->name . '/js/mon_script.js',
                [
                    'priority' => 200,
                    'attribute' => 'async',
                ]
            );
        }
    }
}