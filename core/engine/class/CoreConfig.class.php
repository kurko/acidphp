<?php
/**
 * Classe que cont�m a configura��o do core do sistema
 *
 * @package Configura��es
 * @name CoreConfig
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @since v0.1.5 24/06/2009
 */

class CoreConfig {

    protected static $config;

    /**
     * Escreve uma nova vari�vel com configura��es.
     *
     * @param string $varName Nome da vari�vel a ser gravada.
     * @param string $varValor Valor a ser gravado na nova vari�vel.
     * @return bool Se a vari�vel foi gravada com sucesso. 
     */
    public function write($varName, $varValor){
        return self::$config[$varName] = $varValor;
    }

    /**
     * Retorna um valor de uma configura��o.
     *
     * @param string $varName Nome da configura��o que se deseja saber o valor.
     * @param string $default Valor retornado caso a configura��o n�o exista.
     * @return string
     */
    public function read($varName, $default = ''){
        if( !empty(self::$config[$varName]) ){
            return self::$config[$varName];
        } else {
            return $default;
        }
    }
}
?>