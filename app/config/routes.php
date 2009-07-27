<?php
/**
 * Possui configurações de redirecionamento de URLs.
 *
 * @package Config
 * @name Routes
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @since v0.1 16/07/2009
 */

/**
 * ROUTES
 *
 * Configurações de redirecionamento de URL.
 *
 * Por padrão, o sistema procura por qualquer endereço escrito no índice de
 * array $routes e redireciona tudo.
 */
$routes = array(
    "/" => array(
        "controller" => "site", "action" => "index"
    )
);

/**
 * Salva a configuração acima
 */
Config::write("routes", $routes)

?>