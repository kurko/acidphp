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

$conn = new Conexao($dbConn);
$engine = new Engine(array(
        'conn' => $conn,
    )
);

if( is_file(APP_CONTROLLER_DIR.$engine->callController."_controller.php") ){
    include(APP_CONTROLLER_DIR.$engine->callController."_controller.php");
}


/**
 * MVC
 */

$callControllerClass = $engine->callControllerClass."Controller";
$param = array(
    "engine" => $engine,
);

$appRunningController = new $callControllerClass( $param );


?>