<?php
/**
 * WMailBuilder class file.
 *
 * @author Michal Lupatus Kluszewski <lupatus@gmail.com>
 * @link http://yii.lupatus.com/wmailer
 * @copyright Copyright &copy; 2011 Lupatus.com
 * @license http://yii.lupatus.com/license/
 */


$dir = dirname(__FILE__) . '/';
require_once($dir . 'WMailBase.php');
require_once($dir . 'WMailPart.php');
require_once($dir . 'WMailPartMulti.php');
require_once($dir . 'WMailPartAlternative.php');
require_once($dir . 'WMailPartRelated.php');
require_once($dir . 'WMailPartFile.php');
require_once($dir . 'WMailPartText.php');
require_once($dir . 'WMailPartHtml.php');
unset($dir);

/**
 * WMailBuilder is MIME message creator class
 *
 * @property string $from From address
 * @property string[]|null $to To addresses
 * @property string[]|null $cc Cc addresses
 * @property string[]|null $bcc Bcc addresses
 * @property string $subject Message subject (quoted printable)
 * @property WMailPartHtml $html HTML message part
 * @property WMailPartText $text Text message part
 * @property WMailPart|null $message Message main part
 * @property string $data Message body
 * @property string $headers Message headers
 */
class WMailBuilder extends WMailBase {
    
    /**
     * @var WMailPartText $_text Text message part
     */
    protected $_text;
    
    /**
     * @var WMailPartHtml $_html HTML message part
     */
    protected $_html;
    
    /**
     * @var WMailPartFile[] $_files Message attachments
     */
    protected $_files = array();
    
    /**
     * @var WMailPart $_message Message main part
     */
    protected $_message;
    
    /**
     * @var WSendMailAbstract $_sendmail Mail message transport
     */
    protected $_sendmail;
    
    /**
     * @var string[] $_to To addresses list
     */
    protected $_to;
    
    /**
     * @var string $_from From address
     */
    protected $_from;
    
    /**
     * @var string[] $_cc Cc addresses list
     */
    protected $_cc;
    
    /**
     * @var string[] $_bcc Bcc addresses list
     */
    protected $_bcc;
    
    /**
     * @var string $_subject Message subject (quoted printable string)
     */
    protected $_subject = '';
    
    /**
     * @param WSendMailAbstract Mail message transport
     */
    public function __construct(WSendMailAbstract $sendmail) {
        $this->_sendmail = $sendmail;
    }
    
    /**
     * Parse addresses list
     *
     * @param string|string[] $address addresses list
     *
     * @return string|string[]
     */
    protected function _parseAddress($address) {
        if (is_string($address) && false !== strpos($address, ',')) {
            $address = explode(',', $address);
        }
        if (is_array($address)) {
            $tmp = array();
            foreach ($address as $a) {
                $a = $this->_parseAddress($a);
                if ($a) {
                    $tmp[] = $a;
                }
            }
            return $tmp;
        }
        if (is_string($address) && preg_match('/([a-z0-9_\.\-]+@[a-z0-9_\.\-]+)/i', $address, $m)) {
            return $m[1];
        }
        return null;
    }
    
    /**
     * Returns From email address
     *
     * @return string|null
     */
    public function getFrom() {
        return $this->_from;
    }
    
    /**
     * Sets recipients list to proper header name
     *
     * @param string $header Header name
     * @param string|array Email addresses list
     * 
     * @return string[]
     */
    public function _setRecipients($header, $address) {
        $address = $this->_parseAddress($address);
        if (!is_array($address)) {
            $address = array($address);
        }
        if ($address === array()) {
            if (isset($this->_headers[$header])) {
                unset($this->_headers[$header]);
            }
        } else {
            $this->_headers[$header] = implode(',', $address);
        }
        return $address;
    }
    
    /**
     * Sets from email address
     *
     * @param string $from From address
     */
    public function setFrom($from) {
        $this->_from = array_shift(($f = $this->_setRecipients('From', $from)));
    }
    
    /**
     * Sets Reply-To address
     *
     * @param string $replyTp Reply-To email address
     */
    public function setReplyTo($replyTo) {
        $this->_setRecipients('Reply-To', $replyTo);
    }
    
    /**
     * Setts to addresses list
     *
     * @param string|string[] $to To addresses list
     */
    public function setTo($to) {
        $this->_to = $this->_setRecipients('To', $to);
    }
    
    /**
     * Setts cc addresses list
     *
     * @param string|string[] $cc Cc addresses list
     */
    public function setCc($cc) {
        $this->_cc = $this->_setRecipients('Cc', $cc);
    }
    
