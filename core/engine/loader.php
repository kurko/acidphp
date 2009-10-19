<?php
/**
 * Arquivo responsável pelo carregamento da estrutura de arquivos essenciais
 * ao funcionamento do sistema.
 *
 * @package Startup
 * @name Loader
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @since v0.1
 */
/*
 * Xdebug Notes:
 *
 *      - include mais rápido que include_once aqui.
 */
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
include(CORE_CLASS_DIR."Engine.php");
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
if( is_file(APP_CONFIG_DATABASE) )
    include(APP_CONFIG_DATABASE);
else {
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
?>