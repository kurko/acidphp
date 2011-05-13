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

# 'stop' => true will halt the rest of the Routes if it's validated
#	"/refresh_page/:arg" => array( "controller" => "controller_name", "action" => "refresh_page", 'stop' => true ),
#	"/refresh_page" => array( "controller" => "controller_name", "action" => "refresh_page", 'stop' => true ),

# pass custom variables (e.g. idiom)
#	"/pt_br" => array( "arg" => "idiom:pt_br" ),
#	"/es" => array( "arg" => "idiom:es" ),
#	"/en" => array( "arg" => "idiom:en" ),

# a bit more complex routing for i18n
#	"/pt_br/:controller/:action" => array( "controller" => ":controller", "action" => ":action", 'idiom:pt_br', 'stop' => true ),
#	"/en/:controller/:action" => array( "controller" => ":controller", "action" => ":action", 'idiom:en', 'stop' => true  ),
#	"/es/:controller/:action" => array( "controller" => ":controller", "action" => ":action", 'idiom:es', 'stop' => true  ),

	
	"/:controller/:action/:arg" => array( "controller" => ":controller", "action" => ":action", ":arg" ),
	"/:controller/:action" => array( "controller" => ":controller", "action" => ":action" ),

# Este deve ser o último,
	"/" => array(
	    "controller" => "site", "action" => "index"
	),
);

/**
 * Salva a configuração acima
 */
Config::write("routes", $routes)

?>