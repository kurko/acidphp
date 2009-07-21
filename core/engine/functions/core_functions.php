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


function pr($str){
    echo "<pre>";
    print_r($str);
    echo "</pre>";
}

/**
 * function showWarning()
 *
 * Mostra um aviso para o usuário. Somente se debug > 0
 *
 * @param string $str Aviso a ser mostrado
 */
function showWarning($str){
    if( Config::read("debug") > 0 ){
        trigger_error( $str , E_USER_WARNING);
    }
}

?>
