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

class MercanetRedirectModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function initContent()
    {
        $this->display_column_left = false;
        $this->display_column_right = false;
        parent::initContent();

        // Recheck before sending the request to BNP
        if (!$this->module->canDisplayPayment()) {
            return $this->displayError('None card has been selected, please return to the payment choice and choose one');
        }

        // Recheck One Time Paymenet
        if (Tools::getIsset('one_time')) {
            if (!$this->module->canDisplayOneTimePayment()) {
                return $this->displayError('An error has occured, please return to the payment choice and retry.');
            }
        }

        // Recheck Nx Payment
        if (Tools::getIsset('nx_time')) {
            if (!$this->module->canDisplayNxTimePayment()) {
                return $this->displayError('An error has occured, please return to the payment choice and retry.');
            }
        }

        // Recheck Recurring Payment
        if (Tools::getIsset('recurring')) {
            if (!$this->module->canDisplayPaymentRecurring()) {
                return $this->displayError('An error has occured, please return to the payment choice and retry.');
            }
        }
    }

    /**
     * Do whatever you have to before redirecting the customer on the website of your payment processor.
     */
    public function postProcess()
    {
        $use_iframe = false;
        $is_nx = false;
        if (Tools::getIsset('nx_time') && Tools::getIsset('id_nx_payment')) {
            $is_nx = true;
        }
        // Retrieve the default params
        $params = MercanetApi::getDefaultParameters($is_nx);
               
        // Display Card
        if (Configuration::get('MERCANET_CARD_DISPLAY_METHOD') == 'DISPLAY_CARDS') {
            $mercanet_card = Tools::getValue('mercanet_card');
            if (!empty($mercanet_card)) {
                // Change the parameters for one card
                $params = MercanetApi::getDirectCardParameters($params, Tools::getValue('mercanet_card'));
            }
        }

        // Nx payment
        if (Tools::getIsset('nx_time') && Tools::getIsset('id_nx_payment')) {
            $params = MercanetApi::getNxParameters($params, Tools::getValue('id_nx_payment'));
        }

        // Recurring payment
        if (Tools::getIsset('recurring')) {
            $params = MercanetApi::getRecurringParameters($params);
        }

        // Iframe
        if (Configuration::get('MERCANET_CARD_DISPLAY_METHOD') == 'IFRAME') {
            $use_iframe = true;
        }

        // exception case AURORE specific breandMeanList
        if (isset($params['paymentMeanBrandList'])) {
            if (stristr($params['paymentMeanBrandList'], 'AURORE')) {
                $params['paymentMeanBrandList'] = str_replace('AURORE', 'AURORE, AURORE_LECLERC', $params['paymentMeanBrandList']);
            }
        }
        
        // Seal
        $seal = MercanetApi::buildSeal($params);

        // Log
        $message = 'Data send to mercanet => ';
        $message .= 'Customer: '.$this->context->customer->id.' '.$this->context->customer->firstname.' '.$this->context->customer->lastname;
        $message .= ' || ';
        $message .= ' Params: ';
        $message .= implode(
            ', ',
            array_map(
                function ($v, $k) {
                    return $k.'='.$v;
                },
                $params,
                array_keys($params)
            )
        );

        MercanetLogger::log($message, MercanetLogger::LOG_DEBUG, MercanetLogger::FILE_DEBUG, false, 1, true);


        if (Tools::getValue('action') == 'error') {
            // Log
            $message = 'Cannot send data to mercanet => ';
            $message .= 'Customer: '.$this->context->customer->id.' '.$this->context->customer->firstname.' '.$this->context->customer->lastname;
            $message .= ' || ';
            $message .= ' Params: ';
            $message .= implode(
                ', ',
                array_map(
                    function ($v, $k) {
                        return $k.'='.$v;
                    },
                    $params,
                    array_keys($params)
                )
            );

            MercanetLogger::log($message, MercanetLogger::LOG_ERROR, MercanetLogger::FILE_DEBUG);
            return $this->displayError('An error occurred while trying to redirect the customer');
        } else {
            $this->context->smarty->assign(array(
                'data_mercanet' => MercanetApi::getRawData($params),
                'interface_version' => Configuration::get('MERCANET_INTERFACE_VERSION'),
                'seal' => $seal,
                'use_iframe' => $use_iframe,
                'url_mercanet' => $this->getServerUrl(),
                'secure_key' => Context::getContext()->customer->secure_key,
                'module_display_name' => $this->module->displayName,
            ));
               
            return $this->setTemplate('module:mercanet/views/templates/front/redirect.tpl');
        }
    }

    protected function displayError($message, $description = false)
    {
        $this->context->smarty->assign(
            'path',
            '<a href="'.$this->context->link->getPageLink('order', null, null, 'step=3').'">'.$this->module->l('Payment').'</a><span class="navigation-pipe">&gt;</span>'.$this->module->l('Error')
        );
        array_push($this->errors, $this->module->l($message), $description);
        $this->context->smarty->assign('link', new Link());
        return $this->setTemplate('error.tpl');
    }

    /**
     * Get the server URL to use to contact BNP Mercanet
     * @return URL
     */
    protected function getServerUrl()
    {
        // TEST
        if ((bool)Configuration::get('MERCANET_TEST_MODE') == true && Configuration::get('MERCANET_TEST_PAYMENT_PAGE_URL')) {
            return Configuration::get('MERCANET_TEST_PAYMENT_PAGE_URL');
        }

        // PRODUCTION
        return Configuration::get('MERCANET_PAYMENT_PAGE_URL');
    }
}
