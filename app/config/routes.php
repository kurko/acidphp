<?php
/**
 * 
 *
 * @package Config
 * @name Routes
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @since v0.1 16/07/2009
 */

$routes = array(
    "/" => array(
        "controller" => "site", "action" => "index"
    )
);
Config::write("routes", $routes)

?>
