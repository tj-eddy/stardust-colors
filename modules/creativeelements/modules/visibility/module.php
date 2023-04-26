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

use CE\CoreXBaseXModule as BaseModule;

class ModulesXVisibilityXModule extends BaseModule
{
    private $device;

    private $groups;

    public function getName()
    {
        return 'visibility';
    }

    public function isElementVisibleByDevice($should_render, ElementBase $element)
    {
        $hide = $element->getSettings('hide_' . $this->device);

        $element->setSettings('hide_desktop', '');
        $element->setSettings('hide_tablet', '');
        $element->setSettings('hide_mobile', '');

        return $should_render && !$hide;
    }

    public function isElementVisibleBySchedule($should_render, ElementBase $element)
    {
        if (!$should_render) {
            return false;
        }

        if (!$element->getSettings('schedule')) {
            return true;
        }

        $from = $element->getSettings('schedule_from');
        $to = $element->getSettings('schedule_to');

        $date_now = strtotime(date('Y-m-d H:i'));
        $date_from = $from ? strtotime($from) : $date_now;
        $date_to = $to ? strtotime($to) : $date_now;

        return $date_now >= $date_from && $date_now <= $date_to;
    }

    public function isElementVisibleByGroup($should_render, ElementBase $element)
    {
        if (!$should_render) {
            return false;
        }

        if (!$filter = $element->getSettings('group_filter')) {
            return true;
        }

        $groups = $element->getSettings('groups') ?: [];

        return
            'include' === $filter && array_intersect($this->groups, $groups) ||
            'exclude' === $filter && !array_intersect($this->groups, $groups)
        ;
    }

    public function getGroupOptions()
    {
        static $options;

        if (null === $options) {
            $groups = \Group::getGroups(\Context::getContext()->language->id);

            foreach ($groups as &$group) {
                $options[$group['id_group']] = $group['name'];
            }
        }

        return $options;
    }

    public function registerControls(ControlsStack $element, array $args)
    {
        $element->startControlsSection(
            '_section_visibility',
            [
                'label' => __('Visibility'),
                'tab' => ControlsManager::TAB_ADVANCED,
            ]
        );

        $element->addControl(
            'schedule',
            [
                'label' => __('Schedule'),
                'type' => ControlsManager::SWITCHER,
            ]
        );

        $element->addControl(
            'schedule_from',
            [
                'label' => __('From'),
                'type' => ControlsManager::DATE_TIME,
                'dynamic' => [
                    'active' => true,
                ],
                'default' => date('Y-m-d H:i'),
                'condition' => [
                    'schedule!' => '',
                ],
            ]
        );

        $element->addControl(
            'schedule_to',
            [
                'label' => __('To'),
                'type' => ControlsManager::DATE_TIME,
                'dynamic' => [
                    'active' => true,
                ],
                'default' => date('Y-m-d H:i', strtotime('+1 month')),
                'description' => sprintf(__('Date set according to your timezone: %s.'), Utils::getTimezoneString()),
                'condition' => [
                    'schedule!' => '',
                ],
            ]
        );

        if (\Group::isFeatureActive()) {
            $element->addControl(
                'group_filter',
                [
                    'label' => __('Customer Group'),
                    'type' => ControlsManager::SELECT,
                    'options' => [
                        '' => __('All'),
                        'include' => __('Show for'),
                        'exclude' => __('Hide for'),
                    ],
                    'separator' => 'before',
                ]
            );

            $element->addControl(
                'groups',
                [
                    'label_block' => 'true',
                    'type' => ControlsManager::SELECT2,
                    'multiple' => true,
                    'select2options' => [
                        'placeholder' => __('Select...'),
                    ],
                    'options' => $this->getGroupOptions(),
                    'condition' => [
                        'group_filter!' => '',
                    ],
                ]
            );
        }

        $element->endControlsSection();
    }

    public function __construct()
    {
        if (!is_admin() && \Configuration::get('elementor_remove_hidden')) {
            $mobile_detect = \Context::getContext()->mobile_detect;
            $this->device = $mobile_detect->isTablet() ? 'tablet' : ($mobile_detect->isMobile() ? 'mobile' : 'desktop');

            add_filter('elementor/frontend/section/should_render', [$this, 'isElementVisibleByDevice']);
            add_filter('elementor/frontend/column/should_render', [$this, 'isElementVisibleByDevice']);
            add_filter('elementor/frontend/widget/should_render', [$this, 'isElementVisibleByDevice']);
        }

        if (\Configuration::get('elementor_visibility')) {
            if (!is_admin()) {
                add_filter('elementor/frontend/section/should_render', [$this, 'isElementVisibleBySchedule']);
                add_filter('elementor/frontend/column/should_render', [$this, 'isElementVisibleBySchedule']);
                add_filter('elementor/frontend/widget/should_render', [$this, 'isElementVisibleBySchedule']);

                if (\Group::isFeatureActive()) {
                    $id_customer = \Context::getContext()->customer->id;
                    $this->groups = \Customer::getGroupsStatic($id_customer);

                    add_filter('elementor/frontend/section/should_render', [$this, 'isElementVisibleByGroup']);
                    add_filter('elementor/frontend/column/should_render', [$this, 'isElementVisibleByGroup']);
                    add_filter('elementor/frontend/widget/should_render', [$this, 'isElementVisibleByGroup']);
                }
            }

            add_action('elementor/element/section/_section_responsive/before_section_start', [$this, 'registerControls']);
            add_action('elementor/element/column/_section_responsive/before_section_start', [$this, 'registerControls']);
            add_action('elementor/element/common/_section_responsive/before_section_start', [$this, 'registerControls']);
        }
    }
}
