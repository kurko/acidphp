<?php
/**
 * [Descrição do arquivo].
 *
 * [mais informações precisa ter 1 [ENTER] para definir novo parágrafo]
 *
 * [pode usar quantas linhas forem necessárias]
 * [linhas logo abaixo como esta, são consideradas mesmo parágrafo]
 *
 * @package [Nome do pacote de Classes, ou do sistema]
 * @category [Categoria a que o arquivo pertence]
 * @name [Apelido para o arquivo]
 * @author [nome do autor] <[e-mail do autor]>
 * @copyright [Informações de Direitos de Cópia]
 * @license [link da licença] [Nome da licença]
 * @link [link de onde pode ser encontrado esse arquivo]
 * @version [Versão atual do arquivo]
 * @since [Arquivo existe desde: Data ou Versao]
 */

if (!defined('CORE_DIR')) {
    define("CORE_DIR", "core/");
}

/**
 * ENGINE START
 */
define("ENGINE_DIR", CORE_DIR."engine/");
define("ENGINE_START", ENGINE_DIR."start.php");

define("CORE_LOADER", ENGINE_DIR."loader.php");

define("CORE_FUNCTIONS_DIR", CORE_DIR."engine/functions/");
define("CORE_FUNCTIONS_FILE", CORE_FUNCTIONS_DIR."core_functions.php");

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

define("CORE_VIEW_DIR", CORE_DIR.VIEW_DIR);
define("CORE_LAYOUT_DIR", CORE_DIR.VIEW_DIR.LAYOUT_DIR);



/**
 * APP
 */
if (!defined('APP_DIR')) {
    define("APP_DIR", "app/");
}
define("APP_CONTROLLER_DIR", "app/controller/");
define("APP_MODEL_DIR", "app/model/");
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

/**
 * View paths
 */
define("APP_LAYOUT_DIR", APP_VIEW_DIR.LAYOUT_DIR);

/**
 * APP CORE
 */
define("APP_CORE_DIR", APP_DIR."core/");
define("APP_CSS_DIR", APP_CORE_DIR."css/");
define("APP_JS_DIR", APP_CORE_DIR."js/");
define("APP_FLASH_DIR", APP_CORE_DIR."flash/");

/**
 * APP SUPPORT FILES
 */
/**
 * AppController do Core
 */
define("CORE_APP_CONTROLLER_DEFAULT", CORE_CLASS_DIR."AppController.php");
/**
 * AppController criado pelo usuário na aplicação
 */
define("APP_CONTROLLER_DEFAULT", APP_DIR."app_controller.php");



?>