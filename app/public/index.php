<?php
/**
 * Bem-vindo ao AcidPHP. Este é o arquivo principal do sistema, o centralizador
 * que chamará todos os outros necessários.
 *
 * @package Core
 * @name Index
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @since v0.1 15/07/2009
 */

/*
 * Define o caminho até o diretório raiz
 */
define("THIS_PATH_TO_ROOT", "../../");

define("CORE_DIR", THIS_PATH_TO_ROOT."core/");
define("CORE_CONFIG_VARIABLES", CORE_DIR."config/core_variables.php");
define("APP_CONFIG_VARIABLES", CORE_DIR."config/app_variables.php");

include(CORE_CONFIG_VARIABLES);

/*
 * APP
 */
$url = $_GET["url"];
if( empty($url) ){

    $webRoot = $_SERVER["REQUEST_URI"];
    define("WEBROOT", $webRoot);

    $webAppRoot = array_reverse( array_values(array_filter( explode("/", WEBROOT) )) );

    /*
     * Se é uma aplicação especificada
     */
    if( is_dir(THIS_PATH_TO_ROOT.$webAppRoot[0]) ){
        define("APP_DIR", THIS_PATH_TO_ROOT .$webAppRoot[0]."/");
    }
    /*
     * app/ padrão
     */
    else {
        define("APP_DIR", THIS_PATH_TO_ROOT ."app/");
    }
    
    define("APP_DIR", THIS_PATH_TO_ROOT."app/");
} else {
    if( is_string($url) ){
        unset($url);
        $url[0] = $_GET["url"];
    }

    $webRoot = str_replace( implode("/", $url), "", $_SERVER["REQUEST_URI"] );
    define("WEBROOT", $webRoot);
    
    $webAppRoot = array_reverse( array_values(array_filter( explode("/", WEBROOT) )) );

    /*
     * Se é uma aplicação especificada
     */
    if( is_dir(THIS_PATH_TO_ROOT.$webAppRoot[0]) ){
        define("APP_DIR", THIS_PATH_TO_ROOT .$webAppRoot[0]."/");
    }
    /*
     * app/ padrão
     */
    else {
        define("APP_DIR", THIS_PATH_TO_ROOT ."app/");
    }

}

/*
 * APP_VARIABLES
 *
 * Paths do app/
 */
include(APP_CONFIG_VARIABLES);


include(CORE_LOADER);
include(ENGINE_START);

?>