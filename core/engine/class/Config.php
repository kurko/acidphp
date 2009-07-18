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
     * Escreve uma nova variável com configura��es.
     *
     * @param string $varName Nome da vari�vel a ser gravada.
     * @param string $varValor Valor a ser gravado na nova vari�vel.
     * @return bool Se a vari�vel foi gravada com sucesso. 
     */
    static public function write($varName, $varValor){
        return self::$config[$varName] = $varValor;
    }

    /**
     * Retorna um valor de uma configura��o.
     *
     * @param string $varName Nome da configura��o que se deseja saber o valor.
     * @param string $default Valor retornado caso a configura��o n�o exista.
     * @return string
     */
    static public function read($varName, $default = ''){
        if( !empty(self::$config[$varName]) ){
            return self::$config[$varName];
        } else {
            return $default;
        }
    }
}
?>