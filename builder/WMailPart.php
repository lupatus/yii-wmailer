<?php
/**
 * WMailPart class file.
 *
 * @author Michal Lupatus Kluszewski <lupatus@gmail.com>
 * @link http://yii.lupatus.com/wmailer
 * @copyright Copyright &copy; 2011 Lupatus.com
 * @license http://yii.lupatus.com/license/
 */

/**
 * WMailPart is basic class for MIME message part classes
 */
abstract class WMailPart extends WMailBase {
    
    /**
     * @var string $_data Part body
     */
    protected $_data    = '';
    
    /**
     * Returns part content (headers + body)
     *
     * @return string Part contents
     */
    public function getContent() {
        return $this->headers . self::NL . self::NL . $this->data;
    }
    
    /**
     * Returns parts body
     *
     * @return string Part body
     */
    public function getData() {
        return $this->_data;
    }
    
    /**
     * Sets part body
     *
     * @param string $_data Part body
     */
    public function setData($data) {
        $this->_data = $data;
    }
    
}