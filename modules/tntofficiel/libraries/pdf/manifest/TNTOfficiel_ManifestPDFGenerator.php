<?php
/**
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 *
 * @author    Inetum <inetum.world>
 * @copyright 2016-2021 Inetum, 2016-2021 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 */

require_once _PS_MODULE_DIR_.'tntofficiel/libraries/TNTOfficiel_ClassLoader.php';

class TNTOfficiel_ManifestPDFGenerator extends PDFGenerator
{
    public function Header()
    {
        TNTOfficiel_Logstack::log();

        $this->writeHTML($this->header);

        $this->writeHTML('<table><tr><td style="width: 33%;">&nbsp;</td><td style="width: 33%">&nbsp;</td><td style="width: 33%;text-align:right;">Page : '.$this->getAliasNumPage().' de '.$this->getAliasNbPages().'</td></tr></table>');
    }

}
