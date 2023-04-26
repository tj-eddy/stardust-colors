<?php
/**
 * Creative Elements - live Theme & Page Builder
 *
 * @author    WebshopWorks
 * @copyright 2019-2023 WebshopWorks.com
 * @license   One domain support license
 */

namespace CE;

defined('_PS_VERSION_') or exit;

/**
 * Language Selector widget
 *
 * @since 2.5.0
 */
class WidgetLanguageSelector extends WidgetBase
{
    use NavTrait;

    /**
     * Get widget name.
     *
     * @since 2.5.0
     *
     * @return string Widget name
     */
    public function getName()
    {
        return 'language-selector';
    }

    /**
     * Get widget title.
     *
     * @since 2.5.0
     *
     * @return string Widget title
     */
    public function getTitle()
    {
        return __('Language Selector');
    }

    /**
     * Get widget icon.
     *
     * @since 2.5.0
     *
     * @return string Widget icon
     */
    public function getIcon()
    {
        return 'eicon-langs';
    }

    /**
     * Get widget categories.
     *
     * @since 2.5.0
     *
     * @return array Widget categories
     */
    public function getCategories()
    {
        return ['theme-elements'];
    }

    /**
     * Get widget keywords.
     *
     * @since 2.5.0
     *
     * @return array Widget keywords
     */
    public function getKeywords()
    {
        return ['language', 'selector', 'chooser'];
    }

    /**
     * Register language selector widget controls.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @since 2.5.0
     */
    protected function _registerControls()
    {
        $this->startControlsSection(
            'section_selector',
            [
                'label' => $this->getTitle(),
            ]
        );

        $this->addControl(
            'skin',
            [
                'label' => __('Skin'),
                'type' => ControlsManager::SELECT,
                'default' => 'dropdown',
                'options' => [
                    'classic' => __('Classic'),
                    'dropdown' => __('Dropdown'),
                ],
                'separator' => 'after',
            ]
        );

        $this->addControl(
            'content',
            [
                'label' => __('Content'),
                'label_block' => true,
                'type' => ControlsManager::SELECT2,
                'default' => ['name'],
                'options' => [
                    'flag' => __('Country Flag'),
                    'code' => __('ISO Code'),
                    'name' => __('Language'),
                ],
                'multiple' => true,
            ]
        );

        $this->addControl(
            'show_current',
            [
                'label' => __('Current Language'),
                'type' => ControlsManager::SWITCHER,
                'label_on' => __('Show'),
                'label_off' => __('Hide'),
                'prefix_class' => 'elementor-nav--',
                'return_value' => 'active',
            ]
        );

        $this->registerNavContentControls([
            'layout_options' => [
                'horizontal' => __('Horizontal'),
                'vertical' => __('Vertical'),
            ],
            'submenu_condition' => [
                'skin' => 'dropdown',
            ],
        ]);

        $this->endControlsSection();

        $this->registerNavStyleSection([
            'active_condition' => [
                'show_current!' => '',
            ],
            'space_between_condition' => [
                'skin' => 'classic',
            ],
        ]);

        $this->registerDropdownStyleSection([
            'dropdown_condition' => [
                'skin' => 'dropdown',
            ],
            'active_condition' => [
                'show_current!' => '',
            ],
        ]);
    }

    protected function getHtmlWrapperClass()
    {
        return parent::getHtmlWrapperClass() . ' elementor-widget-nav-menu';
    }

    /**
     * Render language selector widget output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @since 2.5.0
     */
    protected function render()
    {
        $settings = $this->getSettingsForDisplay();
        $context = \Context::getContext();
        $languages = \Language::getLanguages(true, $context->shop->id);

        if (count($languages) <= 1 || !$settings['content']) {
            return;
        }
        $this->lang_flag = in_array('flag', $settings['content']);
        $this->lang_code = in_array('code', $settings['content']);
        $this->lang_name = in_array('name', $settings['content']);
        $this->indicator = isset($settings['indicator']) && !isset($settings['__fa4_migrated']['submenu_icon'])
            ? $settings['indicator']
            : $settings['submenu_icon']['value'];
        $id_lang = $context->language->id;
        $menu = [
            '0' => [
                'id_lang' => $id_lang,
                'name' => $context->language->name,
                'iso_code' => $context->language->iso_code,
                'url' => 'javascript:;',
                'current' => false,
                'children' => [],
            ],
        ];
        foreach ($languages as &$lang) {
            $lang['current'] = $id_lang == $lang['id_lang'];
            $lang['url'] = $context->link->getLanguageLink($lang['id_lang']);

            if ($context->controller instanceof \IndexController) {
                // Remove rewritten URL from home page
                $lang['url'] = preg_replace('~[^/]+$~', '', $lang['url']);
            }
            $menu[0]['children'][] = $lang;
        }
        if ('classic' === $settings['skin']) {
            $menu = &$menu[0]['children'];
        }
        $ul_class = 'elementor-nav';

        if ('vertical' === $settings['layout']) {
            $ul_class .= ' sm-vertical';
        }

        // General Menu.
        ob_start();
        $this->selectorList($menu, 0, $ul_class);
        $menu_html = ob_get_clean();

        $this->addRenderAttribute('main-menu', 'class', [
            'elementor-langs',
            'elementor-nav--main',
            'elementor-nav__container',
            'elementor-nav--layout-' . $settings['layout'],
        ]);

        if ('none' !== $settings['pointer']) {
            $animation_type = self::getPointerAnimationType($settings['pointer']);

            $this->addRenderAttribute('main-menu', 'class', [
                'e--pointer-' . $settings['pointer'],
                'e--animation-' . $settings[$animation_type],
            ]);
        } ?>
        <nav <?php $this->printRenderAttributeString('main-menu'); ?>><?php echo $menu_html; ?></nav>
        <?php
    }

    protected function selectorList(array &$nodes, $depth = 0, $ul_class = '')
    {
        ?>
        <ul <?php echo $depth ? 'class="sub-menu elementor-nav--dropdown"' : 'id="selector-' . $this->getId() . '" class="' . $ul_class . '"'; ?>>
        <?php foreach ($nodes as &$node) { ?>
            <li class="<?php echo sprintf(self::$li_class, 'lang', "lang-{$node['id_lang']}", $node['current'] ? ' current-menu-item' : '', !empty($node['children']) ? ' menu-item-has-children' : ''); ?>">
                <a class="<?php echo($depth ? 'elementor-sub-item' : 'elementor-item') . ($node['current'] ? ' elementor-item-active' : ''); ?>" href="<?php echo esc_attr($node['url']); ?>">
                <?php if ($this->lang_flag) { ?>
                    <img class="elementor-langs__flag" src="<?php echo esc_attr(Helper::getMediaLink("img/l/{$node['id_lang']}.jpg")); ?>" alt="<?php echo $node['iso_code']; ?>" width="16" height="11">
                <?php } ?>
                <?php if ($this->lang_code) { ?>
                    <span class="elementor-langs__code"><?php echo $node['iso_code']; ?></span>
                <?php } ?>
                <?php if ($this->lang_name) { ?>
                    <span class="elementor-langs__name"><?php echo explode(' (', $node['name'])[0]; ?></span>
                <?php } ?>
                <?php if ($this->indicator && !empty($node['children'])) { ?>
                    <span class="sub-arrow <?php echo esc_attr($this->indicator); ?>"></span>
                <?php } ?>
                </a>
                <?php empty($node['children']) or $this->selectorList($node['children'], $depth + 1); ?>
            </li>
        <?php } ?>
        </ul>
        <?php
    }
}
