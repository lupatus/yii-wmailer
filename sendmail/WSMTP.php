<?php
/**
 * WSMTP class file.
 *
 * @author Michal Lupatus Kluszewski <lupatus@gmail.com>
 * @link http://yii.lupatus.com/wmailer
 * @copyright Copyright &copy; 2011 Lupatus.com
 * @license http://yii.lupatus.com/license/
 */

/**
 * SMTP error exception
 */
class WSMTPError extends WSendMailError {
    
    /**
     * Returns string formatted message
     *
     * @return string
     */
    public function __toString() {
        return 'SMTP Error (' . $this->getCode() . '): ' . $this->getMessage() . "\n" . $this->getTraceAsString();
    }
    
}

/**
 * WSMTP class sends email messages using SMTP protocol
 */
class WSMTP extends WSendMailAbstract {
    
    const CR         = "\r";
    const LF         = "\n";
    const CRLF       = "\r\n";
    
    /**
     * $var string $host SMTP server hostname/ip address
     */
    public $host     = null;
    
    /**
     * $var int $port SMTP server port
     */
    public $port     = 25;
    
    /**
     * $var string|null SMTP server username
     */
    public $username = null;
    
    /**
     * $var string|null SMTP server user password
     */
    public $password = null;
    
    /**
     * $var string helo string, hostname by default
     */
    public $helo     = null;
    
    /**
     * $var int $timeout connection timeout value
     */
    public $timeout  = 5;
    
    /**
     * $var string $endline line ending string
     */
    public $endline  = self::CRLF;
    
    private $_auth        = false;
    private $_headers     = array();
    private $_body        = '';
    private $_recipients  = array();
    private $_connection  = null;
    
    public function __construct() {
        $this->_auth = $this->username && $this->password;
        if (!$this->host || !is_string($this->host)) {
            $this->host = 'localhost';
        }
        if (!$this->helo || !is_string($this->helo)) {
            if (isset($_SERVER) && isset($_SERVER['HTTP_HOST'])) {
                $this->helo    = $_SERVER['HTTP_HOST'];
            } else {
                $this->helo    = 'localhost';
            }
        }
    }
    
    private function _connect() {
        $this->_connection = @fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout);
        if ($errno > 0) {
            throw new WSMTPError($errstr, $errno);
        } else if (!is_resource($this->_connection)) {
            throw new WSMTPError("Couldn't connect to {$this->host}:{$this->port}.", 0);
        }
        $this->_readCheck(220);
        if ($this->_auth) {
            $this->_writeCheck('EHLO ' . $this->helo);
            $this->_writeCheck('AUTH LOGIN', 334);
            $this->_writeCheck(base64_encode($this->username), 334);
            $this->_writeCheck(base64_encode($this->password), 235);
        } else {
            $this->_writeCheck('HELO ' . $this->helo);
        }
    }
    
    private function _write($data) {
        return fwrite($this->_connection, $data . $this->endline);
    }
    
    
    private function _writeCheck($data, $okCode = 250, $exception = true) {
        $this->_write($data);
        $this->_readCheck($okCode, $exception);
    }
    
    private function _readParse() {
        $line = trim($this->_read());
        $code = (int) substr($line, 0, 3);
        $msg  = trim(substr($line, 3));
        return array($msg, $code);
    }
    
    private function _readCheck($okCode = 250, $exception = true) {
        list($msg, $code) = $this->_readParse();
        if ($okCode === $code || (is_array($okCode) && in_array($code, $okCode, true))) {
            return array($msg, $code);
        }
        throw new WSMTPError($msg, $code);
    }
    
    private function _read() {
        $return = '';
        $line   = '';
        $loops  = 0;
        if (is_resource($this->_connection)) {
            while ((strpos($return, $this->endline) === false || substr($line, 3, 1) !== ' ') && $loops < 100){
                $line    = fgets($this->_connection, 512);
                $return .= $line;
                $loops++;
            }
            return $return;
        } else {
            return false;
        }
    }
    
    private function _disconnect() {
        if (is_resource($this->_connection)) {
            // !! should be 221 but sometimes it is 250..
            //$this->_writeCheck('QUIT', 221);
            $this->_writeCheck('QUIT', array(221, 250));
            @fclose($this->_connection);
        }
    }
    
    /**
     * Checks transport settings
     *
     * @return boolean
     *
     * @throws WSMTPError
     */
    public function check() {
        $this->_connect();
        $this->_disconnect();
    }
    
    /**
     * Sends email message using SMTP protocol
     *
     * @param WMailBuilder $message email message
     *
     * @throws WSMTPError
     */
    public function send(WMailBuilder $message) {
        $this->_connect();
        $this->_writeCheck('MAIL FROM: <' . $message->from . '>');
        $undisclosed = true;
        foreach (array('to', 'cc', 'bcc') as $name) {
            $addresses = $message->{$name};
            if (is_array($addresses)) {
                foreach ($addresses as $address) {
                    if ($name == 'to' || $name == 'cc') {
                        $tocc = false;
                    }
                    // !! sometimes not implemented
                    //$this->_writeCheck('RCPT ' . $name . ':<' . $addr . '>');
                    $this->_writeCheck('RCPT TO:<' . $address . '>');
                }
            }
        }
        
        $this->_writeCheck('DATA', 354);
        foreach ($message->getHeaders(true) as $header => $content) {
            $this->_write($header . ': ' . $content);
        }
        if ($undisclosed) {
            $this->_write('To: undisclosed-recipients;');
        }
        $this->_write('');
        $this->_write('.' . $message->data);
        $this->_writeCheck('.');
        $this->_disconnect();
    }
    
    
    
    
    
}