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
 *
 * DEBUG
 * --------------------------------------------------------------------------
 *
 */
/**
 * Modo de Debugging
 *
 * Configura em que modo de debug o sistema rodará. As opções possíveis são.
 *
 *      - 0: Não mostra erro algum;
 *      - 1: Mostra quais comandos SQL foram rodados;
 *      - 2: Mostra todos os erros mais configuração do modo 1;
 *      - 3: Mostra todos os Warnings mais configuração do modo 2;
 */
Config::write("debug", 3);
/**
 * Debug: SQL com estilo (negrito, cores, etc) para melhor interpretação
 */
Config::write("debugSQLStyle", true);

/*
 *
 * VIEWING
 * --------------------------------------------------------------------------
 *
 */
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

/*
 *
 * SEGURANÇA
 * --------------------------------------------------------------------------
 *
 */
/**
 * CHAVE DE SEGURANÇA
 */
Config::write("securityKey", "woeviwp9g7W9G6wovinvo8wHBWEIBH");
/**
 * AUTH
 *
 * Quantidade de minutos em que a session de Auth expirará. Usuários logados
 * serão desligados.
 */
Config::write("authExpireTime", ""); // in minutes

?>