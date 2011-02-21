<?php
/**
 * WMailPartText class file.
 *
 * @author Michal Lupatus Kluszewski <lupatus@gmail.com>
 * @link http://yii.lupatus.com/wmailer
 * @copyright Copyright &copy; 2011 Lupatus.com
 * @license http://yii.lupatus.com/wmailer/license/
 * @package WolfLibs4Yii
 * @subpackage WMailer
 */

/**
 * WMailPartText is text MIME message part class
 */
class WMailPartText extends WMailPart {
    
    /**
     * @var array $_headers MIME headers list
     */
    protected $_headers = array(
        'Content-type' => 'text/plain; charset="utf-8"',
        'Content-transfer-encoding' => 'quoted-printable',
    );
    
    /**
     * Returns parts body
     *
     * @return string
     */
    public function getData($raw = false) {
        if ($raw) {
            return $this->_data;
        }
        if (function_exists('quoted_printable_encode')) {
            return quoted_printable_encode($this->_data);
        } else if (function_exists('imap_8bit')) {
            return imap_8bit($this->_data);
        } else {
            $input    = preg_replace('/([^\x20\x21-\x3C\x3E-\x7E\x0A\x0D])/e', 'sprintf("=%02X", ord("\1"))', $this->_data);
            $inputLen = strlen($input);
            $outLines = array();
            $output   = '';
            $lineMax  = 76;
            $lines = preg_split('/\r?\n/', $input);
            for ($i=0; $i<count($lines); $i++) {
                if (strlen($lines[$i]) > $lineMax) {
                    $minus   = 1;
                    if (substr($lines[$i], $lineMax - 2, 1) == '=') {
                        $minus = 2;
                    } else if (substr($lines[$i], $lineMax - 3, 1) == '=') {
                        $minus = 3;
                    }
                    $outLines[] = substr($lines[$i], 0, $lineMax - $minus) . '=';
                    $lines[$i]  = substr($lines[$i], $lineMax - $minus);
                    --$i;
                } else {
                    $outLines[] = $lines[$i];
                }
            }
            $output = preg_replace('/(\x20+)$/me', 'str_replace(" ", "=20", "\1")', $outLines);
            return implode(self::NL, $output);
        }
    }
    
    /**
     * Renders text from template
     *
     * @param string $view Template path (@see CControler::getViewFile())
     * @param array $params Template variables
     */
    public function render($_viewName_, $_params_ = array()) {
        $_viewFile_ = Yii::app()->controller->getViewFile($_viewName_);
        if (!$_viewFile_) {
            throw new WMailError('Failed to find email template: ' . $_viewName_);
        }
        if (is_array($_params_)) {
            extract($_params_, EXTR_PREFIX_SAME, 'data');
        }
        ob_start();
        require($_viewFile_);
        $this->data = ob_get_contents();
        ob_end_clean();
    }
    
}