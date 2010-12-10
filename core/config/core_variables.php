<?php
/**
 * Variáveis genéricas do sistema.
 *
 * @package Core Config
 * @name Variables
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @since v0.1 17/7/2009
 */


/*
 * Define o caminho até o diretório raiz
 */
if (!defined('THIS_PATH_TO_ROOT')) {
    define('THIS_PATH_TO_ROOT', '');
}

if (!defined('CORE_DIR')) {
    define("CORE_DIR", THIS_PATH_TO_ROOT."core/");
}



/**
 * ENGINE START
 */
define("ENGINE_DIR", CORE_DIR."engine/");
define("ENGINE_IGNITION", ENGINE_DIR."ignition.php");

define("CORE_CONFIG_DIR", CORE_DIR."config/");
define("CORE_SUPPORT_DIR", CORE_DIR."support/");

define("CORE_FUNCTIONS_DIR", CORE_DIR."engine/functions/");
define("CORE_FUNCTIONS_FILE", CORE_FUNCTIONS_DIR."core_functions.php");
define("URL_FUNCTIONS_FILE", CORE_FUNCTIONS_DIR."url_functions.php");
define("TOOLS_FUNCTIONS_FILE", CORE_FUNCTIONS_DIR."tools_functions.php");

/**
 * MVC CONFIG
 */
define("CONTROLLER_DIR", "controller/");
define("MODEL_DIR", "model/");
define("VIEW_DIR", "view/");
define("LAYOUT_DIR", "layout/");

/**
 * CORE
 */
/**
 * Core Classes
 */
define("CORE_CLASS_DIR", CORE_DIR."engine/class/");

/**
 * Core MVC Config
 */
define("HELPER_CLASSNAME_SUFFIX", "Helper");
define("CORE_HELPERS_DIR", CORE_CLASS_DIR."helpers/");
define("COMPONENT_CLASSNAME_SUFFIX", "Component");
define("CORE_COMPONENTS_DIR", CORE_CLASS_DIR."components/");

define("BEHAVIOR_CLASSNAME_SUFFIX", "Behavior");
define("CORE_BEHAVIOR_DIR", CORE_CLASS_DIR."behaviors/");

/**
 * CORE SUPPORT FILES
 */
    define("CORE_SCRIPTS_DIR", CORE_SUPPORT_DIR."script/");
    define("CORE_JS_DIR", CORE_SCRIPTS_DIR."js/");

    define("CORE_VIEW_DIR", CORE_SUPPORT_DIR.VIEW_DIR);
    define("CORE_LAYOUT_DIR", CORE_VIEW_DIR.LAYOUT_DIR);


/**
 * APP SUPPORT FILES
 */
/**
 * AppController do Core
 */
define("CORE_APP_CONTROLLER_DEFAULT", CORE_CLASS_DIR."AppController.php");
/**
 * AppModel do Core
 */
define("CORE_APP_MODEL_DEFAULT", CORE_CLASS_DIR."AppModel.php");



?>