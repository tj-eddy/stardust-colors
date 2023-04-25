<?php
/**
 * 1961-2019 BNP Paribas
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0) that is available
 * through the world-wide-web at this URL: http://www.opensource.org/licenses/OSL-3.0
 * If you are unable to obtain it through the world-wide-web, please send an email
 * to modules@quadra-informatique.fr so we can send you a copy immediately.
 *
 *  @author    Quadra Informatique <modules@quadra-informatique.fr>
 *  @copyright 1961-2019 BNP Paribas
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class AdminMercanetnxpaymentController extends ModuleAdminController
{
    public function __construct()
    {
        $this->module = 'mercanet';
        $this->table = 'mercanet_nx_payment';
        $this->className = 'MercanetNxPayment';
        $this->lang = true;
        $this->edit = true;
        $this->delete = true;
        $this->deleted = false;
        $this->bootstrap = true;
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        parent::__construct();
        $this->context = Context::getContext();

        $this->fields_list = array(
            'id_mercanet_nx_payment' => array(
                'title' => $this->l('Id'),
                'align' => 'left',
                'type' => 'int',
                'width' => 30,
            ),
            'method_name' => array(
                'title' => $this->l('Name'),
                'align' => 'left',
                'width' => 220,
            ),
            'active' => array(
                'title' => $this->module->l('Active'),
                'align' => 'center',
                'type' => 'bool',
                'width' => 25,
                'active' => 'status'
            )
        );
    }

    protected function l($string, $class = null, $addslashes = false, $htmlentities = true)
    {
        if ($class === null || $class == 'AdminTab') {
            $class = Tools::substr(get_class($this), 0, -10);
        } elseif (Tools::strtolower(Tools::substr($class, -10)) == 'controller') {
            $class = Tools::substr($class, 0, -10);
        }
        if ($addslashes) {
            $addslashes = true;
        }
        if ($htmlentities) {
            $htmlentities = true;
        }

        return Translate::getModuleTranslation($this->module, $string, 'adminmercanetnxpayment');
    }

    public function renderForm()
    {
        $obj = $this->loadObject(true);
        if (!($obj)) {
            $obj = $this->loadObject(true);
            return false;
        }
        $this->fields_form = array(
            'legend' => array(
                'title' => $this->l('Mercanet Several Time Payment'),
                'icon' => 'icon-money'
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Label:'),
                    'desc' => $this->l('Payment name'),
                    'name' => 'method_name',
                    'lang' => true,
                    'size' => 30,
                    'maxlength' => 255,
                    'required' => true,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Minimum amount:'),
                    'desc' => $this->l('Minimum amount to offer the option'),
                    'name' => 'minimum_amount',
                    'size' => 10,
                    'maxlength' => 10,
                    'required' => false,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Maximum amount:'),
                    'desc' => $this->l('Maximum amount to offer the option'),
                    'name' => 'maximum_amount',
                    'size' => 10,
                    'maxlength' => 10,
                    'required' => false,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Number:'),
                    'desc' => $this->l('Total number of payments. (Between 2 and 12)'),
                    'name' => 'number',
                    'size' => 10,
                    'maxlength' => 10,
                    'required' => true,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Periodicity:'),
                    'desc' => $this->l('Time between two payments (in days)'),
                    'name' => 'periodicity',
                    'size' => 10,
                    'maxlength' => 10,
                    'required' => false,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('First payment:'),
                    'desc' => $this->l('Amount of the first payment as a percentage of total. If empty, all payments will have the same amount'),
                    'name' => 'first_payment',
                    'size' => 10,
                    'maxlength' => 10,
                    'required' => false,
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Active:'),
                    'desc' => $this->l('Enabled or disabled'),
                    'name' => 'active',
                    'required' => false,
                    'class' => 't',
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => true,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => false,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'name' => 'submitAddMercanetNxPayment'
            ),
        );
        if (!Tools::getIsset('id_nx_payment')) {
            $this->fields_value['active'] = true;
        }

        return parent::renderForm();
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitAddMercanetNxPayment')) {
            $mercanet_helper_form = new MercanetHelperForm();
            $minimum_amount = Tools::getValue('minimum_amount');
            $maximum_amount = Tools::getValue('maximum_amount');
            $number = Tools::getValue('number');
            $periodicity = Tools::getValue('periodicity');
            $first_payment = Tools::getValue('first_payment');

            // Minimum amount)
            if (!$mercanet_helper_form->validateIsSuperior((float)$minimum_amount, 0)) {
                $this->errors[] = $this->l('The minimum amount should be greater than 0.');
            }

            if (!empty($maximum_amount) && (int)$maximum_amount != 0) {
                if (!$mercanet_helper_form->validateIsInferior((float)$minimum_amount, (float)$maximum_amount)) {
                    $this->errors[] = $this->l('The minimum amount should be less than the maximum amount.');
                }

                // Maximum amount
                if (!$mercanet_helper_form->validateIsSuperior((float)$maximum_amount, (float)$minimum_amount)) {
                    $this->errors[] = $this->l('The maximum amount should be greater than the minimum amount.');
                }
            }

            // Number
            if (!$mercanet_helper_form->validBetweenThese((int)$number, 2, 12)) {
                $this->errors[] = $this->l('The number of occurrence must be between 2 and 12.');
            }

            // Periodicity
            if (!$mercanet_helper_form->validatePeriodicity((float)$number, (int)$periodicity)) {
                $this->errors[] = $this->l('Payment n times should be less than 90 days duration.');
            }

            // First payment
            if (!empty($first_payment)) {
                if (!$mercanet_helper_form->validateIsSuperior((float)$first_payment, 0)) {
                    $this->errors[] = $this->l('The first payment should be greater than 0.');
                }

                if (!$mercanet_helper_form->validateIsInferior((float)$first_payment, 100)) {
                    $this->errors[] = $this->l('The first payment should be less than 100.');
                }
            }
        }
        return parent::postProcess();
    }
}
