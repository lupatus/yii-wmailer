<?php
/**
 * WMailPartRelated class file.
 *
 * @author Michal Lupatus Kluszewski <lupatus@gmail.com>
 * @link http://yii.lupatus.com/wmailer
 * @copyright Copyright &copy; 2011 Lupatus.com
 * @license http://yii.lupatus.com/license/
 */

/**
 * WMailPartRelated is miltipart/alternative MIME message part class
 */
class WMailPartRelated extends WMailPartMulti {
    
    /**
     * @var string $_contentType Part content type
     */
    protected $_contentType = 'multipart/related';
    
    /**
     * @var WMailPartHtml Html part
     */
    protected $_html;
    
    /**
     * @param WMailPart $html Html part
     */
    public function __construct(WMailPartHtml $html) {
        $this->_html = $html;
    }
    
    /**
     * Returns message MIME headers string or list
     *
     * @param boolean $plain Return headers as list
     *
     * @return string|array
     */
    public function getHeaders($plain = false) {
        if ($this->_parts === array()) {
            return $this->_html->headers;
        }
        return parent::getHeaders($plain);
    }
    
    /**
     * Returns parts body
     *
     * @return string
     */
    public function getData() {
        if ($this->_parts === array()) {
            return $this->_html->data;
        }
        return $this->_renderParts(array_merge(
            array($this->_html),
            $this->_parts
        ));
    }
    
}