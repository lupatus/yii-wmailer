<?php
/**
 * WMailPartFile class file.
 *
 * @author Michal Lupatus Kluszewski <lupatus@gmail.com>
 * @link http://yii.lupatus.com/wmailer
 * @copyright Copyright &copy; 2011 Lupatus.com
 * @license http://yii.lupatus.com/license/
 */

/**
 * WMailPartFile is attachment MIME message part class
 */
class WMailPartFile extends WMailPart {
    
    /**
     * @var string $_idFormat Format string for Content-ID
     */
    protected static $_idFormat;
    
    /**
     * @var int $_idIndex Index of next Content-ID
     */
    protected static $_idIndex = 0;
    
    /**
     * Returns unique id for attachment part (Content-ID)
     *
     * @return string
     */
    protected static function createId() {
        if (!isset(self::$_idFormat)) {
            self::$_idFormat = sprintf('wmailer.id.%.5f.%d.%%05d', microtime(true), rand(1000, 9999));
        }
        return sprintf(self::$_idFormat, ++self::$_idIndex);
    }
    
    /**
     * @var string $_name File name
     */
    protected $_name;
    
    /**
     * @var boolean $_inline Attachment display inline switch
     */
    protected $_inline = false;
    
    /**
     * @var string $_id Attachment Content-ID
     */
    protected $_id = null;
    
    /**
     * @var array $_headers MIME headers list
     */
    protected $_headers = array(
        'Content-Transfer-Encoding' => 'base64',
    );
    
    /**
     * @param string $data File contents
     * @param string $mime File mime content type
     * @param string $name File name
     */
    public function __construct($data, $mime, $name = null) {
        $this->addHeader('Content-type', $mime);
        $this->_name = $name;
        $this->_data = chunk_split(base64_encode($data));
    }
    
    /**
     * Returns Content-ID of attachment
     *
     * @return string
     */
    public function getId($setInline = true) {
        if (!$this->_id) {
            $this->_id = self::createId();
            if ($setInline) {
                $this->_inline = true;
            }
        }
        return $this->_id;
    }
    
    /**
     * Return if file should be displayed inline
     *
     * @return boolean
     */
    public function getDisplayInline() {
        return $this->_inline;
    }
    
    /**
     * Sets if file should be displayed inline
     *
     * @param boolean $inline
     */
    public function setDisplayInline($inline) {
        $this->_inline = (bool) $inline;
    }
    
    /**
     * Returns message MIME headers string or list
     *
     * @param boolean $plain Return headers as list
     *
     * @return string|array
     */
    public function getHeaders($plain = false) {
        $this->addHeader('Content-Disposition', 
            ($this->_inline ? 'inline' : 'attachment'),
            array('filename' => $this->_encodeHeader($this->_name))
        );
        if ($this->_id) {
            $this->addHeader('Content-ID', '<' . $this->_id . '>');
        }
        return parent::getHeaders($plain);
    }
    
}