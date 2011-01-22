<?php
	
	$startTime = microtime(true);
    /*
     * APP_VARIABLES
     *
     * Paths do app/
     */
    include(APP_CONFIG_VARIABLES);


/*
 * 
 * LOADING CLASSES AND LIBS
 * 
 */
    /*
     * Class auto-load
     */
        function __autoload($class){
            if( is_file(CORE_CLASS_DIR.$class.".php") ){
                include_once(CORE_CLASS_DIR.$class.".php");
            } else if( is_file(CORE_HELPERS_DIR.$class.".php") ){
                include_once(CORE_HELPERS_DIR.$class.".php");
            } else if( is_file(CORE_COMPONENTS_DIR.$class.".php") ){
                include_once(CORE_COMPONENTS_DIR.$class.".php");
            } else if( is_file(CORE_BEHAVIOR_DIR.$class.".php") ){
                include_once(CORE_BEHAVIOR_DIR.$class.".php");
            } else {
                return false;
            }
        }

    /**
     * CLASSES CRÍTICAS
     *
     * Classe de configuração do sistema
     */
    include_once(CORE_CLASS_DIR."Config.php");

    /**
     * CORE FUNCTIONS
     */
        /**
         * FUNÇÕES
         */
	    include(CORE_FUNCTIONS_FILE);
		include(HELPERS_FUNCTIONS_FILE);
        include(URL_FUNCTIONS_FILE);
        include(TOOLS_FUNCTIONS_FILE);

        /**
         * Classes de funções
         */
        include(CORE_FUNCTIONS_DIR."StrTreatment.php");
        include(CORE_FUNCTIONS_DIR."Security.php");
        include(CORE_FUNCTIONS_DIR."Validation.php");

    /**
     * CONFIGURAÇÕES
     */
    /**
     * core/config/models.php contem configurações genéricas relacionadas a Models
     * e diretivas de dados.
     */
    include(CORE_CONFIG_DIR."models.php");

    /**
     * CARREGA CLASSES
     */
    include(CORE_CLASS_DIR."Connection.php");
    include(CORE_CLASS_DIR."Controller.php");
    include(CORE_CLASS_DIR."Model.php");
    include(CORE_CLASS_DIR."Behavior.php");


    include(CORE_CLASS_DIR."DataAbstractor.php");
    include(CORE_CLASS_DIR."SQLObject.php");
    include(CORE_CLASS_DIR."DatabaseAbstractor.php");

    /**
     * Helpers
     */
    include(CORE_CLASS_DIR."Helper.php");
    /**
     * Components
     */
    include(CORE_CLASS_DIR."Component.php");

    /**
     * CONTROLLERS
     */
    /**
     * Verifica se existe um AppController especificado, senão carrega o do core
     */
    if( is_file(APP_CONTROLLER_DEFAULT) ){
        include(APP_CONTROLLER_DEFAULT);
    } else {
        include(CORE_APP_CONTROLLER_DEFAULT);
    }

    /**
     * MODELS
     */
    /**
     * Verifica se existe um AppModel especificado, senão carrega o do core
     */
    if( is_file(APP_MODEL_DEFAULT) ){
        include(APP_MODEL_DEFAULT);
    } else {
        include(CORE_APP_MODEL_DEFAULT);
    }

    /*
     * CONFIGURAÇÕES
     */
    /*
     * Carregas as rotas do sistema (controllers e actions padrão)
     */
//    include(APP_CONFIG_ROUTES);
    /*
     * Configuração do núcleo do sistema
     */
    include(APP_CONFIG_CORE);
    /**
     * Debug
     */
    if( Config::read("debug") > 1 ){
        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);
    } else {
        error_reporting(0);
    }
    /*
     * CONFIGURAÇÕES DE BANCO DE DADOS
     */

    if( is_file(APP_CONFIG_DATABASE) ){
        include(APP_CONFIG_DATABASE);
    } else {
        $useDB = false;
        if( Config::read("debug") > 0 ){
            /**
             * @todo - mostrar página adequada mostrando que nâo há um DB
             * configurado.
             */
            trigger_error("Renomeie seu arquivo <em>app/config/database.sample.php</em> ".
                          "para <em>database.php</em> e configure seu banco de dados.", E_USER_ERROR);

        }

    }


/*
 *
 * IGNITES THE SYSTEM
 *
 * Ignit start the system
 *
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
    $conn = Connection::getInstance();
}

/**
 * NEW ENGINE
 *
 * Engine é o responsável pela inicialização do sistema.
 */
/*
$dispatcher = Dispatcher::getInstance();
$dispatcher->initialize();
$dispatcher->app = $this->appDir;
$dispatcher->appPublicDir = $this->_getSystemPublicDir();
$dispatcher->translateUrl();
*/
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
    include(APP_CONTROLLER_DIR.$dispatcher->callController."_controller.php");
//}

/**
 * MVC
 */
/**
 * Prepara parâmetros de carregamentos de controllers
 */
$callControllerClass = $dispatcher->callControllerClass."Controller";
$param = array(
    "dispatcher" => $dispatcher,
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

$conn = Connection::getInstance();
$conn->destroy();
?>