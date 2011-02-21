<?php
/**
 * WMailBase class file.
 *
 * @author Michal Lupatus Kluszewski <lupatus@gmail.com>
 * @link http://yii.lupatus.com/wmailer
 * @copyright Copyright &copy; 2011 Lupatus.com
 * @license http://yii.lupatus.com/wmailer/license/
 * @package WolfLibs4Yii
 * @subpackage WMailer
 */

/**
 * WMailBase is basic class for WMailBuilder classes
 *
 * @property string $headers MIME headers string
 */
abstract class WMailBase extends CComponent {
    
    const NL = "\r\n";
    
    /**
     * $var array $_headers MIME headers list
     */
    protected $_headers = array();
    
    /**
     * Ads MIME header
     *
     * @param string $name header name
     * @param string $value header value
     * @param array $params header additional params
     */
    public function addHeader($name, $value, $params = array()) {
        if ($value !== null) {
            $p = array();
            $s = false;
            foreach ($params as $k => $v) {
                if ($v !== null) {
                    $s   = true;
                    $p[] = $k . '="' . $v . '"';
                }
            }
            if ($s) {
                $value .= '; ' . implode('; ', $p);
            }
            $this->_headers[$name] = $value;
        }
    }
    
    /**
     * Encodes header value string into quoted printable string
     *
     * @param string $value header value
     *
     * @return string quoted printable string
     */
    protected function _encodeHeader($value) {
        if ($value !== null) {
            // important!!!
            @mb_internal_encoding('utf-8');
            return mb_encode_mimeheader($value, 'utf-8', "Q", self::NL, 2);
        }
    }
    
    /**
     * Returns MIME headers string from headers list
     *
     * @param array $headers MIME headers list
     *
     * @return string MIME headers string
     */
    protected function _renderHeaders($headers) {
        $h = array();
        foreach ($headers as $header => $value) {
            if ($value !== null) {
                $h[] = $header . ': ' . $value;
            }
        }
        return implode(self::NL, $h);
    }
    
    /**
     * Returns MIME headers string or MIME headers list
     *
     * @param boolean $plain return headers as list
     *
     * @return string|array MIME headers
     */
    public function getHeaders($plain = false) {
        if ($plain) {
            return $this->_headers;
        }
        return $this->_renderHeaders($this->_headers);
    }
    
}