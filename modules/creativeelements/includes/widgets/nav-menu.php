<?php
/**
 * Creative Elements - live Theme & Page Builder
 *
 * @author    WebshopWorks, Elementor
 * @copyright 2019-2023 WebshopWorks.com & Elementor.com
 * @license   https://www.gnu.org/licenses/gpl-3.0.html
 */

namespace CE;

defined('_PS_VERSION_') or exit;

use CE\CoreXResponsiveXResponsive as Responsive;

/**
 * Nav Menu widget
 *
 * @since 2.5.0
 */
class WidgetNavMenu extends WidgetCategoryBase
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
        return 'nav-menu';
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
        return __('Nav Menu');
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
        return 'eicon-nav-menu';
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
        return ['menu', 'nav', 'button'];
    }

    public function onExport($element)
    {
        unset($element['settings']['menu'], $element['settings']['linklist_hook']);

        return $element;
    }

    private function getAvailableHooks()
    {
        $hooks = [];
        $db = \Db::getInstance();
        $ps = _DB_PREFIX_;
        $rows = $db->executeS("
            SELECT h.name FROM {$ps}link_block AS lb
            INNER JOIN {$ps}hook AS h ON h.id_hook = lb.id_hook
            ORDER BY h.name
        ");
        if ($rows) {
            foreach ($rows as &$row) {
                $hooks[$row['name']] = $row['name'];
            }
        }

        return $hooks;
    }

    /**
     * Register nav menu widget controls.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @since 2.5.0
     */
    protected function _registerControls()
    {
        $is_admin = is_admin();

        $this->startControlsSection(
            'section_layout',
            [
                'label' => __('Layout'),
            ]
        );

        $this->addControl(
            'menu',
            [
                'label' => __('Menu'),
                'type' => ControlsManager::SELECT,
                'options' => [
                    'mainmenu' => __('Main Menu'),
                    'categorytree' => __('Category Tree'),
                    'linklist' => __('Link List'),
                ],
                'default' => 'mainmenu',
                'save_default' => true,
            ]
        );

        if ($is_admin && \Module::getInstanceByName('ps_mainmenu')) {
            $this->addControl(
                'mainmenu_description',
                [
                    'raw' => sprintf(
                        __("Go to the <a href='%s' target='_blank'>%s module</a> to manage your menu items."),
                        $this->context->link->getAdminLink('AdminModules') . '&configure=ps_mainmenu',
                        __('Main Menu')
                    ),
                    'type' => ControlsManager::RAW_HTML,
                    'content_classes' => 'elementor-descriptor',
                    'condition' => [
                        'menu' => 'mainmenu',
                    ],
                ]
            );
        } else {
            $this->addControl(
                'mainmenu_description',
                [
                    'raw' => sprintf(__('%s module (%s) must be installed!'), __('Main Menu'), 'ps_mainmenu'),
                    'type' => ControlsManager::RAW_HTML,
                    'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning',
                    'condition' => [
                        'menu' => 'mainmenu',
                    ],
                ]
            );
        }

        $ps_linklist = \Module::getInstanceByName('ps_linklist');

        $this->addControl(
            'linklist_hook',
            [
                'label' => __('Hook'),
                'type' => ControlsManager::SELECT,
                'default' => 'displayFooter',
                'options' => $is_admin && $ps_linklist ? $this->getAvailableHooks() : [],
                'condition' => [
                    'menu' => 'linklist',
                ],
            ]
        );

        if ($is_admin && $ps_linklist) {
            $this->addControl(
                'linklist_description',
                [
                    'raw' => sprintf(
                        __('Go to the <a href="%s" target="_blank">%s module</a> to manage your menu items.'),
                        $this->context->link->getAdminLink('AdminModules') . '&configure=ps_linklist',
                        __('Link List')
                    ),
                    'type' => ControlsManager::RAW_HTML,
                    'content_classes' => 'elementor-descriptor',
                    'condition' => [
                        'menu' => 'linklist',
                    ],
                ]
            );
        } else {
            $this->addControl(
                'linklist_description',
                [
                    'raw' => sprintf(__('%s module (%s) must be installed!'), __('Link List'), 'ps_linklist'),
                    'type' => ControlsManager::RAW_HTML,
                    'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning',
                    'condition' => [
                        'menu' => 'linklist',
                    ],
                ]
            );
        }

        $this->registerNavContentControls([
            'layout_options' => [
                'horizontal' => __('Horizontal'),
                'vertical' => __('Vertical'),
                'dropdown' => __('Dropdown'),
            ],
        ]);

        $this->addControl(
            'heading_mobile_dropdown',
            [
                'label' => __('Mobile Dropdown'),
                'type' => ControlsManager::HEADING,
                'separator' => 'before',
                'condition' => [
                    'layout!' => 'dropdown',
                ],
            ]
        );

        $breakpoints = Responsive::getBreakpoints();

        $this->addControl(
            'dropdown',
            [
                'label' => __('Breakpoint'),
                'type' => ControlsManager::SELECT,
                'default' => 'tablet',
                'options' => [
                    /* translators: %d: Breakpoint number. */
                    'mobile' => sprintf(__('Mobile (< %dpx)'), $breakpoints['md']),
                    /* translators: %d: Breakpoint number. */
                    'tablet' => sprintf(__('Tablet (< %dpx)'), $breakpoints['lg']),
                ],
                'prefix_class' => 'elementor-nav--dropdown-',
                'condition' => [
                    'layout!' => 'dropdown',
                ],
            ]
        );

        $this->addControl(
            'full_width',
            [
                'label' => __('Full Width'),
                'type' => ControlsManager::SWITCHER,
                'description' => __('Stretch the dropdown of the menu to full width.'),
                'prefix_class' => 'elementor-nav--',
                'return_value' => 'stretch',
                'frontend_available' => true,
            ]
        );

        $this->addControl(
            'text_align',
            [
                'label' => __('Align'),
                'type' => ControlsManager::SELECT,
                'default' => 'aside',
                'options' => [
                    'aside' => __('Aside'),
                    'center' => __('Center'),
                ],
                'prefix_class' => 'elementor-nav--text-align-',
            ]
        );

        $this->addControl(
            'animation_dropdown',
            [
                'label' => __('Animation'),
                'type' => ControlsManager::SELECT,
                'default' => 'toggle',
                'options' => [
                    'toggle' => __('Toggle'),
                    'accordion' => __('Accordion'),
                ],
                'frontend_available' => true,
            ]
        );

        $this->addControl(
            'toggle',
            [
                'label' => __('Toggle Button'),
                'type' => ControlsManager::SELECT,
                'default' => 'burger',
                'options' => [
                    '' => __('None'),
                    'burger' => __('Hamburger'),
                ],
                'prefix_class' => 'elementor-nav--toggle elementor-nav--',
                'render_type' => 'template',
                'frontend_available' => true,
            ]
        );

        $this->addControl(
            'toggle_align',
            [
                'label' => __('Toggle Align'),
                'type' => ControlsManager::CHOOSE,
                'default' => 'left',
                'options' => [
                    'left' => [
                        'title' => __('Left'),
                        'icon' => 'eicon-h-align-left',
                    ],
                    'center' => [
                        'title' => __('Center'),
                        'icon' => 'eicon-h-align-center',
                    ],
                    'right' => [
                        'title' => __('Right'),
                        'icon' => 'eicon-h-align-right',
                    ],
                ],
                'selectors_dictionary' => [
                    'left' => 'margin-right: auto',
                    'center' => 'margin: 0 auto',
                    'right' => 'margin-left: auto',
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-menu-toggle' => '{{VALUE}}',
                ],
                'condition' => [
                    'toggle!' => '',
                ],
                'label_block' => false,
            ]
        );

        $this->endControlsSection();

        $this->registerCategoryTreeSection([
            'condition' => [
                'menu' => 'categorytree',
            ],
        ]);

        $this->registerNavStyleSection([
            'devices' => ['desktop', 'tablet'],
            'scheme' => true,
            'condition' => [
                'layout!' => 'dropdown',
            ],
        ]);

        $this->registerDropdownStyleSection([
            'scheme' => true,
            'show_description' => true,
        ]);

        $this->startControlsSection(
            'style_toggle',
            [
                'label' => __('Toggle Button'),
                'tab' => ControlsManager::TAB_STYLE,
                'condition' => [
                    'toggle!' => '',
                ],
            ]
        );

        $this->startControlsTabs('tabs_toggle_style');

        $this->startControlsTab(
            'tab_toggle_style_normal',
            [
                'label' => __('Normal'),
            ]
        );

        $this->addControl(
            'toggle_color',
            [
                'label' => __('Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} div.elementor-menu-toggle' => 'color: {{VALUE}}', // Harder selector to override text color control
                ],
            ]
        );

        $this->addControl(
            'toggle_background_color',
            [
                'label' => __('Background Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-menu-toggle' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->endControlsTab();

        $this->startControlsTab(
            'tab_toggle_style_hover',
            [
                'label' => __('Hover'),
            ]
        );

        $this->addControl(
            'toggle_color_hover',
            [
                'label' => __('Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} div.elementor-menu-toggle:hover' => 'color: {{VALUE}}', // Harder selector to override text color control
                ],
            ]
        );

        $this->addControl(
            'toggle_background_color_hover',
            [
                'label' => __('Background Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-menu-toggle:hover' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->endControlsTab();

        $this->endControlsTabs();

        $this->addControl(
            'toggle_size',
            [
                'label' => __('Size'),
                'type' => ControlsManager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 15,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-menu-toggle' => 'font-size: {{SIZE}}{{UNIT}}',
                ],
                'separator' => 'before',
            ]
        );

        $this->addControl(
            'toggle_border_width',
            [
                'label' => __('Border Width'),
                'type' => ControlsManager::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 10,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-menu-toggle' => 'border-width: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->addControl(
            'toggle_border_radius',
            [
                'label' => __('Border Radius'),
                'type' => ControlsManager::SLIDER,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .elementor-menu-toggle' => 'border-radius: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->endControlsSection();
    }

    /**
     * Render nav menu widget output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @since 2.5.0
     */
    protected function render()
    {
        $settings = $this->getSettingsForDisplay();
        $children = 'children';
        $this->indicator = isset($settings['indicator']) && !isset($settings['__fa4_migrated']['submenu_icon'])
            ? $settings['indicator']
            : $settings['submenu_icon']['value'];

        if ('categorytree' === $settings['menu']) {
            $render_method = 'psCategoryTree';

            $menu = $this->getCategoryTree(
                $this->getRootCategory($settings['root_category']),
                $settings
            );
        } elseif ('mainmenu' === $settings['menu']) {
            $render_method = 'psMainMenu';

            if (!$module = \Module::getInstanceByName('ps_mainmenu')) {
                return;
            }
            $menu = $module->getWidgetVariables('displayCE', []);
        } elseif ('linklist' === $settings['menu']) {
            $render_method = 'psLinkList';
            $children = 'linkBlocks';

            if (!$module = \Module::getInstanceByName('ps_linklist')) {
                return;
            }
            $menu = $module->getWidgetVariables($settings['linklist_hook'], []);
        }

        if (empty($menu[$children])) {
            return;
        }

        $ul_class = 'elementor-nav';

        if ('vertical' === $settings['layout']) {
            $ul_class .= ' sm-vertical';
        }

        ob_start();
        $this->$render_method($menu[$children], 0, $ul_class);
        $menu_html = ob_get_clean();

        $this->addRenderAttribute('menu-toggle', 'class', [
            'elementor-menu-toggle',
        ]);

        if (Plugin::$instance->editor->isEditMode()) {
            $this->addRenderAttribute('menu-toggle', [
                'class' => 'elementor-clickable',
            ]);
        }

        if ('dropdown' !== $settings['layout']) {
            $this->addRenderAttribute('main-menu', 'class', [
                'elementor-nav-menu',
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
        // Don't render mobile menu when widget isn't visible on mobile
        if ('mobile' === $settings['dropdown'] && $settings['hide_mobile'] ||
            'tablet' === $settings['dropdown'] && $settings['hide_mobile'] && $settings['hide_tablet']
        ) {
            return;
        } ?>
        <div <?php $this->printRenderAttributeString('menu-toggle'); ?>>
            <i class="fa" aria-hidden="true"></i>
            <span class="elementor-screen-only"><?php _e('Menu'); ?></span>
        </div>
        <nav class="elementor-nav--dropdown elementor-nav__container"><?php echo str_replace('menu-1-', 'menu-2-', $menu_html); ?></nav>
        <?php
    }

    protected function psMainMenu(array &$nodes, $depth = 0, $ul_class = '')
    {
        ?>
        <ul <?php echo $depth ? 'class="sub-menu elementor-nav--dropdown"' : 'id="menu-1-' . $this->getId() . '" class="' . $ul_class . '"'; ?>>
        <?php foreach ($nodes as &$node) { ?>
            <li class="<?php echo sprintf(self::$li_class, $node['type'], $node['page_identifier'], $node['current'] ? ' current-menu-item' : '', $node['children'] ? ' menu-item-has-children' : ''); ?>">
                <a class="<?php echo($depth ? 'elementor-sub-item' : 'elementor-item') . (strrpos($node['url'], '#') !== false ? ' elementor-item-anchor' : '') . ($node['current'] ? ' elementor-item-active' : ''); ?>" href="<?php echo esc_attr($node['url']); ?>"<?php echo $node['open_in_new_window'] ? ' target="_blank"' : ''; ?>>
                    <?php echo $node['label']; ?>
                <?php if ($this->indicator && $node['children']) { ?>
                    <span class="sub-arrow <?php echo esc_attr($this->indicator); ?>"></span>
                <?php } ?>
                </a>
                <?php empty($node['children']) or $this->psMainMenu($node['children'], $node['depth']); ?>
            </li>
        <?php } ?>
        </ul>
        <?php
    }

    protected function psCategoryTree(array &$nodes, $depth = 0, $ul_class = '')
    {
        ?>
        <ul <?php echo $depth ? 'class="sub-menu elementor-nav--dropdown"' : 'id="menu-1-' . $this->getId() . '" class="' . $ul_class . '"'; ?>>
        <?php foreach ($nodes as &$node) {
            $current = ($this->context->controller instanceof \ProductController || $this->context->controller instanceof \CategoryController) &&
                $node['id'] == $this->context->cookie->last_visited_category; ?>
            <li class="<?php echo sprintf(self::$li_class, 'category', "category-{$node['id']}", $current ? ' current-menu-item' : '', $node['children'] ? ' menu-item-has-children' : ''); ?>">
                <a class="<?php echo($depth ? 'elementor-sub-item' : 'elementor-item') . ($current ? ' elementor-item-active' : ''); ?>" href="<?php echo esc_attr($node['link']); ?>">
                    <?php echo $node['name']; ?>
                <?php if ($this->indicator && $node['children']) { ?>
                    <span class="sub-arrow <?php echo esc_attr($this->indicator); ?>"></span>
                <?php } ?>
                </a>
                <?php empty($node['children']) or $this->psCategoryTree($node['children'], $depth + 1); ?>
            </li>
        <?php } ?>
        </ul>
        <?php
    }

    protected function psLinkList(array &$nodes, $depth = 0, $ul_class = '')
    {
        ?>
        <ul <?php echo $depth ? 'class="sub-menu elementor-nav--dropdown"' : 'id="menu-1-' . $this->getId() . '" class="' . $ul_class . '"'; ?>>
        <?php foreach ($nodes as &$node) { ?>
            <li class="<?php echo sprintf(self::$li_class, 'link', isset($node['url']) ? $node['id'] : "link-{$node['id']}", '', !empty($node['links']) ? ' menu-item-has-children' : ''); ?>">
                <a class="<?php echo $depth ? 'elementor-sub-item' : 'elementor-item'; ?>" href="<?php echo isset($node['url']) ? esc_attr($node['url']) : 'javascript:;'; ?>">
                    <?php echo $node['title']; ?>
                <?php if ($this->indicator && !empty($node['links'])) { ?>
                    <span class="sub-arrow <?php echo esc_attr($this->indicator); ?>"></span>
                <?php } ?>
                </a>
                <?php empty($node['links']) or $this->psLinkList($node['links'], $depth + 1); ?>
            </li>
        <?php } ?>
        </ul>
        <?php
    }
}
