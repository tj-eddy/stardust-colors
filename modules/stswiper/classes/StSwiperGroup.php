<?php
/*
* 2007-2017 PrestaShop
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
*  @author    ST-themes <hellolee@gmail.com>
*  @copyright 2007-2017 ST-themes
*  @license   Use, by you or one client for one Prestashop instance.
*/

class StSwiperGroup extends ObjectModel
{
	/** @var integer*/
	public $id;
	/** @var string*/
	public $name;
	/** @var integer */
	public $location;
    /** @var integer */
	public $templates;
    /** @var integer */
    public $items_huge;
    /** @var integer */
    public $items_xxlg;
    /** @var integer */
    public $items_xlg;
    /** @var integer */
	public $items_lg;
    /** @var integer */
	public $items_md;
    /** @var integer */
	public $items_sm;
    /** @var integer */
	public $items_xs;
    /** @var integer */
	public $items_xxs;
	/** @var integer */
	public $id_category;
    public $id_manufacturer;
    /** @var integer */
    /** @var integer */
	public $id_cms;
    /** @var integer */
	public $id_cms_category;
	/** @var integer */
	public $trans_period;
	/** @var boolen */
	public $auto_advance;
	/** @var integer */
	public $time;
    /** @var boolen */
    public $auto_height;
	/** @var boolen */
	public $pause;
	/** @var boolen */
	public $pag_nav;
    /** @var string */
    public $pag_nav_bg;
    /** @var string */
    public $pag_nav_bg_active;
	/** @var boolen */
	public $prev_next;
    /** @var string */
    public $prev_next_color;
    /** @var string */
    public $prev_next_hover;
    /** @var string */
    public $prev_next_bg;
    /** @var string */
    public $prev_next_bg_hover;
	/** @var boolen */
	public $hide_on_mobile;
	/** @var string */
	public $progress_bar;
    /** @var string */
    public $prog_bar_color;
    /** @var string */
    public $prog_bar_bg;
    /** @var integer */
    public $active;
    /** @var integer */
    public $transition_style;
	/** @var integer */
	public $rewind_nav;
	/** @var integer */
	public $position;
	/** @var boolen */
	public $mouse_drag;
    /** @var string */
    public $top_spacing; 
    /** @var string */
    public $bottom_spacing;
    /** @var integer */
    public $show_on_sub;  
    /** @var string */
    public $spacing_between;
    /** @var string */
    public $height;
    /** @var integer */
    public $direction;  
    /** @var integer */
    public $lazy_load;  
    /** @var integer */
    public $move;   
    /** @var integer */
    public $slides_per_view;
    /** @var integer */
    public $centered_slides;
    /** @var integer */
    public $display_on;
    /** @var integer */
    public $full_screen;
    /** @var integer */
    public $two_slider_width;
    /** @var string */
    public $two_spacing;
      
	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = array(
		'table'     => 'st_swiper_group',
		'primary'   => 'id_st_swiper_group',
		'fields'    => array(
            'name'             => array('type' => self::TYPE_STRING, 'size' => 255, 'validate' => 'isGenericName', 'required' => true),
            'location'         => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'templates'        => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'items_huge'       => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'items_xxlg'       => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'items_xlg'        => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'items_lg'         => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'items_md'         => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'items_sm'         => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'items_xs'         => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'items_xxs'        => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'id_category'      => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'id_manufacturer'  => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'id_cms'           => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'id_cms_category'  => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'trans_period'     => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
            'auto_advance'     => array('type' => self::TYPE_BOOL, 'validate' => 'isUnsignedInt'),
            'auto_height'      => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'time'             => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true),
            'pause'            => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'pag_nav'          => array('type' => self::TYPE_BOOL, 'validate' => 'isUnsignedInt'),
            'pag_nav_bg'       => array('type' => self::TYPE_STRING, 'size' => 7),
            'pag_nav_bg_active'=> array('type' => self::TYPE_STRING, 'size' => 7),
            'prev_next'        => array('type' => self::TYPE_BOOL, 'validate' => 'isUnsignedInt'),
            'prev_next_color'  => array('type' => self::TYPE_STRING, 'size' => 7),
            'prev_next_hover'  => array('type' => self::TYPE_STRING, 'size' => 7),
            'prev_next_bg'     => array('type' => self::TYPE_STRING, 'size' => 7),
            'prev_next_bg_hover'=> array('type' => self::TYPE_STRING, 'size' => 7),
            'hide_on_mobile'   => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'progress_bar'     => array('type' => self::TYPE_STRING),
            'prog_bar_color'   => array('type' => self::TYPE_STRING, 'size' => 7),
            'prog_bar_bg'      => array('type' => self::TYPE_STRING, 'size' => 7),
            'mouse_drag'       => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'active'           => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'transition_style' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'rewind_nav'       => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'position'         => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'top_spacing'      => array('type' => self::TYPE_STRING, 'size' => 10),
            'bottom_spacing'   => array('type' => self::TYPE_STRING, 'size' => 10),
            'show_on_sub'      => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'spacing_between'  => array('type' => self::TYPE_STRING, 'size' => 10),
            'height'           => array('type' => self::TYPE_STRING, 'size' => 10),
            'direction'        => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'slides_per_view'  => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'centered_slides'  => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'move'             => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'lazy_load'        => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'display_on'       => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'full_screen'       => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'two_slider_width'       => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'two_spacing'       => array('type' => self::TYPE_STRING, 'size' => 10),
        ),
	);
    
    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        Shop::addTableAssociation(self::$definition['table'], array('type' => 'shop'));
        parent::__construct($id, $id_lang, $id_shop);
    }
    
	public static function getAll()
	{
        Shop::addTableAssociation('st_swiper_group', array('type' => 'shop'));
		return  Db::getInstance()->executeS('
			SELECT smsg.*
			FROM `'._DB_PREFIX_.'st_swiper_group` smsg
			'.Shop::addSqlAssociation('st_swiper_group', 'smsg')
            );
	}

	public function copyFromPost()
	{
		/* Classical fields */
		foreach ($_POST AS $key => $value)
			if (property_exists($this, $key) && $key != 'id_'.$this->table && !isset($_FILES[$key]))
				$this->{$key} = $value;
	}
    public static function categoryHasSlide($identify, $show_on_sub = 1)
    {
        return Db::getInstance()->getValue('
			SELECT count(0)
			FROM `'._DB_PREFIX_.'st_swiper_group` smsg
			'.Shop::addSqlAssociation('st_swiper_group', 'smsg').'
			WHERE smsg.`active`=1 '.($show_on_sub ? ' and smsg.`show_on_sub`=1' : '').( is_array($identify) ? ' and smsg.`id_category` IN ('.implode(',',$identify).') ' : ' and smsg.`id_category` = '.(int)$identify )
        );
    }
    public static function getSlideGroup($identify,$type=1)
    {
        $identify = (array)$identify;
        Shop::addTableAssociation('st_swiper_group', array('type' => 'shop'));
        $where = '';
        if($type==1)
            $where .= ' AND smsg.`location` IN ('.implode(',',$identify).')';
        elseif($type==2)
        {
            if(self::categoryHasSlide($identify, 0))
                $where .= ' AND smsg.`id_category` IN ('.implode(',',$identify).')';
            else
            {
                $id_has = 0; 
                foreach($identify as $id_category)
                {
                    if($id_has)
                        break;
                    $category = new Category($id_category);
                    $parents = $category->getParentsCategories();
                    
                    foreach($parents as $parent)
                    {
                        if(self::categoryHasSlide($parent['id_category'], 1))
                        {
                            $id_has = $parent['id_category'];
                            break;
                        }
                    }
                }
                if($id_has)
                    $where .= ' AND smsg.`id_category` = '.$id_has;
            }
            if (!$where) {
                return false;
            }
        }
        elseif($type==3)
            $where .= ' AND smsg.`id_st_swiper_group` IN ('.implode(',',$identify).')';
        elseif($type==4)
            $where .= ' AND smsg.`id_cms` IN ('.implode(',',$identify).')';
        elseif($type==5)
            $where .= ' AND smsg.`id_cms_category` IN ('.implode(',',$identify).')';
        elseif($type==6)
            $where .= ' AND smsg.`id_manufacturer` IN ('.implode(',',$identify).')';
        elseif ($type == 100) {
            $where .= ' AND smsg.`location`=0  AND smsg.`id_manufacturer`=0  AND smsg.`id_cms`=0  AND smsg.`id_cms_category`=0  AND smsg.`id_category`=0 ';
        }
            
        if(!$where && $type)
            return false;
        return Db::getInstance()->executeS('
			SELECT smsg.*
			FROM `'._DB_PREFIX_.'st_swiper_group` smsg
			'.Shop::addSqlAssociation('st_swiper_group', 'smsg').'
			WHERE smsg.`active`=1 '.$where.'
            ORDER BY smsg.`position`');
    }
    
    public function clearShopIds()
    {
        if ($this->id) {
            return Db::getInstance()->delete($this->table.'_shop', '`'.bqSQL($this->identifier).'` = '.(int)$this->id);
        }
    }
    
    public function getShopIds()
    {
        $result =array();
        if ($this->id) {
            $result = Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.$this->table.'_shop WHERE `'.bqSQL($this->identifier).'` = '.(int)$this->id);
        }
        return $result;
    }
    
    public function restoreShopIds($data = array())
    {
        if ($data && count($data) > 0 && $this->id) {
            Db::getInstance()->insert($this->table.'_shop', $data);
        }
    }

    public static function getOptions()
    {
        return Db::getInstance()->executeS('
            SELECT * 
            FROM `'._DB_PREFIX_.'st_swiper_group` 
            WHERE `active` = 1 
        ');
    }
}