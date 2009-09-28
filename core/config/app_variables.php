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


/**
 *
 * DEFINE
 *
 * APP/
 *
 *
 */
if (!defined('APP_DIR')) {
    define("APP_DIR", THIS_PATH_TO_ROOT ."app/");
}

/**
 * Nome da pasta solicitada, sem '../'
 */
define('APP_REQUESTED', str_replace("../", "", "") );


define("APP_CONTROLLER_DIR", APP_DIR."controller/");
define("APP_MODEL_DIR", APP_DIR."model/");
define("APP_VIEW_DIR", APP_DIR.VIEW_DIR);

/**
 * Principais caminhos dentro de app/
 */
/**
 * APP CONFIG
 */
define("APP_CONFIG_DIR", APP_DIR."config/");
define("APP_CONFIG_ROUTES", APP_CONFIG_DIR."routes.php");
define("APP_CONFIG_CORE", APP_CONFIG_DIR."core.php");
define("APP_CONFIG_DATABASE", APP_CONFIG_DIR."database.php");

/**
 * View paths
 */
define("APP_LAYOUT_DIR", APP_VIEW_DIR.LAYOUT_DIR);

/**
 * APP CORE CLIENT-SIDE FILES
 */
define("APP_PUBLIC_DIR", APP_DIR."public/");
define("APP_CORE_DIR", APP_PUBLIC_DIR);
define("APP_IMAGES_DIR", APP_REQUESTED."images/");
define("APP_CSS_DIR", APP_REQUESTED."css/");
define("APP_JS_DIR", APP_REQUESTED."js/");
define("APP_FLASH_DIR", APP_REQUESTED."flash/");

/**
 * APP SUPPORT FILES
 */
/**
 * AppController criado pelo usuário na aplicação
 */
define("APP_CONTROLLER_DEFAULT", APP_DIR."app_controller.php");

/**
 * AppModel criado pelo usuário na aplicação
 */
define("APP_MODEL_DEFAULT", APP_DIR."app_model.php");

?>