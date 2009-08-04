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



include(CORE_FUNCTIONS_FILE);
include(CORE_FUNCTIONS_DIR."StrTreatment.php");

/**
 * CARREGA CLASSES
 */
include(CORE_CLASS_DIR."Connection.php");
include(CORE_CLASS_DIR."Engine.php");
include(CORE_CLASS_DIR."Controller.php");
include(CORE_CLASS_DIR."Model.php");


include(CORE_CLASS_DIR."DataAbstractor.php");
include(CORE_CLASS_DIR."SQLObject.php");
include(CORE_CLASS_DIR."DatabaseAbstractor.php");

include(CORE_CLASS_DIR."Helper.php");

/**
 * Classe de configuração do sistema
 */
include(CORE_CLASS_DIR."Config.php");


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

include(APP_CONFIG_ROUTES);
include(APP_CONFIG_CORE);
include(APP_CONFIG_DATABASE);

/**
 * AJUSTA CONFIGURAÇÃO DE DEBUG
 */
if( Config::read("debug") > 1 ){
    error_reporting(E_ALL);
    ini_set('display_errors', TRUE);
    ini_set('display_startup_errors', TRUE);
} else {
    error_reporting(0);
}


/**
 * Carrega todos os models
 */
foreach (glob(APP_MODEL_DIR."*.php") as $filename) {
   include($filename);
}



?>