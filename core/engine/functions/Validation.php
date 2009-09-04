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
     * @param string $str Valor a ser validado
     * @return bool
     */
    static function notEmpty($str){
        if( !empty($str) )
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
            /*
             * Vericação de DNS (deixa o processamento lento)
             */
            //if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A"))) {
                // domain not found in DNS
                //$isValid = false;
            //}
        }
        return $isValid;
    }

    /**
     * alphaNumeric()
     *
     * Verifica se um valor é alphaNumeric. Retorna falso se for vazio.
     *
     * @param string $str
     * @return bool
     */
    static function alphaNumeric($str){
        if( !self::notEmpty($str) ){
            return false;
        } else {
            return (strspn($str, "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789") == strlen($str));
        }
        return false;
    }

    /**
     * numeric()
     *
     * Retorna falso se outros caracteres que não numéricos forem encontrados.
     *
     * Retorna falso se for vazio.
     *
     * @param string $str
     * @return bool
     */
    static function numeric($str){
        if( !self::notEmpty($str) )
            return false;
        else
            return ( ! ereg("^[0-9\.]+$", $str)) ? FALSE : TRUE;
    }

    /**
     * alpha()
     * 
     * Verifica se o valor passado contém somente letras.
     *
     * Retorna falso se for vazio.
     *
     * @param string $str
     * @return bool
     */
    static function alpha($str){
        if( !self::notEmpty($str) )
            return false;
        else
            return ( ! preg_match("/^([-a-z])+$/i", $str)) ? FALSE : TRUE;
    }

    /**
     * url()
     * 
     * Verifica se o valor passado é uma url válida.
     *
     * Retorna falso se for vazio.
     *
     * @param string $str
     * @return bool
     */
    static function url($str){
        if( !self::notEmpty($str) )
            return false;
        else
            return ( ! preg_match('/^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i', $str)) ? FALSE : TRUE;
    }

    /**
     * ip()
     * 
     * Verifica se o valor passado é um ip válido.
     * 
     * Retorna falso se for vazio.
     * 
     * @param string $str
     * @return bool 
     */
    static function ip($str){
        if( !self::notEmpty($str) )
            return false;
        else
            return ( ! preg_match( "/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/", $ip)) ? FALSE : TRUE;
    }

    /**
     * max()
     *
     * @param string $str
     * @param int $arg Tamanho máximo da string
     * @return bool
     */
    static function max($str, $arg = 5){
        if( strlen($str) > $arg )
            return false;
        else
            return true;
    }
    
    /**
     * min()
     *
     * @param string $str
     * @param int $arg Tamanho mínimo da string
     * @return bool
     */
    static function min($str, $arg = 5){
        if( strlen($str) < $arg OR !self::notEmpty($str) )
            return false;
        else
            return true;
    }

    /*
     *
     * VALIDAÇÕES ESPECIAIS E REGIONAIS
     *
     * Validações especiais para a região da aplicação.
     */

    /**
     * cpf()
     *
     * Verifica o CPF para a região do Brasil.
     *
     * @param string $str
     * @return bool
     */
    static function cpf($str){

        /*
         * Retirar todos os caracteres que nao sejam 0-9
         */
        $s = "";
        for ($x=1; $x<=strlen($str); $x=$x+1){
            $ch = substr($str,$x-1,1);
            if (ord($ch) >= 48 && ord($ch) <= 57){
                $s = $s.$ch;
            }
        }

        $str = $s;
        /*
         * Tamanho diferente do padrão correto
         */
        if (strlen($str)!=11){
            return false;
        }
        /*
         * String zerada
         */
        elseif( $str == "00000000000" ){
            return false;
        } else {
            $Numero[1]=intval(substr($str,1-1,1));
            $Numero[2]=intval(substr($str,2-1,1));
            $Numero[3]=intval(substr($str,3-1,1));
            $Numero[4]=intval(substr($str,4-1,1));
            $Numero[5]=intval(substr($str,5-1,1));
            $Numero[6]=intval(substr($str,6-1,1));
            $Numero[7]=intval(substr($str,7-1,1));
            $Numero[8]=intval(substr($str,8-1,1));
            $Numero[9]=intval(substr($str,9-1,1));
            $Numero[10]=intval(substr($str,10-1,1));
            $Numero[11]=intval(substr($str,11-1,1));

            $soma = 10*$Numero[1]+9*$Numero[2]+8*$Numero[3]+7*$Numero[4]+6*$Numero[5]+5*$Numero[6]+4*$Numero[7]+3*$Numero[8]+2*$Numero[9];
            $soma = $soma-(11*(intval($soma/11)));

            if ($soma==0 || $soma==1){
                $resultado1 = 0;
            } else {
                $resultado1 = 11-$soma;
            }

            if ($resultado1==$Numero[10]){
                $soma=$Numero[1]*11+$Numero[2]*10+$Numero[3]*9+$Numero[4]*8+$Numero[5]*7+$Numero[6]*6+$Numero[7]*5+
                $Numero[8]*4+$Numero[9]*3+$Numero[10]*2;
                $soma=$soma-(11*(intval($soma/11)));

                if ($soma==0 || $soma==1){
                    $resultado2=0;
                } else {
                    $resultado2=11-$soma;
                }

                /*
                 * Tudo OK!
                 */
                if ($resultado2 == $Numero[11]){
                    return true;
                }
                /*
                 * Não validou
                 */
                else {
                    return false;
                }
            } else {
                return false;
            }
        }
    }

    /**
     * cnpj()
     *
     * Valida um número de CNPJ para a região do Brasil.
     *
     * @param string $str
     * @return bool
     */
    static function cnpj($str){

        $s = "";

        for ($x=1; $x <= strlen($str); $x=$x+1){
            $ch = substr($str,$x-1,1);
            if (ord($ch) >= 48 && ord($ch) <= 57){
                $s=$s.$ch;
            }
        }

        $str = $s;
        if (strlen($str)!=14){
            return false;
        } else if ($str=="00000000000000"){
            return false;
        } else {
            $Numero[1] = intval(substr($str,1-1,1));
            $Numero[2] = intval(substr($str,2-1,1));
            $Numero[3] = intval(substr($str,3-1,1));
            $Numero[4] = intval(substr($str,4-1,1));
            $Numero[5] = intval(substr($str,5-1,1));
            $Numero[6] = intval(substr($str,6-1,1));
            $Numero[7] = intval(substr($str,7-1,1));
            $Numero[8] = intval(substr($str,8-1,1));
            $Numero[9] = intval(substr($str,9-1,1));
            $Numero[10] = intval(substr($str,10-1,1));
            $Numero[11] = intval(substr($str,11-1,1));
            $Numero[12] = intval(substr($str,12-1,1));
            $Numero[13] = intval(substr($str,13-1,1));
            $Numero[14] = intval(substr($str,14-1,1));

            $soma = $Numero[1]*5+$Numero[2]*4+$Numero[3]*3+$Numero[4]*2+
            $Numero[5]*9+$Numero[6]*8+$Numero[7]*7+ $Numero[8]*6+$Numero[9]*5+
            $Numero[10]*4+$Numero[11]*3+$Numero[12]*2;

            $soma=$soma-(11*(intval($soma/11)));

            if( $soma==0 || $soma == 1 ){
                $resultado1 = 0;
            } else {
                $resultado1 = 11-$soma;
            }
            
            if( $resultado1 == $Numero[13] ){
                $soma=$Numero[1]*6+$Numero[2]*5+$Numero[3]*4+$Numero[4]*3+$Numero[5]*2+$Numero[6]*9+
                $Numero[7]*8+$Numero[8]*7+$Numero[9]*6+$Numero[10]*5+$Numero[11]*4+$Numero[12]*3+$Numero[13]*2;
                $soma=$soma-(11*(intval($soma/11)));

                if ($soma==0 || $soma==1){
                    $resultado2=0;
                } else {
                    $resultado2=11-$soma;
                }

                if ($resultado2==$Numero[14]) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
    }

}
?>