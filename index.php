<?php
/**
 * Bem-vindo ao AcidPHP. Este é o arquivo principal do sistema, o centralizador
 * que chamará todos os outros necessários.
 *
 * @package Core
 * @name Index
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @since v0.1 15/07/2009
 */

define("CORE_DIR", "core/");
define("CORE_CONFIG_VARIABLES", CORE_DIR."config/variables.php");

include(CORE_CONFIG_VARIABLES);
include(CORE_LOADER);
include(ENGINE_START);

?>