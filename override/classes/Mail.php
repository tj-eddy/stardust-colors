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
class Mail extends MailCore
{
    /**
     * Send Email
     * [Mercanet] Do not send an email for order create by Recurring Payment
     *
     * @param int $id_lang Language ID of the email (to translate the template)
     * @param string $template Template: the name of template not be a var but a string !
     * @param string $subject Subject of the email
     * @param string $template_vars Template variables for the email
     * @param string $to To email
     * @param string $to_name To name
     * @param string $from From email
     * @param string $from_name To email
     * @param array $file_attachment Array with three parameters (content, mime and name). You can use an array of array to attach multiple files
     * @param bool $mode_smtp SMTP mode (deprecated)
     * @param string $template_path Template path
     * @param bool $die Die after error
     * @param int $id_shop Shop ID
     * @param string $bcc Bcc recipient (email address)
     * @param string $reply_to Email address for setting the Reply-To header
     * @return bool|int Whether sending was successful. If not at all, false, otherwise amount of recipients succeeded.
     */
    /*
    * module: mercanet
    * date: 2023-04-26 07:52:47
    * version: 1.6.12
    */
    public static function Send(
        $idLang,
        $template,
        $subject,
        $templateVars,
        $to,
        $toName = null,
        $from = null,
        $fromName = null,
        $fileAttachment = null,
        $mode_smtp = null,
        $templatePath = _PS_MAIL_DIR_,
        $die = false,
        $idShop = null,
        $bcc = null,
        $replyTo = null,
        $replyToName = null
    ) {
        if (isset($template_vars['mercanet_order_recurring']) && $template_vars['mercanet_order_recurring'] == true && $template == 'order_conf') {
            return true;
        }
        return parent::Send(
            $idLang,
            $template,
            $subject,
            $templateVars,
            $to,
            $toName ,
            $from ,
            $fromName ,
            $fileAttachment,
            $mode_smtp,
            $templatePath,
            $die ,
            $idShop ,
            $bcc ,
            $replyTo,
            $replyToName
        );
    }
}
