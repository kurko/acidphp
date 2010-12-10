<?php
/**
 * ROUTES
 *
 * Configurações de redirecionamento de URL.
 *
 * Por padrão, o sistema procura por qualquer endereço escrito no índice de
 * array $routes e redireciona tudo.
 *
 *
 * Os Routes são lidos na ordem. O sistema vai usar o primeiro item que
 * coincidir com o padrão.
 *
 */

$routes = array(
	
	// este é o padrão de posicionamento dos elementos na URL
	"/:controller/:action/:arg" => array(
	    "controller" => ":controller", "action" => ":action", ":arg"
	),
	
	// este é o padrão de posicionamento dos elementos na URL
	"/:controller/:action" => array(
	    "controller" => ":controller", "action" => ":action"
	),

	// este deve ser o último,
	"/" => array(
	    "controller" => "site", "action" => "index"
	),
);

/**
 * Salva a configuração acima
 */
Config::write("routes", $routes)

?>