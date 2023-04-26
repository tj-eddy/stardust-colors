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

class MercanetHelperForm extends Mercanet
{
    /**
     * Return an html input with a title
     * @param string $tab
     * @return array
     */
    protected function getInputHtmlRestrictionsAmount($tab)
    {
        if (empty($tab)) {
            return false;
        }
        return array(
            'tab' => $tab,
            'type' => 'html',
            'name' => 'restriction_amount',
            'html_content' => '<h3>'.$this->l('Restrictions on the amount').'</h3>'
        );
    }

    /**
     * Return an html input with a title
     * @param string $tab
     * @return array
     */
    protected function getInputHtmlModuleOptions($tab)
    {
        if (empty($tab)) {
            return false;
        }
        return array(
            'tab' => $tab,
            'type' => 'html',
            'name' => 'module_options',
            'html_content' => '<h3>'.$this->l('Module options').'</h3>'
        );
    }

    /**
     * Return an html input with a title
     * @param string $tab
     * @return array
     */
    protected function getInputHtmlPaymentOptions($tab)
    {
        if (empty($tab)) {
            return false;
        }
        return array(
            'tab' => $tab,
            'type' => 'html',
            'name' => 'payment_options',
            'html_content' => '<h3>'.$this->l('Payment options').'</h3>'
        );
    }

    /**
     * Return the input for 3DSecure
     * @param string $tab
     * @param string $prefix
     * @return array
     */
    protected function getInputName($tab, $prefix, $name = null, $desc = null)
    {
        if (empty($tab) || empty($prefix)) {
            return false;
        }

        return array(
            'tab' => $tab,
            'type' => 'text',
            'size' => 128,
            'label' => (!empty($name)) ? (string)$name : $this->l('Name of the payment'),
            'name' => 'MERCANET_'.$prefix.'_NAME',
            'lang' => true,
            'required' => true,
            'desc' => (!empty($desc)) ? $desc : $this->l('Name of the simple payment'),
        );
    }

    /**
     * Return the input for Activation
     * @param string $tab
     * @param string $prefix
     * @return array
     */
    protected function getInputActivation($tab, $prefix, $desc = null)
    {
        if (empty($tab) || empty($prefix)) {
            return false;
        }

        return array(
            'tab' => $tab,
            'type' => 'switch',
            'label' => (!empty($desc)) ? $desc : $this->l('Activation'),
            'name' => 'MERCANET_'.$prefix.'_ACTIVE',
            'is_bool' => true,
            'required' => false,
            'desc' => (!empty($desc)) ? $desc : $this->l('Enable / Disabled this payment'),
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
        );
    }

    /**
     * Return the input for Min Amount
     * @param string $tab
     * @param string $prefix
     * @return array
     */
    protected function getInputMinAmount($tab, $prefix)
    {
        if (empty($tab) || empty($prefix)) {
            return false;
        }

        return array(
            'tab' => $tab,
            'type' => 'text',
            'size' => 64,
            'label' => $this->l('Minimum amount'),
            'name' => 'MERCANET_'.$prefix.'_MIN_AMOUNT',
            'required' => false,
            'desc' => $this->l('Minimum amount for which this payment method is available'),
        );
    }

    /**
     * Return the input for Max Amount
     * @param string $tab
     * @param string $prefix
     * @return array
     */
    protected function getInputMaxAmount($tab, $prefix)
    {
        if (empty($tab) || empty($prefix)) {
            return false;
        }

        return array(
            'tab' => $tab,
            'type' => 'text',
            'size' => 64,
            'label' => $this->l('Maximum amount'),
            'name' => 'MERCANET_'.$prefix.'_MAX_AMOUNT',
            'required' => false,
            'desc' => $this->l('Maximum amount for which this payment method is available'),
        );
    }

    /**
     * Return the input for Card Display Method
     * @param string $tab
     * @param string $prefix
     * @return array
     */
    protected function getDisplayChoices()
    {
        return array(
            0 => array(
                'id' => 'DIRECT_MERCANET',
                'name' => $this->l('Choice of payment and data cards on Mercanet')
            ),
            1 => array(
                'id' => 'DISPLAY_CARDS',
                'name' => $this->l('Choice of payment card on Prestashop and data cards on Mercanet')
            ),
            2 => array(
                'id' => 'IFRAME',
                'name' => $this->l('Choice of payment and data cards on Prestashop (IFRAME)')
            ),
        );
    }

