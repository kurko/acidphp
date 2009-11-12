<?php
/**
 *
 * PUBLIC/INDEX.PHP
 *
 * Este é o arquivo principal da aplicação, o inicializador do Core Engine.
 *
 * Neste arquivo são feitas verificações na URL para chamar a aplicação correta.
 * É possível mais de uma aplicação, como app/, app2/, app3/ todas para um mesmo
 * core/. Assim, este arquivo verifica qual foi requisitada e configura APP_DIR.
 *
 * @package Core
 * @name Index
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @since v0.1 15/07/2009
 */

/*
 * Define o caminho até o diretório raiz
 */


$startTime = microtime(true);
define("THIS_PATH_TO_ROOT", "../../");

define("CORE_DIR", THIS_PATH_TO_ROOT."core/");
define("CORE_CONFIG_VARIABLES", CORE_DIR."config/core_variables.php");
define("APP_CONFIG_VARIABLES", CORE_DIR."config/app_variables.php");

include(CORE_CONFIG_VARIABLES);

/*
 * APP ATUAL
 *
 * Toma o nome do diretório da APP atual
 */
$scriptName = str_replace("/public/index.php", "", $_SERVER["SCRIPT_NAME"] );
$scriptNameDivided = array_reverse( explode("/", $scriptName ) );

$app = $scriptNameDivided[0];

    /*
     * Verifica o diretório anterior a app. Se alguém inventar de criar algo como
     * app/app/app/, isto pode dar problema para descobrir o WEBROOT_ABSOLUTE.
     */
        $equalAppBeforeAppDir = false;
        if( !empty($scriptNameDivided[1]) ){
            $beforeAppDir = $scriptNameDivided[1];

            if( $app == $beforeAppDir)
                $equalAppBeforeAppDir = true;

        } else {
            $beforeAppDir = "";
        }

/*
 * ROOT DIR
 *
 * Contém o endereço da aplicação sem app alguma na URL.
 */
DEFINE("ROOT", str_replace($app, "", $scriptName ) );

/*
 * VERIFICAÇÃO DE APP REQUISITADA
 */

if( empty($_GET["url"]) )
    $webRoot = $_SERVER["REQUEST_URI"];
else {
    if( is_string($_GET["url"]) ){
        $url = $_GET["url"];
    }
    $webRoot = str_replace( $url, "", $_SERVER["REQUEST_URI"] );
}

define("WEBROOT", $webRoot);

$webAppRoot = array_reverse( array_values(array_filter( explode("/", WEBROOT) )) );

/*
 * Se é uma aplicação especificada
 */
if( !empty($webAppRoot) AND is_dir(THIS_PATH_TO_ROOT.$webAppRoot[0]) ){
    define("APP_DIR", THIS_PATH_TO_ROOT .$webAppRoot[0]."/");
}
/*
 * app/ padrão
 */
else {
    define("APP_DIR", THIS_PATH_TO_ROOT ."app/");
}
/*
 * APP_VARIABLES
 *
 * Paths do app/
 */
include(APP_CONFIG_VARIABLES);

/*
 * WEBROOT_ABSOLUTE
 *
 * Esta url contém SEMPRE o app atual ao final. Assim, é possível salvar
 * imagens com path absoluto, entre outras funcionalidades.
 *
 * Ex.:
 *
 *      /myfolder/anotherfolder/app/
 */
    /*
     * O último diretório antes do controller é o mesmo da app
     */
    if( !empty($webAppRoot[0])
        AND $webAppRoot[0] == $app )
    {
        define( "WEBROOT_ABSOLUTE", WEBROOT );
    }
    /*
     * O último diretório antes do controller é diferente da app. Isto acontece
     * quando não se digita o app atual. O padrão é carregar app/
     */
    else if( !empty($webAppRoot[0])
        AND $webAppRoot[0] != $app )
    {
        define( "WEBROOT_ABSOLUTE", WEBROOT."app/" );
    }
    /*
     * Se estamos no root do servidor (/), então vemos a app atual.
     */
    else if( WEBROOT == "/" ){
        define( "WEBROOT_ABSOLUTE", "/".$app."/" );
    }
    /*
     * Nenhuma alternativa acima
     */
    else {
        define( "WEBROOT_ABSOLUTE", WEBROOT );
    }

include(CORE_LOADER);
include(ENGINE_START);
?>