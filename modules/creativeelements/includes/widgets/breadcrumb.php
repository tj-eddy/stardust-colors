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

class WidgetBreadcrumb extends WidgetBase
{
    const REMOTE_RENDER = true;

    public function getName()
    {
        return 'breadcrumb';
    }

    public function getTitle()
    {
        return __('Breadcrumb');
    }

    public function getIcon()
    {
        return 'eicon-product-breadcrumbs';
    }

    public function getCategories()
    {
        return [
            'theme-elements',
            'product-elements',
        ];
    }

    protected function _registerControls()
    {
        $this->startControlsSection(
            'section_breadcrumb',
            [
                'label' => __('Breadcrumb'),
                'tab' => ControlsManager::TAB_STYLE,
            ]
        );

        $this->addControl(
            'breadcrumb_color',
            [
                'label' => __('Text Color'),
                'type' => ControlsManager::COLOR,
                'dynamic' => [],
                'selectors' => [
                    '{{WRAPPER}} .breadcrumb:not(#e) li' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->addControl(
            'breadcrumb_link_color',
            [
                'label' => __('Link Color'),
                'type' => ControlsManager::COLOR,
                'dynamic' => [],
                'selectors' => [
                    '{{WRAPPER}} .breadcrumb:not(#e) li a' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->addControl(
            'breadcrumb_link_color_hover',
            [
                'label' => __('Link Hover Color'),
                'type' => ControlsManager::COLOR,
                'dynamic' => [],
                'selectors' => [
                    '{{WRAPPER}} .breadcrumb:not(#e) li a:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->addGroupControl(
            GroupControlTypography::getType(),
            [
                'label' => __('Typography'),
                'name' => 'breadcrumb_typography',
                'selector' => '{{WRAPPER}} .breadcrumb:not(#e) li',
            ]
        );

        $this->addResponsiveControl(
            'breadcrumb_align',
            [
                'label' => __('Alignment'),
                'type' => ControlsManager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Left'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __('Center'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => __('Right'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .breadcrumb ol' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->endControlsSection();
    }

    protected function render()
    {
        echo smartyInclude(['file' => '_partials/breadcrumb.tpl']);
    }
}
