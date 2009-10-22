<?php
/**
 * HELPER
 *
 * Javascript
 *
 * Contém geradores automáticos de elementos Javascript
 *
 * @package Helpers
 * @name Javascript
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @since v0.0.6, 22/10/2009
 */
class JavascriptHelper extends Helper
{

    function __construct($params = ""){
        parent::__construct($params);
    }

    /**
     * BACK()
     */
    public function back($linkText ){
        return '<a href="javascript: void(0);" class="jsBack" onclick="history.back()">'.$linkText.'</a>';
    }


}

?>