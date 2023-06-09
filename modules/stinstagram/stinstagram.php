<?php
/**
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    ST-themes
*  @copyright 2007-2016 ST-themes
*  @license   Use, by you or one client for one Prestashop instance.
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

require_once _PS_MODULE_DIR_.'stinstagram/classes/StInstagramClass.php';
class StInstagram extends Module implements WidgetInterface
{
    private $_html = '';
    public $fields_list;
    public $fields_form;
    public $fields_value;
    public $list_result = array();
    public $validation_errors = array();
    private $_prefix_st = 'ST_INSTAGRAM_';
    private static $client_id = '252820955722250';
    private static $client_secret = 'd4630736674415da4b8ea1c4ed8a9595';
    public $imgtype = array('jpg', 'gif', 'jpeg', 'png');
    protected static $access_rights = 0775;
    public static $location = array();
    public static $wide_map = array(
        array('id'=>'1', 'name'=>'1/12'),
        array('id'=>'1-2', 'name'=>'1.2/12'),
        array('id'=>'1-5', 'name'=>'1.5/12'),
        array('id'=>'2', 'name'=>'2/12'),
        array('id'=>'2-4', 'name'=>'2.4/12'),
        array('id'=>'3', 'name'=>'3/12'),
        array('id'=>'4', 'name'=>'4/12'),
        array('id'=>'5', 'name'=>'5/12'),
        array('id'=>'6', 'name'=>'6/12'),
        array('id'=>'7', 'name'=>'7/12'),
        array('id'=>'8', 'name'=>'8/12'),
        array('id'=>'9', 'name'=>'9/12'),
        array('id'=>'10', 'name'=>'10/12'),
        array('id'=>'11', 'name'=>'11/12'),
        array('id'=>'12', 'name'=>'12/12'),
    );

    private $_tabs = array();
    private $_hooks = array();
    public $vc_hooks;
    protected $templatePath;
    private $binding_title;
    private $error_msg;
    public function __construct()
    {
        $this->name           = 'stinstagram';
        $this->tab            = 'front_office_features';
        $this->version        = '1.1.3';
        $this->author        = 'SUNNYTOO.COM';
        $this->need_instance  = 0;
        $this->bootstrap      = true;

        parent::__construct();

        $this->displayName = $this->getTranslator()->trans('Advanced instagram feed', array(), 'Modules.Stinstagram.Admin');
        $this->binding_title = $this->getTranslator()->trans('List of authorized accounts', array(), 'Modules.Stinstagram.Admin');
        $this->description = $this->getTranslator()->trans('Display your instagram media to your web page.', array(), 'Modules.Stinstagram.Admin');
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->error_msg=[
            1=>$this->getTranslator()->trans('Save success', array(), 'Modules.Stinstagram.Admin'),
            2=>$this->getTranslator()->trans('Save fail', array(), 'Modules.Stinstagram.Admin'),
            3=>$this->getTranslator()->trans('Data not found', array(), 'Modules.Stinstagram.Admin'),
        ];

        $this->checkAccessToken();
        
        self::$location = array(
            //to do add other hooks
            4  => array('id' =>4,    'name' => $this->getTranslator()->trans('Full width top', array(), 'Modules.Stinstagram.Admin'),    'hook' => 'FullWidthTop', 'full_width'=>1),
            30  => array('id' =>30,    'name' => $this->getTranslator()->trans('Full width top 2', array(), 'Modules.Stinstagram.Admin'),    'hook' => 'FullWidthTop2', 'full_width'=>1),
            31  => array('id' =>31,    'name' => $this->getTranslator()->trans('Homepage top', array(), 'Modules.Stinstagram.Admin'),               'hook' => 'HomeTop'),
            3  => array('id' =>3,    'name' => $this->getTranslator()->trans('Homepage', array(), 'Modules.Stinstagram.Admin'),               'hook' => 'Home'),
            33  => array('id' =>33,    'name' => $this->getTranslator()->trans('Homepage Left', array(), 'Modules.Stinstagram.Admin'),               'hook' => 'HomeLeft', 'column_slider'=>1),
            34  => array('id' =>34,    'name' => $this->getTranslator()->trans('Homepage Right', array(), 'Modules.Stinstagram.Admin'),               'hook' => 'HomeRight', 'column_slider'=>1),
            35  => array('id' =>35,    'name' => $this->getTranslator()->trans('Homepage first  quarter', array(), 'Modules.Stinstagram.Admin'),               'hook' => 'HomeFirstQuarter', 'column_slider'=>1),
            36  => array('id' =>36,    'name' => $this->getTranslator()->trans('Homepage second quarter', array(), 'Modules.Stinstagram.Admin'),               'hook' => 'HomeSecondQuarter', 'column_slider'=>1),
            37  => array('id' =>37,    'name' => $this->getTranslator()->trans('Homepage third quarter', array(), 'Modules.Stinstagram.Admin'),               'hook' => 'HomeThirdQuarter', 'column_slider'=>1),
            38  => array('id' =>38,    'name' => $this->getTranslator()->trans('Homepage fourth quarter', array(), 'Modules.Stinstagram.Admin'),               'hook' => 'HomeFourthQuarter', 'column_slider'=>1),
            39  => array('id' =>39,    'name' => $this->getTranslator()->trans('Homepage Bottom', array(), 'Modules.Stinstagram.Admin'),               'hook' => 'HomeBottom'),
            15 => array('id' =>15,   'name' => $this->getTranslator()->trans('Full width Bottom', array(), 'Modules.Stinstagram.Admin'),    'hook' => 'FullWidthBottom', 'full_width'=>1),
    
            5  => array('id' =>5,    'name' => $this->getTranslator()->trans('Left column except the produt page', array(), 'Modules.Stinstagram.Admin'),            'hook' => 'LeftColumn', 'column_slider'=>1),
            6  => array('id' =>6,    'name' => $this->getTranslator()->trans('Right column except the produt page', array(), 'Modules.Stinstagram.Admin'),           'hook' => 'RightColumn', 'column_slider'=>1),
            7  => array('id' =>7,    'name' => $this->getTranslator()->trans('Left column on the product page only', array(), 'Modules.Stinstagram.Admin'),    'hook' => 'LeftColumnProduct', 'column_slider'=>1),
            8  => array('id' =>8,    'name' => $this->getTranslator()->trans('Right column on the product page only', array(), 'Modules.Stinstagram.Admin'),   'hook' => 'RightColumnProduct', 'column_slider'=>1),
            25 => array('id' =>25,   'name' => $this->getTranslator()->trans('Product right column', array(), 'Modules.Stinstagram.Admin'),'hook' => 'ProductRightColumn', 'column_slider'=>1),
            
            55 => array('id' =>55,   'name' => $this->getTranslator()->trans('Stacked footer1', array(), 'Modules.Stinstagram.Admin'),                 'hook' => 'StackedFooter1', 'footer_slider'=>1, 'is_stacked_footer'=>1),
            56 => array('id' =>56,   'name' => $this->getTranslator()->trans('Stacked footer2', array(), 'Modules.Stinstagram.Admin'),                 'hook' => 'StackedFooter2', 'footer_slider'=>1, 'is_stacked_footer'=>1),
            57 => array('id' =>57,   'name' => $this->getTranslator()->trans('Stacked footer3', array(), 'Modules.Stinstagram.Admin'),                 'hook' => 'StackedFooter3', 'footer_slider'=>1, 'is_stacked_footer'=>1),
            58 => array('id' =>58,   'name' => $this->getTranslator()->trans('Stacked footer4', array(), 'Modules.Stinstagram.Admin'),                 'hook' => 'StackedFooter4', 'footer_slider'=>1, 'is_stacked_footer'=>1),
            59 => array('id' =>59,   'name' => $this->getTranslator()->trans('Stacked footer5', array(), 'Modules.Stinstagram.Admin'),                 'hook' => 'StackedFooter5', 'footer_slider'=>1, 'is_stacked_footer'=>1),
            60 => array('id' =>60,   'name' => $this->getTranslator()->trans('Stacked footer6', array(), 'Modules.Stinstagram.Admin'),                 'hook' => 'StackedFooter6', 'footer_slider'=>1, 'is_stacked_footer'=>1),
            61 => array('id' =>61,   'name' => $this->getTranslator()->trans('Footer', array(), 'Modules.Stinstagram.Admin'),                 'hook' => 'Footer', 'footer_slider'=>1),
            63 => array('id' =>63,   'name' => $this->getTranslator()->trans('Footer after', array(), 'Modules.Stinstagram.Admin'),                 'hook' => 'FooterAfter', 'footer_slider'=>1),
            62 => array('id' =>62,   'name' => $this->getTranslator()->trans('Product Footer', array(), 'Modules.Stinstagram.Admin'),         'hook' => 'FooterProduct'),
            
            /*20 => array('id' =>20,   'name' => $this->getTranslator()->trans('displayAfterBodyOpeningTag full screen', array(), 'Modules.Stinstagram.Admin'),'hook' => 'AfterBodyOpeningTag', 'full_width'=>1),
            21 => array('id' =>21,   'name' => $this->getTranslator()->trans('displayBeforeBodyClosingTag full screen', array(), 'Modules.Stinstagram.Admin'),'hook' => 'BeforeBodyClosingTag', 'full_width'=>1),*/
            23 => array('id' =>23,   'name' => $this->getTranslator()->trans('displayFooterBefore', array(), 'Modules.Stinstagram.Admin'),'hook' => 'FooterBefore'),
            24 => array('id' =>24,   'name' => $this->getTranslator()->trans('displayReassurance', array(), 'Modules.Stinstagram.Admin'),'hook' => 'Reassurance', 'column_slider'=>1),    
    
            70 => array('id' =>70,   'name' => $this->getTranslator()->trans('Blog full width top', array(), 'Modules.Stinstagram.Admin'),    'hook' => 'StBlogFullWidthTop', 'is_blog'=>1),
            71  => array('id' =>71,    'name' => $this->getTranslator()->trans('Blog homepage top', array(), 'Modules.Stinstagram.Admin'),               'hook' => 'StBlogHomeTop', 'is_blog'=>1),
            72  => array('id' =>72,    'name' => $this->getTranslator()->trans('Blog homepage', array(), 'Modules.Stinstagram.Admin'),               'hook' => 'StBlogHome', 'is_blog'=>1),
            73 => array('id' =>73,   'name' => $this->getTranslator()->trans('Blog full width bottom', array(), 'Modules.Stinstagram.Admin'),    'hook' => 'StBlogFullWidthBottom', 'is_blog'=>1),
            50  => array('id' =>50,    'name' => $this->getTranslator()->trans('Blog left column', array(), 'Modules.Stinstagram.Admin'),   'hook' => 'StBlogLeftColumn', 'column_slider'=>1, 'is_blog'=>1),
            51  => array('id' =>51,    'name' => $this->getTranslator()->trans('Blog right column', array(), 'Modules.Stinstagram.Admin'),   'hook' => 'StBlogRightColumn', 'column_slider'=>1, 'is_blog'=>1),
        );
        $this->vc_hooks = array(
            'Block'                                     => 'displayHome',
            'Column block'                              => 'displayLeftColumn',
            'Small block uses settings of column block' => 'displayHomeFirstQuarter',
            'Footer block'                              => 'displayStackedFooter1',
        );
        $this->templatePath = 'module:'.$this->name.'/views/templates/hook/';
    }
    private function initTabNames()
    {
        $this->_tabs = array(
            array('id'  => '0', 'name' => $this->getTranslator()->trans('General settings', array(), 'Admin.Theme.Panda')),
            array('id'  => '5', 'name' => $this->getTranslator()->trans('How to display media information', array(), 'Modules.Stinstagram.Admin')),
            array('id'  => '3,4,11,12', 'name' => $this->getTranslator()->trans('Color settings', array(), 'Admin.Theme.Panda')),
            array('id'  => '1', 'name' => $this->getTranslator()->trans('Settings for displaying media in a slider', array(), 'Modules.Stinstagram.Admin')),
            array('id'  => '2', 'name' => $this->getTranslator()->trans('Settings for displaying media on left or right column and footer', array(), 'Modules.Stinstagram.Admin')),
        );
    }
    /*public function __call($method, $param = array())
    {
        $method = trim($method, 'hook');
        if ($method && strpos(Tools::strtolower($method), 'display') === 0 && method_exists($this, '_renderCustomHooks')) {
            return $this->_renderCustomHooks($method, $param);
        }
    }
*/
    public function install()
    {
        if (!parent::install()
            || !$this->registerHook('displayHeader')
            || !$this->registerHook('actionOutputHTMLBefore')
            || !$this->registerHook('vcBeforeInit')
            || !$this->installDB()
            || !Configuration::updateValue($this->_prefix_st.'CLIENT_ID', self::$client_id)
            || !Configuration::updateValue($this->_prefix_st.'ACCESS_TOKEN', '')
            || !Configuration::updateValue($this->_prefix_st.'USER_NAME', '')
            || !Configuration::updateValue($this->_prefix_st.'USER_ID', '')

            || !Configuration::updateValue($this->_prefix_st.'COMPATIBILITY_MODE', 0)
            || !Configuration::updateValue($this->_prefix_st.'LOAD_OWL_CAROUSEL', 1)
            || !Configuration::updateValue($this->_prefix_st.'LOAD_TIMEAGO', 1)
            || !Configuration::updateValue($this->_prefix_st.'CUSTOM_CSS_CODE', '')
            || !Configuration::updateValue($this->_prefix_st.'CUSTOM_JS_CODE', '')
            || !Configuration::updateValue($this->_prefix_st.'CUSTOM_HOOKS', '')
            || !Configuration::updateValue($this->_prefix_st.'CACHE_IMG_DATE', 24*60)
            || !Configuration::updateValue($this->_prefix_st.'CACHE_IMG_DATE_LEN', time()+24*60*60)

        ) {
            return false;
        }
        //$this->prepareHooks();
        $this->clearCache();
        return true;
    }

    /**
     * Creates tables
     */
    public function installDB()
    {
        /* Banners */
        $return = (bool)Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'st_instagram` (
                `id_st_instagram` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `location` int(10) unsigned NOT NULL DEFAULT 0,
                `custom_hook` varchar(64) DEFAULT NULL,
                `user_name` varchar(32) DEFAULT NULL,
                `user_id` varchar(32) DEFAULT NULL,
                `hash_tag` varchar(255) DEFAULT NULL,
                `grid` tinyint(1) unsigned NOT NULL DEFAULT 0,
                `count` smallint(6) unsigned NOT NULL DEFAULT 10,
                `image_size` tinyint(1) unsigned NOT NULL DEFAULT 2,
                `padding` varchar(10) DEFAULT NULL,
                `top_padding` varchar(10) DEFAULT NULL,
                `bottom_padding` varchar(10) DEFAULT NULL,
                `top_margin` varchar(10) DEFAULT NULL,
                `bottom_margin` varchar(10) DEFAULT NULL,
                `title` tinyint(1) unsigned NOT NULL DEFAULT 1,
                `hide_on_mobile` tinyint(1) unsigned NOT NULL DEFAULT 0,
                `show_likes` tinyint(1) unsigned NOT NULL DEFAULT 1,
                `show_comments` tinyint(1) unsigned NOT NULL DEFAULT 1,
                `show_timestamp` tinyint(1) unsigned NOT NULL DEFAULT 0,
                `time_format` tinyint(1) unsigned NOT NULL DEFAULT 0,
                `show_username` tinyint(1) unsigned NOT NULL DEFAULT 0,
                `show_caption` tinyint(1) unsigned NOT NULL DEFAULT 0,
                `lenght_of_caption` tinyint(1) unsigned NOT NULL DEFAULT 0,
                `show_media_type` tinyint(1) unsigned NOT NULL DEFAULT 1,
                `click_action` tinyint(1) unsigned NOT NULL DEFAULT 1,
                `hover_effect` tinyint(1) unsigned NOT NULL DEFAULT 1,
                `show_profile` tinyint(1) unsigned NOT NULL DEFAULT 0,
                `show_avatar` tinyint(1) unsigned NOT NULL DEFAULT 1,
                `show_counts` tinyint(1) unsigned NOT NULL DEFAULT 1,
                `show_bio` tinyint(1) unsigned NOT NULL DEFAULT 1,
                `show_website` tinyint(1) unsigned NOT NULL DEFAULT 1,
                `follow` tinyint(1) unsigned NOT NULL DEFAULT 1,
                `title_font_size` smallint(6) unsigned NOT NULL DEFAULT 0,
                `title_color` varchar(7) DEFAULT NULL,
                `title_bg` varchar(7) DEFAULT NULL,
                `title_border_height` varchar(10) DEFAULT NULL,
                `title_border_color` varchar(7) DEFAULT NULL,
                `title_border_color_h` varchar(7) DEFAULT NULL,
                `bg_hover_color` varchar(7) DEFAULT NULL,
                `bg_opacity_nohover` float(2,2)  NOT NULL DEFAULT 0,
                `bg_opacity` float(2,2)  NOT NULL DEFAULT 0.6,
                `image_border` tinyint(3) unsigned NOT NULL DEFAULT 0,
                `image_border_color` varchar(7) DEFAULT NULL,
                `image_border_radius` tinyint(3) unsigned NOT NULL DEFAULT 0,
                `font_size` tinyint(3) unsigned NOT NULL DEFAULT 0,
                `caption_color` varchar(7) DEFAULT NULL,
                `media_info_color` varchar(7) DEFAULT NULL,
                `media_info_a_color` varchar(7) DEFAULT NULL,
                `media_info_a_color_hover` varchar(7) DEFAULT NULL,
                `media_info_bg` varchar(7) DEFAULT NULL,
                `shadow_effect` tinyint(1) unsigned NOT NULL DEFAULT 0,
                `h_shadow` tinyint(3)  NOT NULL DEFAULT 0,
                `v_shadow` tinyint(3)  NOT NULL DEFAULT 0,
                `shadow_blur` tinyint(3) unsigned NOT NULL DEFAULT 6,
                `shadow_color` varchar(7) DEFAULT NULL,
                `shadow_opacity` float(2,2) NOT NULL DEFAULT 0.2,
                `bg_color` varchar(7) DEFAULT NULL,
                `bg_pattern` tinyint(3) unsigned NOT NULL DEFAULT 0,
                `bg_img` varchar(255) DEFAULT NULL,
                `profile_text` varchar(7) DEFAULT NULL,
                `profile_a_color` varchar(7) DEFAULT NULL,
                `profile_a_color_hover` varchar(7) DEFAULT NULL,
                `profile_bg` varchar(7) DEFAULT NULL,
                `follow_color` varchar(7) DEFAULT NULL,
                `follow_bg` varchar(7) DEFAULT NULL,
                `slideshow` tinyint(1) unsigned NOT NULL DEFAULT 0,
                `s_speed` int(10) unsigned NOT NULL DEFAULT 7000,
                `a_speed` int(10) unsigned NOT NULL DEFAULT 400,
                `pause_on_hover` tinyint(1) unsigned NOT NULL DEFAULT 1,
                `pause_on_enter` tinyint(1) unsigned NOT NULL DEFAULT 0,
                `reverse_direction` tinyint(1) unsigned NOT NULL DEFAULT 0,
                `rewind_nav` tinyint(1) unsigned NOT NULL DEFAULT 1,
                `move` tinyint(1) unsigned NOT NULL DEFAULT 1,
                `direction_nav` tinyint(1) unsigned NOT NULL DEFAULT 6,
                `control_nav` tinyint(1) unsigned NOT NULL DEFAULT 0,
                `direction_color` varchar(7) DEFAULT NULL,
                `direction_color_hover` varchar(7) DEFAULT NULL,
                `direction_color_disabled` varchar(7) DEFAULT NULL,
                `direction_bg` varchar(7) DEFAULT NULL,
                `direction_hover_bg` varchar(7) DEFAULT NULL,
                `direction_disabled_bg` varchar(7) DEFAULT NULL,
                `pag_nav_bg` varchar(7) DEFAULT NULL,
                `pag_nav_bg_hover` varchar(7) DEFAULT NULL,
                `picture_size_col` smallint(6) unsigned NOT NULL DEFAULT 80,
                `hide_mob_col` tinyint(1) unsigned NOT NULL DEFAULT 0,
                `wide_on_footer` varchar(7) DEFAULT 3,
                `load_more` tinyint(1) unsigned NOT NULL DEFAULT 1,
                `load_more_color` varchar(7) DEFAULT NULL,
                `load_more_bg` varchar(7) DEFAULT NULL,
                `load_more_bg_hover` varchar(7) DEFAULT NULL,
                `account_stats_color` varchar(7) DEFAULT NULL,
                `account_stats_bg` varchar(7) DEFAULT NULL,
                `self_liked` tinyint(1) unsigned NOT NULL DEFAULT 0,
                `active` tinyint(1) unsigned NOT NULL DEFAULT 1,
                `position` int(10) unsigned NOT NULL DEFAULT 0,
                `pro_per_fw` int(10) unsigned NOT NULL DEFAULT 0,
                `pro_per_xxl` int(10) unsigned NOT NULL DEFAULT 7,
                `pro_per_xl` int(10) unsigned NOT NULL DEFAULT 6,
                `pro_per_lg` int(10) unsigned NOT NULL DEFAULT 5,
                `pro_per_md` int(10) unsigned NOT NULL DEFAULT 5,
                `pro_per_sm` int(10) unsigned NOT NULL DEFAULT 4,
                `pro_per_xs` int(10) unsigned NOT NULL DEFAULT 3,
                `popup_text_color` varchar(7) DEFAULT NULL,
                `popup_a_color` varchar(7) DEFAULT NULL,
                `popup_a_color_hover` varchar(7) DEFAULT NULL,
                `cc_text_color` varchar(7) DEFAULT NULL,
                `cc_a_color` varchar(7) DEFAULT NULL,
                `cc_a_color_hover` varchar(7) DEFAULT NULL,
                `cc_bg` varchar(7) DEFAULT NULL,
                `force_square` tinyint(1) unsigned NOT NULL DEFAULT 0,
                PRIMARY KEY (`id_st_instagram`)
            ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 ;');

        /* Banners lang configuration */
        $return &= Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'st_instagram_lang` (
                `id_st_instagram` int(10) UNSIGNED NOT NULL,
                `id_lang` int(10) unsigned NOT NULL ,
                `block_title` varchar(128) DEFAULT NULL,
                `url` varchar(255) DEFAULT NULL,
                `image_multi_lang` varchar(255) DEFAULT NULL,
                `width` int(10) unsigned NOT NULL DEFAULT 0,
                `height` int(10) unsigned NOT NULL DEFAULT 0,
                `custom_content` text,
                PRIMARY KEY (`id_st_instagram`, `id_lang`)
            ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 ;');

        $return &= (bool)Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'st_instagram_shop` (
                `id_st_instagram` int(10) UNSIGNED NOT NULL,
                `id_shop` int(11) NOT NULL,
                PRIMARY KEY (`id_st_instagram`,`id_shop`),
                KEY `id_shop` (`id_shop`)
            ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 ;');

        $return &= Db::getInstance()->Execute('
            CREATE TABLE IF NOT EXISTS `'. _DB_PREFIX_ .'st_instagram_img` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `instagram_id` BIGINT(30) NOT NULL,
                `media_type` VARCHAR(60) DEFAULT NULL,
                `thumbnail_url` TEXT DEFAULT NULL,
                `media_url` TEXT DEFAULT NULL,
                `permalink` TEXT DEFAULT NULL,
                `inst_time` VARCHAR(32) DEFAULT NULL,
                `username` VARCHAR(32) DEFAULT NULL,
                `caption` TEXT DEFAULT NULL,
                `ctime` INT(11) DEFAULT NULL,
                `utime` INT(11) DEFAULT NULL,
                `st_instagram_uid` VARCHAR(30) DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `NewIndex1` (`instagram_id`)
            ) ENGINE='._MYSQL_ENGINE_.' AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE = utf8mb4_unicode_ci;');
        $return &= Db::getInstance()->Execute('
                CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'st_instagram_bind` (
                `id_st_instagram_bind` INT(10) NOT NULL AUTO_INCREMENT,
                `utoken` VARCHAR(255) DEFAULT NULL,
                `userid` VARCHAR(60) DEFAULT NULL,
                `username` VARCHAR(255) DEFAULT NULL,
                `shop_ids` VARCHAR(255) DEFAULT NULL,
                `tk_time` INT(11) DEFAULT NULL,
                PRIMARY KEY (`id_st_instagram_bind`)
              ) ENGINE='._MYSQL_ENGINE_.' AUTO_INCREMENT=1 DEFAULT CHARSET=utf8');

        return $return;
    }
    
    public function uninstall()
    {
        $this->clearCache();
        return $this->deleteTables() &&
            parent::uninstall();
    }
    /**
     * deletes tables
     */
    public function deleteTables()
    {
        return Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'st_instagram`,`'._DB_PREFIX_.'st_instagram_lang`,`'._DB_PREFIX_.'st_instagram_shop`,`'._DB_PREFIX_.'st_instagram_img`,`'._DB_PREFIX_.'st_instagram_bind`');
    }

    public function getContent()
    {
        $this->context->smarty->assign("update_img_url",AdminController::$currentIndex.'&configure='.$this->name.'&ajax=1&act=update_cache_img&token='.Tools::getAdminTokenLite('AdminModules'));
        $this->context->smarty->assign("clear_img_url",AdminController::$currentIndex.'&configure='.$this->name.'&ajax=1&act=clear_cache_img&token='.Tools::getAdminTokenLite('AdminModules'));

        if (Tools::getValue('act')=='delete_image' && $ident = Tools::getValue('ident')) {
            $result = array(
                'r' => false,
                'm' => '',
                'd' => ''
            );
            $instagram = new StInstagramClass($ident);
            $image = $instagram->bg_img;
            $instagram->bg_img = '';
            if ($instagram->save()) {
                @unlink(_PS_UPLOAD_DIR_.$image);
                $result['r'] = true;
            }
            die(json_encode($result));
        }
        if (Tools::getValue('act')=='verify_user') {
            $name = trim(Tools::getValue('user'));
            $result = $this->searchUser($name);
            if (!isset($result['error']) || !$result['error']) {
                // Permission denied
                if (!isset($result['data']) && isset($result['meta'])) {
                    $message = '';
                    if (isset($result['meta']['error_type'])) {
                        $message .= 'error_type: '.$result['meta']['error_type'].', ';
                    }
                    if (isset($result['meta']['code'])) {
                        $message .= 'code: '.$result['meta']['code'].', ';
                    }
                    if (isset($result['meta']['error_message'])) {
                        $message .= 'error_message: '.$result['meta']['error_message'].',';
                    }
                    $result = array('error'=>true, 'message'=>trim($message, ','));
                } elseif (isset($result['data'])) {
                    $verified = false;
                    foreach ($result['data'] as $user) {
                        if ($user['username'] == $name) {
                            $verified = true;
                            $result = array('error'=>false, 'message'=>$this->getTranslator()->trans('Success, everything went fine, we have got the user info from Instagram. Do not forget to save the change.', array(), 'Modules.Stinstagram.Admin'), 'id'=>$user['id']);
                            break;
                        }
                    }
                    if (!$verified) {
                        $result = array('error'=>true, 'message'=>$this->getTranslator()->trans('It seems that this user does not exist, please double check if you filled in the correct username.', array(), 'Modules.Stinstagram.Admin'));
                    }
                }
            }
            die(json_encode($result));
        }

        if (Tools::getValue('ajax') == 1) {
            if(Tools::getValue('act')=='clear_cache_img'){
                $inst_acc=Db::getInstance()->executeS('select * from '._DB_PREFIX_.'st_instagram_bind');
                if($inst_acc && count($inst_acc)>0){
                    foreach($inst_acc as $val){
                        (new StInstagramClass())->getApidata1000id($val['id_st_instagram_bind'],1);
                    }
                }
                die;
            }
            if(Tools::getValue('act')=='update_cache_img'){
                $inst_acc=Db::getInstance()->executeS('select * from '._DB_PREFIX_.'st_instagram_bind');
                if($inst_acc && count($inst_acc)>0){
                    foreach($inst_acc as $val){
                        (new StInstagramClass())->getApidata1000id($val['id_st_instagram_bind'],2);
                    }
                }
                die;
            }
           if (Tools::getValue('act') == 'get_instagram') {
                $result = '';
                switch(Configuration::get($this->_prefix_st.'SHOW_IMAGE'))
                {
                    case 0:
                        $result = $this->getUser();
                        break;
                    case 1:
                        $result = $this->getHashTag();
                        break;
                    case 2:
                        $result = $this->getLiked();    
                        break;
                    default:
                        break;
                }
                die($result);
            } elseif (tools::geTvalue('act') == 'like_media') {
                $result = '';
                $id = Tools::getValue('id_media');
                $id && $result = $this->likeMedia($id);
                die($result);
            } elseif (tools::geTvalue('act') == 'delete_liked_media') {
                $result = '';
                $id = Tools::getValue('id_media');
                $id && $result = $this->deleteLikedMedia($id);
                die($result);
            }
        }


        $this->context->controller->addCSS(($this->_path).'views/css/admin.css');
        $this->context->controller->addJS(($this->_path).'views/js/admin.js');

        $id_st_instagram = (int)Tools::getValue('id_st_instagram');
        if (Tools::isSubmit('save'.$this->name) || Tools::isSubmit('save'.$this->name.'AndStay')) {
            if ($id_st_instagram) {
                $instagram = new StInstagramClass($id_st_instagram);
                // verify user_name and user_id
                /*if ($instagram->user_name != Tools::getValue('user_name')) {
                    if (!Tools::getValue('user_id') || Tools::getValue('user_id') == $instagram->user_id) {
                        $_POST['user_name'] = $instagram->user_name;
                        $_POST['user_id'] = $instagram->user_id;
                    }
                } elseif ($instagram->user_name == Tools::getValue('user_name') && $instagram->user_id != Tools::getValue('user_id')) {
                    $_POST['user_id'] = $instagram->user_id;
                }*/
            } else {
                $instagram = new StInstagramClass();
            }

            $_POST['user_name'] = Configuration::get($this->_prefix_st.'USER_NAME');
            $_POST['user_id'] = Configuration::get($this->_prefix_st.'USER_ID');

            //Same all validate changes.
            $fatal_error = $waring_error = array();
            $error_message = '';
            $this->initForm();
            foreach ($this->fields_form as $form) {
                foreach ($form['form']['input'] as $field) {
                    if (isset($field['validation'])) {
                        $value = Tools::getValue($field['name']);
                        if (isset($field['required']) && $field['required'] && $value==false && (string)$value != '0') {
                            $fatal_error[] = sprintf(Tools::displayError('Field "%s" is required.'), $field['label']);
                        } elseif ($value) {
                            $field_validation = $field['validation'];
                            if (($field_validation!='isColor' && !Validate::$field_validation($value)) || ($field_validation=='isColor' && !preg_match('/^#([a-f0-9]{3}){1,2}$/i', $value))) {
                                 $waring_error[] = sprintf(Tools::displayError('Field "%s" was not saved, cause the value you filled was invalid. Other fields were saved.'), $field['label']);
                                 $_POST[$field['name']] = (int)$instagram->id > 0 ?  $instagram->$field['name'] : (isset($field['default_value']) ? $field['default_value'] : '');
                            }
                        }
                    }
                }
            }

            if (count($waring_error) || count($fatal_error)) {
                $error_message .= $this->displayError(implode('<br/>', array_merge($waring_error, $fatal_error)));
            }
            
            if (!count($fatal_error)) {
                $error = array();
                $instagram->copyFromPost();

                if ($instagram->hash_tag) {
                    $instagram->hash_tag = trim($instagram->hash_tag, '#');
                }
                
                if($instagram->grid) {
                    foreach ($this->fields_form[0]['form']['input']['dropdownlistgroup']['values']['medias'] as $v) {
                        if(in_array($instagram->{'pro_per_'.$v}, array(7,9,11))) {
                            $instagram->{'pro_per_'.$v}--;
                        }
                    }
                }

                if ($instagram->location) {
                    $item_arr = explode('-', $instagram->location);
                    if (count($item_arr)==2) {
                        $instagram->location = 0;
                        $instagram->custom_hook = '';
                        if ($item_arr[0]==1) {
                            $instagram->location = (int)$item_arr[1];
                        } elseif ($item_arr[0]==2) {
                            $instagram->custom_hook = $item_arr[1];
                            if (!($id_hook = Hook::getIdByName($instagram->custom_hook)) || !Hook::getModulesFromHook($id_hook, $this->id)) {
                                $this->registerHook($instagram->custom_hook);
                            }
                        }
                    }
                }
                $res = $this->stUploadImage('bg_img');
                if (count($res['error'])) {
                    $error = array_merge($error, $res['error']);
                } elseif ($res['image']) {
                    $instagram->bg_img = $res['image'];
                }
                $validate_result = $instagram->validateFields(false, true);
                if (!count($error) && $validate_result===true) {
                    $shop_ids = $instagram->getShopIds();
                    $instagram->clearShopIds();
                    $id_shop_list = array();
                    if($assos_shop = Tools::getValue('checkBoxShopAsso_st_instagram')) {
                        foreach ($assos_shop as $id_shop => $row) {
                            $id_shop_list[] = $id_shop;
                        }
                    }
                    if (!$id_shop_list) {
                        $id_shop_list = array(Context::getContext()->shop->id);
                    }
                    $instagram->id_shop_list = array_unique($id_shop_list);
                    if ($instagram->save()) {
                        $this->prepareHooks();
                        $this->clearCache();
                        if (!$error_message) {
                            if (Tools::isSubmit('save'.$this->name.'AndStay')) {
                                Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&id_st_instagram='.$instagram->id.'&update'.$this->name.'&conf='.($id_st_instagram?4:3).'&token='.Tools::getAdminTokenLite('AdminModules'));
                            } else {
                                $this->_html .= $this->displayConfirmation($this->getTranslator()->trans('Instagram settings', array(), 'Admin.Theme.Panda').' '.($id_st_instagram ? $this->getTranslator()->trans('updated', array(), 'Admin.Theme.Panda') : $this->getTranslator()->trans('added', array(), 'Admin.Theme.Panda')));
                            }    
                        }
                    } else {
                        $instagram->restoreShopIds($shop_ids);
                        $error_message .= $this->displayError($this->getTranslator()->trans('An error occurred during banner group', array(), 'Admin.Theme.Panda').' '.($id_st_instagram ? $this->getTranslator()->trans('updating', array(), 'Admin.Theme.Panda') : $this->getTranslator()->trans('creation', array(), 'Admin.Theme.Panda')));
                    }
                } else {
                    $error_message .= count($error) ? implode('', $error) : ($validate_result ? $this->displayError($validate_result) : $this->displayError($this->getTranslator()->trans('Invalid value for field(s).', array(), 'Admin.Theme.Panda')));
                }
                $this->clearCache();
            }
            if ($error_message) {
                if (Tools::isSubmit('save'.$this->name.'AndStay')) {
                    $this->context->cookie->st_instagram_error = $error_message;
                    $this->context->cookie->write();
                    Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.($instagram->id?'&id_st_instagram='.$instagram->id.'&update':'&add').$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'));
                } else {
                    $this->_html .= $error_message;
                }  
            }
        }
        if (Tools::isSubmit('save'.$this->name.'setting')) {
            $this->initSettingForm();
            foreach ($this->fields_form as $form) {
                foreach ($form['form']['input'] as $field) {
                    if (isset($field['validation'])) {
                        $errors = array();
                        $value = Tools::getValue($field['name']);
                        $ishtml = ($field['validation']=='isAnything') ? true : false;
                        if (isset($field['required']) && $field['required'] && $value==false && (string)$value != '0') {
                            $errors[] = sprintf(Tools::displayError('Field "%s" is required.'), $field['label']);
                        } elseif ($value) {
                            $field_validation = $field['validation'];
                            if (!Validate::$field_validation($value)) {
                                $errors[] = sprintf(Tools::displayError('Field "%s" is invalid.'), $field['label']);
                            }
                        }
                        // Set default value
                        if ($value === false && isset($field['default_value'])) {
                            $value = $field['default_value'];
                        }

                        if (count($errors)) {
                            $this->validation_errors = array_merge($this->validation_errors, $errors);
                        } elseif ($value==false) {
                            switch($field['validation'])
                            {
                                case 'isUnsignedId':
                                case 'isUnsignedInt':
                                case 'isInt':
                                case 'isBool':
                                    $value = 0;
                                    break;
                                case 'isNullOrUnsignedId':
                                    $value = $value==='0' ? '0' : '';
                                    break;
                                default:
                                    $value = '';
                                    break;
                            }
                            Configuration::updateValue($this->_prefix_st.Tools::strtoupper($field['name']), $value);
                            
                        } else {
                            Configuration::updateValue($this->_prefix_st.Tools::strtoupper($field['name']), $value, $ishtml);
                        }

                    }
                }
            }

            if (count($this->validation_errors)) {
                $this->_html .= $this->displayError(implode('<br/>', $this->validation_errors));
            } else {
                $this->clearCache();
                Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&conf=4&set'.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'));
            }
        }
        if (Tools::isSubmit('status'.$this->name)) {
            $instagram = new StInstagramClass($id_st_instagram);
            if ($instagram->id && $instagram->toggleStatus()) {
                $this->clearCache();
                Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'));
            } else {
                $this->_html .= $this->displayError($this->getTranslator()->trans('An error occurred while updating the status.', array(), 'Modules.Stinstagram.Admin'));
            }
        }
        if (Tools::isSubmit('delete'.$this->name) && $id_st_instagram) {
            $instagram = new StInstagramClass($id_st_instagram);
            $instagram->delete();
            $this->prepareHooks();
            $this->clearCache();
            Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'));
        }

        //yige dianpu zhineng bangding yige inst zhanghao,  yige inst zhanghao keyi bangding duoge dianpu

        if ((Tools::isSubmit('submitOptionsst_instagram_bind') || Tools::isSubmit('savest_instagram_bind'))) {
            $id_st_instagram_bind = Tools::getValue('id_st_instagram_bind');
            $alData=Db::getInstance()->executeS((new DbQuery())->from('st_instagram_bind'));
            //panduan shifou yijing bangding guo bangding guo tishi cuowu 
            if($alData && $shop_ids=Tools::getValue('shop_ids_selected')){
                if(!empty($alData) && count($alData)>0){
                    $jg=[];
                    foreach($alData as $alVal){
                        if($alVal['id_st_instagram_bind']==$id_st_instagram_bind || $alVal['shop_ids']==null || $alVal['shop_ids']=='' || $alVal['shop_ids']==null)
                            continue;
                        if($jj=array_intersect(explode(',',$alVal['shop_ids']),$shop_ids)){
                            $jg=array_merge($jj,$jg);
                        }
                    }
                    if(!empty($jg) && count($jg)>0){
                        $tmp_sname='';
                        if($slist=Shop::getShops()){
                            foreach($slist as $sval){
                                if(in_array($sval['id_shop'],$jg)){
                                    $tmp_sname.=$sval['name'].' ,';
                                }
                            }
                        }
                        $tmp_sname=trim($tmp_sname,',');
                        $this->_errors[]=$this->getTranslator()->trans('Shop "%s" already bound to another account',[$tmp_sname]);
                    }
                }
            }
            if(empty($this->_errors)){
                $this->savebindingdata();
            }
        }

        if(Tools::isSubmit('deletest_instagram_bind') && ($id_st_instagram_bind = Tools::getValue('id_st_instagram_bind'))){
            $sql=(new Dbquery())
                ->from('st_instagram_bind')
                ->where('id_st_instagram_bind='.(int)$id_st_instagram_bind);
            if($instD=Db::getInstance()->getRow($sql)){
                if(Db::getInstance()->delete('st_instagram_bind','id_st_instagram_bind='.(int)$id_st_instagram_bind)){
                    Db::getInstance()->delete('st_instagram_img','st_instagram_uid='.(int)$instD['userid']);
                }
            }else{
                $this->_errors[]=$this->getTranslator()->trans('The deleted data does not exist');  
            }
        }
        //

        if (Tools::isSubmit('addstinstagram') || Tools::isSubmit('updatestinstagram')) {
            $id_shop=Context::getContext()->shop->id;
            $inst_acc=Db::getInstance()->getRow('select * from '._DB_PREFIX_.'st_instagram_bind where FIND_IN_SET('.$id_shop.',`shop_ids`)');
            if (!$inst_acc) {
                return $this->_html.$this->displayError($this->getTranslator()->trans('The store has not been attached to Instagram account, please do so first', array(), 'Modules.Stinstagram.Admin'));
            }
            if (isset($this->context->cookie->st_instagram_error) && $this->context->cookie->st_instagram_error) {
                $this->_html .= $this->context->cookie->st_instagram_error;
                $this->context->cookie->st_instagram_error = '';
            }
            $this->initTabNames();
            $helper = $this->initForm();
            $this->smarty->assign(array(
                'ins_tabs' => $this->_tabs,
                'ins_tab_content' => $helper->generateForm($this->fields_form),
            ));
            return $this->_html.$this->display(__FILE__, 'bo_tab_layout.tpl');
        } elseif (tools::isSubmit('setstinstagram')) {
            $helper = $this->initSettingForm();
            return $this->_html.$helper->generateForm($this->fields_form);
        }elseif(Tools::isSubmit('bindingnewinst') || Tools::isSubmit('updatest_instagram_bind')){
            return $this->bingdinginst();
        } else {
            $user_list = $this->_getUserListHtml();
            $this->list_result = StInstagramClass::getAll();
            $helper = $this->initList();
            if($err_msg=Tools::getValue('err_msg')){
                if(isset($this->error_msg[$err_msg])){
                    if($err_msg==1){
                        $this->_html.=$this->displayConfirmation($this->error_msg[$err_msg]);
                    }else{
                        $this->_html.=$this->displayError($this->error_msg[$err_msg]);
                    }
                }
            }       
            return $this->_html.$this->_getMsgHtml().$user_list.$helper->generateList($this->list_result, $this->fields_list);
        }
    }

        public function _getMsgHtml(){
        $this->smarty->assign(
            array(
                'cron_url'      => $this->context->link->getModuleLink('stinstagram', 'cron', ['token' => $this->getToken()]))
        );
        return $this->display(__FILE__, 'userinfo.tpl');
    }


    public function _getUserListHtml(){

        $fields_list = array(
            'id_st_instagram_bind' => array(
                'title' => $this->getTranslator()->trans('Id', array(), 'Admin.Theme.Panda'),
                'class' => 'fixed-width-sm',
                'type' => 'text',
                'search' => false,
                'orderby' => false
            ),
            'username' => array(
                'title' => $this->getTranslator()->trans('UserName', array(), 'Admin.Theme.Panda'),
                'width' => 200,
                'type' => 'text',
                'search' => false,
                'orderby' => false
            ),
            'userid' => array(
                'title' => $this->getTranslator()->trans('User Id', array(), 'Admin.Theme.Panda'),
                'width' => 200,
                'type' => 'text',
                'search' => false,
                'orderby' => false
            ),
            'utoken' => array(
				'title' => $this->getTranslator()->trans('Token', array(), 'Admin.Theme.Panda'),
				'width' => 200,
				'type' => 'text',
				'callback' => 'showTokenXinhao',
				'callback_object' => 'StInstagram',
                'search' => false,
                'orderby' => false
			),
            'shop_ids' => array(
                'title' => $this->getTranslator()->trans('Shop', array(), 'Admin.Theme.Panda'),
                'class' => '',
                'type' => 'text',
                'callback' => 'showShopname',
                'callback_object' => 'StInstagram',
                'search' => false,
                'orderby' => false
            )
        );


        $list_result=Db::getInstance()->executeS('select * from '._DB_PREFIX_.'st_instagram_bind');

        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->simple_header = false;
        $helper->module = $this;
        $helper->listTotal = count($list_result);
        $helper->identifier = 'id_st_instagram_bind';
        $helper->actions = array('edit', 'delete');
        $helper->show_toolbar = true;
        $helper->toolbar_btn['new'] =  array(
            'href' => AdminController::$currentIndex.'&configure='.$this->name.'&bindingnewinst&token='.Tools::getAdminTokenLite('AdminModules'),
            'desc' => $this->getTranslator()->trans('Add instagram', array(), 'Modules.Stinstagram.Admin'),
        );

        $helper->title = $this->binding_title;
        $helper->table = 'st_instagram_bind';
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
        return $helper->generateList($list_result, $fields_list);

    }

    public function bingdinginst($id=0){
        $error_msg     =!empty($this->_errors)?implode('<br>',$this->_errors) :'';
        $client_id     = Configuration::get($this->_prefix_st.'CLIENT_ID') ? Configuration::get($this->_prefix_st.'CLIENT_ID') : self::$client_id;
        $redirect_uri  = 'https://www.sunnytoo.com/instagram/';
        $response_type = 'code';
        $cron_url      = $this->context->link->getModuleLink('stinstagram', 'cron', ['token' => $this->getToken()]);
        $scope         = implode(',', array('user_profile', 'user_media'));
        $api_url       = 'https://api.instagram.com/oauth/authorize';

        $upData=[
           'shop_ids'=>[] 
        ];
        $id_st_instagram_bind=$id>0?$id:Tools::getValue('id_st_instagram_bind');
        if($id_st_instagram_bind){
            if($upData=Db::getInstance()->getRow('select * from '._DB_PREFIX_.'st_instagram_bind where id_st_instagram_bind='.(int)$id_st_instagram_bind)){
                if($upData['shop_ids']!=''){
                    $upData['shop_ids']=explode(',',$upData['shop_ids']);
                }else{
                    $upData['shop_ids']=[];
                }
            }
        }
        $fields_form[0]['form']=[];
        $shop_data=Shop::getShops();
        if(!empty($this->_errors))
            $fields_form[0]['form']['error']=implode('<br>',$this->_errors);
        if(!empty($this->_confirmations)){
            $fields_form[0]['form']['success']=implode('<br>',$this->_confirmations);
        }
        $fields_form[0]['form']['legend'] =array(
                'title' => $this->getTranslator()->trans('Sources', array(), 'Modules.Stinstagram.Admin'),
                'icon'  => 'icon-cogs'
        );
        $fields_form[0]['form']['input'] = array(
                array(
                    'type'=>'html',
                    'label'=>'',
                    'name'=>'curl',
                    'html_content'=>'<a href="'.$api_url.'?client_id='.$client_id.'&redirect_uri='.$redirect_uri.'&scope='.$scope.'&response_type='.$response_type.'" id="a_linker" target="_blank">'.$this->getTranslator()->trans('The first step to use the Instagram module is to get Access token and User ID.').'</a>'
                ),
                array(
                    'type'=>'text',
                    'label'=>$this->getTranslator()->trans('Access token:'),
                    'name'=>'access_token',
                    'required'=>true,
                    'default_value'=>'',
                ),
                array(
                    'type'=>'text',
                    'label'=>$this->getTranslator()->trans('User ID:'),
                    'name'=>'user_id',
                    'required'=>true,
                    'default_value'=>'',
                ),
                array(
                    'type'=>'swap',
                    'name'=>'shop_ids',
                    'label'=>$this->getTranslator()->trans('Shop ID:'),
                    'options'=>[
                        'query'=>$shop_data,
                        'id'=>'id_shop',
                        'name'=>'name'
                    ],
                ),
                array(
                    'type'=>'html',
                    'label'=>'',
                    'name'=>'surl',
                    'html_content'=>$this->getTranslator()->trans('By using this module, you are agreeing to the').'<a href="http://instagram.com/about/legal/terms/api/" target="_blank">'.$this->getTranslator()->trans('Instagram API Terms of Use').'</a><br>'. $this->getTranslator()->trans('Cron URL:').$this->context->link->getModuleLink('stinstagram', 'cron', ['token' => $this->getToken()])
                ),
                array(
                    'type' => 'go_back_to_list',
                    'label' => '',
                    'name' => AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'),
                ),

            );
        $fields_form[0]['form']['buttons'] = array(
            array(
                'type' => 'submit',
                'title'=> $this->getTranslator()->trans(' Save', array(), 'Admin.Theme.Panda'),
                'icon' => 'process-icon-save',
                'class'=> 'pull-right'
            ),
        );
        // $fields_form[0]['form']['submit'] = array(
        //     'title' => $this->getTranslator()->trans('   Save and stay   ', array(), 'Admin.Actions'),
        //     'stay' => true
        // );
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->id = $id_st_instagram_bind;
        $helper->module = $this;
        $helper->table =  'st_instagram_bind';
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;

        $helper->identifier = 'id_st_instagram_bind';
        $helper->submit_action = 'savest_instagram_bind';
        // $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => [
                'user_id'=>(isset($upData['userid']) && $upData['userid']!='')?$upData['userid']:'',
                'shop_ids'=>$upData['shop_ids'],
                'access_token'=>(isset($upData['utoken']) && $upData['utoken']!='')?$upData['utoken']:'',
            ],
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );
        return $helper->generateForm($fields_form);

    }

    public function savebindingdata(){
        $user_id = Tools::getValue('user_id');
        $token = Tools::getValue('access_token');
        if(!$token || !$user_id){
            $this->_errors[]=$this->getTranslator()->trans('You must fill in the Token and User ID');
            return;
        }
        if($id_st_instagram_bind = Tools::getValue('id_st_instagram_bind')){
            //updata
            dump('sssss');
            $sql=(new Dbquery())
                ->from('st_instagram_bind')
                ->where('id_st_instagram_bind='.(int)$id_st_instagram_bind);
            if(!$instD=Db::getInstance()->getRow($sql))
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'&err_msg=3');
            $updata=[
                'userid'=>$user_id,
                'utoken'=>$token
            ];
            
            if(empty($this->_errors)){
                $refres_img=false; 
                if($shop_ids=Tools::getValue('shop_ids_selected')){
                    if(is_array($shop_ids) && count($shop_ids)>0){
                        $updata['shop_ids']=implode(',',$shop_ids);
                    }
                }
                if($instD['userid']!=$user_id){
                    $new_token=$this->refreshAccessToken($token);
                    if(isset($new_token['access_token'])){
                        $updata['utoken']=$new_token['access_token'];
                        $updata['tk_time']=time();
                        $refres_img=true;
                    }
                }elseif($instD['utoken']!=$token){
                    $new_token=$this->refreshAccessToken($token);
                    if(isset($new_token['access_token'])){
                        $updata['utoken']=$new_token['access_token'];
                        $updata['tk_time']=time();
                    }
                }
                if(Db::getInstance()->update('st_instagram_bind',$updata,'id_st_instagram_bind='.(int)$id_st_instagram_bind)){
                    if($refres_img){
                        (new StInstagramClass())->getApidata1000id($id_st_instagram_bind,1);
                    }
                    $this->_confirmations[]=$this->getTranslator()->trans('Data modification succeeded');
                }else{
                    $this->_errors[]=$this->getTranslator()->trans('Failed to modify data');
                }
                if(Tools::isSubmit('savest_instagram_bindAndStay')){

                    return $this->bingdinginst();
                }
            }
        }else{
            //new binding
            if(empty($this->_errors)){
                $instData=[
                    'tk_time'=>time(),
                    'shop_ids'=>'',
                    'username'=>'',
                    'userid'=>$user_id,
                    'utoken'=>''
                ];
                $new_token=$this->refreshAccessToken($token);
                if(isset($new_token['access_token'])){
                    $instData['utoken']=$new_token['access_token'];
                    $instData['tk_time']=time();
                }
                if($shop_ids=Tools::getValue('shop_ids_selected')){
                    if(is_array($shop_ids) && count($shop_ids)>0){
                        $instData['shop_ids']=implode(',',$shop_ids);
                    }
                }
                if($instData['utoken']==''){
                    $this->_errors[]=$this->getTranslator()->trans('Failed to modify data');
                }else{
                    $user=$this->getUserInfo($instData['utoken']);
                    if(is_array($user) && isset($user['id']) && isset($user['username'])){
                        $instData['userid']=$user['id'];
                        $instData['username']=$user['username'];
                        if(Db::getInstance()->insert('st_instagram_bind',$instData)){
                            $in_id=Db::getInstance()->Insert_ID();
                            (new StInstagramClass())->getApidata1000id($in_id,1);
                            $this->_confirmations[]=$this->getTranslator()->trans('New binding succeeded');
                            if(Tools::isSubmit('savest_instagram_bindAndStay')){
                                return $this->bingdinginst($in_id);
                            }
                        }else{
                            $this->_errors[]=$this->getTranslator()->trans('New binding failed');
                        }
                    }else{
                        $this->_errors[]=$this->getTranslator()->trans('New binding failed');
                    }
                }
                                    
            }
        }
    }

    public static function showShopname($value,$row){
        if($value=='')
            return;
        $shop_ids=explode(',',$value);
        if(count($shop_ids)>0){
            $name='';
            foreach($shop_ids as $val){
                $name.=(new Shop($val))->name.'<br>';
            }
            return $name;
        }
        return;
    }

    public static function showTokenXinhao($value,$row){
        if($value=='')
            return;
        return substr($value,0,5).'****************'.substr($value,-5);
    }

    public function getPatternsArray()
    {
        $arr = array();
        for ($i=1; $i<=27; $i++) {
            $arr[] = array('id'=>$i,'name'=>$i);
        }
        return $arr;
    }

    public function getToken()
    {
        return md5($this->name.'-ST-Themes');
    }


    public function initForm()
    {
        $id_st_instagram = (int)Tools::getValue('id_st_instagram');
        $instagram = new StInstagramClass($id_st_instagram);
        $is_vc_installed = Module::isInstalled('jscomposer') && Module::isEnabled('jscomposer');
        /*$this->fields_form[10]['form'] = array(
            'legend' => array(
                'title' => $this->getTranslator()->trans('Sources', array(), 'Modules.Stinstagram.Admin'),
                'icon'  => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('Tag:', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'hash_tag',
                    'default_value' => '',
                    'validation' => 'isGenericName',
                    'class' => 'fixed-width-xxl',
                    'desc' => array(
                        $this->getTranslator()->trans('Fill in one tag, without "#"', array(), 'Modules.Stinstagram.Admin'),
                        $this->getTranslator()->trans('If you fill in this field, media from Instagram have this hashtag will be displayed, not only media from your account.', array(), 'Modules.Stinstagram.Admin')
                        ),
                ),
            ),
            'buttons' => array(
                array(
                    'type' => 'submit',
                    'title'=> $this->getTranslator()->trans(' Save all ', array(), 'Admin.Theme.Panda'),
                    'icon' => 'process-icon-save',
                    'class'=> 'pull-right'
                ),
            ),
            'submit' => array(
                'title' => $this->getTranslator()->trans('   Save and stay   ', array(), 'Admin.Actions'),
                'stay' => true
            ),
        );
        $this->fields_form[10]['form']['input'][] = array(
            'type' => 'go_back_to_list',
            'label' => '',
            'name' => AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'),
        );*/
        $this->fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->getTranslator()->trans('Layout settings', array(), 'Modules.Stinstagram.Admin'),
                'icon'  => 'icon-cogs'
            ),
            'input' => array(
                'location' => array(
                    'type' => 'select',
                    'label' => $this->getTranslator()->trans('Show on:', array(), 'Admin.Theme.Panda'),
                    'name' => 'location',
                    'options' => array(
                        'optiongroup' => array (
                            'query' => $this->getApplyTo(),
                            'label' => 'name'
                        ),
                        'options' => array (
                            'query' => 'query',
                            'id' => 'id',
                            'name' => 'name'
                        ),
                        'default' => array(
                            'value' => 0,
                            'label' => $is_vc_installed ? $this->getTranslator()->trans('For visual composer module', array(), 'Admin.Theme.Panda') : $this->getTranslator()->trans('--', array(), 'Admin.Theme.Panda'),
                        )
                    ),
                    'default_value' => '1-3',
                    'validation' => 'isGenericName',
                ),
                array(
                    'type' => 'radio',
                    'label' => $this->getTranslator()->trans('How to display media:', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'grid',
                    'values' => array(
                        array(
                            'id' => 'grid_slider',
                            'value' => 0,
                            'label' => $this->getTranslator()->trans('Slider', array(), 'Admin.Theme.Panda')),
                        array(
                            'id' => 'grid_grid',
                            'value' => 1,
                            'label' => $this->getTranslator()->trans('Grid view', array(), 'Admin.Theme.Panda')),
                    ),
                    'default_value' => 0,
                    'validation' => 'isUnsignedInt',
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->getTranslator()->trans('Display a load more button when in grid view:', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'load_more',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'load_more_on',
                            'value' => 1,
                            'label' => $this->getTranslator()->trans('Yes', array(), 'Admin.Theme.Panda')),
                        array(
                            'id' => 'load_more_off',
                            'value' => 0,
                            'label' => $this->getTranslator()->trans('No', array(), 'Admin.Theme.Panda')),
                    ),
                    'default_value' => 1,
                    'validation' => 'isBool',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('How many media do you want to show:', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'count',
                    'default_value' => 10,
                    'validation' => 'isUnsignedInt',
                    'class' => 'fixed-width-sm'
                ),
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('Space between images:', array(), 'Admin.Theme.Panda'),
                    'name' => 'padding',
                    'prefix' => 'px',
                    'class' => 'fixed-width-lg',
                    'default_value' => '',
                    'validation' => 'isNullOrUnsignedId',
                    'desc' => $this->getTranslator()->trans('Leave it empty to use the default value.', array(), 'Admin.Theme.Panda'),
                ),
                'dropdownlistgroup' => array(
                    'type' => 'dropdownlistgroup',
                    'label' => $this->getTranslator()->trans('The number of columns on home page:', array(), 'Admin.Theme.Panda'),
                    'name' => 'pro_per',
                    'values' => array(
                            'maximum' => 12,
                            'medias' => array('fw','xxl','xl','lg','md','sm','xs'),
                        ),
                    'desc' => array(
                        $this->getTranslator()->trans('The "Full screen" drop down list is for advanced users, leave it empty if you do know how to use it. Setting a value for it can make the module to be full screen if in a full screen hook, that means if it is empty and the module is in a full screen hook, the module would be boxed, not full screen. The value only take effect in slider layout, not in grid layout, that means in gird layout the value is used to make the module to display in full screen, but the number of meida per row is the value of "Large devices" drop down list.', array(), 'Admin.Theme.Panda'),
                        $this->getTranslator()->trans('7, 9 and 11 can not be used in grid view, they will be automatically decreased to 6, 8 and 10. ', array(), 'Admin.Theme.Panda'),
                        ),
                ),
                /*array(
                    'type' => 'radio',
                    'label' => $this->getTranslator()->trans('Image size:', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'image_size',
                    'values' => array(
                        array(
                            'id' => 'standard_resolution',
                            'value' => 2,
                            'label' => $this->getTranslator()->trans('Standard resolution 612px in wide', array(), 'Modules.Stinstagram.Admin')),
                        array(
                            'id' => 'low_resolution',
                            'value' => 0,
                            'label' => $this->getTranslator()->trans('Low resolution 306px in wide', array(), 'Modules.Stinstagram.Admin')),
                        array(
                            'id' => 'thumbnail',
                            'value' => 1,
                            'label' => $this->getTranslator()->trans('Thumbnail 150px x 150px', array(), 'Modules.Stinstagram.Admin')),
                    ),
                    'default_value' => 2,
                    'validation' => 'isGenericName',
                ),*/
                array(
                    'type' => 'switch',
                    'label' => $this->getTranslator()->trans('Force non-square images to be square:', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'force_square',
                    'is_bool' => true,
                    'default_value' => 0,
                    'validation' => 'isBool',
                    'values' => array(
                        array(
                            'id' => 'force_square_on',
                            'value' => 1,
                            'label' => $this->getTranslator()->trans('Enabled', array(), 'Admin.Theme.Panda')
                        ),
                        array(
                            'id' => 'force_square_off',
                            'value' => 0,
                            'label' => $this->getTranslator()->trans('Disabled', array(), 'Admin.Theme.Panda')
                        )
                    ),
                ),
                /*array(
                    'type' => 'radio',
                    'label' => $this->getTranslator()->trans('Caption alignment:', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'caption_align',
                    'default_value' => 2,
                    'validation' => 'isUnsignedInt',
                    'values' => array(
                        array(
                            'id' => 'text_align_left',
                            'value' => 1,
                            'label' => $this->getTranslator()->trans('Left', array(), 'Modules.Stinstagram.Admin')),
                        array(
                            'id' => 'text_align_center',
                            'value' => 2,
                            'label' => $this->getTranslator()->trans('Center', array(), 'Modules.Stinstagram.Admin')),
                        array(
                            'id' => 'text_align_right',
                            'value' => 3,
                            'label' => $this->getTranslator()->trans('Right', array(), 'Modules.Stinstagram.Admin')),
                    ),
                ),*/
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('Top padding:', array(), 'Admin.Theme.Panda'),
                    'name' => 'top_padding',
                    'default_value' => '',
                    'validation' => 'isNullOrUnsignedId',
                    'prefix' => 'px',
                    'class' => 'fixed-width-lg',
                    'desc' => $this->getTranslator()->trans('Leave it empty to use the default value.', array(), 'Admin.Theme.Panda'),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('Bottom padding:', array(), 'Admin.Theme.Panda'),
                    'name' => 'bottom_padding',
                    'default_value' => '',
                    'validation' => 'isNullOrUnsignedId',
                    'prefix' => 'px',
                    'class' => 'fixed-width-lg',
                    'desc' => $this->getTranslator()->trans('Leave it empty to use the default value.', array(), 'Admin.Theme.Panda'),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('Top spacing:', array(), 'Admin.Theme.Panda'),
                    'name' => 'top_margin',
                    'prefix' => 'px',
                    'class' => 'fixed-width-lg',
                    'default_value' => '',
                    'validation' => 'isNullOrUnsignedId',
                    'desc' => $this->getTranslator()->trans('Leave it empty to use the default value.', array(), 'Admin.Theme.Panda'),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('Bottom spacing:', array(), 'Admin.Theme.Panda'),
                    'name' => 'bottom_margin',
                    'prefix' => 'px',
                    'class' => 'fixed-width-lg',
                    'default_value' => '',
                    'validation' => 'isNullOrUnsignedId',
                    'desc' => $this->getTranslator()->trans('Leave it empty to use the default value.', array(), 'Admin.Theme.Panda'),
                ),
                array(
                    'type' => 'radio',
                    'label' => $this->getTranslator()->trans('Show block title:', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'title',
                    'default_value' => 1,
                    'validation' => 'isUnsignedInt',
                    'values' => array(
                        array(
                            'id' => 'title_no',
                            'value' => 3,
                            'label' => $this->getTranslator()->trans('No', array(), 'Admin.Theme.Panda')),
                        array(
                            'id' => 'title_left',
                            'value' => 0,
                            'label' => $this->getTranslator()->trans('Left', array(), 'Admin.Theme.Panda')),
                        array(
                            'id' => 'title_center',
                            'value' => 1,
                            'label' => $this->getTranslator()->trans('Center', array(), 'Admin.Theme.Panda')),
                        array(
                            'id' => 'title_right',
                            'value' => 2,
                            'label' => $this->getTranslator()->trans('Right', array(), 'Admin.Theme.Panda')),
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('Block title:', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'block_title',
                    'lang' => true,
                ),
                array(
                    'type' => 'radio',
                    'label' => $this->getTranslator()->trans('Hide on mobile:', array(), 'Admin.Theme.Panda'),
                    'name' => 'hide_on_mobile',
                    'default_value' => 0,
                    'validation' => 'isUnsignedInt',
                    'values' => array(
                        array(
                            'id' => 'hide_on_mobile_0',
                            'value' => 0,
                            'label' => $this->getTranslator()->trans('No', array(), 'Admin.Theme.Panda')),
                        array(
                            'id' => 'hide_on_mobile_1',
                            'value' => 1,
                            'label' => $this->getTranslator()->trans('Hide on mobile (screen width < 992px)', array(), 'Admin.Theme.Panda')),
                        array(
                            'id' => 'hide_on_mobile_2',
                            'value' => 2,
                            'label' => $this->getTranslator()->trans('Hide on PC (screen width > 992px)', array(), 'Admin.Theme.Panda')),
                    ),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->getTranslator()->trans('Status:', array(), 'Admin.Theme.Panda'),
                    'name' => 'active',
                    'is_bool' => true,
                    'default_value' => 1,
                    'validation' => 'isBool',
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->getTranslator()->trans('Enabled', array(), 'Admin.Theme.Panda')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->getTranslator()->trans('Disabled', array(), 'Admin.Theme.Panda')
                        )
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('position:', array(), 'Admin.Theme.Panda'),
                    'name' => 'position',
                    'default_value' => 0,
                    'validation' => 'isUnsignedInt',
                    'class' => 'fixed-width-xxl',
                ),
                /*array(
                    'type' => 'textarea',
                    'label' => $this->getTranslator()->trans('Custom content:', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'custom_content',
                    'lang' => true,
                    'cols' => 60,
                    'rows' => 12,
                    'autoload_rte' => true,
                    'desc' => $this->getTranslator()->trans('Content here will be placed to right under the title of this module, you can use html code.', array(), 'Modules.Stinstagram.Admin'),
                ),*/
            ),
            'buttons' => array(
                array(
                    'type' => 'submit',
                    'title'=> $this->getTranslator()->trans(' Save all ', array(), 'Admin.Theme.Panda'),
                    'icon' => 'process-icon-save',
                    'class'=> 'pull-right'
                ),
            ),
            'submit' => array(
                'title' => $this->getTranslator()->trans('   Save and stay   ', array(), 'Admin.Actions'),
                'stay' => true
            ),
        );
        if (Shop::isFeatureActive())
        {
            $this->fields_form[0]['form']['input'][] = array(
                'type' => 'shop',
                'label' => $this->getTranslator()->trans('Shop association:', array(), 'Admin.Theme.Panda'),
                'name' => 'checkBoxShopAsso',
            );
        }
        $this->fields_form[0]['form']['input'][] = array(
            'type' => 'go_back_to_list',
            'label' => '',
            'name' => AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'),
        );

        $this->fields_form[5]['form'] = array(
            'legend' => array(
                'title' => $this->getTranslator()->trans('Media information', array(), 'Modules.Stinstagram.Admin'),
                'icon'  => 'icon-cogs'
            ),
            'input' => array(
                /*array(
                    'type' => 'radio',
                    'label' => $this->getTranslator()->trans('Show likes counter:', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'show_likes',
                    'values' => array(
                        array(
                            'id' => 'show_likes_1',
                            'value' => 1,
                            'label' => $this->getTranslator()->trans('Yes, show this on image when mouse hover.', array(), 'Modules.Stinstagram.Admin')),
                        array(
                            'id' => 'show_likes_2',
                            'value' => 2,
                            'label' => $this->getTranslator()->trans('Yes, show this on image.', array(), 'Modules.Stinstagram.Admin')),
                        array(
                            'id' => 'show_likes_3',
                            'value' => 3,
                            'label' => $this->getTranslator()->trans('Yes, show this below image.', array(), 'Modules.Stinstagram.Admin')),
                        array(
                            'id' => 'show_likes_0',
                            'value' => 0,
                            'label' => $this->getTranslator()->trans('No', array(), 'Admin.Theme.Panda')),
                    ),
                    'default_value' => 1,
                    'validation' => 'isUnsignedInt',
                ),
                array(
                    'type' => 'radio',
                    'label' => $this->getTranslator()->trans('Show comments counter:', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'show_comments',
                    'values' => array(
                        array(
                            'id' => 'show_comments_1',
                            'value' => 1,
                            'label' => $this->getTranslator()->trans('Yes, show this on image when mouse hover.', array(), 'Modules.Stinstagram.Admin')),
                        array(
                            'id' => 'show_comments_2',
                            'value' => 2,
                            'label' => $this->getTranslator()->trans('Yes, show this on image.', array(), 'Modules.Stinstagram.Admin')),
                        array(
                            'id' => 'show_comments_3',
                            'value' => 3,
                            'label' => $this->getTranslator()->trans('Yes, show this below image.', array(), 'Modules.Stinstagram.Admin')),
                        array(
                            'id' => 'show_comments_off',
                            'value' => 0,
                            'label' => $this->getTranslator()->trans('No', array(), 'Admin.Theme.Panda')),
                    ),
                    'default_value' => 1,
                    'validation' => 'isUnsignedInt',
                ),*/
                array(
                    'type' => 'radio',
                    'label' => $this->getTranslator()->trans('Show time:', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'show_timestamp',
                    'values' => array(
                        array(
                            'id' => 'show_timestamp_1',
                            'value' => 1,
                            'label' => $this->getTranslator()->trans('Yes, show this on image when mouse hover.', array(), 'Modules.Stinstagram.Admin')),
                        array(
                            'id' => 'show_timestamp_2',
                            'value' => 2,
                            'label' => $this->getTranslator()->trans('Yes, show this on image.', array(), 'Modules.Stinstagram.Admin')),
                        array(
                            'id' => 'show_timestamp_3',
                            'value' => 3,
                            'label' => $this->getTranslator()->trans('Yes, show this below image.', array(), 'Modules.Stinstagram.Admin')),
                        array(
                            'id' => 'show_timestamp_off',
                            'value' => 0,
                            'label' => $this->getTranslator()->trans('No', array(), 'Admin.Theme.Panda')),
                    ),
                    'default_value' => 0,
                    'validation' => 'isUnsignedInt',
                ),
                array(
                    'type' => 'radio',
                    'label' => $this->getTranslator()->trans('Time format:', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'time_format',
                    'values' => array(
                        array(
                            'id' => 'time_format_1',
                            'value' => 1,
                            'label' => $this->getTranslator()->trans('Publish date', array(), 'Modules.Stinstagram.Admin')),
                        array(
                            'id' => 'time_format_0',
                            'value' => 0,
                            'label' => $this->getTranslator()->trans('Time ago, like 2 days ago, 1 month ago', array(), 'Modules.Stinstagram.Admin')),
                    ),
                    'default_value' => 0,
                    'validation' => 'isUnsignedInt',
                ),
                /*array(
                    'type' => 'radio',
                    'label' => $this->getTranslator()->trans('Show name:', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'show_username',
                    'values' => array(
                        array(
                            'id' => 'show_username_1',
                            'value' => 1,
                            'label' => $this->getTranslator()->trans('Yes, show this on image when mouse hover.', array(), 'Modules.Stinstagram.Admin')),
                        array(
                            'id' => 'show_username_2',
                            'value' => 2,
                            'label' => $this->getTranslator()->trans('Yes, show this on image.', array(), 'Modules.Stinstagram.Admin')),
                        array(
                            'id' => 'show_username_3',
                            'value' => 3,
                            'label' => $this->getTranslator()->trans('Yes, show this below image.', array(), 'Modules.Stinstagram.Admin')),
                        array(
                            'id' => 'show_username_off',
                            'value' => 0,
                            'label' => $this->getTranslator()->trans('No', array(), 'Admin.Theme.Panda')),
                    ),
                    'default_value' => 0,
                    'validation' => 'isUnsignedInt',
                ),*/
                array(
                    'type' => 'radio',
                    'label' => $this->getTranslator()->trans('Show description:', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'show_caption',
                    'default_value' => 0,
                    'validation' => 'isUnsignedInt',
                    'values' => array(
                        array(
                            'id' => 'show_caption_1',
                            'value' => 1,
                            'label' => $this->getTranslator()->trans('Yes, show this on image when mouse hover.', array(), 'Modules.Stinstagram.Admin')),
                        array(
                            'id' => 'show_caption_1',
                            'value' => 4,
                            'label' => $this->getTranslator()->trans('Yes, show this on image when mouse hover, hide when screen width < 992px..', array(), 'Modules.Stinstagram.Admin')),
                        array(
                            'id' => 'show_caption_2',
                            'value' => 2,
                            'label' => $this->getTranslator()->trans('Yes, show this on image.', array(), 'Modules.Stinstagram.Admin')),
                        array(
                            'id' => 'show_caption_2',
                            'value' => 5,
                            'label' => $this->getTranslator()->trans('Yes, show this on image, hide when screen width < 992px.', array(), 'Modules.Stinstagram.Admin')),
                        array(
                            'id' => 'show_caption_3',
                            'value' => 3,
                            'label' => $this->getTranslator()->trans('Yes, show this below image.', array(), 'Modules.Stinstagram.Admin')),
                        array(
                            'id' => 'show_caption_0',
                            'value' => 0,
                            'label' => $this->getTranslator()->trans('No', array(), 'Admin.Theme.Panda')),
                    ),
                ),
                array(
                    'type' => 'radio',
                    'label' => $this->getTranslator()->trans('The lenght of description:', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'lenght_of_caption',
                    'default_value' => 0,
                    'validation' => 'isUnsignedInt',
                    'values' => array(
                        array(
                            'id' => 'lenght_of_caption_0',
                            'value' => 0,
                            'label' => $this->getTranslator()->trans('Full caption', array(), 'Modules.Stinstagram.Admin')),
                        array(
                            'id' => 'lenght_of_caption_1',
                            'value' => 1,
                            'label' => $this->getTranslator()->trans('Truncated (200 characters)', array(), 'Modules.Stinstagram.Admin')),
                    ),
                ),
                array(
                    'type' => 'radio',
                    'label' => $this->getTranslator()->trans('Display an icon to show media type:', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'show_media_type',
                    'default_value' => 1,
                    'validation' => 'isUnsignedInt',
                    'values' => array(
                        array(
                            'id' => 'show_media_type_0',
                            'value' => 0,
                            'label' => $this->getTranslator()->trans('No.', array(), 'Admin.Theme.Panda')),
                        array(
                            'id' => 'show_media_type_1',
                            'value' => 1,
                            'label' => $this->getTranslator()->trans('Only show an video icon for vidoes.', array(), 'Modules.Stinstagram.Admin')),
                        array(
                            'id' => 'show_media_type_2',
                            'value' => 2,
                            'label' => $this->getTranslator()->trans('Yes.', array(), 'Admin.Theme.Panda')),
                    ),
                ),
                array(
                    'type' => 'radio',
                    'label' => $this->getTranslator()->trans('When click on a image:', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'click_action',
                    'default_value' => 1,
                    'validation' => 'isUnsignedInt',
                    'values' => array(
                        array(
                            'id' => 'click_action_0',
                            'value' => 0,
                            'label' => $this->getTranslator()->trans('View the image on Instagram.', array(), 'Modules.Stinstagram.Admin')),
                        array(
                            'id' => 'click_action_1',
                            'value' => 1,
                            'label' => $this->getTranslator()->trans('Show the image on a lightbox.', array(), 'Modules.Stinstagram.Admin')),
                    ),
                ),
                array(
                    'type' => 'radio',
                    'label' => $this->getTranslator()->trans('Hover effect:', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'hover_effect',
                    'default_value' => 1,
                    'validation' => 'isUnsignedInt',
                    'values' => array(
                        array(
                            'id' => 'hover_effect_0',
                            'value' => 0,
                            'label' => $this->getTranslator()->trans('None', array(), 'Modules.Stinstagram.Admin')
                        ),
                        array(
                            'id' => 'hover_effect_1',
                            'value' => 1,
                            'label' => $this->getTranslator()->trans('Scale up', array(), 'Modules.Stinstagram.Admin')
                        ),
                        array(
                            'id' => 'hover_effect_2',
                            'value' => 2,
                            'label' => $this->getTranslator()->trans('Scale down', array(), 'Modules.Stinstagram.Admin')
                        ),
                    ),
                ),
            ),
            'buttons' => array(
                array(
                    'type' => 'submit',
                    'title'=> $this->getTranslator()->trans(' Save all ', array(), 'Admin.Theme.Panda'),
                    'icon' => 'process-icon-save',
                    'class'=> 'pull-right'
                ),
            ),
            'submit' => array(
                'title' => $this->getTranslator()->trans('   Save and stay   ', array(), 'Admin.Actions'),
                'stay' => true
            ),
        );
        $this->fields_form[5]['form']['input'][] = array(
            'type' => 'go_back_to_list',
            'label' => '',
            'name' => AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'),
        );
        /*$this->fields_form[6]['form'] = array(
            'legend' => array(
                'title' => $this->getTranslator()->trans('Profile section', array(), 'Modules.Stinstagram.Admin'),
                'icon'  => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'type' => 'radio',
                    'label' => $this->getTranslator()->trans('Show profile:', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'show_profile',
                    'values' => array(
                        array(
                            'id' => 'show_profile_1',
                            'value' => 1,
                            'label' => $this->getTranslator()->trans('Yes', array(), 'Admin.Theme.Panda')),
                        array(
                            'id' => 'show_profile_0',
                            'value' => 0,
                            'label' => $this->getTranslator()->trans('No', array(), 'Admin.Theme.Panda')),
                    ),
                    'default_value' => 0,
                    'validation' => 'isUnsignedInt',
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->getTranslator()->trans('Show account stats. Post counts, follower counts and following counts:', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'show_counts',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'show_counts_on',
                            'value' => 1,
                            'label' => $this->getTranslator()->trans('Yes', array(), 'Admin.Theme.Panda')),
                        array(
                            'id' => 'show_counts_off',
                            'value' => 0,
                            'label' => $this->getTranslator()->trans('No', array(), 'Admin.Theme.Panda')),
                    ),
                    'default_value' => 1,
                    'validation' => 'isBool',
                ),
                array(
                    'type' => 'radio',
                    'label' => $this->getTranslator()->trans('Show avatar:', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'show_avatar',
                    'values' => array(
                        array(
                            'id' => 'show_avatar_1',
                            'value' => 1,
                            'label' => $this->getTranslator()->trans('Yes', array(), 'Admin.Theme.Panda')),
                        array(
                            'id' => 'show_avatar_2',
                            'value' => 2,
                            'label' => $this->getTranslator()->trans('Yes, a round avatar', array(), 'Modules.Stinstagram.Admin')),
                        array(
                            'id' => 'show_avatar_0',
                            'value' => 0,
                            'label' => $this->getTranslator()->trans('No', array(), 'Admin.Theme.Panda')),
                    ),
                    'default_value' => 1,
                    'validation' => 'isUnsignedInt',
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->getTranslator()->trans('Show bio:', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'show_bio',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'show_bio_on',
                            'value' => 1,
                            'label' => $this->getTranslator()->trans('Yes', array(), 'Admin.Theme.Panda')),
                        array(
                            'id' => 'show_bio_off',
                            'value' => 0,
                            'label' => $this->getTranslator()->trans('No', array(), 'Admin.Theme.Panda')),
                    ),
                    'default_value' => 1,
                    'validation' => 'isBool',
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->getTranslator()->trans('Show website link:', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'show_website',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'show_website_on',
                            'value' => 1,
                            'label' => $this->getTranslator()->trans('Yes', array(), 'Admin.Theme.Panda')),
                        array(
                            'id' => 'show_website_off',
                            'value' => 0,
                            'label' => $this->getTranslator()->trans('No', array(), 'Admin.Theme.Panda')),
                    ),
                    'default_value' => 1,
                    'validation' => 'isBool',
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->getTranslator()->trans('Show a Follow button:', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'follow',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'follow_on',
                            'value' => 1,
                            'label' => $this->getTranslator()->trans('Yes', array(), 'Admin.Theme.Panda')),
                        array(
                            'id' => 'follow_off',
                            'value' => 0,
                            'label' => $this->getTranslator()->trans('No', array(), 'Admin.Theme.Panda')),
                    ),
                    'default_value' => 1,
                    'validation' => 'isBool',
                ),
            ),
            'buttons' => array(
                array(
                    'type' => 'submit',
                    'title'=> $this->getTranslator()->trans(' Save all ', array(), 'Admin.Theme.Panda'),
                    'icon' => 'process-icon-save',
                    'class'=> 'pull-right'
                ),
            ),
            'submit' => array(
                'title' => $this->getTranslator()->trans('   Save and stay   ', array(), 'Admin.Actions'),
                'stay' => true
            ),
        );
        $this->fields_form[6]['form']['input'][] = array(
            'type' => 'go_back_to_list',
            'label' => '',
            'name' => AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'),
        );*/

        $this->fields_form[3]['form'] = array(
            'legend' => array(
                'title' => $this->getTranslator()->trans('Color settings', array(), 'Admin.Theme.Panda'),
                'icon'  => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Overlay color:', array(), 'Admin.Theme.Panda'),
                    'name' => 'bg_hover_color',
                    'size' => 33,
                    'default_value' => '',
                    'validation' => 'isColor',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('Overlay opacity:', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'bg_opacity_nohover',
                    'default_value' => 0,
                    'validation' => 'isFloat',
                    'class' => 'fixed-width-lg',
                    'desc' => $this->getTranslator()->trans('From 0.0 (fully transparent) to 1.0 (fully opaque).', array(), 'Modules.Stinstagram.Admin'),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('Overlay opacity when mouseover:', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'bg_opacity',
                    'default_value' => 0.6,
                    'validation' => 'isFloat',
                    'class' => 'fixed-width-lg',
                    'desc' => $this->getTranslator()->trans('From 0.0 (fully transparent) to 1.0 (fully opaque).', array(), 'Modules.Stinstagram.Admin'),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('Image border size:', array(), 'Admin.Theme.Panda'),
                    'name' => 'image_border',
                    'prefix' => 'px',
                    'class' => 'fixed-width-lg',
                    'default_value' => 0,
                    'validation' => 'isNullOrUnsignedId',
                ),
                array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Image border color:', array(), 'Admin.Theme.Panda'),
                    'name' => 'image_border_color',
                    'size' => 33,
                    'default_value' => '',
                    'validation' => 'isColor',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('Image border radius:', array(), 'Admin.Theme.Panda'),
                    'name' => 'image_border_radius',
                    'prefix' => 'px',
                    'class' => 'fixed-width-lg',
                    'default_value' => 0,
                    'validation' => 'isNullOrUnsignedId',
                    'desc' => $this->getTranslator()->trans('Set a large value for this field to have round images.', array(), 'Modules.Stinstagram.Admin'),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('Media info text size:', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'font_size',
                    'prefix' => 'px',
                    'class' => 'fixed-width-lg',
                    'default_value' => '',
                    'validation' => 'isNullOrUnsignedId',
                    'desc' => $this->getTranslator()->trans('Set it to 0 to use the default setting.', array(), 'Admin.Theme.Panda'),
                ),
                array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Media info color when they are on image:', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'caption_color',
                    'size' => 33,
                    'default_value' => '',
                    'validation' => 'isColor',
                ),
                array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Media info color when they are blow image:', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'media_info_color',
                    'size' => 33,
                    'default_value' => '',
                    'validation' => 'isColor',
                ),
                array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Media info link color when they are blow image:', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'media_info_a_color',
                    'size' => 33,
                    'default_value' => '',
                    'validation' => 'isColor',
                ),
                array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Media info link hover color when they are blow image:', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'media_info_a_color_hover',
                    'size' => 33,
                    'default_value' => '',
                    'validation' => 'isColor',
                ),
                array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Media info background color they are blow image:', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'media_info_bg',
                    'size' => 33,
                    'default_value' => '',
                    'validation' => 'isColor',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('Block title font size:', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'title_font_size',
                    'prefix' => 'px',
                    'class' => 'fixed-width-lg',
                    'default_value' => 0,
                    'validation' => 'isNullOrUnsignedId',
                    'desc' => $this->getTranslator()->trans('Set it to 0 to use the default setting.', array(), 'Admin.Theme.Panda'),
                ),
                 array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Block title color:', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'title_color',
                    'class' => 'color',
                    'size' => 20,
                    'default_value' => '',
                    'validation' => 'isColor',
                 ),
                 array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Block title background color:', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'title_bg',
                    'class' => 'color',
                    'size' => 20,
                    'default_value' => '',
                    'validation' => 'isColor',
                 ),
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('Block title broder height:', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'title_border_height',
                    'prefix' => 'px',
                    'class' => 'fixed-width-lg',
                    'validation' => 'isNullOrUnsignedId',
                ),
                 array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Block title broder color:', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'title_border_color',
                    'class' => 'color',
                    'size' => 20,
                    'default_value' => '',
                    'validation' => 'isColor',
                 ),
                 array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Block title broder highlight color:', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'title_border_color_h',
                    'class' => 'color',
                    'size' => 20,
                    'default_value' => '',
                    'validation' => 'isColor',
                 ),
                array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Load more button color:', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'load_more_color',
                    'size' => 33,
                    'default_value' => '',
                    'validation' => 'isColor',
                ),
                array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Load more button background:', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'load_more_bg',
                    'size' => 33,
                    'default_value' => '',
                    'validation' => 'isColor',
                ),
                array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Load more button hover background:', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'load_more_bg_hover',
                    'size' => 33,
                    'default_value' => '',
                    'validation' => 'isColor',
                ),
                array(
                    'type' => 'radio',
                    'label' => $this->getTranslator()->trans('Shadows around images:', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'shadow_effect',
                    'values' => array(
                        array(
                            'id' => 'shadow_effect_on',
                            'value' => 1,
                            'label' => $this->getTranslator()->trans('Yes', array(), 'Admin.Theme.Panda')),
                        array(
                            'id' => 'shadow_effect_hover',
                            'value' => 2,
                            'label' => $this->getTranslator()->trans('Show shadows when mouseover', array(), 'Modules.Stinstagram.Admin')),
                        array(
                            'id' => 'shadow_effect_off',
                            'value' => 0,
                            'label' => $this->getTranslator()->trans('No', array(), 'Admin.Theme.Panda')),
                    ),
                    'default_value' => 0,
                    'validation' => 'isUnsignedInt',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('H-shadow:', array(), 'Admin.Theme.Panda'),
                    'name' => 'h_shadow',
                    'default_value' => 0,
                    'validation' => 'isInt',
                    'prefix' => 'px',
                    'class' => 'fixed-width-lg',
                    'desc' => $this->getTranslator()->trans('The position of the horizontal shadow. Negative values are allowed.', array(), 'Modules.Stinstagram.Admin'),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('V-shadow:', array(), 'Admin.Theme.Panda'),
                    'name' => 'v_shadow',
                    'default_value' => 0,
                    'validation' => 'isInt',
                    'prefix' => 'px',
                    'class' => 'fixed-width-lg',
                    'desc' => $this->getTranslator()->trans('The position of the vertical shadow. Negative values are allowed.', array(), 'Modules.Stinstagram.Admin'),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('The blur distance of shadow:', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'shadow_blur',
                    'default_value' => 6,
                    'validation' => 'isUnsignedInt',
                    'prefix' => 'px',
                    'class' => 'fixed-width-lg',
                ),
                array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Shadow color:', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'shadow_color',
                    'class' => 'color',
                    'size' => 20,
                    'default_value' => '',
                    'validation' => 'isColor',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('Shadow opacity:', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'shadow_opacity',
                    'default_value' => 0.4,
                    'validation' => 'isFloat',
                    'class' => 'fixed-width-lg',
                    'desc' => $this->getTranslator()->trans('From 0.0 (fully transparent) to 1.0 (fully opaque).', array(), 'Modules.Stinstagram.Admin'),
                ),
                 array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Background color:', array(), 'Admin.Theme.Panda'),
                    'name' => 'bg_color',
                    'class' => 'color',
                    'size' => 20,
                    'default_value' => '',
                    'validation' => 'isColor',
                 ),
                array(
                    'type' => 'select',
                    'label' => $this->getTranslator()->trans('Select a pattern number:', array(), 'Admin.Theme.Panda'),
                    'name' => 'bg_pattern',
                    'options' => array(
                        'query' => $this->getPatternsArray(),
                        'id' => 'id',
                        'name' => 'name',
                        'default' => array(
                            'value' => 0,
                            'label' => $this->getTranslator()->trans('None', array(), 'Admin.Theme.Panda'),
                        ),
                    ),
                    'default_value' => 0,
                    'validation' => 'isUnsignedInt',
                ),
                array(
                    'type' => 'show_bg_patterns',
                    'label' => '',
                    'size' => 27,
                    'name' => _MODULE_DIR_.'stinstagram/views/img/patterns/',
                ),
                'bg_img_field' => array(
                    'type' => 'file',
                    'label' => $this->getTranslator()->trans('Upload your own pattern or background image:', array(), 'Admin.Theme.Panda'),
                    'name' => 'bg_img',
                    'desc' => '',
                ),
            ),
            'buttons' => array(
                array(
                    'type' => 'submit',
                    'title'=> $this->getTranslator()->trans(' Save all ', array(), 'Admin.Theme.Panda'),
                    'icon' => 'process-icon-save',
                    'class'=> 'pull-right'
                ),
            ),
            'submit' => array(
                'title' => $this->getTranslator()->trans('   Save and stay   ', array(), 'Admin.Actions'),
                'stay' => true
            ),
        );
        $this->fields_form[3]['form']['input'][] = array(
            'type' => 'go_back_to_list',
            'label' => '',
            'name' => AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'),
        );

        $this->fields_form[4]['form'] = array(
            'legend' => array(
                'title' => $this->getTranslator()->trans('Profile section', array(), 'Modules.Stinstagram.Admin'),
                'icon'  => 'icon-cogs'
            ),
            'input' => array(
                 array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Text color:', array(), 'Admin.Theme.Panda'),
                    'name' => 'profile_text',
                    'class' => 'color',
                    'size' => 20,
                    'default_value' => '',
                    'validation' => 'isColor',
                 ),
                 array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Link color:', array(), 'Admin.Theme.Panda'),
                    'name' => 'profile_a_color',
                    'class' => 'color',
                    'size' => 20,
                    'default_value' => '',
                    'validation' => 'isColor',
                 ),
                 array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Link hover color:', array(), 'Admin.Theme.Panda'),
                    'name' => 'profile_a_color_hover',
                    'class' => 'color',
                    'size' => 20,
                    'default_value' => '',
                    'validation' => 'isColor',
                 ),
                 array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Background color:', array(), 'Admin.Theme.Panda'),
                    'name' => 'profile_bg',
                    'class' => 'color',
                    'size' => 20,
                    'default_value' => '',
                    'validation' => 'isColor',
                 ),
                 array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Account stats color:', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'account_stats_color',
                    'class' => 'color',
                    'size' => 20,
                    'default_value' => '',
                    'validation' => 'isColor',
                 ),
                 array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Account stats background:', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'account_stats_bg',
                    'class' => 'color',
                    'size' => 20,
                    'default_value' => '',
                    'validation' => 'isColor',
                 ),
                 array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Follow button color:', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'follow_color',
                    'class' => 'color',
                    'size' => 20,
                    'default_value' => '',
                    'validation' => 'isColor',
                 ),
                 array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Follow button background:', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'follow_bg',
                    'class' => 'color',
                    'size' => 20,
                    'default_value' => '',
                    'validation' => 'isColor',
                 ),
            ),
            'buttons' => array(
                array(
                    'type' => 'submit',
                    'title'=> $this->getTranslator()->trans(' Save all ', array(), 'Admin.Theme.Panda'),
                    'icon' => 'process-icon-save',
                    'class'=> 'pull-right'
                ),
            ),
            'submit' => array(
                'title' => $this->getTranslator()->trans('   Save and stay   ', array(), 'Admin.Actions'),
                'stay' => true
            ),
        );
        $this->fields_form[4]['form']['input'][] = array(
            'type' => 'go_back_to_list',
            'label' => '',
            'name' => AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'),
        );
        $this->fields_form[11]['form'] = array(
            'legend' => array(
                'title' => $this->getTranslator()->trans('Lightbox', array(), 'Modules.Stinstagram.Admin'),
                'icon'  => 'icon-cogs'
            ),
            'input' => array(
                 array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Text:', array(), 'Admin.Theme.Panda'),
                    'name' => 'popup_text_color',
                    'class' => 'color',
                    'size' => 20,
                    'default_value' => '',
                    'validation' => 'isColor',
                 ),
                 array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Link color:', array(), 'Admin.Theme.Panda'),
                    'name' => 'popup_a_color',
                    'class' => 'color',
                    'size' => 20,
                    'default_value' => '',
                    'validation' => 'isColor',
                 ),
                 array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Link hover color:', array(), 'Admin.Theme.Panda'),
                    'name' => 'popup_a_color_hover',
                    'class' => 'color',
                    'size' => 20,
                    'default_value' => '',
                    'validation' => 'isColor',
                 ),

            ),
            'buttons' => array(
                array(
                    'type' => 'submit',
                    'title'=> $this->getTranslator()->trans(' Save all ', array(), 'Admin.Theme.Panda'),
                    'icon' => 'process-icon-save',
                    'class'=> 'pull-right'
                ),
            ),
            'submit' => array(
                'title' => $this->getTranslator()->trans('   Save and stay   ', array(), 'Admin.Actions'),
                'stay' => true
            ),
        );
        $this->fields_form[11]['form']['input'][] = array(
            'type' => 'go_back_to_list',
            'label' => '',
            'name' => AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'),
        );
        /*$this->fields_form[12]['form'] = array(
            'legend' => array(
                'title' => $this->getTranslator()->trans('Custom content block', array(), 'Modules.Stinstagram.Admin'),
                'icon'  => 'icon-cogs'
            ),
            'input' => array(
                 array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Text:', array(), 'Admin.Theme.Panda'),
                    'name' => 'cc_text_color',
                    'class' => 'color',
                    'size' => 20,
                    'default_value' => '',
                    'validation' => 'isColor',
                 ),
                 array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Link color:', array(), 'Admin.Theme.Panda'),
                    'name' => 'cc_a_color',
                    'class' => 'color',
                    'size' => 20,
                    'default_value' => '',
                    'validation' => 'isColor',
                 ),
                 array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Link hover color:', array(), 'Admin.Theme.Panda'),
                    'name' => 'cc_a_color_hover',
                    'class' => 'color',
                    'size' => 20,
                    'default_value' => '',
                    'validation' => 'isColor',
                 ),
                 array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Background:', array(), 'Admin.Theme.Panda'),
                    'name' => 'cc_bg',
                    'class' => 'color',
                    'size' => 20,
                    'default_value' => '',
                    'validation' => 'isColor',
                 ),

            ),
            'buttons' => array(
                array(
                    'type' => 'submit',
                    'title'=> $this->getTranslator()->trans(' Save all ', array(), 'Admin.Theme.Panda'),
                    'icon' => 'process-icon-save',
                    'class'=> 'pull-right'
                ),
            ),
            'submit' => array(
                'title' => $this->getTranslator()->trans('   Save and stay   ', array(), 'Admin.Actions'),
                'stay' => true
            ),
        );
        $this->fields_form[12]['form']['input'][] = array(
            'type' => 'go_back_to_list',
            'label' => '',
            'name' => AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'),
        );*/
        $this->fields_form[1]['form'] = array(
            'legend' => array(
                'title' => $this->getTranslator()->trans('Settings for displaying images in a slider', array(), 'Modules.Stinstagram.Admin'),
                'icon'  => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'type' => 'switch',
                    'label' => $this->getTranslator()->trans('Autoplay:', array(), 'Admin.Theme.Panda'),
                    'name' => 'slideshow',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'slideshow_on',
                            'value' => 1,
                            'label' => $this->getTranslator()->trans('Yes', array(), 'Admin.Theme.Panda')),
                        array(
                            'id' => 'slideshow_off',
                            'value' => 0,
                            'label' => $this->getTranslator()->trans('No', array(), 'Admin.Theme.Panda')),
                    ),
                    'default_value' => 0,
                    'validation' => 'isBool',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('Time:', array(), 'Admin.Theme.Panda'),
                    'name' => 's_speed',
                    'desc' => $this->getTranslator()->trans('The period, in milliseconds, between the end of a transition effect and the start of the next one. Default: 7000', array(), 'Admin.Theme.Panda'),
                    'default_value' => 7000,
                    'validation' => 'isUnsignedInt',
                    'class' => 'fixed-width-sm'
                ),
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('Transition period:', array(), 'Admin.Theme.Panda'),
                    'name' => 'a_speed',
                    'desc' => $this->getTranslator()->trans('The period, in milliseconds, of the transition effect. Default: 400', array(), 'Admin.Theme.Panda'),
                    'default_value' => 400,
                    'validation' => 'isUnsignedInt',
                    'class' => 'fixed-width-sm'
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->getTranslator()->trans('Pause On Hover:', array(), 'Admin.Theme.Panda'),
                    'name' => 'pause_on_hover',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'pause_on_hover_on',
                            'value' => 1,
                            'label' => $this->getTranslator()->trans('Yes', array(), 'Admin.Theme.Panda')),
                        array(
                            'id' => 'pause_on_hover_off',
                            'value' => 0,
                            'label' => $this->getTranslator()->trans('No', array(), 'Admin.Theme.Panda')),
                    ),
                    'default_value' => 1,
                    'validation' => 'isBool',
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->getTranslator()->trans('Rewind to first after the last slide:', array(), 'Admin.Theme.Panda'),
                    'name' => 'rewind_nav',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'rewind_nav_on',
                            'value' => 1,
                            'label' => $this->getTranslator()->trans('Yes', array(), 'Admin.Theme.Panda')),
                        array(
                            'id' => 'rewind_nav_off',
                            'value' => 0,
                            'label' => $this->getTranslator()->trans('No', array(), 'Admin.Theme.Panda')),
                    ),
                    'default_value' => 0,
                    'validation' => 'isBool',
                ),
                array(
                    'type' => 'radio',
                    'label' => $this->getTranslator()->trans('Scroll:', array(), 'Admin.Theme.Panda'),
                    'name' => 'move',
                    'values' => array(
                        array(
                            'id' => 'move_on',
                            'value' => 1,
                            'label' => $this->getTranslator()->trans('Scroll per page', array(), 'Admin.Theme.Panda')),
                        array(
                            'id' => 'move_off',
                            'value' => 0,
                            'label' => $this->getTranslator()->trans('Scroll per item', array(), 'Admin.Theme.Panda')),
                    ),
                    'default_value' => 1,
                    'validation' => 'isBool',
                ),
                array(
                    'type' => 'radio',
                    'label' => $this->getTranslator()->trans('Display "next" and "prev" buttons:', array(), 'Admin.Theme.Panda'),
                    'name' => 'direction_nav',
                    'default_value' => 5,
                    'values' => array(
                        array(
                            'id' => 'direction_nav_none',
                            'value' => 0,
                            'label' => $this->getTranslator()->trans('NO', array(), 'Admin.Theme.Panda')),
                        array(
                            'id' => 'direction_nav_top-right',
                            'value' => 1,
                            'label' => $this->getTranslator()->trans('Top right-hand side', array(), 'Admin.Theme.Panda')),
                        array(
                            'id' => 'direction_nav_full_height',
                            'value' => 2,
                            'label' => $this->getTranslator()->trans('Full height', array(), 'Admin.Theme.Panda')),
                        array(
                            'id' => 'direction_nav_full_height_hover',
                            'value' => 3,
                            'label' => $this->getTranslator()->trans('Full height, show out when mouseover', array(), 'Admin.Theme.Panda')),
                        array(
                            'id' => 'direction_nav_square',
                            'value' => 4,
                            'label' =>$this->getTranslator()->trans('Square', array(), 'Admin.Theme.Panda')),
                        array(
                            'id' => 'direction_nav_square_hover',
                            'value' => 5,
                            'label' =>$this->getTranslator()->trans('Square, show out when mouseover', array(), 'Admin.Theme.Panda')),
                        array(
                            'id' => 'direction_nav_circle',
                            'value' => 6,
                            'label' =>$this->getTranslator()->trans('Circle', array(), 'Admin.Theme.Panda')),
                        array(
                            'id' => 'direction_nav_circle_hover',
                            'value' => 7,
                            'label' =>$this->getTranslator()->trans('Circle, show out when mouseover', array(), 'Admin.Theme.Panda')),
                        array(
                            'id' => 'direction_nav_arrow',
                            'value' => 8,
                            'label' =>$this->getTranslator()->trans('Arrow', array(), 'Admin.Theme.Panda')),
                        array(
                            'id' => 'direction_nav_arrow_hover',
                            'value' => 9,
                            'label' =>$this->getTranslator()->trans('Arrow, show out when mouseover', array(), 'Admin.Theme.Panda')),
                    ),
                    'validation' => 'isUnsignedInt',
                ),
                array(
                    'type' => 'radio',
                    'label' => $this->getTranslator()->trans('Show navigation:', array(), 'Admin.Theme.Panda'),
                    'name' => 'control_nav',
                    'default_value' => 1,
                    'values' => array(
                        array(
                            'id' => 'control_nav_1',
                            'value' => 1,
                            'label' => $this->getTranslator()->trans('Bullets', array(), 'Admin.Theme.Panda')),
                        array(
                            'id' => 'control_nav_2',
                            'value' => 2,
                            'label' => $this->getTranslator()->trans('Number', array(), 'Admin.Theme.Panda')),
                        array(
                            'id' => 'control_nav_3',
                            'value' => 3,
                            'label' => $this->getTranslator()->trans('Progress', array(), 'Admin.Theme.Panda')),
                        array(
                            'id' => 'control_nav_0',
                            'value' => 0,
                            'label' => $this->getTranslator()->trans('No', array(), 'Admin.Theme.Panda')),
                    ),
                    'validation' => 'isUnsignedInt',
                ),
                 array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Prev/next color:', array(), 'Admin.Theme.Panda'),
                    'name' => 'direction_color',
                    'class' => 'color',
                    'size' => 20,
                    'default_value' => '',
                    'validation' => 'isColor',
                 ),
                 array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Prev/next hover color:', array(), 'Admin.Theme.Panda'),
                    'name' => 'direction_color_hover',
                    'class' => 'color',
                    'size' => 20,
                    'default_value' => '',
                    'validation' => 'isColor',
                 ),
                 array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Prev/next disabled color:', array(), 'Admin.Theme.Panda'),
                    'name' => 'direction_color_disabled',
                    'class' => 'color',
                    'size' => 20,
                    'default_value' => '',
                    'validation' => 'isColor',
                 ),
                 array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Prev/next background:', array(), 'Admin.Theme.Panda'),
                    'name' => 'direction_bg',
                    'class' => 'color',
                    'size' => 20,
                    'default_value' => '',
                    'validation' => 'isColor',
                 ),
                 array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Prev/next hover background:', array(), 'Admin.Theme.Panda'),
                    'name' => 'direction_hover_bg',
                    'class' => 'color',
                    'size' => 20,
                    'default_value' => '',
                    'validation' => 'isColor',
                 ),
                 array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Prev/next disabled background:', array(), 'Admin.Theme.Panda'),
                    'name' => 'direction_disabled_bg',
                    'class' => 'color',
                    'size' => 20,
                    'default_value' => '',
                    'validation' => 'isColor',
                 ),
                 array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Navigation color:', array(), 'Admin.Theme.Panda'),
                    'name' => 'pag_nav_bg',
                    'class' => 'color',
                    'size' => 20,
                    'default_value' => '',
                    'validation' => 'isColor',
                 ),
                 array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Navigation active color:', array(), 'Admin.Theme.Panda'),
                    'name' => 'pag_nav_bg_hover',
                    'class' => 'color',
                    'size' => 20,
                    'default_value' => '',
                    'validation' => 'isColor',
                 ),
            ),
            'buttons' => array(
                array(
                    'type' => 'submit',
                    'title'=> $this->getTranslator()->trans(' Save all ', array(), 'Admin.Theme.Panda'),
                    'icon' => 'process-icon-save',
                    'class'=> 'pull-right'
                ),
            ),
            'submit' => array(
                'title' => $this->getTranslator()->trans('   Save and stay   ', array(), 'Admin.Actions'),
                'stay' => true
            ),
        );
        $this->fields_form[1]['form']['input'][] = array(
            'type' => 'go_back_to_list',
            'label' => '',
            'name' => AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'),
        );
        $this->fields_form[2]['form'] = array(
            'legend' => array(
                'title' => $this->getTranslator()->trans('Settings for displaying images on left or right column and footer', array(), 'Modules.Stinstagram.Admin'),
                'icon'  => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('Image size:', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'picture_size_col',
                    'prefix' => 'px',
                    'class' => 'fixed-width-lg',
                    'default_value' => 80,
                    'validation' => 'isNullOrUnsignedId',
                    'desc' => $this->getTranslator()->trans('Default 80', array(), 'Modules.Stinstagram.Admin'),
                ),
                array(
                    'type' => 'radio',
                    'label' => $this->getTranslator()->trans('Hide on mobile:', array(), 'Admin.Theme.Panda'),
                    'name' => 'hide_mob_col',
                    'default_value' => 0,
                    'validation' => 'isUnsignedInt',
                    'values' => array(
                        array(
                            'id' => 'hide_mob_col_0',
                            'value' => 0,
                            'label' => $this->getTranslator()->trans('No', array(), 'Admin.Theme.Panda')),
                        array(
                            'id' => 'hide_mob_col_3',
                            'value' => 3,
                            'label' => $this->getTranslator()->trans('Open', array(), 'Admin.Theme.Panda')),
                        array(
                            'id' => 'hide_mob_col_1',
                            'value' => 1,
                            'label' => $this->getTranslator()->trans('Hide on mobile (screen width < 992px)', array(), 'Admin.Theme.Panda')),
                        array(
                            'id' => 'hide_mob_col_2',
                            'value' => 2,
                            'label' => $this->getTranslator()->trans('Hide on PC (screen width > 992px)', array(), 'Admin.Theme.Panda')),
                    ),
                    'desc' => $this->getTranslator()->trans('Screen width < 992px.', array(), 'Admin.Theme.Panda'),
                    'default_value' => 0,
                    'validation' => 'isBool',
                ),
                array(
                    'type' => 'select',
                    'label' => $this->getTranslator()->trans('Wide on footer:', array(), 'Admin.Theme.Panda'),
                    'name' => 'wide_on_footer',
                    'options' => array(
                        'query' => self::$wide_map,
                        'id' => 'id',
                        'name' => 'name',
                        'default' => array(
                            'value' => 3,
                            'label' => '3/12',
                        ),
                    ),
                    'default_value' => 3,
                    'validation' => 'isGenericName',
                ),
            ),
            'buttons' => array(
                array(
                    'type' => 'submit',
                    'title'=> $this->getTranslator()->trans(' Save all ', array(), 'Admin.Theme.Panda'),
                    'icon' => 'process-icon-save',
                    'class'=> 'pull-right'
                ),
            ),
            'submit' => array(
                'title' => $this->getTranslator()->trans('   Save and stay   ', array(), 'Admin.Actions'),
                'stay' => true
            ),
        );
        $this->fields_form[2]['form']['input'][] = array(
            'type' => 'go_back_to_list',
            'label' => '',
            'name' => AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->id = (int)$instagram->id;
        $helper->module = $this;
        $helper->table =  'st_instagram';
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;

        $helper->identifier = 'id_st_instagram';
        $helper->submit_action = 'save'.$this->name;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getFieldsValueSt($instagram),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        $helper->tpl_vars['fields_value'] = array_merge($helper->tpl_vars['fields_value'], array(
            'pro_per_fw' => $instagram->pro_per_fw,
            'pro_per_xxl' => $instagram->pro_per_xxl ? $instagram->pro_per_xxl : 7,
            'pro_per_xl' => $instagram->pro_per_xl ? $instagram->pro_per_xl : 6,
            'pro_per_lg' => $instagram->pro_per_lg ? $instagram->pro_per_lg : 5,
            'pro_per_md' => $instagram->pro_per_md ? $instagram->pro_per_md : 5,
            'pro_per_sm' => $instagram->pro_per_sm ? $instagram->pro_per_sm : 4,
            'pro_per_xs' => $instagram->pro_per_xs ? $instagram->pro_per_xs : 3,
        ));

        if ($instagram->id) {
            $location = '';
            if ($instagram->location) {
                $location = '1-'.$instagram->location;
            } elseif ($instagram->custom_hook) {
                $location = '2-'.$instagram->custom_hook;
            }
            $helper->tpl_vars['fields_value']['location'] = $location;
            if ($instagram->bg_img) {
                StInstagramClass::fetchMediaServer($instagram->bg_img);
                $this->fields_form[3]['form']['input']['bg_img_field']['desc'] = '<img src="'.$instagram->bg_img.'" width="200"/><p><a class="btn btn-default st_delete_image" href="javascript:;" data-id="'.$instagram->id.'"><i class="icon-trash"></i> '.$this->getTranslator()->trans('Delete', array(), 'Modules.Stinstagram.Admin').'</a></p>';
            }
        }

        return $helper;
    }

    protected function initList()
    {
        $this->fields_list = array(
            'id_st_instagram' => array(
                'title' => $this->getTranslator()->trans('Id', array(), 'Admin.Theme.Panda'),
                'class' => 'fixed-width-sm',
                'type' => 'text',
                'search' => false,
                'orderby' => false
            ),
            'block_title' => array(
                'title' => $this->getTranslator()->trans('Title', array(), 'Admin.Theme.Panda'),
                'width' => 200,
                'type' => 'text',
                'search' => false,
                'orderby' => false
            ),
            'user_id' => array(
				'title' => $this->getTranslator()->trans('Short code', array(), 'Admin.Theme.Panda'),
				'width' => 200,
				'type' => 'text',
				'callback' => 'showShortCode',
				'callback_object' => 'StInstagram',
                'search' => false,
                'orderby' => false
			),
            'location' => array(
                'title' => $this->getTranslator()->trans('Hook into', array(), 'Admin.Theme.Panda'),
                'class' => '',
                'type' => 'text',
                'callback' => 'showApplyTo',
                'callback_object' => 'StInstagram',
                'search' => false,
                'orderby' => false
            ),
            'position' => array(
                'title' => $this->getTranslator()->trans('Position', array(), 'Admin.Theme.Panda'),
                'align' => 'center',
                'class'=>'fixed-width-xl',
                'type' => 'text',
                'search' => false,
                'orderby' => false,
            ),
            'active' => array(
                'title' => $this->getTranslator()->trans('Status', array(), 'Admin.Theme.Panda'),
                'align' => 'center',
                'active' => 'status',
                'type' => 'bool',
                'class' => 'fixed-width-sm',
                'search' => false,
                'orderby' => false
            ),
        );

        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->simple_header = false;
        $helper->module = $this;
        $helper->listTotal = count($this->list_result);
        $helper->identifier = 'id_st_instagram';
        $helper->actions = array('edit', 'delete');
        $helper->show_toolbar = true;
        $helper->toolbar_btn['new'] =  array(
            'href' => AdminController::$currentIndex.'&configure='.$this->name.'&addstinstagram&token='.Tools::getAdminTokenLite('AdminModules'),
            'desc' => $this->getTranslator()->trans('Add instagram', array(), 'Modules.Stinstagram.Admin'),
        );
        $helper->toolbar_btn['edit'] =  array(
            'href' => AdminController::$currentIndex.'&configure='.$this->name.'&setstinstagram&token='.Tools::getAdminTokenLite('AdminModules'),
            'desc' => $this->getTranslator()->trans('Settings', array(), 'Modules.Stinstagram.Admin'),
        );

        $helper->title = $this->displayName;
        $helper->table = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
        return $helper;
    }

    protected function initSettingForm()
    {
        $this->fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->getTranslator()->trans('Advanced settings', array(), 'Admin.Theme.Panda'),
                'icon'  => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('Custom hooks:', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'custom_hooks',
                    'class' => 'fixed-width-xxl',
                    'validation' => 'isAnything',
                    'desc' => $this->getTranslator()->trans('Separate custom hooks by commas, like displayHomeTop,displayHomeBottom,displayCategoryHeader.', array(), 'Modules.Stinstagram.Admin'),
                ),
                /*array(
                    'type' => 'switch',
                    'label' => $this->getTranslator()->trans('Compatibility mode:', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'compatibility_mode',
                    'default_value' => 0,
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'compatibility_mode_on',
                            'value' => 1,
                            'label' => $this->getTranslator()->trans('Yes', array(), 'Modules.Stinstagram.Admin')),
                        array(
                            'id' => 'compatibility_mode_off',
                            'value' => 0,
                            'label' => $this->getTranslator()->trans('No', array(), 'Modules.Stinstagram.Admin')),
                    ),
                    'validation' => 'isBool',
                    'desc' => $this->getTranslator()->trans('If you have already installed some other modules from sunnytoo.com, then you can turn on this option to avoid loading common files for several times.', array(), 'Modules.Stinstagram.Admin'),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->getTranslator()->trans('Load owl carousel:', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'load_owl_carousel',
                    'default_value' => 1,
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'load_owl_carousel_on',
                            'value' => 1,
                            'label' => $this->getTranslator()->trans('Yes', array(), 'Modules.Stinstagram.Admin')),
                        array(
                            'id' => 'load_owl_carousel_off',
                            'value' => 0,
                            'label' => $this->getTranslator()->trans('No', array(), 'Modules.Stinstagram.Admin')),
                    ),
                    'validation' => 'isBool',
                    'desc' => $this->getTranslator()->trans('If this module can not show images in a sldier correctly, then try turning on this option, then this module would not load Owl carousel script, a widely used jQuery plugin, which is used to create sliders. Because of it might have already been loaded. Loading the same script more that once may cause problems.', array(), 'Modules.Stinstagram.Admin'),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->getTranslator()->trans('Load jquery timeago plugin:', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'load_timeago',
                    'default_value' => 1,
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'load_timeago_on',
                            'value' => 1,
                            'label' => $this->getTranslator()->trans('Yes', array(), 'Modules.Stinstagram.Admin')),
                        array(
                            'id' => 'load_timeago_off',
                            'value' => 0,
                            'label' => $this->getTranslator()->trans('No', array(), 'Modules.Stinstagram.Admin')),
                    ),
                    'validation' => 'isBool',
                    'desc' => $this->getTranslator()->trans('If one of your modules has already loaded this script, then turnning of this option to do not laod the same script twice.', array(), 'Modules.Stinstagram.Admin'),
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->getTranslator()->trans('Custom CSS code', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'custom_css_code',
                    'cols' => 60,
                    'rows' => 12,
                    'validation' => 'isAnything',
                    'desc' => $this->getTranslator()->trans('Put your CSS code here without wraping them in a html style tag.', array(), 'Modules.Stinstagram.Admin'),
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->getTranslator()->trans('Custom javascript code', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'custom_js_code',
                    'cols' => 60,
                    'rows' => 12,
                    'validation' => 'isAnything',
                    'desc' => array(
                        $this->getTranslator()->trans('Use html script tag to wrap you custom javascript code.', array(), 'Modules.Stinstagram.Admin'),
                        $this->getTranslator()->trans('Code here will be palced to the head tag.', array(), 'Modules.Stinstagram.Admin'),
                        $this->getTranslator()->trans('If you want to load a file using this field, you have got to turn off the "Use HTMLPurifier Library" setting on the Preferences > General page.', array(), 'Modules.Stinstagram.Admin'),
                        ),
                ),*/
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('Client ID for advanced users:', array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'client_id',
                    'default_value' => '',
                    'required' => true,
                    'validation' => 'isGenericName',
                    'class' => 'fixed-width-xxl',
                    'desc' => array(
                        $this->getTranslator()->trans('Do not change the vaule. This module would work 100% fine to show media from Instagram with the default value:',array(), 'Modules.Stinstagram.Admin').self::$client_id,
                        ),
                ),
                array(
                    'type' => 'text',
                    'label' =>$this->getTranslator()->trans('Cache lifetime:',array(), 'Modules.Stinstagram.Admin'),
                    'name' => 'cache_img_date',
                    'default_value' => 7*24*60,
                    'required' => true,
                    'validation' => 'isUnsignedInt',
                    'suffix'=>'minutes',
                    'class' => 'fixed-width-xxl',
                    'desc' => array(
                        $this->getTranslator()->trans('Due to the limitation of the number of Instagram API requests, it\'s recommended to set a large value, like 1440(One day).',array(), 'Modules.Stinstagram.Admin'),
                        ),
                ),
            ),
            'submit' => array(
                'title' => $this->getTranslator()->trans('   Save all   ', array(), 'Admin.Theme.Panda')
            ),
        );
        $this->fields_form[0]['form']['input'][] = array(
            'type' => 'go_back_to_list',
            'label' => '',
            'name' => AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->module = $this;
        $helper->table =  $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'save'.$this->name.'setting';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );
        return $helper;
    }

    public function hookDisplayHeader($params)
    {
        $this->context->controller->addCSS($this->_path.'views/css/stinstagram.css');
        $this->context->controller->addJS($this->_path.'views/js/stinstagram.js');
        $this->context->controller->addJqueryPlugin('fancybox');
        if (!$this->isCached($this->templatePath.'header.tpl', $this->getCacheId())) {
            $ins = StInstagramClass::getInstagram();
            foreach ($ins as &$v) {
                if ($v['bg_hover_color'] && Validate::isColor($v['bg_hover_color'])) {
                    $v['bg_hover_color_arr'] = self::hex2rgb($v['bg_hover_color']);
                    $v['bg_opacity_nohover'] = $v['bg_opacity_nohover']<0 || $v['bg_opacity_nohover']>1 ? 0 : $v['bg_opacity_nohover'];
                    $v['bg_opacity'] = $v['bg_opacity']<0 || $v['bg_opacity']>1 ? 0.6 : $v['bg_opacity'];
                } else {
                    $v['bg_hover_color'] = '';
                }

                if ($v['shadow_effect']) {
                    if (!$v['shadow_color'] || !validate::isColor($v['shadow_color'])) {
                        $v['shadow_color'] = '#000000';
                    }
                    $v['shadow_color_arr'] = self::hex2rgb($v['shadow_color']);
                    $v['shadow_opacity'] = $v['shadow_opacity']<0 || $v['shadow_opacity']>1 ? 0.2 : $v['shadow_opacity'];
                }

                if ($v['image_border']) {
                    $v['image_border_color'] = $v['image_border_color'] && Validate::isColor($v['image_border_color']) ? $v['image_border_color'] : '#444444';
                }
                if ($v['bg_img']) {
                    $this->fetchMediaServer($v['bg_img']);
                }
                if ($v['bg_pattern']) {
                    $bg_pattern = _MODULE_DIR_.'stinstagram/patterns/'.$v['bg_pattern'].'.png';
                    $v['bg_pattern'] = $this->context->link->protocol_content.Tools::getMediaServer($bg_pattern).$bg_pattern;
                }
            }
            $this->smarty->assign(array(
                'stins_for_css' => $ins,
            ));
        }
        Media::addJsDef(array(
            'st_ins_getimgurl'=>$this->context->link->getModuleLink($this->name,'list')
        ));
        return $this->fetch($this->templatePath.'header.tpl', $this->getCacheId());
    }

    private function _renderCustomHooks($method, $param = array())
    {
        if ($method) {
            $hash_hook = $this->getHookHash($method);
            return $this->hookDisplayHome($param, $method, $hash_hook, 2);
        }
    }

    private function _prepareHook($identify, $type = 1, $vc=null)
    {

        if(!Db::getInstance()->executeS('select * 
        from information_schema.TABLES 
        where TABLE_SCHEMA=\''._DB_NAME_.'\' and TABLE_NAME like \'%st_instagram_bind\'')){
            return $this->displayError($this->getTranslator()->trans('You have to upgrade it before it works "Instagram"'));
        }
        $id_shop=Context::getContext()->shop->id;
        $inst_acc=Db::getInstance()->getRow('select * from '._DB_PREFIX_.'st_instagram_bind where FIND_IN_SET('.$id_shop.',`shop_ids`)');
        if(!$inst_acc)
            return false;
        $access_token = $inst_acc['utoken'];
        $ins = StInstagramClass::getInstagram($identify, $type);
        $user_name = $inst_acc['username'];
        $user_id = $inst_acc['userid'];
        //dangqian dianpu meiyou keyong token  return
        if($access_token==''){
            return false;
        }
        foreach ($ins as &$in) {
            $in['user_name'] = $user_name;
            $in['user_id'] = $user_id;
            $in['show_profile'] = 0;
            $in['display_pro_col']=0;
            
            $in['lazy'] = 0;
            $in['spacing_between'] = $in['padding'];
            $tmpData=(new StInstagramClass($in['id_st_instagram']))->getListInstagram( $user_id);
            $in['inst'] = [];
            if($in['pro_per_fw']>count($tmpData))
                $in['rewind_nav']=false;
            if(!empty($tmpData)){
                foreach($tmpData as &$val){
                    $val['ago_time']=$this->format_date($val['inst_time']);
                }
                $in['inst']=$tmpData;
            }
        }
        $this->smarty->assign(array(
            'stins'            => $ins,
            'ins_access_token' => $access_token,
            'homeverybottom'   => $type==1 && isset(self::$location[$identify]['full_width']),
            'footer_slider'    => ($type==1 && isset(self::$location[$identify]['footer_slider'])) || (!is_null($vc) && isset($vc['footer_slider']) && $vc['footer_slider']),
            'column_slider'    => ($type==1 && isset(self::$location[$identify]['column_slider'])) || (!is_null($vc) && isset($vc['column_slider']) && $vc['column_slider']),
            'is_quarter'    => ($type==1 && isset(self::$location[$identify]['is_quarter'])) || (!is_null($vc) && isset($vc['is_quarter']) && $vc['is_quarter']),
            'is_stacked_footer'    => $type==1 && isset(self::$location[$identify]['is_stacked_footer']),
            'is_blog'    => $type==1 && isset(self::$location[$identify]['is_blog']),
            'ins_placeholder' => $this->context->link->getMediaLink($this->_path.'views/img/resolution_2.png'),
        ));
        return true;
    }

    public function format_date($time){
		$t=time()-strtotime($time);
		$f=array(
			'31536000'=>[$this->getTranslator()->trans('year ago',array(), 'Shop.Theme.Panda'),$this->getTranslator()->trans('years ago',array(), 'Shop.Theme.Panda')],
			'2592000'=>[$this->getTranslator()->trans('month ago',array(), 'Shop.Theme.Panda'),$this->getTranslator()->trans('months ago',array(), 'Shop.Theme.Panda')],
			'604800'=>[$this->getTranslator()->trans('week ago',array(), 'Shop.Theme.Panda'),$this->getTranslator()->trans('weeks ago',array(), 'Shop.Theme.Panda')],
			'86400'=>[$this->getTranslator()->trans('day ago',array(), 'Shop.Theme.Panda'),$this->getTranslator()->trans('days ago',array(), 'Shop.Theme.Panda')],
			'3600'=>[$this->getTranslator()->trans('hour ago',array(), 'Shop.Theme.Panda'),$this->getTranslator()->trans('hours ago',array(), 'Shop.Theme.Panda')],
			'60'=>[$this->getTranslator()->trans('minute ago',array(), 'Shop.Theme.Panda'),$this->getTranslator()->trans('minutes ago',array(), 'Shop.Theme.Panda')],
			'1'=>[$this->getTranslator()->trans('second ago',array(), 'Shop.Theme.Panda'),$this->getTranslator()->trans('seconds ago',array(), 'Shop.Theme.Panda')],
		);
		foreach ($f as $k=>$v)    {
			if (0 !=$c=floor($t/(int)$k)) {
                if($c==1){
                    return $c.' '.$v[0];
                }else{
                    return $c.' '.$v[1];
                }
			}
		}
	}
    
    public function hookActionOutputHTMLBefore($params)
    {
        $regex = '/<p>\[stinstagram\s+id=\s*[\'\"]?(\d+)[\'\"]?\s*\]<\/p>|\[stinstagram\s+id=\s*[\'\"]?(\d+)[\'\"]?\s*\]/Us';
        if(!preg_match_all($regex, $params['html'], $matches)) {
            return;
        }
        if ($html = preg_replace_callback($regex, array($this, 'displayByInstagramId'), $params['html'])) {
            $params['html'] = $html;
        }
    }
    
    public function displayByInstagramId($identify)
    {
        if (is_array($identify)) {
            $identify = isset($identify[2]) && $identify[2] > 0 ? (int)$identify[2] : $identify[1];
        }
        if (!$identify) {
            return;
        }
        $tpl = 'module:stinstagram/views/templates/hook/stinstagram.tpl';
		if (!$this->isCached($tpl, $this->stGetCacheId($identify.'id')))
            $this->_prepareHook($identify, 3);
		return $this->fetch($tpl, $this->stGetCacheId($identify.'id'));
    }

    public function renderWidget($hookName = null, array $configuration = [])
    {

        //to do don't return false, using cache.
        $identify = $hookName;
        $column_slider = $full_width = $footer_slider = $is_blog = $is_quarter = 0;
        foreach (self::$location as $k=>$v) {
            if('display'.Tools::strtolower($v['hook'])==Tools::strtolower($hookName))
            {
                $identify = $k;
                $full_width = isset($v['full_width']);
                $column_slider = isset($v['column_slider']);
                $is_quarter = isset($v['is_quarter']);
                $footer_slider = isset($v['footer_slider']);
                $is_blog = isset($v['is_blog']);
                break;
            }
        }
        //change identify to id here for vc
        $id_st_instagram = isset($configuration['id_st_instagram']) ? (int)$configuration['id_st_instagram'] : null;

        $hook_hash = $this->getHookHash($hookName).$identify.(string)$id_st_instagram;

        if($full_width && Dispatcher::getInstance()->getController()!='index' && is_null($id_st_instagram))
            return ;
        if($is_blog && (!Module::isInstalled('stblog') || !Module::isEnabled('stblog')) && is_null($id_st_instagram))
            return ;

        if($column_slider || $footer_slider)
        {
            $tpl = 'module:stinstagram/views/templates/hook/stinstagram-footer.tpl';
            if (!$this->isCached($tpl, $this->stGetCacheId($hook_hash)))
            {
                if(is_null($id_st_instagram))
                    $this->_prepareHook($identify, (validate::isInt($identify) ? 1 : 2));
                else
                    $this->_prepareHook($id_st_instagram, 3, array('is_quarter'=>$is_quarter,'column_slider'=>$column_slider,'footer_slider'=>$footer_slider));                    
            }
            return $this->fetch($tpl, $this->stGetCacheId($hook_hash));
        }
        else
        {
            $tpl = 'module:stinstagram/views/templates/hook/stinstagram.tpl';
            if (!$this->isCached($tpl, $this->stGetCacheId($hook_hash)))
            {
                if(is_null($id_st_instagram))
                    $this->_prepareHook($identify, (validate::isInt($identify) ? 1 : 2));
                else
                    $this->_prepareHook($id_st_instagram, 3);        
            }
            return $this->fetch($tpl, $this->stGetCacheId($hook_hash));
        }
    }

    public function getWidgetVariables($hookName = null, array $configuration = [])
    {
        return;
    }

    public function clearCache()
    {
        $this->_clearCache('*');
    }

    protected function stGetCacheId($key, $name = null)
    {
        $cache_id = parent::getCacheId($name);
        return $cache_id.'_'.$key;
    }

    private function getConfigFieldsValues()
    {
        $fields_values = array(
            'client_id'                => Configuration::get($this->_prefix_st.'CLIENT_ID'),

            'compatibility_mode'       => Configuration::get($this->_prefix_st.'COMPATIBILITY_MODE'),
            'load_owl_carousel'        => Configuration::get($this->_prefix_st.'LOAD_OWL_CAROUSEL'),
            'load_timeago'             => Configuration::get($this->_prefix_st.'LOAD_TIMEAGO'),
            'custom_css_code'          => html_entity_decode(Configuration::get($this->_prefix_st.'CUSTOM_CSS_CODE')),
            'custom_js_code'           => html_entity_decode(Configuration::get($this->_prefix_st.'CUSTOM_JS_CODE')),
            'custom_hooks'             => Configuration::get($this->_prefix_st.'CUSTOM_HOOKS'),
            'cache_img_date'             => Configuration::get($this->_prefix_st.'CACHE_IMG_DATE',null,null,null,7*24*60),
        );

        return $fields_values;
    }

    public function getHookHash($func = '')
    {
        if (!$func) {
            return '';
        }
        return Tools::substr(md5($func.$this->name), 0, 10);
    }

    public function getUserInfo($access_token)
    {
        return $this->makeCall('me', ['fields' => 'id,username','access_token'=>$access_token]);
    }

    public function getUser()
    {
        return $this->makeCall('users/self/media/recent');
    }

    public function searchUser($name = '')
    {
        if (!$name) {
            return array('error' => true,'message' => $this->getTranslator()->trans('User name is required.', array(), 'Modules.Stinstagram.Admin'));
        }
        return $this->makeCall('users/search', array('q'=>trim($name)));
    }

    public function getHashTag()
    {
        $result = array();
        $hashtag = Configuration::get($this->_prefix_st . 'HASH_TAG');
        if (!$hashtag) {
            return $result;
        }
        foreach (explode(',', $hashtag) as $tag) {
            $result = array_merge_recursive($result, $this->makeCall('tags/' . $tag . '/media/recent'));
        }
        return $result;
    }

    public function getMedia()
    {
        $media = '';
        return $this->makeCall('media/' . $media);
    }

    public function getLiked()
    {
        return $this->makeCall('users/self/media/liked');
    }

    public function likeMedia($id)
    {
        return $this->makeCall('media/' . $id . '/likes', array(), 'POST');
    }

    public function deleteLikedMedia($id)
    {
        return $this->makeCall('media/' . $id . '/likes', array(), 'DELETE');
    }

    public function checkAccessToken()
    {
        $old_v=Db::getInstance()->getValue('select `version` from '._DB_PREFIX_.'module where name=\'stinstagram\'');
        if(tools::version_compare($old_v,$this->version,'<')){
            return true;
        }
        if(!Db::getInstance()->executeS('select * 
        from information_schema.TABLES 
        where TABLE_SCHEMA=\''._DB_NAME_.'\' and TABLE_NAME like \'%st_instagram_bind\''))
            return true;
        // Refresh access token after 50 days.
        if($data=Db::getInstance()->executeS((new DbQuery())->from('st_instagram_bind'))){
            if(count($data)>0){
                foreach($data as $val){
                    if((time()-$val['tk_time'])>4320000){
                        $new_token=$this->refreshAccessToken($val['utoken']);
                        $updata=[];
                        if(isset($new_token['access_token'])){
                            $updata['utoken']=$new_token['access_token'];
                            $updata['tk_time']=time();
                        }
                        Db::getInstance()->update('st_instagram_bind',$updata,'id_st_instagram_bind='.(int)$val['id_st_instagram_bind']);
                    }
                }
            }
        }
    }

    public function getUserMedia()
    {
        // https://developers.facebook.com/docs/instagram-basic-display-api/reference/user/media#reading
        $user_id = Configuration::get($this->_prefix_st.'USER_ID');
        return $this->makeCall($user_id.'/media');
    }

    public function getMediaDetail($media_id)
    {
        // https://developers.facebook.com/docs/instagram-basic-display-api/reference/media#fields
        return $this->makeCall($media_id, ['fields' => 'caption,id,media_type,media_url,permalink,thumbnail_url,timestamp,username']);
    }

    public function getLongAccessToken($access_token)
    {
        $params = [
            'grant_type' => 'ig_exchange_token',
            'client_secret' => self::$client_secret,
            'access_token'=>$access_token
        ];
        return $this->makeCall('access_token', $params);
    }

    public function refreshAccessToken($access_token)
    {
        $params = [
            'grant_type' => 'ig_refresh_token',
            'access_token' => $access_token,
        ];
        return $this->makeCall('refresh_access_token', $params);
    }

    public function makeCall($function, $params = array(), $method = 'GET')
    {
        $ret = array('error' => true,'message' => '');
        $api_url = 'https://graph.instagram.com/';
        if (!$access_token = $params['access_token']) {
            $ret['message'] = $this->getTranslator()->trans('No access token, please click the red link on the first page of this module to get an access token form Instagram.', array(), 'Modules.Stinstagram.Admin');
            return $ret;
        }

        if (!$function) {
            $ret['message'] = $this->getTranslator()->trans('function error.', array(), 'Modules.Stinstagram.Admin');
            return $ret;
        }

        $params = (array)$params;
        unset($params['access_token']);
        if ($method == 'GET' && (!isset($params['count']) || !$params['count'])) {
            $count = Configuration::get($this->_prefix_st.'COUNT') ? Configuration::get($this->_prefix_st.'COUNT') : '32';
            $params['count'] = $count;
        }

        if (is_array($params) && count($params)) {
            $paramString = '&' . http_build_query($params);
        } else {
            $paramString = null;
        }

        $api_url = $api_url . trim($function, '/').'/?access_token=' . $access_token . (('GET' === $method) ? $paramString : null);

        try {
            $curl_connection = curl_init($api_url);
            curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 60);
            curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);

            if ('POST' == $method) {
                curl_setopt($curl_connection, CURLOPT_POST, count($params));
                curl_setopt($curl_connection, CURLOPT_POSTFIELDS, ltrim($paramString, '&'));
            } elseif ('DELETE' == $method) {
                curl_setopt($curl_connection, CURLOPT_CUSTOMREQUEST, 'DELETE');
            }

            $data = json_decode(curl_exec($curl_connection), true);
            curl_close($curl_connection);
            if (!$data) {
                $ret['message'] = $this->getTranslator()->trans('Can not get data from instagram.com, try reloading this page.', array(), 'Modules.Stinstagram.Admin');
                return $ret;
            }
            return $data;
        } catch (Exception $e) {
            $ret['message'] = $this->getTranslator()->trans('Can not get data from instagram.com, try reloading this page.', array(), 'Modules.Stinstagram.Admin').$e->getMessage();
            return $ret;
        }
    }

    public static function hex2rgb($hex)
    {
        $hex = str_replace("#", "", $hex);

        if (Tools::strlen($hex) == 3) {
            $r = hexdec(Tools::substr($hex, 0, 1).Tools::substr($hex, 0, 1));
            $g = hexdec(Tools::substr($hex, 1, 1).Tools::substr($hex, 1, 1));
            $b = hexdec(Tools::substr($hex, 2, 1).Tools::substr($hex, 2, 1));
        } else {
            $r = hexdec(Tools::substr($hex, 0, 2));
            $g = hexdec(Tools::substr($hex, 2, 2));
            $b = hexdec(Tools::substr($hex, 4, 2));
        }
        $rgb = array($r, $g, $b);
        return $rgb;
    }

    public function getPrefix()
    {
        if (isset($this->_prefix_st) && $this->_prefix_st) {
            return $this->_prefix_st;
        }
        return false;
    }

    private function _checkImageDir()
    {
        $result = true;
        if (!file_exists(_PS_UPLOAD_DIR_.$this->name)) {
            $result = @mkdir(_PS_UPLOAD_DIR_.$this->name, self::$access_rights, true)
                        || @chmod(_PS_UPLOAD_DIR_.$this->name, self::$access_rights);
            if (!$result) {
                $this->_html .= $this->displayError('"'._PS_UPLOAD_DIR_.$this->name.'" '.$this->getTranslator()->trans('An error occurred during new folder creation', array(), 'Modules.Stinstagram.Admin'));
            }
        }

        if (!is_writable(_PS_UPLOAD_DIR_)) {
            $result = false;
            $this->_html .= $this->displayError('"'._PS_UPLOAD_DIR_.$this->name.'" '.$this->getTranslator()->trans('directory isn\'t writable.', array(), 'Modules.Stinstagram.Admin'));
        }

        return $result;
    }

    public function uploadCheckAndGetName($name)
    {
        $type = Tools::strtolower(Tools::substr(strrchr($name, '.'), 1));
        if (!in_array($type, $this->imgtype)) {
            return false;
        }
        $filename = Tools::encrypt($name.sha1(microtime()));
        while (file_exists(_PS_UPLOAD_DIR_.$filename.'.'.$type)) {
            $filename .= rand(10, 99);
        }
        return $filename.'.'.$type;
    }

    public function fetchMediaServer(&$image)
    {
        $image = _THEME_PROD_PIC_DIR_.$image;
        $image = context::getContext()->link->protocol_content.Tools::getMediaServer($image).$image;
    }

    /**
     * Return the list of fields value
     *
     * @param object $obj Object
     * @return array
     */
    public function getFieldsValueSt($obj, $fields_form = "fields_form")
    {
        foreach ($this->$fields_form as $fieldset) {
            if (isset($fieldset['form']['input'])) {
                foreach ($fieldset['form']['input'] as $input) {
                    if (!isset($this->fields_value[$input['name']])) {
                        if (isset($input['type']) && $input['type'] == 'shop') {
                            if ($obj->id) {
                                $result = Shop::getShopById((int)$obj->id, $this->identifier, $this->table);
                                foreach ($result as $row) {
                                    $this->fields_value['shop'][$row['id_'.$input['type']]][] = $row['id_shop'];
                                }
                            }
                        } elseif (isset($input['lang']) && $input['lang']) {
                            foreach (Language::getLanguages(false) as $language) {
                                $fieldValue = $this->getFieldValueSt($obj, $input['name'], $language['id_lang']);
                                if (empty($fieldValue)) {
                                    if (isset($input['default_value']) && is_array($input['default_value']) && isset($input['default_value'][$language['id_lang']])) {
                                        $fieldValue = $input['default_value'][$language['id_lang']];
                                    } elseif (isset($input['default_value'])) {
                                        $fieldValue = $input['default_value'];
                                    }
                                }
                                $this->fields_value[$input['name']][$language['id_lang']] = $fieldValue;
                            }
                        } else {
                            $fieldValue = $this->getFieldValueSt($obj, $input['name']);
                            if ($fieldValue===false && isset($input['default_value'])) {
                                $fieldValue = $input['default_value'];
                            }
                            $this->fields_value[$input['name']] = $fieldValue;
                        }
                    }
                }
            }
        }
        return $this->fields_value;
    }

    /**
     * Return field value if possible (both classical and multilingual fields)
     *
     * Case 1 : Return value if present in $_POST / $_GET
     * Case 2 : Return object value
     *
     * @param object $obj Object
     * @param string $key Field name
     * @param integer $id_lang Language id (optional)
     * @return string
     */
    public function getFieldValueSt($obj, $key, $id_lang = null)
    {
        if ($id_lang) {
            $default_value = ($obj->id && isset($obj->{$key}[$id_lang])) ? $obj->{$key}[$id_lang] : false;
        } else {
            $default_value = isset($obj->{$key}) ? $obj->{$key} : false;
        }

        return Tools::getValue($key.($id_lang ? '_'.$id_lang : ''), $default_value);
    }

    protected function stUploadImage($item)
    {
        $result = array(
            'error' => array(),
            'image' => '',
        );
        if (isset($_FILES[$item]) && isset($_FILES[$item]['tmp_name']) && !empty($_FILES[$item]['tmp_name'])) {
            $type = Tools::strtolower(Tools::substr(strrchr($_FILES[$item]['name'], '.'), 1));
            $name = str_replace(strrchr($_FILES[$item]['name'], '.'), '', $_FILES[$item]['name']);
            $imagesize = array();
            $imagesize = @getimagesize($_FILES[$item]['tmp_name']);
            if (!empty($imagesize) &&
                in_array(Tools::strtolower(Tools::substr(strrchr($imagesize['mime'], '/'), 1)), array('jpg', 'gif', 'jpeg', 'png')) &&
                in_array($type, array('jpg', 'gif', 'jpeg', 'png'))) {
                $temp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS');
                $salt = $name ? Tools::str2url($name) : sha1(microtime());
                $c_name = $salt;
                if ($upload_error = ImageManager::validateUpload($_FILES[$item])) {
                    $result['error'][] = $upload_error;
                } elseif (!$this->_checkImageDir() || !$temp_name || !move_uploaded_file($_FILES[$item]['tmp_name'], $temp_name)) {
                    $result['error'][] = $this->getTranslator()->trans('An error occurred during the image upload.', array(), 'Modules.Stinstagram.Admin');
                } else {
                    if (!imagEmanager::resize($temp_name, _PS_UPLOAD_DIR_.$this->name.'/'.$c_name.'.'.$type, null, null, $type)) {
                        $result['error'][] = $this->getTranslator()->trans('An error occurred during the image upload.', array(), 'Modules.Stinstagram.Admin');
                    }
                }
                if (isset($temp_name)) {
                    @unlink($temp_name);
                }

                if (!count($result['error'])) {
                    $result['image'] = $this->name.'/'.$c_name.'.'.$type;
                    $result['width'] = $imagesize[0];
                    $result['height'] = $imagesize[1];
                }
                return $result;
            }
        } else {
            return $result;
        }
    }

    public static function getApplyTo()
    {
        $result = array();
        $module = new StInstagram();
        $location = array();
        foreach (self::$location as $v) {
            $location[] = array('id'=>'1-'.$v['id'],'name'=>$v['name']);
        }
        $result[] = array('name'=>$module->getTranslator()->trans('Hook', array(), 'Admin.Theme.Panda'),'query'=>$location);

        if ($custom_hooks = Configuration::get($module->_prefix_st.'CUSTOM_HOOKS')) {
            $hook_arr = array();
            foreach (explode(',', $custom_hooks) as $hook) {
                $hook = trim($hook);
                $hook_arr[] = array('id'=>'2-'.Tools::strtolower($hook), 'name'=>$hook);
            }
            $result[] = array('name'=>$module->getTranslator()->trans('Custom hook', array(), 'Admin.Theme.Panda'),'query'=>$hook_arr);
        }

        return $result;
    }

    public static function showApplyTo($value, $row)
    {
        $result = '';
        if ($value) {
            $result = isset(self::$location[$value]) ? self::$location[$value]['name'] : '';
        } elseif ($row['custom_hook']) {
            $result = $row['custom_hook'];
        } else {
            $context = Context::getContext();
            $result = Module::isInstalled('jscomposer') && Module::isEnabled('jscomposer') ? $context->getTranslator()->trans('For visual composer module', array(), 'Admin.Theme.Panda') : $context->getTranslator()->trans('--', array(), 'Admin.Theme.Panda');
        }
        return $result;
    }
    
    public static function showShortCode($value,$row)
    {
        return '<label data-html="true" data-toggle="tooltip" class="label-tooltip" data-original-title="'.
            Context::getContext()->getTranSlator()->trans('You can copy the short code to anywhere to show the banner if need.', array(), 'Admin.Theme.Panda').
            '">&lt;div&gt;[stinstagram id="'.(int)$row['id_st_instagram'].'"]&lt;/div&gt;</label>';
    }

    public function prepareHooks()
    {
        //to do can not work
        $location = array();
        $rows = Db::getInstance()->executeS('SELECT location FROM `'._DB_PREFIX_.'st_instagram` i
            LEFT JOIN `'._DB_PREFIX_.'st_instagram_shop` iss
            ON i.`id_st_instagram` = iss.`id_st_instagram`
            WHERE iss.`id_shop`='.(int)$this->context->shop->id);
        foreach ($rows as $value) {
            if (key_exists($value['location'], self::$location) && isset(self::$location[$value['location']]['hook'])) {
                $location[$value['location']] = self::$location[$value['location']]['hook'];
            }
        }
        foreach (self::$location as $local) {
            if (!isset($local['hook'])) {
                continue;
            }
            $hook = 'display'.Tools::ucfirst($local['hook']);
            $id_hook = Hook::getIdByName($hook);
            if (count($location) && in_array($local['hook'], $location)) {
                if ($id_hook && Hook::getModulesFromHook($id_hook, $this->id)) {
                    continue;
                }
                if (!$this->isHookableOn($hook)) {
                    $this->validation_errors[] = $this->getTranslator()->trans('This module cannot be transplanted to ', array(), 'Admin.Theme.Panda').$hook;
                } else {
                    $this->registerHook($hook, Shop::getContextListShopID());
                }
            } else {
                if ($id_hook && Hook::getModulesFromHook($id_hook, $this->id)) {
                    $this->unregisterHook($id_hook, Shop::getContextListShopID());
                    $this->unregisterExceptions($id_hook, Shop::getContextListShopID());
                }
            }
        }
        Cache::clean('hook_module_list');
        return true;
    }
    public function hookvcBeforeInit()
    {
        JsComposer::add_shortcode($this->name, array($this,'vc_shortcode_init'));
        if(isset($this->context->controller->admin_webpath) && !empty($this->context->controller->admin_webpath)) {
            $this->vc_map_init();
        }  
    }
    public function vc_shortcode_init($atts, $content = null)
    {
        extract(JsComposer::shortcode_atts(array(
            'hook_name' => '',
            'st_time' => '',
            'id_st_instagram' => 0,
            ), $atts));
        if(!isset($this->vc_hooks[$hook_name]))
            return ;
        $hook = $this->vc_hooks[$hook_name];
        return $this->renderWidget($hook, array('id_st_instagram'=>$id_st_instagram,'st_time'=>$st_time));
    }
    function vc_map_init()
    {
        $content = array();
        $default = 0;
        $data = StInstagramClass::getInstagram(0, 1);
        foreach($data AS $value) {
            $content[$this->getTranslator()->trans('ID:', array(), 'Admin.Theme.Panda').$value['id_st_instagram'].' '.$value['user_name'].' '.$value['hash_tag']] = $value['id_st_instagram'];
            !$default && $default = $value['id_st_instagram'];
        }
        $vc_hooks = array();
        foreach ($this->vc_hooks as $key => $value) {
            $vc_hooks[$key] = $key;
        }
        $params = array(
            'name' => $this->displayName,
            'base' => $this->name,
            'icon' => 'icon-panda',
            'category' => 'Panda',
            'params' => array(
                array(
                    'type' => 'dropdown',
                    'holder' => 'div',
                    'class' => 'hide_in_vc_editor',
                    'heading' => $this->getTranslator()->trans('Please select', array(), 'Admin.Theme.Panda'),
                    'param_name' => 'id_st_instagram',
                    'value' => $content,
                    'std' => $default
                ),
                array(
                    'type' => 'dropdown',
                    'holder' => 'div',
                    'class' => 'hide_in_vc_editor',
                    'heading' => $this->getTranslator()->trans('How to display', array(), 'Admin.Theme.Panda'),
                    'param_name' => 'hook_name',
                    'value' => $vc_hooks,
                    'std' => 'Block'
                ),
                array(
                    'type' => 'textfield',
                    'holder' => 'div',
                    'param_name' => 'st_time',
                    'value' => time(),
                ),
                array(
                    'type' => 'html',
                    'param_name' => 'st_conf_link_box',
                    'code' => '<a href="'.$this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&module_name='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'" target="_blank" class="st_conf_link">'.$this->getTranslator()->trans('Go to the module to change settings.', array(), 'Admin.Theme.Panda').'</a>',
                ),
                array(
                    'type' => 'html',
                    'param_name' => 'st_refeash_link_box',
                    'code' => '<a href="javascript:;" class="st_refeash_link">'.$this->getTranslator()->trans('Refresh this window to get new data.', array(), 'Admin.Theme.Panda').'</a>',
                ),
            )
        );
        vc_map($params);
    }
}
