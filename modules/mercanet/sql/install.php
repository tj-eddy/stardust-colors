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

$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mercanet` (
    `id_mercanet` int(11) NOT NULL AUTO_INCREMENT,
    PRIMARY KEY  (`id_mercanet`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';


// --- TABLES --- //
// MERCANET ORDER REFERENCE
$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mercanet_order_reference` (
    `id_cart` INT(10) NOT NULL,
    `reference` VARCHAR(128) NOT NULL,
    PRIMARY KEY (`id_cart`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

// MERCANET NX PAYMENT TABLE
$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mercanet_nx_payment` (
    `id_mercanet_nx_payment` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `minimum_amount` DECIMAL(20,2) NULL,
    `maximum_amount` DECIMAL(20,2) NULL,
	`number` INT(10) NOT NULL,
    `periodicity` INT(10) NULL,
    `first_payment` DECIMAL(20,2) NULL,
    `active` TINYINT(1) NOT NULL DEFAULT \'0\',
    PRIMARY KEY (`id_mercanet_nx_payment`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

// MERCANET NX PAYMENT LANG TABLE
$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mercanet_nx_payment_lang` (
    `id_mercanet_nx_payment` INT(10) NOT NULL,
    `id_lang` INT(10) NOT NULL,
    `method_name` VARCHAR(255) NOT NULL,
    PRIMARY KEY ( `id_mercanet_nx_payment` , `id_lang` )
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

// MERCANET RESPONSE CODE TABLE
$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mercanet_response_code` (
    `id_mercanet_response_code` VARCHAR(10) NOT NULL,
    PRIMARY KEY (`id_mercanet_response_code`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

// MERCANET RESPONSE CODE TABLE LANG TABLE
$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mercanet_response_code_lang` (
    `id_mercanet_response_code` VARCHAR(10) NOT NULL,
    `id_lang` INT(10) NOT NULL,
    `message` VARCHAR(255) NOT NULL,
    PRIMARY KEY ( `id_mercanet_response_code` , `id_lang` )
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

// MERCANET ACQUIRER RESPONSE CODE TABLE
$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mercanet_acquirer_response_code` (
    `id_mercanet_acquirer_response_code` VARCHAR(10) NOT NULL,
    PRIMARY KEY (`id_mercanet_acquirer_response_code`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

// MERCANET ACQUIRER RESPONSE CODE TABLE LANG TABLE
$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mercanet_acquirer_response_code_lang` (
    `id_mercanet_acquirer_response_code` VARCHAR(10) NOT NULL,
    `id_lang` INT(10) NOT NULL,
    `message` VARCHAR(255) NOT NULL,
    PRIMARY KEY ( `id_mercanet_acquirer_response_code` , `id_lang` )
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

// MERCANET COMPLEMNTARY CODE TABLE
$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mercanet_complementary_code` (
    `id_mercanet_complementary_code` VARCHAR(10) NOT NULL,
    PRIMARY KEY (`id_mercanet_complementary_code`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

// MERCANET COMPLEMNTARY CODE TABLE LANG TABLE
$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mercanet_complementary_code_lang` (
    `id_mercanet_complementary_code` VARCHAR(10) NOT NULL,
    `id_lang` INT(10) NOT NULL,
    `message` VARCHAR(255) NOT NULL,
    PRIMARY KEY ( `id_mercanet_complementary_code` , `id_lang` )
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

// MERCANET HISTORY TABLE
$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mercanet_history` (
    `id_mercanet_history` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`id_mercanet_transaction` VARCHAR(10) NOT NULL,
	`id_mercanet_response_code` VARCHAR(10) NULL,
	`id_mercanet_acquirer_response_code` VARCHAR(10) NULL,
	`id_mercanet_complementary_code` VARCHAR(10) NULL,
	`date_add` DATETIME,
    PRIMARY KEY (`id_mercanet_history`),
	KEY (`id_mercanet_transaction`),
	KEY (`id_mercanet_response_code`),
	KEY (`id_mercanet_acquirer_response_code`),
	KEY (`id_mercanet_complementary_code`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

// MERCANET TRANSACTION TABLE
$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mercanet_transaction` (
    `id_mercanet_transaction` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`id_order` INT(10) NOT NULL,
    `id_order_slip` INT(10) NULL,
    `id_order_recurring` INT(10) NULL,
	`authorisation_id` VARCHAR(128) NULL,
	`transaction_reference` VARCHAR(128) NOT NULL,
    `transaction_type` VARCHAR(128) NOT NULL,
	`capture_mode` VARCHAR(128) NULL,
	`masked_pan` VARCHAR(128) NULL,
	`amount` DECIMAL(20,2) NOT NULL,
	`payment_mean_brand` VARCHAR(128) NOT NULL,
	`payment_mean_type` VARCHAR(128) NOT NULL,
	`transaction_date_time` DATETIME,
    `complementary_info` TEXT NOT NULL,
	`raw_data` TEXT NOT NULL,
    PRIMARY KEY (`id_mercanet_transaction`),
	KEY (`id_order`),
    KEY (`id_order_slip`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

// MERCANET WALLET
$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mercanet_wallet` (
    `id_mercanet_wallet` VARCHAR(36) NOT NULL UNIQUE,
	`id_customer` INT(10) NOT NULL UNIQUE,
    PRIMARY KEY (`id_mercanet_wallet`),
	KEY (`id_customer`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

// MERCANET SCHEDULE TABLE
$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mercanet_schedule` (
    `id_mercanet_schedule` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`id_order` INT(10) NOT NULL,
	`id_mercanet_transaction` INT(10) NULL,
	`transaction_reference` VARCHAR(128) NOT NULL,
	`masked_pan` VARCHAR(128) NULL,
	`amount` DECIMAL(20,2) NOT NULL,
	`date_add` DATETIME,
    `date_to_capture` DATE NOT NULL,
	`date_capture` DATE NULL,
	`captured` TINYINT(1) NOT NULL DEFAULT \'0\',
    `state` VARCHAR(128) NULL,
    PRIMARY KEY (`id_mercanet_schedule`),
	KEY (`id_order`),
	KEY (`id_mercanet_transaction`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

// MERCANET ORDER QUEUE
$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mercanet_order_queue` (
    `id_mercanet_order_queue` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_cart` INT(10) NOT NULL,
    `id_order` INT(10) NOT NULL,
    `source` VARCHAR(128) NOT NULL,
    `date_add` DATETIME,
    `date_done` DATETIME,
    PRIMARY KEY (`id_mercanet_order_queue`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

// MERCANET PAYMENT RECURRING
$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mercanet_payment_recurring` (
    `id_mercanet_payment_recurring` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_product` INT(10) NOT NULL,
    `type` INT(10) NULL,
    `periodicity` VARCHAR(10) NOT NULL,
    `number_occurences` INT(10) NOT NULL,
    `recurring_amount` FLOAT(6) NULL,
    PRIMARY KEY (`id_mercanet_payment_recurring`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

// MERCANET CUSTOMER PAYMENT RECURRING
$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mercanet_customer_payment_recurring` (
    `id_mercanet_customer_payment_recurring` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_product` INT(10) NOT NULL,
    `id_tax_rules_group` INT(10) NOT NULL,
    `id_order` INT(10) NOT NULL,
    `id_customer` INT(10) NOT NULL,
    `id_mercanet_transaction` INT(10) NOT NULL,
    `status` INT(10) NOT NULL,
    `amount_tax_exclude` FLOAT(6) NOT NULL,
    `periodicity` VARCHAR(10) NOT NULL,
    `number_occurences` INT(10) NOT NULL,
    `current_occurence` INT(10) NOT NULL DEFAULT 0,
    `date_add` DATETIME,
    `last_schedule` DATETIME,
    `next_schedule` DATETIME,
    `current_specific_price` INT(10) NOT NULL DEFAULT 0,
    `id_cart_paused_currency` INT(10) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id_mercanet_customer_payment_recurring`),
    KEY (`id_product`),
    KEY (`id_tax_rules_group`),
    KEY (`id_order`),
    KEY (`id_mercanet_transaction`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mercanet_reference_payed` (
    `order_reference` varchar(128) NOT NULL,
    `source` varchar(128) NOT NULL,
    `date_add` datetime DEFAULT NULL,
    PRIMARY KEY (`order_reference`),
    UNIQUE KEY `order_reference` (`order_reference`)
  ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

// --- UPGRADE & ALTER --- //
$sql[] = '
    SET @preparedStatement = (SELECT IF(
        (SELECT COUNT(*)
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE  table_name = "'._DB_PREFIX_.'mercanet_transaction"
        AND table_schema = DATABASE()
        AND column_name = "id_order_recurring"
        ) > 0,
        "SELECT 1",
        "ALTER TABLE `'._DB_PREFIX_.'mercanet_transaction` ADD `id_order_recurring` INT(10) NULL DEFAULT 0;"
    ));

    PREPARE alterIfNotExists FROM @preparedStatement;
    EXECUTE alterIfNotExists;
    DEALLOCATE PREPARE alterIfNotExists;
';

// --- DATA --- //
$id_lang_fr = Language::getIdByIso('fr');
$id_lang_en = Language::getIdByIso('en');
if (empty($id_lang_en)) {
    if ($id_lang_fr != Configuration::getGlobalValue('PS_LANG_DEFAULT')) {
        $id_lang_en = Configuration::getGlobalValue('PS_LANG_DEFAULT');
    } else {
        $id_lang_en = 0;
    }
}

// MERCANET NX PAYMENT DATA
$sql[] = 'TRUNCATE TABLE `'._DB_PREFIX_.'mercanet_nx_payment`';
$sql[] = 'TRUNCATE TABLE `'._DB_PREFIX_.'mercanet_nx_payment_lang`';

$sql[] = 'INSERT INTO `'._DB_PREFIX_.'mercanet_nx_payment` (`id_mercanet_nx_payment`,`number`,`periodicity`,`minimum_amount`,`maximum_amount`,`first_payment`,`active`)
			VALUES
				(1,2,29,0,0,50,1),
				(2,3,29,0,0,33,1)
';

$sql[] = 'INSERT INTO `'._DB_PREFIX_.'mercanet_nx_payment_lang` (`id_mercanet_nx_payment`,`id_lang`,`method_name`)
			VALUES
				(1,'.pSQL((int)$id_lang_en).',"Pay in 2x"),
				(1,'.pSQL((int)$id_lang_fr).',"Paiement en 2x"),

				(2,'.pSQL((int)$id_lang_en).',"Pay in 3x"),
				(2,'.pSQL((int)$id_lang_fr).',"Paiement en 3x")
';

// MERCANET RESPONSE CODE DATA
$sql[] = 'TRUNCATE TABLE `'._DB_PREFIX_.'mercanet_response_code`';
$sql[] = 'TRUNCATE TABLE `'._DB_PREFIX_.'mercanet_response_code_lang`';

$sql[] = 'INSERT INTO `'._DB_PREFIX_.'mercanet_response_code` (`id_mercanet_response_code`)
			VALUES
				("00"),
				("02"),
				("03"),
				("05"),
				("11"),
				("12"),
				("14"),
				("17"),
				("24"),
				("25"),
				("30"),
				("34"),
				("40"),
				("51"),
				("54"),
				("60"),
				("63"),
				("75"),
				("90"),
				("94"),
				("97"),
				("99")
';

$sql[] = 'INSERT INTO `'._DB_PREFIX_.'mercanet_response_code_lang` (`id_mercanet_response_code`,`id_lang`, `message`)
			VALUES
				("00", '.pSQL((int)$id_lang_en).',"Authorisation accepted"),
				("00", '.pSQL((int)$id_lang_fr).',"Autorisation acceptée"),

				("02", '.pSQL((int)$id_lang_en).',"Authorisation request to be performed via telephone with the issuer, as the card authorisation threshold has been exceeded. You need to be authorised to force transactions"),
				("02", '.pSQL((int)$id_lang_fr).',"Demande d’autorisation par téléphone à la banque à cause d’un dépassement du plafond d’autorisation sur la carte, si vous êtes autorisé à forcer les transactions."),

				("03", '.pSQL((int)$id_lang_en).',"Invalid Merchant contract"),
				("03", '.pSQL((int)$id_lang_fr).',"Contrat commerçant invalide"),

				("05", '.pSQL((int)$id_lang_en).',"Authorisation refused"),
				("05", '.pSQL((int)$id_lang_fr).',"Autorisation refusée"),

				("11", '.pSQL((int)$id_lang_en).',"Used for differed check. The PAN is blocked."),
				("11", '.pSQL((int)$id_lang_fr).',"Utilisé pour plusieurs contrôle, la carte est bloquée"),

				("12", '.pSQL((int)$id_lang_en).',"Invalid transaction, check the request parameters"),
				("12", '.pSQL((int)$id_lang_fr).',"Transaction invalide, vérifier les paramètres transférés dans la requête"),

				("14", '.pSQL((int)$id_lang_en).',"Invalid PAN or payment mean data (ex: card security code)"),
				("14", '.pSQL((int)$id_lang_fr).',"Coordonnées du moyen de paiement invalides (ex: n° de carte ou cryptogramme visuel de la carte)"),

				("17", '.pSQL((int)$id_lang_en).',"Buyer cancellation"),
				("17", '.pSQL((int)$id_lang_fr).',"Annulation de l’internaute"),

				("24", '.pSQL((int)$id_lang_en).',"Operation not authorized. The operation you wish to perform is not compliant with the transaction status"),
				("24", '.pSQL((int)$id_lang_fr).',"Opération impossible. L\'opération que vous souhaitez réaliser n’est pas compatible avec l\'état de la transaction."),

				("25", '.pSQL((int)$id_lang_en).',"Transaction unknown by Sips"),
				("25", '.pSQL((int)$id_lang_fr).',"Transaction non trouvée dans la base de données Mercanet"),

				("30", '.pSQL((int)$id_lang_en).',"Format error"),
				("30", '.pSQL((int)$id_lang_fr).',"Erreur de format"),

				("34", '.pSQL((int)$id_lang_en).',"Fraud suspicion"),
				("34", '.pSQL((int)$id_lang_fr).',"Suspicion de fraude"),

				("40", '.pSQL((int)$id_lang_en).',"Function not supported: the operation that you wish to perform is not part of the operation type for which you are authorised "),
				("40", '.pSQL((int)$id_lang_fr).',"Fonction non supportée : l\'opération que vous souhaitez réaliser ne fait pas partie de la liste des opérations auxquelles vous êtes autorisés"),

				("51", '.pSQL((int)$id_lang_en).',"Amount too high"),
				("51", '.pSQL((int)$id_lang_fr).',"Montant trop élevé"),

				("54", '.pSQL((int)$id_lang_en).',"Payment mean expiry date is past"),
				("54", '.pSQL((int)$id_lang_fr).',"Date de validité du moyen de paiement est dépassée"),

				("60", '.pSQL((int)$id_lang_en).',"Transaction pending"),
				("60", '.pSQL((int)$id_lang_fr).',"Transaction en attente"),

				("63", '.pSQL((int)$id_lang_en).',"Security rules not observed, transaction stopped"),
				("63", '.pSQL((int)$id_lang_fr).',"Règles de sécurité non respectées, transaction arrêtée"),

				("75", '.pSQL((int)$id_lang_en).',"Exceeded number of PAN attempts"),
				("75", '.pSQL((int)$id_lang_fr).',"Nombre de tentatives de saisie des coordonnées du moyen de paiement dépassé"),

				("90", '.pSQL((int)$id_lang_en).',"Service temporarily not available"),
				("90", '.pSQL((int)$id_lang_fr).',"Service temporairement indisponible"),

				("94", '.pSQL((int)$id_lang_en).',"Duplicated transaction: the transactionReference has been used previously"),
				("94", '.pSQL((int)$id_lang_fr).',"Transaction dupliquée : le transactionReference de la transaction a déjà été utilisé"),

				("97", '.pSQL((int)$id_lang_en).',"Time frame exceeded, transaction refused"),
				("97", '.pSQL((int)$id_lang_fr).',"Délais expiré, transation refusée"),

				("99", '.pSQL((int)$id_lang_en).',"Temporary problem at the Sips server level"),
				("99", '.pSQL((int)$id_lang_fr).',"Problème temporaire au niveau du serveur Mercanet")
';

// MERCANET ACQUIRER RESPONSE CODE DATA
$sql[] = 'TRUNCATE TABLE `'._DB_PREFIX_.'mercanet_acquirer_response_code`';
$sql[] = 'TRUNCATE TABLE `'._DB_PREFIX_.'mercanet_acquirer_response_code_lang`';

$sql[] = 'INSERT INTO `'._DB_PREFIX_.'mercanet_acquirer_response_code` (`id_mercanet_acquirer_response_code`)
			VALUES
				("00"),
				("02"),
				("03"),
				("04"),
				("05"),
				("07"),
				("08"),
				("12"),
				("13"),
				("14"),
				("15"),
				("17"),
				("24"),
				("25"),
				("30"),
				("31"),
				("33"),
				("34"),
				("40"),
				("41"),
				("43"),
				("51"),
				("54"),
				("56"),
				("57"),
				("58"),
				("59"),
				("60"),
				("61"),
				("62"),
				("63"),
				("65"),
				("68"),
				("75"),
				("87"),
				("90"),
				("91"),
				("92"),
				("94"),
				("96"),
				("97"),
				("98"),
				("99")
';

$sql[] = 'INSERT INTO `'._DB_PREFIX_.'mercanet_acquirer_response_code_lang` (`id_mercanet_acquirer_response_code`,`id_lang`, `message`)
			VALUES
				("00", '.pSQL((int)$id_lang_en).',"Transaction approved or processed successfully"),
				("00", '.pSQL((int)$id_lang_fr).',"Transaction approuvée ou traitée avec succès"),

				("02", '.pSQL((int)$id_lang_en).',"Contact payment mean issuer"),
				("02", '.pSQL((int)$id_lang_fr).',"Contactez l\'émetteur du moyen de paiement"),

				("03", '.pSQL((int)$id_lang_en).',"Invalid acceptor"),
				("03", '.pSQL((int)$id_lang_fr).',"Accepteur invalide"),

				("04", '.pSQL((int)$id_lang_en).',"Keep the payment mean"),
				("04", '.pSQL((int)$id_lang_fr).',"Conservez le support du moyen de paiement"),

				("05", '.pSQL((int)$id_lang_en).',"Do not honour"),
				("05", '.pSQL((int)$id_lang_fr).',"Ne pas honorer"),

				("07", '.pSQL((int)$id_lang_en).',"Keep the payment mean, special conditions"),
				("07", '.pSQL((int)$id_lang_fr).',"Conservez le support du moyen de paiement, conditions spéciales"),

				("08", '.pSQL((int)$id_lang_en).',"Approve after identification"),
				("08", '.pSQL((int)$id_lang_fr).',"Approuvez après l\'identification"),

				("12", '.pSQL((int)$id_lang_en).',"Invalid transaction"),
				("12", '.pSQL((int)$id_lang_fr).',"Transaction invalide"),

				("13", '.pSQL((int)$id_lang_en).',"Invalid amount"),
				("13", '.pSQL((int)$id_lang_fr).',"Montant invalide"),

				("14", '.pSQL((int)$id_lang_en).',"Invalid PAN"),
				("14", '.pSQL((int)$id_lang_fr).',"Coordonnées du moyen de paiement invalides"),

				("15", '.pSQL((int)$id_lang_en).',"Unknown payment mean issuer"),
				("15", '.pSQL((int)$id_lang_fr).',"Émetteur du moyen de paiement inconnu"),

				("17", '.pSQL((int)$id_lang_en).',"Payment aborted by the buyer"),
				("17", '.pSQL((int)$id_lang_fr).',"Paiement interrompu par l\'acheteur"),

				("24", '.pSQL((int)$id_lang_en).',"Operation not authorised"),
				("24", '.pSQL((int)$id_lang_fr).',"Opération impossible"),

				("25", '.pSQL((int)$id_lang_en).',"Transaction not found"),
				("25", '.pSQL((int)$id_lang_fr).',"Transaction inconnue"),

				("30", '.pSQL((int)$id_lang_en).',"Format error"),
				("30", '.pSQL((int)$id_lang_fr).',"Erreur de format"),

				("31", '.pSQL((int)$id_lang_en).',"Id of the acquiring organisation unknown"),
				("31", '.pSQL((int)$id_lang_fr).',"Id de l\'organisation d\'acquisition inconnu"),

				("33", '.pSQL((int)$id_lang_en).',"Payment mean expired"),
				("33", '.pSQL((int)$id_lang_fr).',"Moyen de paiement expiré"),

				("34", '.pSQL((int)$id_lang_en).',"Fraud suspicion"),
				("34", '.pSQL((int)$id_lang_fr).',"Suspicion de fraude"),

				("40", '.pSQL((int)$id_lang_en).',"Function not supported"),
				("40", '.pSQL((int)$id_lang_fr).',"Fonction non supportée"),

				("41", '.pSQL((int)$id_lang_en).',"Payment mean lost"),
				("41", '.pSQL((int)$id_lang_fr).',"Moyen de paiement perdu"),

				("43", '.pSQL((int)$id_lang_en).',"Payment mean stolen"),
				("43", '.pSQL((int)$id_lang_fr).',"Moyen de paiement volé"),

				("51", '.pSQL((int)$id_lang_en).',"Insufficient or exceeded credit"),
				("51", '.pSQL((int)$id_lang_fr).',"Provision insuffisante ou crédit dépassé"),

				("54", '.pSQL((int)$id_lang_en).',"Payment mean expired"),
				("54", '.pSQL((int)$id_lang_fr).',"Moyen de paiement expiré"),

				("56", '.pSQL((int)$id_lang_en).',"Payment mean missing from the file"),
				("56", '.pSQL((int)$id_lang_fr).',"Moyen de paiement manquant dans le fichier"),

				("57", '.pSQL((int)$id_lang_en).',"Transaction unauthorised for this payment mean holder"),
				("57", '.pSQL((int)$id_lang_fr).',"Transaction non autorisée pour ce porteur"),

				("58", '.pSQL((int)$id_lang_en).',"Transaction forbidden to the terminal"),
				("58", '.pSQL((int)$id_lang_fr).',"Transaction interdite au terminal"),

				("59", '.pSQL((int)$id_lang_en).',"Fraud suspicion"),
				("59", '.pSQL((int)$id_lang_fr).',"Suspicion de fraude"),

				("60", '.pSQL((int)$id_lang_en).',"The payment mean acceptor must contact the acquirer"),
				("60", '.pSQL((int)$id_lang_fr).',"L\'accepteur du moyen de paiement doit contacter l\'acquéreur"),

				("61", '.pSQL((int)$id_lang_en).',"Exceeds the amount limit"),
				("61", '.pSQL((int)$id_lang_fr).',"Excède le maximum autorisé"),

				("62", '.pSQL((int)$id_lang_en).',"Transaction awaiting payment confirmation"),
				("62", '.pSQL((int)$id_lang_fr).',"Transaction en attente de confirmation de paiement"),

				("63", '.pSQL((int)$id_lang_en).',"Security rules not complied with"),
				("63", '.pSQL((int)$id_lang_fr).',"Règles de sécurité non respectées"),

				("65", '.pSQL((int)$id_lang_en).',"Allowed number of daily transactions has been exceeded"),
				("65", '.pSQL((int)$id_lang_fr).',"Nombre de transactions du jour dépassé"),

				("68", '.pSQL((int)$id_lang_en).',"Response not received or received too late"),
				("68", '.pSQL((int)$id_lang_fr).',"Réponse non parvenue ou reçue trop tard"),

				("75", '.pSQL((int)$id_lang_en).',"Exceeded number of PAN attempts"),
				("75", '.pSQL((int)$id_lang_fr).',"Nombre de tentatives de saisie des coordonnées du moyen de paiement dépassé"),

				("87", '.pSQL((int)$id_lang_en).',"Terminal unknown"),
				("87", '.pSQL((int)$id_lang_fr).',"Terminal inconnu"),

				("90", '.pSQL((int)$id_lang_en).',"System temporarily stopped"),
				("90", '.pSQL((int)$id_lang_fr).',"Arrêt momentané du système"),

				("91", '.pSQL((int)$id_lang_en).',"Payment mean issuer inaccessible"),
				("91", '.pSQL((int)$id_lang_fr).',"Emetteur du moyen de paiement inaccessible"),

				("92", '.pSQL((int)$id_lang_en).',"The transaction does not contain enough information to be routed to the authorizing agency"),
				("92", '.pSQL((int)$id_lang_fr).',"La transaction ne contient pas les informations suffisantes pour être redirigées vers l\'organisme d\'autorisation"),

				("94", '.pSQL((int)$id_lang_en).',"Duplicated transaction"),
				("94", '.pSQL((int)$id_lang_fr).',"Transaction dupliquée"),

				("96", '.pSQL((int)$id_lang_en).',"System malfunction"),
				("96", '.pSQL((int)$id_lang_fr).',"Mauvais fonctionnement du système"),

				("97", '.pSQL((int)$id_lang_en).',"Request time-out; transaction refused"),
				("97", '.pSQL((int)$id_lang_fr).',"Requête expirée: transaction refusée"),

				("98", '.pSQL((int)$id_lang_en).',"Server unavailable; network routing requested again"),
				("98", '.pSQL((int)$id_lang_fr).',"Serveur inaccessible"),

				("99", '.pSQL((int)$id_lang_en).',"Incident with initiator domain"),
				("99", '.pSQL((int)$id_lang_fr).',"Incident technique")

';

// MERCANET COMPLEMNTARY CODE DATA
$sql[] = 'TRUNCATE TABLE `'._DB_PREFIX_.'mercanet_complementary_code`';
$sql[] = 'TRUNCATE TABLE `'._DB_PREFIX_.'mercanet_complementary_code_lang`';

$sql[] = 'INSERT INTO `'._DB_PREFIX_.'mercanet_complementary_code` (`id_mercanet_complementary_code`)
			VALUES
				("00"),
				("02"),
				("03"),
				("04"),
				("05"),
				("06"),
				("07"),
				("08"),
				("09"),
				("10"),
				("11"),
				("12"),
				("13"),
				("14"),
				("15"),
				("16"),
				("17"),
				("18"),
				("19"),
				("20"),
				("21"),
				("22"),
				("3L"),
				("99")
';

$sql[] = 'INSERT INTO `'._DB_PREFIX_.'mercanet_complementary_code_lang` (`id_mercanet_complementary_code`,`id_lang`, `message`)
			VALUES
				("00", '.pSQL((int)$id_lang_en).',"All  controls  that  you  adhered  to  have  been  successfully completed"),
				("00", '.pSQL((int)$id_lang_fr).',"Tous les contrôles auxquels vous avez adhérés se sont effectués avec succès"),

				("02", '.pSQL((int)$id_lang_en).',"The card used has exceeded the authorised balance limit"),
				("02", '.pSQL((int)$id_lang_fr).',""),

				("03", '.pSQL((int)$id_lang_en).',"The card used is on the merchant\'s « grey list »"),
				("03", '.pSQL((int)$id_lang_fr).',"La carte utilisée appartient à la « liste grise » du commerçant"),

				("05", '.pSQL((int)$id_lang_en).',"The  BIN  of  the  card  used  belongs  to  a  range  which  is  not referenced on Sip\'s platform BIN table"),
				("05", '.pSQL((int)$id_lang_fr).',"Le BIN de la carte utilisée appartient à une plage non référencée dans la table des BIN de la plate-forme Mercanet"),

				("06", '.pSQL((int)$id_lang_en).',"The country code related to the card number is not on the list of countries allowed by the merchant"),
				("06", '.pSQL((int)$id_lang_fr).',"Le numéro de carte n\'est pas dans une plage de même nationalité que celle du commerçant"),

				("07", '.pSQL((int)$id_lang_en).',"Virtual card (e-card) detected"),
				("07", '.pSQL((int)$id_lang_fr).',"e-Carte Bleue détectée"),

				("08", '.pSQL((int)$id_lang_en).',"The card BIN is present in a range on the merchant\'s « grey list »"),
				("08", '.pSQL((int)$id_lang_fr).',"Plage de BIN KO"),

				("09", '.pSQL((int)$id_lang_en).',"Unknown country IP"),
				("09", '.pSQL((int)$id_lang_fr).',"Pays IP inconnu"),

				("10", '.pSQL((int)$id_lang_en).',"Denied country IP "),
				("10", '.pSQL((int)$id_lang_fr).',"Pays IP interdit"),

				("11", '.pSQL((int)$id_lang_en).',"Card in hot/black list"),
				("11", '.pSQL((int)$id_lang_fr).',"Carte dans OPPOTOTA"),

				("12", '.pSQL((int)$id_lang_en).',"Country card / IP address country combination"),
				("12", '.pSQL((int)$id_lang_fr).',"Combinaison pays carte/IP interdite"),

				("13", '.pSQL((int)$id_lang_en).',"Unknown country IP or card. The country code cannot be determined from the card number"),
				("13", '.pSQL((int)$id_lang_fr).',"Pays IP ou carte inconnu. Le code pays n\'est pas déterminable à partir du numéro de carte"),

				("14", '.pSQL((int)$id_lang_en).',"Systematic authorisation card"),
				("14", '.pSQL((int)$id_lang_fr).',"Carte à autorisation systématique"),

				("15", '.pSQL((int)$id_lang_en).',"Unknown BIN (on control of systematic authorisation card) "),
				("15", '.pSQL((int)$id_lang_fr).',"BIN inconnu (sur le contrôle de carte à autorisation systématique)"),

				("16", '.pSQL((int)$id_lang_en).',"IP address in progress exceeded"),
				("16", '.pSQL((int)$id_lang_fr).',"En-cours IP KO"),

				("17", '.pSQL((int)$id_lang_en).',"Blocking related the status of the 3-D Secure authentication process "),
				("17", '.pSQL((int)$id_lang_fr).',"Blocage dû au résultat du processus d’authentification 3D Secure"),

				("18", '.pSQL((int)$id_lang_en).',"The card number is a commercial card number"),
				("18", '.pSQL((int)$id_lang_fr).',"Le numéro de carte correspond à un numéro de carte commerciale"),

				("19", '.pSQL((int)$id_lang_en).',"The card number is not part of the CB scheme"),
				("19", '.pSQL((int)$id_lang_fr).',"Le numéro de carte n\'appartient pas au réseau CB"),

				("20", '.pSQL((int)$id_lang_en).',"Customer ID in progress exceeded "),
				("20", '.pSQL((int)$id_lang_fr).',"En-cours client dépassé"),

				("21", '.pSQL((int)$id_lang_en).',"Maximum number of customer ID per card exceeded"),
				("21", '.pSQL((int)$id_lang_fr).',"En-cours client par carte dépassé"),

				("22", '.pSQL((int)$id_lang_en).',"Maximum number of cards per customer ID exceeded"),
				("22", '.pSQL((int)$id_lang_fr).',"En-cours de carte par client dépassé"),

				("3L", '.pSQL((int)$id_lang_en).',"Reason of the refusal of the transaction which is the transaction is not guaranteed by any entity (acquirer, wallet provider, etc.)"),
				("3L", '.pSQL((int)$id_lang_fr).',"Refus de la transaction en raison de non garantie de la transaction par une entité (l\'acquéreur, le fournisseur de portefeuille, etc)"),

				("99", '.pSQL((int)$id_lang_en).',"The Sips server encountered a problem during the processing of one of the additional local checks"),
				("99", '.pSQL((int)$id_lang_fr).',"Le serveur Mercanet a un rencontré un problème lors du traitement d’un des contrôles locaux complémentaires")

';

$query = '
    SELECT * FROM INFORMATION_SCHEMA.COLUMNS
    WHERE COLUMN_NAME= "received_data"
    AND TABLE_NAME= "'._DB_PREFIX_.'mercanet_reference_payed"
    AND TABLE_SCHEMA = "'._DB_NAME_.'"
';

$result = Db::getInstance()->ExecuteS($query);

if (!$result) {
    $sql[] = 'ALTER TABLE `'._DB_PREFIX_.'mercanet_reference_payed` ADD `received_data` TEXT NOT NULL AFTER `date_add`';
}

//--- EXECUTE SQL --- //
foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
