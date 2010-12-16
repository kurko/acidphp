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
    include(CORE_CLASS_DIR."Config.php");

    /**
     * CORE FUNCTIONS
     */
        /**
         * FUNÇÕES
         */
        include(CORE_FUNCTIONS_FILE);
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
    include(CORE_CLASS_DIR."Dispatcher.php");
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
    include(APP_CONFIG_ROUTES);
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
$dispatcher = Dispatcher::getInstance();

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