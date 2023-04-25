<?php
header('Content-type: text/plain');
require('../../../config/config.inc.php');

require_once('../libraries/PDFMerger.php');
@$pdf = new PDFMerger;
$pdfs = $_POST["pdfs"];

foreach ($pdfs as $pdf_path) {
    $realpath = "../" . $pdf_path;
    if (file_exists($realpath)) {
        @$pdf->addPDF($realpath, 'all');
    }
}

$name = '/skybills/Chronopost-LT-' . date('Ymd-Hi') . '.pdf';
$pdf->merge('file', '..' . $name);

echo $name;

