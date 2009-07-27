<?php
/**
 * Arquivo de configuração do Core do sistema
 *
 * @package Config
 * @name Core
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @since v0.1 19/07/2009
 */

/**
 * DEBUG
 *
 * Configura em que modo de debug o sistema rodará. As opções possíveis são.
 *
 *      - 0: Não mostra erro algum;
 *      - 1: Mostra quais comandos SQL foram rodados;
 *      - 2: Mostra todos os erros mais configuração do modo 1;
 */
Config::write("debug", 1);

/**
 * CHARSET
 *
 * Configure o Charset de amostragem da sua aplicação. Este charset é o usado
 * para mostrar seus views.
 *
 * Dica: Use UTF-8 para os arquivos e para o banco de dados. UTF-8 é o padrão
 * internacional de caracteres mais amplamente aceito.
 */
Config::write("charset", "UTF-8");

?>