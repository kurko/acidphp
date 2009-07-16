<?php
/**
 * HELPER
 *
 * Form
 *
 * Cont�m gerador de elementos HTML autom�ticos
 *
 * @package Helpers
 * @name Form
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @since v0.1.6, 13/07/2009
 */
class FormHelper
{

    function __construct(){
        //echo "form in�cio";
    }

    public function input($fieldName, $options = ''){
        $conteudo ='';

        $conteudo.= '<div class="input">';

        /**
         * LABEL
         *
         * Se Label n�o foi especificado
         */
        if( empty($options["label"]) ){
            $conteudo.= '<label for="input-'.$fieldName.'">'.$fieldName.'</label>';
        } else {
            $conteudo.= '<label for="input-'.$fieldName.'">'.$options["label"].'</label>';
        }
        
        $conteudo.= '<div class="input-field">';
        $conteudo.= '<input type="text" name="'.$fieldName.'" id="input-'.$fieldName.'">';
        $conteudo.= '</div>';


        $conteudo.= '</div>';


        return $conteudo;
    }

}

?>