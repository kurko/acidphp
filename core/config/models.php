<?php
/**
 * Configurações de Models
 *
 * @package Core Config
 * @name Models
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @since v0.1 04/08/2009
 */
/**
 * Passwords
 *
 * Especifica quais campos são de password
 */
Config::write("modelPasswordFields", array(
    "password", "passw", "passwd", // in english
    "senha" // in portuguese
));

/**
 * SQL LIMIT AUTOMÁTICO
 *
 * Se nenhum limit for especificado, limit é igual ao número a seguir.
 */
Config::write("modelAutoLimit", "50");
?>