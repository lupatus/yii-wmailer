<?php
/**
 * WSimpleMail class file.
 *
 * @author Michal Lupatus Kluszewski <lupatus@gmail.com>
 * @link http://yii.lupatus.com/wmailer
 * @copyright Copyright &copy; 2011 Lupatus.com
 * @license http://yii.lupatus.com/wmailer/license/
 * @package WolfLibs4Yii
 * @subpackage WMailer
 */

/**
 * WSimpleMail is email transport class using imap_mail/mail PHP builtin function
 */
class WSimpleMail extends WSendMailAbstract {
    
    /**
     * Sends email message via imap_mail or mail PHP builtin function
     *
     * @param WMailBuilder $message email message
     * @throws WSendMailError
     */
    public function send(WMailBuilder $message) {
        $addr = array();
        foreach (array('to', 'cc', 'bcc') as $name)  {
            $addresses = $message->{$name};
            if (is_array($addresses)) {
                $addr[$name] = implode(',', $addresses);
            } else {
                $addr[$name] = '';
            }
        }
        if (!$addr['to'] && !$addr['cc']) {
            $addr['to'] = 'undisclosed-recipients;';
        }
        if (function_exists('imap_mail')) {
            $f = 'imap_mail';
            $r = imap_mail(
                $addr['to'],
                $message->subject,
                $message->data,
                $message->headers,
                $addr['cc'],
                $addr['bcc']
            );
        } else {
            $headers = $message->headers;
            if ($addr['bcc']) {
                $headers .= "\r\nBcc: " . $addr['bcc'];
            }
            $f = 'mail';
            $r = mail(
                $addr['to'], 
                $message->subject, 
                $message->data, 
                $headers
            );
        }
        if (!$r) {
            throw new WSendMailError('Sending email via ' . $f . '() function failed.');
        }
        return $r;
    }
    
    
}