    /**
     * Return the table of the payments
     * @param string $tab
     * @param string $prefix
     * @return array
     */
    protected function getInputArrayPayments($tab, $prefix)
    {
        if (empty($tab) || empty($prefix)) {
            return false;
        }

        $nx_payments = MercanetNxPayment::getAllMercanetNxPayment();
        $html = '';

        $html .= '<table class="table">';
        $html .= '<thead><tr>';
        $html .= '<th>'.$this->l('Label').'</th>';
        $html .= '<th>'.$this->l('Minimum amount').'</th>';
        $html .= '<th>'.$this->l('Maximum amount').'</th>';
        $html .= '<th>'.$this->l('Number').'</th>';
        $html .= '<th>'.$this->l('Periodicity').'</th>';
        $html .= '<th>'.$this->l('First payment').'</th>';
        $html .= '<th>'.$this->l('Active').'</th>';
        $html .= '<th></th>';
        $link = new Link();
        $url = $link->getAdminLink('AdminMercanetnxpayment');

        $html .= '</tr><thead>';
        if (!empty($nx_payments)) {
            foreach ($nx_payments as $nx_payment) {
                $html .= '<tr>';
                $html .= '<td>'.$nx_payment['method_name'].'</td>';
                $html .= '<td>'.$nx_payment['minimum_amount'].'</td>';
                $html .= '<td>'.$nx_payment['maximum_amount'].'</td>';
                $html .= '<td>'.$nx_payment['number'].'</td>';
                $html .= '<td>'.$nx_payment['periodicity'].'</td>';
                $html .= '<td>'.$nx_payment['first_payment'].'</td>';
                $html .= '<td> <a href="'.$url.'&id_mercanet_nx_payment='.(int)$nx_payment['id_mercanet_nx_payment'].'&statusmercanet_nx_payment"';
                if ($nx_payment['active'] == true) {
                    $html .= '<i class="icon-check"></i>';
                } else {
                    $html .= '<i class="icon-remove"></i>';
                }
                $html .= '</a></td>';
                $html .= '<td>';
                $html .= '<a class="edit fancybox_nx_payments" href="'.$url.'&submitFormAjax=1&liteDisplaying=1&updatemercanet_nx_payment&id_mercanet_nx_payment='.(int)$nx_payment['id_mercanet_nx_payment'].'"><i class="icon-pencil"></i> '.$this->l('Modify').'</a>';
                $html .= ' ';
                $html .= '<a class="delete" href="'.$url.'&submitFormAjax=1&deletemercanet_nx_payment&id_mercanet_nx_payment='.(int)$nx_payment['id_mercanet_nx_payment'].'"><i class="icon-trash"></i> '.$this->l('Delete').'</a>';
                $html .= '</td>';
                $html .= '</tr>';
            }
        }

        $html .= '</table>';
        $html .= '<a class="fancybox_nx_payments btn btn-default" href="'.$url.'&liteDisplaying=1&addmercanet_nx_payment&submitFormAjax=1">'.$this->l('Add').'</a>';

        return array(
            'tab' => $tab,
            'type' => 'html',
            'name' => 'restriction_amount',
            'html_content' => $html
        );
    }

    /**
     * Create a submit form
     * @param string $tab
     * @param string $submit_name
     * @return array
     */
    protected function getSubmitForm($tab, $submit_name)
    {
        if (empty($tab)) {
            return false;
        }
        $html = '<button class="btn btn-default pull-right" name="'.$submit_name.'" type="submit">';
        $html .= '<i class="process-icon-save"></i>';
        $html .= $this->l('Save');
        $html .= '</button>';
        return array(
            'tab' => $tab,
            'type' => 'html',
            'name' => 'submit',
            'html_content' => $html
        );
    }

