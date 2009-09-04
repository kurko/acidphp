<?php
/**
 * Classe responsável por validar itens de um formulário ou dados para inclusão
 * em um DB.
 *
 * @package Core Functions
 * @name Validation
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @since v0.1, 08/08/2009
 */
class Validation
{

    static function validate($params = array()){

    }

    /**
     * notEmpty()
     *
     * Verifica se o valor é vazio e retorna falso caso seja.
     *
     * @param string $valor Valor a ser validado
     * @return bool
     */
    static function notEmpty($valor){
        if( !empty($valor) )
            return true;
        else
            return false;
    }

    /**
    Validate an email address.
    Provide email address (raw input)
    */
    /**
     * email()
     * 
     * Retorna true se o email tem um formato de endereço de email e o domínio
     * existe
     *
     * @param string $email
     * @return bool
     */
    static function email($email){

        $isValid = true;
        $atIndex = strrpos($email, "@");
        if (is_bool($atIndex) && !$atIndex){
            $isValid = false;
        } else {
            $domain = substr($email, $atIndex+1);
            $local = substr($email, 0, $atIndex);
            $localLen = strlen($local);
            $domainLen = strlen($domain);

            if ($localLen < 1 || $localLen > 64){
                // local part length exceeded
                $isValid = false;
            } else if ($domainLen < 1 || $domainLen > 255) {
                // domain part length exceeded
                $isValid = false;
            } else if ($local[0] == '.' || $local[$localLen-1] == '.') {
                // local part starts or ends with '.'
                $isValid = false;
            } else if (preg_match('/\\.\\./', $local)) {
                // local part has two consecutive dots
                $isValid = false;
            } else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
                // character not valid in domain part
                $isValid = false;
            } else if (preg_match('/\\.\\./', $domain)) {
                // domain part has two consecutive dots
                $isValid = false;
            } else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\","",$local))) {
                // character not valid in local part unless
                // local part is quoted
                if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\","",$local))) {
                    $isValid = false;
                }
            }
            if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A"))) {
                // domain not found in DNS
                $isValid = false;
            }
        }
        return $isValid;
    }

    static function alphaNumeric($valor){
        
    }

}
?>