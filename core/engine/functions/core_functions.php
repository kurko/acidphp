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
    if( Config::read("debug") > 0 ){
        echo "<pre>";
        print_r($str);
        echo "</pre>";
    }
}

/**
 * Retorna se E_USER_ERROR devem aparecer de acordo com debug
 */
function isDebugMode(){
    return Config::read("debug");
}

/**
 * MENSAGENS DE USUÁRIOS
 *
 * Erro, notice, warning
 */
/**
 * function showWarning()
 *
 * Mostra um aviso para o usuário. Somente se debug > 0
 *
 * @param string $str Aviso a ser mostrado
 */
function showWarning($str){
    var_dump($str);
    if( Config::read("debug") > 0 ){
        //trigger_error( $str , E_USER_WARNING);
    }
}

/**
 * showError() mostra uma mensagem de erro na tela.
 *
 * @param <type> $str
 */
function showError($str){
    if( Config::read("debug") > 0 ){
        trigger_error( $str , E_USER_ERROR);
    }
}

/**
 * Escreve na tela o tempo total em segundos, deixando três casa depois
 * da vírgula para milisegundos.
 *
 * @param string $totalTime Tempo total
 */
function showLoadingTime($totalTime){
    if( Config::read("debug") > 0 ){
        echo number_format($totalTime, 3, '.', '');
    }
}

/**
 * Mostra tabela com todos os SQLs rodados
 *
 * @param array $sql Códigos SQL já rodados
 */
function debugSQLs($sql){
    echo '<table width="100%" style="background: white; padding: 10px;">';
    echo '<tr>';
    echo '<td style="font-size: 12px;">';
    echo "<strong>Instruções SQL</strong>";
    echo '</td>';
    echo '<td style="font-size: 12px;">';
    echo "<strong>Tempo</strong>";
    echo '</td>';
    echo '</tr>';

    foreach($sql as $chave=>$valor){
        echo '<tr>';
            echo '<td style="font-size: 12px;">';
            echo $valor["sql"];
            echo '</td>';
            echo '<td style="font-size: 12px;">';
            echo showLoadingTime( $valor["time"] );
            echo '</td>';
        echo '</tr>';

        echo '<tr><td colspan="2" style="font-size: 0; background: silver;"></td></tr>';

    }
    echo '</table>';
}

?>
