<?php
include('../../config/config.inc.php');

if (!Tools::getIsset('orderid') && !Tools::getIsset('orders') && !Tools::getIsset('orderid')) {
    die('<h1>Informations de commande non transmises</h1>');
}

$accounts = json_decode(Configuration::get('CHRONOPOST_GENERAL_ACCOUNTS'), 1);
$account = $accounts[0];

if (Tools::isEmpty('shared_secret') || Tools::getValue('shared_secret') != Configuration::get('CHRONOPOST_SECRET')) {
    die('Secret does not match.');
}

if (Tools::strlen($account['account']) < 8) {
    die('Erreur : veuillez configurer le module avant de procéder à l\'édition du bordereau.');
}

$orders = Tools::getValue('orders');
$orders = explode(';', $orders);
$orders = array_map('intval', $orders); // force cast to int prevent injection
if (count($orders) == 0) {
    die('<h1>Aucune commande sélectionnée</h1>');
}

require_once('libraries/fpdf/fpdf.php');

function cleanStr($s)
{
    $s = iconv('UTF-8', 'ASCII//TRANSLIT', $s);

    return Tools::strtoupper(utf8_decode($s));
}

class BordereauPDF extends FPDF
{
    public function headTable($data, $w = array(75, 75), $mustBold = true)
    {
        foreach ($data as $row) {
            $this->SetFont('Arial', '', 10);
            $this->Cell($w[0], 4, $row[0]);
            if ($mustBold) {
                $this->SetFont('Arial', 'B', 10);
            }
            $this->Cell($w[1], 4, $row[1]);
            $this->Ln();
        }
    }

    public function innerTable($header, $data, $w = array(30, 25, 18, 15, 15, 25, 40, 22))
    {
        $this->SetFillColor(204, 204, 204);
        $this->SetFont('Arial', 'B', 9);

        $sizeof = count($header);
        for ($i = 0; $i < $sizeof; $i++) {
            $this->Cell($w[$i], 6, $header[$i], 1, 0, 'L', true);
        }
        $this->Ln();

        $this->SetFont('Arial', '', 9);
        foreach ($data as $row) {
            $i = 0;
            foreach ($row as $value) {
                $this->Cell($w[$i], 5, utf8_decode($value), 'LRB', 0, 'C');
                $i++;
            }
            $this->Ln();
        }
    }

    public function oxiCell($text, $w = 0, $h = 0)
    {
        $w += $this->GetStringWidth($text);
        $this->Cell($w, $h, $text);
        $this->Ln();
    }
}


/* Get data */
$recapLT = DB::getInstance()->executeS(
    'SELECT lt, account_number, product, zipcode, country, insurance, city, lt_dlc  FROM '
    . _DB_PREFIX_ . 'chrono_lt_history WHERE id_order IN(' . implode(',', $orders) . ') AND cancelled IS NULL'
);

$sum_nat = 0;
$sum_inter = 0;
foreach ($recapLT as $lt) {
    if ($lt['country'] == 'FR') {
        $sum_nat++;
    } else {
        $sum_inter++;
    }
}

/* Build PDF */
$pdf = new BordereauPDF();
$pdf->SetFont('Arial', 'B', 10);
$pdf->AddPage();
$pdf->Cell(150, 10, 'BORDEREAU RECAPITULATIF');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(100, 10, 'date : ' . date('d/m/Y'));
$pdf->Ln();
$pdf->Ln();

$pdf->SetFont('Arial', 'B', 10);

$pdf->OxiCell('EMETTEUR', 0, 10);
$pdf->HeadTable(array(
    array('NOM', cleanStr(Configuration::get('CHRONOPOST_CUSTOMER_NAME'))),
    array('ADRESSE', cleanStr(Configuration::get('CHRONOPOST_CUSTOMER_ADDRESS'))),
    array('ADRESSE (SUITE)', cleanStr(Configuration::get('CHRONOPOST_CUSTOMER_ADDRESS2'))),
    array('VILLE', cleanStr(Configuration::get('CHRONOPOST_CUSTOMER_CITY'))),
    array('CODE POSTAL', Configuration::get('CHRONOPOST_CUSTOMER_ZIPCODE')),
    array('PAYS', 'FRANCE'),
    array('TELEPHONE', Configuration::get('CHRONOPOST_CUSTOMER_PHONE')),
    array('POSTE COMPTABLE', (int)(Configuration::get('CHRONOPOST_CUSTOMER_ZIPCODE') / 1000) * 1000 + 999)
));

$pdf->SetFont('Arial', 'B', 10);
$pdf->Ln();
$pdf->Ln();
$pdf->OxiCell('DETAIL DES ENVOIS', 0, 10);

$pdf->InnerTable(array('NUM DE LT', 'NUM COMPTE', 'PRODUIT', 'CP', 'PAYS', 'ASSURANCE', 'VILLE', 'DLC (Fresh)'),
    $recapLT);

$pdf->SetFont('Arial', 'B', 10);
$pdf->Ln();
$pdf->Ln();
$pdf->OxiCell('RESUME', 0, 10);

$pdf->InnerTable(array('DESTINATION', 'UNITE'), array(
    array('NATIONAL', $sum_nat),
    array('INTERNATIONAL', $sum_inter),
    array('TOTAL', $sum_inter + $sum_nat)
), array(40, 20));

$pdf->Ln(15);
$pdf->SetFont('Arial', 'B', 10);
$pdf->OxiCell('Bien pris en charge ' . ($sum_inter + $sum_nat) . ' colis.', 0, 10);
$pdf->Ln(25);

$pdf->HeadTable(array(
    array('Signature du Client', 'Signature du Messager Chronopost')
), array(95, 95), false);

$pdf->Output('BordereauChronopost-' . date('Ymd') . '.pdf', 'I');
