<?php

if (defined('__PS_VERSION_')) {
    exit('Restricted Access');
}

class AdminImportChronopostController extends ModuleAdminController
{
    /**
     * @throws PrestaShopException
     */
    public function __construct()
    {
        $this->bootstrap = true;
        $this->context = Context::getContext();
        $this->className = 'Configuration';
        $this->table = 'configuration';

        parent::__construct();

        $fields = array(
            'CHRONOPOST_ORDER_REFERENCE_COLUMN' => array(
                'title'      => $this->l('Order reference column number'),
                'visibility' => Shop::CONTEXT_ALL,
                'type'       => 'text'
            ),
            'CHRONOPOST_TRACKING_NUMBER_COLUMN' => array(
                'title'      => $this->l('Tracking column number'),
                'visibility' => Shop::CONTEXT_ALL,
                'type'       => 'text'
            ),
            'importfile'                        => array(
                'title'      => $this->l('Select file to import'),
                'visibility' => Shop::CONTEXT_ALL,
                'type'       => 'file',
                'name'       => 'import'
            )
        );
        $this->fields_options = array(
            'general' => array(
                'title'  => $this->l('Management of imports using a third-party application (eg : Chronoship Station... )'),
                'icon'   => 'icon-cogs',
                'fields' => $fields,
                'submit' => array('title' => $this->l('Import file')),
            ),
        );

        $information = $this->l('Use this function to massively assign Chronopost parcel numbers to the desired orders. This is useful if you edit your waybills from a third-party application. (Eg ChronoShip Station ...). The expected file must be in CSV format with semicolon separator.')
            . '<br><br>' .
            $this->l('It must contain 2 columns :') . '<br><br><ol>' .
            '<li>' . $this->l('Prestashop orders reference') . '</li>' .
            '<li>' . $this->l('Chronopost tracking number') . '</li>' .
            '</ol><br>' .
            $this->l('The orders status will be "Shipment in transit". An email contaning the tracking number and a link to follow the parcel will be sent to the customer.');

        $this->displayInformation($information);
    }

    public function processUpdateOptions()
    {
        $orderReferenceColumn = Tools::getValue('CHRONOPOST_ORDER_REFERENCE_COLUMN');
        $trackingNumberColumn = Tools::getValue('CHRONOPOST_TRACKING_NUMBER_COLUMN');

        if (!$orderReferenceColumn || !$trackingNumberColumn) {
            $this->errors[] = Tools::displayError($this->l('Please specify column numbers.'));

            return;
        }

        if (!is_numeric($orderReferenceColumn) || !is_numeric($trackingNumberColumn)) {
            $this->errors[] = Tools::displayError($this->l('Column number is not numeric.'));

            return;
        }

        Configuration::updateValue('CHRONOPOST_ORDER_REFERENCE_COLUMN', $orderReferenceColumn);
        Configuration::updateValue('CHRONOPOST_TRACKING_NUMBER_COLUMN', $trackingNumberColumn);

        if (!array_key_exists('import', $_FILES) || $_FILES['import']['error'] != UPLOAD_ERR_OK) {
            $this->errors[] = sprintf(Tools::displayError('The file you provided failed to upload.'));

            return;
        }

        $order_id_column = Configuration::get('CHRONOPOST_ORDER_REFERENCE_COLUMN') - 1;
        $tracking_column = Configuration::get('CHRONOPOST_TRACKING_NUMBER_COLUMN') - 1;

        $fp = fopen($_FILES['import']['tmp_name'], 'r');
        while ($line = fgetcsv($fp, 0, ';')) {
            if (!is_numeric($line[$order_id_column]) || empty($line[$order_id_column]) || empty($line[$tracking_column])) {
                continue;
            }

            $tracking_numbers = explode(',', trim($line[$tracking_column], '[]'));

            foreach ($tracking_numbers as $tracking_number) {
                $tracking_number = trim($tracking_number);
                Chronopost::trackingStatus(
                    $line[$order_id_column],
                    $tracking_number
                );

                Db::getInstance()->execute('INSERT INTO `' . _DB_PREFIX_ . 'chrono_lt_history` VALUES (
                    ' . (int)$line[$order_id_column] . ', 
                    "' . pSQL($tracking_number) . '", 
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    NULL
                )');
            }
        }

        $this->confirmations[] = $this->l('File successfully uploaded, the orders have been updated.');
        fclose($fp);
        unlink($_FILES['import']['tmp_name']); /* clean up after yourself, will ya ? */
    }

    /**
     * Translate
     *
     * @param $string
     * @param $class
     * @param $addslashes
     * @param $htmlentities
     *
     * @return mixed|string
     * @throws Exception
     */
    protected function l($string, $class = null, $addslashes = false, $htmlentities = true)
    {
        return Translate::getModuleTranslation(
            'chronopost',
            $string,
            Tools::substr(get_class($this), 0, -10)
        );
    }
}
