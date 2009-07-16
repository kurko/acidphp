<?php
/**
 * Classe que contém a configuração do core do sistema
 *
 * @package Configurações
 * @name CoreConfig
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @since v0.1.5 24/06/2009
 */

class CoreConfig {

    protected static $config;

    /**
     * Escreve uma nova variável com configurações.
     *
     * @param string $varName Nome da variável a ser gravada.
     * @param string $varValor Valor a ser gravado na nova variável.
     * @return bool Se a variável foi gravada com sucesso. 
     */
    public function write($varName, $varValor){
        return self::$config[$varName] = $varValor;
    }

    /**
     * Retorna um valor de uma configuração.
     *
     * @param string $varName Nome da configuração que se deseja saber o valor.
     * @param string $default Valor retornado caso a configuração não exista.
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