    /**
     * Translate
     * @param $string
     * @param bool $specific
     * @param null $locale
     * @return mixed|string
     * @throws Exception
     */
    public function l($string, $specific = false,$locale=NULL)
    {
        if (self::$_generate_config_xml_mode) {
            return $string;
        }
        if ($specific) {
            return Translate::getModuleTranslation($this, $string, 'mercanethelperform');
        }
        return Translate::getModuleTranslation($this, $string, 'mercanethelperform');
    }

    protected function validateForms($tab = null)
    {
        $errors = array();
        if (!empty($tab)) {
            switch ($tab) {
                case 'credentials':
                    $errors = $this->validateFormCredentials();
                    break;
                case 'general':
                    $errors = $this->validateFormGeneral();
                    break;
                case 'payment_one_time':
                    $errors = $this->validateFormOneTime();
                    break;
                case 'payment_nx_time':
                    $errors = $this->validateFormNxTime();
                    break;
            }
        }

        return $errors;
    }

    /**
     * Validate FORM Credentials
     * @return type
     */
    protected function validateFormCredentials()
    {
        $errors = array();

        // MERCHANT ID
        $id_merchant = Tools::getValue('MERCANET_MERCHANT_ID');
        if (empty($id_merchant)) {
            $errors[] = $this->l('You have to register a Merchant ID.');
        } elseif (!$this->isNumeric($id_merchant)) {
            $errors[] = $this->l('The Merchant ID must contain only numeric.');
        } elseif (!$this->validateLength($id_merchant, 15)) {
            $errors[] = $this->l('The Merchant ID can not contain more than 15 characters.');
        }

        // SECRET KEY
        $secret_key = Tools::getValue('MERCANET_SECRET_KEY');
        $mercanet_secret_key = Configuration::get('MERCANET_SECRET_KEY');
        if (empty($secret_key) && empty($mercanet_secret_key)) {
            $errors[] = $this->l('You have to enter your Secret Key.');
        }

        // KEY VERSION
        $key_version = Tools::getValue('MERCANET_KEY_VERSION');
        if (empty($key_version)) {
            $errors[] = $this->l('You have to enter a Key Version.');
        } elseif (!Validate::isInt($key_version)) {
            $errors[] = $this->l('The Key Version must contain only numeric.');
        } elseif (!$this->validateLength($key_version, 10)) {
            $errors[] = $this->l('The Key Version can not contain more than 10 characters.');
        }

        return $errors;
    }

    /**
     * Validate FORM General
     * @return type
     */
    protected function validateFormGeneral()
    {
        $errors = array();

        // MERCANET_DEFAULT_LANG
        $id_lang = Tools::getValue('MERCANET_DEFAULT_LANG');
        if (empty($id_lang)) {
            $errors[] = $this->l('You have to choose a default language.');
        }

        // MERCANET_BANK_DEPOSIT_TIME_LIMIT
        $id_bank_deposit = Tools::getValue('MERCANET_BANK_DEPOSIT_TIME_LIMIT');
        if (!empty($id_bank_deposit)) {
            if (!empty($id_bank_deposit) && !Validate::isInt($id_bank_deposit)) {
                $errors[] = $this->l('The Bank deposit time limit must contain only numeric.');
            } elseif (!$this->validBetweenThese($id_bank_deposit, 0, 99)) {
                $errors[] = $this->l('The Bank deposit time limit must be between 0 and 99.');
            }
        }

        // MERCANET_CSS_THEME_CONFIG
        $css_theme_config = Tools::getValue('MERCANET_CSS_THEME_CONFIG');
        if (!empty($css_theme_config) && !Validate::isUrl($css_theme_config)) {
            $errors[] = $this->l('The format of the Payment page URL is not correct.');
        }

        // MERCANET_3DS_MIN_AMOUNT
        $min_amount_ds = Tools::getValue('MERCANET_3DS_MIN_AMOUNT');
        if (!empty($min_amount_ds)) {
            if (!empty($min_amount_ds) && !Validate::isInt($min_amount_ds)) {
                $errors[] = $this->l('The 3DS minimum amount must contain only numeric.');
            } elseif (!$this->validateIsSuperior($min_amount_ds, 0)) {
                $errors[] = $this->l('The 3DS minimum amount must superior to 0.');
            }
        }

        // MERCANET_LIVE_MODE
        $live_mode = Tools::getValue('MERCANET_TEST_MODE');

        return $errors;
    }