    /**
     * Setts bcc addresses list
     *
     * @param string|string[] $bcc Bcc addresses list
     */
    public function setBcc($bcc) {
        if (!is_array($bcc)) {
            $bcc = array($bcc);
        }
        $this->_bcc = $this->_parseAddress($bcc);
    }
    
    /**
     * Returns To addresses list
     *
     * @return string[]|null
     */
    public function getTo() {
        return $this->_to;
    }
    
    /**
     * Returns Cc addresses list
     *
     * @return string[]|null
     */
    public function getCc() {
        return $this->_cc;
    }
    
    /**
     * Returns Bcc addresses list
     *
     * @return string[]|null
     */
    public function getBcc() {
        return $this->_bcc;
    }
    
    /**
     * Sets message subject
     *
     * @param string $subject Message subject
     */
    public function setSubject($subject) {
        $this->addHeader('Subject', ($this->_subject = $this->_encodeHeader($subject)));
    }
    
    /**
     * Returns message subject
     *
     * @return string Message subject (quoted printable string)
     */
    public function getSubject() {
        return $this->_subject;
    }
    
    /**
     * Returns HTML message part
     *
     * @return WMailPartHtml
     */
    public function getHtml() {
        if (!isset($this->_html)) {
            $this->_message = null;
            $this->_html = new WMailPartHtml();
        }
        return $this->_html;
    }
    
    /**
     * Sets html message
     * 
     * @param string $html Html message
     */
    public function setHtml($html) {
        $this->html->data = $html;
    }
    
    /**
     * Renders HTML message contents from template
     *
     * @param string $template Template name
     * @param array $params Template vriables
     */
    public function renderHtml($template, $params = array()) {
        $this->html->render($template, $params);
    }
    
    /**
     * Returns text message part
     *
     * @return WMailPartText text part
     */
    public function getText() {
        if (!isset($this->_text)) {
            $this->_message = null;
            $this->_text = new WMailPartText();
        }
        return $this->_text;
    }
    
    /**
     * Sets text message 
     * 
     * @param string $text Text message
     */
    public function setText($text) {
        $this->text->data = $text;
    }
    
    /**
     * Renders text message contents from template
     *
     * @param string $template template name
     * @param array $params template variables
     */
    public function renderText($template, $params = array()) {
        $this->text->render($template, $params);
    }
    
    /**
     * Ads file attachment
     *
     * @param string $path File path
     * @param string $mime MIME type
     * @param string $name File name
     */
    public function addFile($path, $mime, $name = null) {
        if (!$name) {
            $name = basename($path);
        }
        $fp = fopen($path, 'r');
        $data = fread($fp, filesize($path));
        fclosE($fp);
        $this->addFileData($data, $mime, $name);
    }
    
    /**
     * Ads file atachment
     *
     * @param string $data File data
     * @param string $mime MIME type
     * @param string $name File name
     */
    public function addFileData($data, $mime, $name) {
        $this->_message = null;
        $this->_files[] = new WMailPartFile($data, $mime, $name);
    }
    
    /**
     * Returns main message part
     *
     * @return WMailPart|null
     */
    protected function getMessage() {
        if (!isset($this->_message)) {
            $message = array();
            if (isset($this->_text) && isset($this->_html)) {
                $message[] = new WMailPartAlternative($this->_text, $this->_html);
            } else if (isset($this->_text)) {
                $message[] = $this->_text;
            } else if (isset($this->_html)) {
                $message[] = $this->_html->mimePart;
            }
            if ($this->_files !== array()) {
                $message = array_merge($message, $this->_files);
            }
            if (($count = count($message)) > 0) {
                if ($count > 1) {
                    $this->_message = new WMailPartMulti();
                    $this->_message->data = $message;
                    
                } else {
                    $this->_message = $message[0];
                }
            } else {
                return null;
            }
        }
        return $this->_message;
    }
    
    /**
     * Returns message body
     *
     * @return string
     */
    public function getData() {
        $message = $this->message;
        return $message ? $message->data : '';
    }
    
    /**
     * Returns MIME headers string or MIME headers list
     *
     * @param boolean $plain return headers as list
     *
     * @return string|array MIME headers
     */
    public function getHeaders($plain = false) {
        $headers = array_merge(
            array('MIME-Version' => '1.0'),
            ($this->message ? $this->message->getHeaders(true) : array()),
            $this->_headers
        );
        if ($plain) {
            return $headers;
        }
        return $this->_renderHeaders($headers);
    }
    
    /**
     * Sends message
     *
     * @throws WSendMailError
     */
    public function send() {
        $this->_sendmail->send($this);
    }
    
}