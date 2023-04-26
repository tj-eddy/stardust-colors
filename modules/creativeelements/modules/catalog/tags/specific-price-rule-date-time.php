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

use CE\CoreXDynamicTagsXDataTag as DataTag;
use CE\ModulesXDynamicTagsXModule as Module;

class ModulesXCatalogXTagsXSpecificPriceRuleDateTime extends DataTag
{
    public function getName()
    {
        return 'specific-price-rule-date-time';
    }

    public function getTitle()
    {
        return __('Catalog Price Rule') . ' ' . __('Date');
    }

    public function getGroup()
    {
        return Module::CATALOG_GROUP;
    }

    public function getCategories()
    {
        return [Module::DATE_TIME_CATEGORY];
    }

    public function getPanelTemplateSettingKey()
    {
        return 'date';
    }

    protected function getSpecificPriceRuleOptions()
    {
        $opts = [];
        $table = _DB_PREFIX_ . 'specific_price_rule';
        $ctx_ids = implode(', ', \Shop::getContextListShopID());
        $rows = \Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS("
            SELECT `id_specific_price_rule`, `name` FROM `$table`
            WHERE `id_shop` IN ($ctx_ids)
        ");
        if ($rows) {
            foreach ($rows as &$row) {
                $opts[$row['id_specific_price_rule']] = "#{$row['id_specific_price_rule']} {$row['name']}";
            }
        }

        return $opts;
    }

    protected function _registerControls()
    {
        $this->addControl(
            'id_specific_price_rule',
            [
                'label' => __('Catalog Price Rule'),
                'type' => ControlsManager::SELECT2,
                'label_block' => true,
                'select2options' => [
                    'placeholder' => __('Select...'),
                ],
                'options' => is_admin() ? $this->getSpecificPriceRuleOptions() : [],
            ]
        );

        $this->addControl(
            'date',
            [
                'label' => __('Date'),
                'type' => ControlsManager::SELECT,
                'options' => [
                    'from' => __('From'),
                    'to' => __('To'),
                ],
                'default' => 'to',
            ]
        );
    }

    public function getValue(array $options = [])
    {
        $settings = $this->getSettings();
        $spr = new \SpecificPriceRule($settings['id_specific_price_rule']);

        return $spr->{$settings['date']};
    }

    protected function getSmartyValue(array $options = [])
    {
        $settings = $this->getSettings();

        return "{\$spr = ce_new(SpecificPriceRule, {$settings['id_specific_price_rule']}}{\$spr->{$settings['date']}}";
    }
}
