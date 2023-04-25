<?php
/**
* NOTICE OF LICENSE
*
* This source file is subject to a commercial license from MigrationPro
* Use, copy, modification or distribution of this source file without written
* license agreement from the MigrationPro is strictly forbidden.
* In order to obtain a license, please contact us: contact@migration-pro.com
*
* INFORMATION SUR LA LICENCE D'UTILISATION
*
* L'utilisation de ce fichier source est soumise a une licence commerciale
* concedee par la societe MigrationPro
* Toute utilisation, reproduction, modification ou distribution du present
* fichier source sans contrat de licence ecrit de la part de la MigrationPro est
* expressement interdite.
* Pour obtenir une licence, veuillez contacter la MigrationPro a l'adresse: contact@migration-pro.com
*
* @author    MigrationPro
* @copyright Copyright (c) 2012-2021 MigrationPro
* @license   Commercial license
* @package   MigrationPro: Prestashop Upgrade and Migrate tool
*/

require_once 'MigrationProDBErrorLogger.php';
require_once 'MigrationProDBWarningLogger.php';
require_once 'EntityTypeMapper.php';

class MigrationProLogger
{
    public function addErrorLog($log, $entityType)
    {
        MigrationProDBErrorLogger::addErrorLog($log, $entityType);
    }

    public function addWarningLog($log, $entityType)
    {
        MigrationProDBWarningLogger::addWarningLogToDB($log, $entityType);
        MigrationProDBWarningLogger::addWarningLogToFile($log);
    }

    public function getAllWarnings()
    {
        $warnings = MigrationProDBWarningLogger::getAllWarnings();

        $warnings = array_map(array($this, 'createWarningMessage'), $warnings);

        return $warnings;
    }

    private function createWarningMessage($warning)
    {
        $warning = 'There is (are) ' . $warning['count'] . ' ' . EntityTypeMapper::getEntityTypeNameByAlias($warning['entity_type']) . ' warning(s).';

        return $warning;
    }
}
