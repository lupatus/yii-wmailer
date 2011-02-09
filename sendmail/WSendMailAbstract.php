<?php
/**
 * WSendMailAbstract class file.
 *
 * @author Michal Lupatus Kluszewski <lupatus@gmail.com>
 * @link http://yii.lupatus.com/wmailer
 * @copyright Copyright &copy; 2011 Lupatus.com
 * @license http://yii.lupatus.com/license/
 */

/**
 * WSendMailAbstract is abstract class for mail transport classes
 */
abstract class WSendMailAbstract extends CComponent implements WSendMailIface {
    
    /**
     * Object initialize
     */
    public function init() {
        
    }
    
    /**
     * Checks transport settings
     *
     * @return boolean
     *
     * @throws WSendMailError
     */
    public function check() {
        return true;
    }
    
}