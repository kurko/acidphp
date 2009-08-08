<?php
/**
 * Classe de tratamento e leitura especial de strings e variáveis em geral
 *
 * @package Core Functions
 * @name StrTreatment
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @since v0.1, 04/08/2009
 */
class StrTreament
{

    /**
     * Retorna subString do caractere 0 até $limitChar de uma string.
     *
     * @param string $str String a ser analisado
     * @param string $limitChar Caractere delimitadora
     * @param int $ocurrence
     * @return string
     */
    static function getNameSubStr($str, $limitChar, $ocurrence = ""){
        
        $limitCharInStr = strpos($str, $limitChar);

        /**
         * Se há uma substring, retorna-a
         */
        if( $limitCharInStr > 0 ){
            $result = substr($str, 0, $limitCharInStr);
        }
        /**
         * Retorna string se não há subString
         */
        else {
            $result = $str;
        }

        return $result;
    } // fim getNameSubStr()

    static function firstToLower($str){
        if( isset($str[1]) ){
            $firstChar = strToLower( $str[0] );
            $restChars = substr($str, 1);

            return $firstChar.$restChars;
        }
    }
}
?>