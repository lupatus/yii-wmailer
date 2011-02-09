<?php
/**
 * WMailer class file.
 *
 * @author Michal Lupatus Kluszewski <lupatus@gmail.com>
 * @link http://yii.lupatus.com/wmailer
 * @copyright Copyright &copy; 2011 Lupatus.com
 * @license http://yii.lupatus.com/license/
 */

/**
 * WMailError is base exception class for WMailer module classes exceptions
 */
class WMailError extends Exception {}

/**
 * WMailer module class
 *
 * This class is factory for WMailBuilder and WSendmailAbstract classes
 *
 * @property WSendmailAbstract $sendmail Returns sendmail class instance
 * @property WMailBuilder $newEmail Retuns new mail builder component instance
 */
class WMailer extends CComponent {
    
    private $_classes   = array();
    private $_sendmail  = null;
    private $_sendmailClass;
    private $_sendmailOptions;
    
    /**
     * @var string sendmail class path
     */
    public $sendmailClass = 'default';
    
    /**
     * @var array sendmail class options
     */
    public $sendmailOptions  = array();
    
    /**
     * Module initialize
     */
    public function init() {
        $path = dirname(__FILE__);
        require_once($path . '/builder/WMailBuilder.php');
        require_once($path . '/sendmail/WSendMailIface.php');
        require_once($path . '/sendmail/WSendMailAbstract.php');
        require_once($path . '/sendmail/WSendMailError.php');
        $this->setup($this->sendmailClass, $this->sendmailOptions);
    }
    
    /**
     * Module setup
     *
     * @param string $sendmailClass sendmail class path
     * @param array $options sendmail class options
     */
    public function setup($sendmailClass = 'default', $options = array()) {
        $this->_sendmail = null;
        if (isset($this->_classes[$sendmailClass])) {
            $class = $this->_classes[$sendmailClass];
        } else {
            $class = null;
            switch ($sendmailClass) {
                case 'smtp' :
                    require_once(dirname(__FILE__) . '/sendmail/WSMTP.php');
                    $class = 'WSMTP';
                    break;
                case 'default'   :
                case 'simple'    : 
                    require_once(dirname(__FILE__) . '/sendmail/WSimpleMail.php');
                    $class = 'WSimpleMail';
                    break;
                default : 
                    $class = Yii::import($sendmailClass);
                    break;
            }
            $this->_classes[$sendmailClass] = $class;
        }
        $this->_sendmailClass   = $class;
        $this->_sendmailOptions = is_array($options) ? $options : array();
    }
    
    /**
     * Returns sendmail class instance
     *
     * @return WSendMailAbstract sendmail class instance
     */
    public function getSendmail() {
        if (!isset($this->_sendmail)) {
            $class            = $this->_sendmailClass;
            $this->_sendmail  = new $class();
            foreach ($this->_sendmailOptions as $opt => $value) {
                $this->_sendmail->{$opt} = $value;
            }
            $this->_sendmail->init();
        }
        return $this->_sendmail;
    }
    
    /**
     * Retuns new mail builder component instance
     *
     * @return WMailBuilder
     */
    public function getNewEmail() {
        return new WMailBuilder($this->sendmail);
    }
    
}