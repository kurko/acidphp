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

/**
 * TIMER INIT
 *
 * Inicializa Timer para saber quanto tempo o sistema levou para carregar
 */

/**
 * SESSION
 *
 * Inicializa Session
 */
session_name( Config::read("securityKey") );
session_start();

/**
 * CONEXÃO
 *
 * Conexão principal com o banco de dados
 */
if( !$useDB ){
    $dbConn = array();
    $conn = false;
} else {
    $conn = new Conexao($dbConn);
}

/**
 * NEW ENGINE
 *
 * Engine é o responsável pela inicialização do sistema.
 */
$engine = new Engine(array(
        'conn' => $conn,
    )
);

/**
 * CONFIGURAÇÕES DO SISTEMA
 */
/**
 * Tabelas que já foram descritas
 */
$describedTables = array();
/**
 * Página com tela de login padrão
 */
$globalVars = array();

/**
 * CARREGA O CONTROLLER NECESSÁRIO
 */
//if( is_file(APP_CONTROLLER_DIR.$engine->callController."_controller.php") ){
    include(APP_CONTROLLER_DIR.$engine->callController."_controller.php");
//}

/**
 * MVC
 */
/**
 * Prepara parâmetros de carregamentos de controllers
 */
$callControllerClass = $engine->callControllerClass."Controller";
$param = array(
    "engine" => $engine,
);
/**
 * Carrega o sistema de controller
 */
$appRunningController = new $callControllerClass( $param );


/**
 * AMOSTRAGEM DE DEBUG
 */
/**
 * TIMER END
 */
$endTime = microtime(true);
showLoadingTime($endTime - $startTime);

/**
 * Mostra debug SQL
 */
if( Config::read("debug") >= 1 )
    debugSQLs( Config::read("SQLs") );

//echo $endTime-$startTime;


?>