    /**
     * Validate FORM One Time
     * @return type
     */
    protected function validateFormOneTime()
    {
        $errors = array();

        // MERCANET_ONE_TIME_NAME
        $languages = Language::getLanguages();
        foreach ($languages as $lang) {
            $name = Tools::getValue('MERCANET_ONE_TIME_NAME_'.$lang['id_lang']);
            if (empty($name)) {
                $errors[] = $this->l('The name of the payment is required for').' '.$lang['name'];
            }
        }

        // MERCANET_ONE_TIME_MIN_AMOUNT
        $min_amount = Tools::getValue('MERCANET_ONE_TIME_MIN_AMOUNT');
        if (!empty($min_amount) && !Validate::isInt($min_amount)) {
            $errors[] = $this->l('The minimum amount must contain only numeric.');
        } elseif (!$this->validateIsSuperior($min_amount, 0)) {
            $errors[] = $this->l('The minimum amount must superior to 0.');
        }

        // MERCANET_ONE_TIME_MAX_AMOUNT
        $max_amount = Tools::getValue('MERCANET_ONE_TIME_MAX_AMOUNT');
        if (!empty($max_amount) && (int)$max_amount != 0) {
            if (!Validate::isInt($max_amount)) {
                $errors[] = $this->l('The maximum amount must contain only numeric.');
            } elseif (!$this->validateIsSuperior($max_amount, $min_amount)) {
                $errors[] = $this->l('The maximum amount should be greater than the minimum amount.');
            }
        }

        // MERCANET_LIVE_MODE
        $live_mode = Tools::getValue('MERCANET_TEST_MODE');

        return $errors;
    }

    /**
     * Validate FORM One Time
     * @return type
     */
    protected function validateFormNxTime()
    {
        $errors = array();

        // MERCANET_NX_TIME_NAME
        $languages = Language::getLanguages();
        foreach ($languages as $lang) {
            $name = Tools::getValue('MERCANET_NX_TIME_NAME_'.$lang['id_lang']);
            if (empty($name)) {
                $errors[] = $this->l('The name of the payment is required for').' '.$lang['name'];
            }
        }

        return $errors;
    }

    /**
     * Validate the number between 2 number
     * @param string $string
     * @param integer $length
     * @return boolean
     */
    public function validBetweenThese($number, $min, $max)
    {
        if (empty($number) || empty($min) || empty($max)) {
            return false;
        }
        if ((int)$number >= (int)$min && (int)$number <= (int)$max) {
            return true;
        }
        return false;
    }

    /**
     * Validate the length of an input
     * @param string $string
     * @param integer $length
     * @return boolean
     */
    public function validateLength($string, $length)
    {
        $string_lenght = Tools::strlen($string);

        if ($string_lenght <= $length) {
            return true;
        }
        return false;
    }

    /**
     * Validate the number is superior
     * @param string $string
     * @param integer $length
     * @return boolean
     */
    public function validateIsSuperior($number, $min)
    {
        if (empty($number)) {
            return false;
        }
        if ((float)$number > (int)$min) {
            return true;
        }
        return false;
    }

    /**
     * Validate the number is superior
     * @param string $string
     * @param integer $length
     * @return boolean
     */
    public function validateIsInferior($number, $max)
    {
        if (empty($number) || empty($max)) {
            return false;
        }
        if ((float)$number < (int)$max) {
            return true;
        }
        return false;
    }

    public function validatePeriodicity($number, $periodicity)
    {
        if (empty($number) || empty($periodicity)) {
            return false;
        }

        if ((float)$number * (int)$periodicity <= Configuration::get('MERCANET_NX_MAX_DAYS')) {
            return true;
        }

        return false;
    }

    /**
     * Check if the string is only numeric
     * @param int $string
     * @return boolean
     */
    public function isNumeric($string)
    {
        if (!empty($string)) {
            return preg_match('/^[0-9]*$/', $string);
        }
        return false;
    }
}
