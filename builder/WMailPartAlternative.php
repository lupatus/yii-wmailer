<?php
/**
 * WMailPartAlternative class file.
 *
 * @author Michal Lupatus Kluszewski <lupatus@gmail.com>
 * @link http://yii.lupatus.com/wmailer
 * @copyright Copyright &copy; 2011 Lupatus.com
 * @license http://yii.lupatus.com/license/
 */

/**
 * WMailPartAlternative is miltipart/alternative MIME message part class
 */
class WMailPartAlternative extends WMailPartMulti {
    
    /**
     * @var string $_contentType Part content type
     */
    protected $_contentType = 'multipart/alternative';
    
    /**
     * @var WMailPartText $_text Text part of alternative content
     */
    protected $_text;
    
    /**
     * @var WMailPartText $_text Html part of alternative content
     */
    protected $_html;
    
    /**
     * @param WMailPartText $text Text part of alternative content
     * @param WMailPartHtml $html Html part of alternative content
     */
    public function __construct(WMailPartText $text, WMailPartHtml $html) {
        $this->_parts = array(null, null);
        $this->html = $html;
        $this->text = $text;
    }
    
    /**
     * Sets html part of alternative content
     *
     * @param WMailPartHtml $html Html part of alternative content
     */
    public function setHtml(WMailPartHtml $html) {
        $this->_html = $html;
    }
    
    /**
     * Sets text part of alternative content
     *
     * @param WMailPartText $text Text part of alternative content
     */
    public function setText(WMailPartText $text) {
        $this->_text = $text;
    }
    
    /**
     * Returns message body
     *
     * @return string
     */
    public function getData() {
        return $this->_renderParts(array(
            $this->_text,
            $this->_html->mimePart,
        ));
    }
    
}