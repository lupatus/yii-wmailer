<?php
/**
 * WMailPartHtml class file.
 *
 * @author Michal Lupatus Kluszewski <lupatus@gmail.com>
 * @link http://yii.lupatus.com/wmailer
 * @copyright Copyright &copy; 2011 Lupatus.com
 * @license http://yii.lupatus.com/wmailer/license/
 * @package WolfLibs4Yii
 * @subpackage WMailer
 */

/**
 * WMailPartHtml is html MIME message part class
 */
class WMailPartHtml extends WMailPartText {
    
    /**
     * @var array $_headers MIME headers list
     */
    protected $_headers = array(
        'Content-type' => 'text/html; charset="utf-8"',
        'Content-transfer-encoding' => 'quoted-printable',
    );
    
    /**
     * @var WMailPartRelated $_related Multipart/related message part (parent for this part nad related messages)
     */
    protected $_related = null;
    
    /**
     * Returns MIME message part containing html message (this or multipart/related if there are related images)
     * 
     * @return WMailPartHtml|WMailPartRelated
     */
    public function getMimePart() {
        if ($this->_related) {
            return $this->_related;
        }
        return $this;
    }
    
    /**
     * Returns multipart/related MIME message part (parent for this part nad related messages)
     *
     * @return WMailPartRelated
     */
    public function getRelated() {
        if (!$this->_related) {
            $this->_related = new WMailPartRelated($this);
        }
        return $this->_related;
    }
    
    /**
     * Ads html related image attachment
     *
     * @param string $path Image file path
     * @param string $mime Image file MIME type
     * @param string $placeholder Placeholder in html which will be replaced with file's Content-ID
     * 
     * @return WMailPartFile
     */
    public function addImage($path, $mime, $placeholder = null) {
        $fp = fopen($path, 'r');
        $data = fread($fp, filesize($path));
        fclose($fp);
        return $this->addImageData($data, $mime, $placeholder);
    }
    
    /**
     * Ads html related image attachment
     *
     * @param string $data Image file contents
     * @param string $mime Image file MIME type
     * @param string $placeholder Placeholder in html which will be replaced with file's Content-ID
     * 
     * @return WMailPartFile
     */
    public function addImageData($data, $mime, $placeholder = null) {
        $file = new WMailPartFile($data, $mime);
        $this->related->part = $file;
        $file->displayInline = true;
        if (is_string($placeholder)) {
            $this->_data = str_replace($placeholder, 'cid:' . $file->id, $this->_data);
        }
        return $file;
    }
}