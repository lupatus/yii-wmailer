<?php
/**
 * WSendMailIface interface file.
 *
 * @author Michal Lupatus Kluszewski <lupatus@gmail.com>
 * @link http://yii.lupatus.com/wmailer
 * @copyright Copyright &copy; 2011 Lupatus.com
 * @license http://yii.lupatus.com/license/
 */

/**
 * WSendMailIface is an interface for mail transport classes
 */
interface WSendMailIface {
    
    /**
     * Sends email
     * 
     * @param WMailBuilder $message email message
     *
     * @throws WSendMailError
     */
    public function send(WMailBuilder $message);
    
    /**
     * Checks transport settings
     *
     * @return boolean
     *
     * @throws WSendMailError
     */
    public function check();
    
}