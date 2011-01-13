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
define("ENGINE_DIR", CORE_DIR."engine/");
define("ENGINE_IGNITION", ENGINE_DIR."ignition.php");

include(ENGINE_IGNITION);
?>