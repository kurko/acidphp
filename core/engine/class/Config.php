<?php
/**
 * Classe que contém a configuração do core do sistema
 *
 * @package Configurações
 * @name CoreConfig
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @since v0.1 18/07/2009
 */

class Config {

    protected static $config;

    /**
     * Escreve uma nova variável com configurações.
     *
     * @param string $varName Nome da variável a ser gravada.
     * @param string $varValor Valor a ser gravado na nova variável.
     * @return bool Se a variável foi gravada com sucesso.
     */
    static function write($varName, $varValor){
        return self::$config[$varName] = $varValor;
    }

    /**
     * Some uma variável a uma array
     *
     * @param string $varName Nome da variável a ser gravada (será uma array).
     * @param string|array $varValor Valor a ser gravado na nova variável.
     * @return bool Se a variável foi gravada com sucesso.
     */
    public function add($varName, $varValor){
        if( empty(self::$config[$varName]) ){
            self::$config[$varName] = null;
        }

        $tempVar = self::$config[$varName];

        if( !is_array($tempVar) )
            $tempVar = array();

        array_push( $tempVar , $varValor);
        self::$config[$varName] = $tempVar;
        return true;
    }

    /**
     * Retorna um valor de uma configuração.
     *
     * @param string $varName Nome da configuração que se deseja saber o valor.
     * @param string $default Valor retornado caso a configuração não exista.
     * @return string
     */
    static function read($varName, $default = ''){
        if( !empty(self::$config[$varName]) ){
            return self::$config[$varName];
        } else {
            return $default;
        }
    }
}
?>