<?php

use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;
use PrestaShop\PrestaShop\Core\Addon\Module\ModuleRepository;

/**
 * Class ChronopostPaymentHelper
 */
class ChronopostPaymentHelper
{

    /**
     * @var array
     */
    protected $shops = [];

    /**
     * @var array
     */
    protected $modules = [];

    /**
     * @var ModuleRepository
     */
    private $moduleRepository;

    /**
     * ChronopostPaymentHelper constructor.
     *
     * @throws PrestaShopDatabaseException
     */
    public function __construct()
    {
        $moduleManagerBuilder = ModuleManagerBuilder::getInstance();
        $this->shops = Db::getInstance()->executeS('SELECT s.id_shop FROM ' . _DB_PREFIX_ . 'shop s');
        $this->modules = Module::getModulesOnDisk(true);
        $this->moduleRepository = $moduleManagerBuilder->buildRepository();
    }

    /**
     * Enable all active payments module for given carrier id
     *
     * @param $id
     *
     * @throws PrestaShopDatabaseException
     */
    public function useAllActivePaymentsForCarrier($id)
    {
        if (is_array($this->shops)) {
            foreach ($this->shops as $shop) {
                $shopId = isset($shop['id_shop']) ? $shop['id_shop'] : null;
                if ($shopId) {
                    $activePayments = $this->getActivePayments($shopId);
                    $values = $this->buildSqlValues($id, $activePayments, $shopId);
                    if (count($values)) {
                        Db::getInstance()->execute('INSERT INTO `' . _DB_PREFIX_ . 'module_carrier`
                        (`id_module`, `id_shop`, `id_reference`) VALUES ' . implode(',', $values));
                    }
                }
            }
        }
    }

    /**
     * Get active payments for specific shop id
     *
     * @param $shopId
     *
     * @return array
     * @throws PrestaShopDatabaseException
     */
    private function getActivePayments($shopId)
    {
        /* Get all modules then select only payment ones */
        $payment_modules = [];
        foreach ($this->modules as $module) {
            $addonModule = $this->moduleRepository->getModule($module->name);
            if ($addonModule->attributes->get('is_paymentModule')) {
                if ($module->id) {
                    if (!get_class($module) == 'SimpleXMLElement') {
                        $module->country = [];
                    }

                    $sql = new DbQuery();
                    $sql->select('`id_country`');
                    $sql->from('module_country');
                    $sql->where('`id_module` = ' . (int)$module->id);
                    $sql->where('`id_shop` = ' . (int)$shopId);
                    $countries = Db::getInstance()->executeS($sql);
                    foreach ($countries as $country) {
                        $module->country[] = $country['id_country'];
                    }

                    if (!get_class($module) == 'SimpleXMLElement') {
                        $module->currency = [];
                    }

                    $sql = new DbQuery();
                    $sql->select('`id_currency`');
                    $sql->from('module_currency');
                    $sql->where('`id_module` = ' . (int)$module->id);
                    $sql->where('`id_shop` = ' . (int)$shopId);
                    $currencies = Db::getInstance()->executeS($sql);
                    foreach ($currencies as $currency) {
                        $module->currency[] = $currency['id_currency'];
                    }

                    if (!get_class($module) == 'SimpleXMLElement') {
                        $module->group = [];
                    }

                    $sql = new DbQuery();
                    $sql->select('`id_group`');
                    $sql->from('module_group');
                    $sql->where('`id_module` = ' . (int)$module->id);
                    $sql->where('`id_shop` = ' . (int)$shopId);
                    $groups = Db::getInstance()->executeS($sql);
                    foreach ($groups as $group) {
                        $module->group[] = $group['id_group'];
                    }

                    if (!get_class($module) == 'SimpleXMLElement') {
                        $module->reference = [];
                    }
                    $sql = new DbQuery();
                    $sql->select('`id_reference`');
                    $sql->from('module_carrier');
                    $sql->where('`id_module` = ' . (int)$module->id);
                    $sql->where('`id_shop` = ' . (int)$shopId);
                    $carriers = Db::getInstance()->executeS($sql);
                    foreach ($carriers as $carrier) {
                        $module->reference[] = $carrier['id_reference'];
                    }
                } else {
                    $module->country = null;
                    $module->currency = null;
                    $module->group = null;
                }

                $payment_modules[] = $module;
            }
        }

        return $payment_modules;
    }

    /**
     * Build SQL values to activate payment for specific carrier
     *
     * @param       $id
     * @param array $activePayments
     * @param       $shopId
     *
     * @return array
     */
    private function buildSqlValues($id, array $activePayments, $shopId)
    {
        $values = [];
        foreach ($activePayments as $module) {
            if ($module->active) {
                $values[] = '(' . (int)$module->id . ', ' . (int)$shopId . ', ' . (int)$id . ')';
            }
        }

        return $values;
    }
}