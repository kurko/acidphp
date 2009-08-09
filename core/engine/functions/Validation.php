<?php
/**
 * Classe responsável validar itens de um formulário.
 *
 * @package Core Functions
 * @name Validation
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @since v0.1, 08/08/2009
 */
class Validation
{

    static function validate($regra, $valor){
    }

    static function notEmpty($valor){
        if( !empty($valor) )
            return 1;
        else
            return 0;
        
    }

}
?>