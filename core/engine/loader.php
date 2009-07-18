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



include(CORE_FUNCTIONS_FILE);

/**
 * Classes
 */
include(CORE_CLASS_DIR."Engine.php");
include(CORE_CLASS_DIR."Controller.php");
/**
 * Classe de configuração do sistema
 */
include(CORE_CLASS_DIR."Config.php");

if( is_file(APP_CONTROLLER_DEFAULT) ){
    include(APP_CONTROLLER_DEFAULT);
} else {
    include(CORE_APP_CONTROLLER_DEFAULT);
}

include(APP_CONFIG_ROUTES);



?>