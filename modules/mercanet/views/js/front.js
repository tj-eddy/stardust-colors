/**
* 1961-2016 BNP Paribas
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    Quadra Informatique <modules@quadra-informatique.fr>
*  @copyright 1961-2016 BNP Paribas
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  
*
* Don't forget to prefix your containers with your own identifier
* to avoid any conflicts with others containers.
*/
$(document).ready(function(){
	$('#stop_recurring').click(function(e){
        e.preventDefault();
        $('.stop_recurring_confirmation').show();
    });
	$('#confirm_stop_recurring').click(function(e){
       $('#mercanet_stop_recurring_form').submit();
    });
    $('#noconfirm_stop_recurring').click(function(e){
        $('.stop_recurring_confirmation').hide();
    });
    $('.stop_recurring_confirmation_information').delay(2500).hide(500);




});

