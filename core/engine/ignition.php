<?php
/**
 * Inicializa todo o sistema, carregando o Engine e os subsequentes mecanismos
 * para funcionamento do framework.
 *
 * @package Startup
 * @name starter
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @since v0.1 15/07/2009
 */

/*
 *
 * INIT PATHS
 *
 * Initializes the main paths of the system
 *
 */
date_default_timezone_set('America/Sao_Paulo');


define("CORE_CONFIG_VARIABLES", CORE_DIR."config/core_variables.php");
define("APP_CONFIG_VARIABLES", CORE_DIR."config/app_variables.php");

include(CORE_CONFIG_VARIABLES);

include(CORE_CLASS_DIR.'App.php');
$app = App::getInstance();


exit();
    /*
     *
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

		echo 'url: '.$url;
		
        $webRoot = str_replace( $url, "", $_SERVER["REQUEST_URI"] );
		echo '<br>';
		echo 'webRoot: '.$webRoot."<br>";
    }

    define("WEBROOT", $webRoot);

	if( empty($_GET['url']) )
		$urlRoot = WEBROOT;
	else
		$urlRoot = $_GET['url'];
		
    $webAppRoot = array_values(array_filter( explode("/", $urlRoot) ));


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
	echo '<br>';
	var_dump($webAppRoot);
	echo '<br>';

	var_dump(APP_DIR);
	echo '<br>';

	var_dump($urlRoot);
	echo '<br>';
	var_dump($_SERVER['REQUEST_URI']);
	echo '<br>WEBROOT: ';
	var_dump($webRoot);
	echo '<br>URL: ';
	var_dump($_GET['url']);
	exit();
	
*/
?>