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

parse_str('id_shop=1', $args);
$_GET = array_merge($args, $_GET);
$_SERVER['QUERY_STRING'] = 'id_shop=1';

require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/mercanet.php');

$context = Context::getContext();
$shop = $context->shop;

if (!isset($_SERVER['HTTP_HOST']) || empty($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = $shop->domain;
}
if (!isset($_SERVER['SERVER_NAME']) || empty($_SERVER['SERVER_NAME'])) {
    $_SERVER['SERVER_NAME'] = $shop->domain;
}
if (!isset($_SERVER['REMOTE_ADDR']) || empty($_SERVER['REMOTE_ADDR'])) {
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
}

$mercanet = new Mercanet();
// Close the recurring payment
if ($mercanet->isFeatureActivated('ABO')) {
    $mercanet_webservice = new MercanetWebservice();
    $mercanet_webservice->sendRecurringSchedules();
    if ((bool)Configuration::getGlobalValue('MERCANET_LOG_ACTIVE') == true) {
        echo 'The script went smoothly and create new orders';
    }
} else {
    if ((bool)Configuration::getGlobalValue('MERCANET_LOG_ACTIVE') == true) {
        echo 'The script went smoothly but it did not create new orders';
    }
}
