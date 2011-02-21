<?php
/**
 * WMailPartMulti class file.
 *
 * @author Michal Lupatus Kluszewski <lupatus@gmail.com>
 * @link http://yii.lupatus.com/wmailer
 * @copyright Copyright &copy; 2011 Lupatus.com
 * @license http://yii.lupatus.com/wmailer/license/
 * @package WolfLibs4Yii
 * @subpackage WMailer
 */

/**
 * WMailPartMulti is miltipart/mixed MIME message part class
 */
class WMailPartMulti extends WMailPart {
    
    /**
     * @var string $_boundaryFormat Format string for boundaries
     */
    protected static $_boundaryFormat;
    
    /**
     * @var int $_boundaryIndex Index of next boundary
     */
    protected static $_boundaryIndex = 0;
    
    /**
     * Created unique boundary string for multipart message part
     *
     * @return string
     */
    protected static function createBoundary() {
        if (!isset(self::$_boundaryFormat)) {
            self::$_boundaryFormat = sprintf('wmail.boundary.%.5f.%4d.%%05d', microtime(true), rand(1000,9999));
        }
        ++self::$_boundaryIndex;
        return sprintf(self::$_boundaryFormat, self::$_boundaryIndex);
    }
    
    /**
     * @var WMailPart[] $_parts Sub parts 
     */
    protected $_parts = array();
    
    /**
     * @var string $_contentType Part content type
     */
    protected $_contentType = 'multipart/mixed';
    
    /**
     * @var string $_boundary Part boundary string
     */
    protected $_boundary;
    
    /**
     * Returns message MIME headers string or list
     *
     * @param boolean $plain Return headers as list
     *
     * @return string|array
     */
    public function getHeaders($plain = false) {
        if (!isset($this->_headers['Content-type'])) {
            $this->addHeader('Content-type', $this->_contentType, array(
                'boundary' => $this->boundary
            ));
        }
        return parent::getHeaders($plain);
    }
    
    /**
     * Returns boundary string
     *
     * @return string
     */
    public function getBoundary() {
        if (!isset($this->_boundary)) {
            $this->_boundary = self::createBoundary();
        }
        return $this->_boundary;
    }
    
    /**
     * Renders part body
     *
     * @param WMailParts[] $parts Parts of part content
     *
     * @return string
     */
    protected function _renderParts($parts) {
        $data = array();
        foreach ($parts as $part) {
            $data[] = $part->content;
        }
        return '--' . $this->boundary . self::NL . 
               implode(self::NL . '--' . $this->boundary . self::NL, $data) . self::NL . 
               '--' . $this->boundary . '--';
    }
    
    /**
     * Returns part body
     *
     * @return string
     */
    public function getData() {
        return $this->_renderParts($this->_parts);
    }
    
    /**
     * Ads part of this message part
     *
     * @param WMailPart $part MIME message part
     */
    public function setPart(WMailPart $part) {
        if (!in_array($part, $this->_parts, true)) {
            $this->_parts[] = $part;
        }
    }
    
    /**
     * Sets parts of this part
     *
     * @param WMailPart[] $data
     */
    public function setData($data) {
        $this->_parts = array();
        if (is_array($data)) {
            foreach ($data as $part) {
                $this->part = $part;
            }
        }
    }
}