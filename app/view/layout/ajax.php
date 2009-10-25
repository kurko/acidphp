<?php
/**
 * LAYOUT AJAX
 *
 * Arquivo de layout próprio para requisições Ajax.
 *
 * @package View
 * @name Ajax Layout
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @since v0.0.6 16/07/2009
 */
/*
 * Este layout é vazio, ideal para quando se retorna dados de requisições
 * Ajax.
 *
 * Use setAjax() no controller para automaticamente desativar o debug e
 * usar este layout para requisições Ajax.
 */
echo $content_for_layout;
?>