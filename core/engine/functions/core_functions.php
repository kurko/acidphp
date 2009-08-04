<?php
/**
 * Arquivo contém funções do core do sistema.
 *
 * @package Core
 * @category Funções
 * @name core_functions
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @since v0.1
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

    $sqlCommands = array(
        "SELECT", "UPDATE", "DELETE", "INSERT", "REPLACE",
        "FROM", "ASC", "WHERE", "ORDER BY", "LIMIT", "TABLES",
        "LEFT JOIN", "DISTINCT", "COUNT", "ON", "DESCRIBE", "SHOW",
        "INTO", "VALUES",
        "IN", "NOT IN", "OR", "AND", "AS", "DESC"
    );
    $boldSqlCommands = array();
    foreach( $sqlCommands as $valor ){
        $boldSqlCommands[] = "<strong>".$valor."</strong>";
    }

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
            if( Config::read("debugSQLStyle") ){
                $sql = $valor["sql"];
                $sql = str_replace($sqlCommands, $boldSqlCommands, $sql );
                echo $sql;
            } else {
                echo $valor["sql"];
            }
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
