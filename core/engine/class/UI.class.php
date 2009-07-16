<?php
/**
 * Classe respons�vel por lidar com a interface do site.
 *
 * UI significa User Interface.
 *
 * @package Class
 * @name UI
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @version 0.1
 * @since v0.1.5, 30/05/2009
 */
class UI {


    public function correctUIPage($param){

        /**
         * Se o usu�rio foi permitido acessar a p�gina atual, retorna a
         * p�gina de acordo com o section que foi pedido.
         *
         * Esta permiss�o � adquirida atrav�s de UI::verificaPermissoes();
         */
        if($param['permitted']){
            return INC_DIR . $_GET['section'] . '.inc.php';
        }
        /**
         * O endere�o ser� de uma p�gina com uma mensagem de acesso negado.
         */
        else {
            return MSG_DENIED_ACCESS;
        }
        
    }

    public function verificaPermissoes(){

        global $navPermissoes;
        global $administrador;
        /**
         * Se uma se��o foi especificada
         */
        if(!empty($_GET['section'])){

            /**
             * @todo - planejar carregamento em formato MVC
             */
            /**
             * Verifica se h� permiss�es quanto � se��o atual
             */
            if( !empty($navPermissoes[$_GET['section']]) AND is_array($navPermissoes[$_GET['section']])){

                /**
                 * Verifica se h� permiss�es quanto ao action atual
                 */
                if( !empty($navPermissoes[$_GET['section']][$_GET['action']])
                    AND is_array($navPermissoes[$_GET['section']][$_GET['action']])
                ){

                    /**
                     * Verifica se o tipo de usu�rio conectado tem permiss�o quanto ao action atual
                     */
                    if(in_array(strtolower($administrador->LeRegistro('tipo')), arraytolower($navPermissoes[$_GET['section']][$_GET['action']]))){
                        /**
                         * Se est� tudo ok, se h� permiss�es para este usu�rio
                         */

                        return true;
                    } else {
                        /**
                         * Se o usu�rio n�o tem permiss�o para acessar esta p�gina
                         */
                        return false;
                    }
                }
                /**
                 * N�o h� permiss�es definidas quanto ao action atual
                 */
                /**
                 * Verifica se o action atual n�o � bloqueado para todos os usu�rios
                 */
                elseif( !empty($navPermissoes[$_GET['section']]['au-permissao'])
                        AND is_array($navPermissoes[$_GET['section']]['au-permissao'])
                ){
                    /**
                     * O action � bloqueado a todos os usu�rios.
                     *
                     * Verifica o ranking do usu�rio atual e ve se este tem
                     * alguma permiss�o.
                     */
                    if(in_array(strtolower($administrador->LeRegistro('tipo')), arraytolower($navPermissoes[$_GET['section']]['au-permissao']))){
                        return true;
                    } else {
                        return false;
                    }
                }
                /**
                 * Este action � livre para acesso global
                 */
                else {
                    return true;
                }

            }
            /**
             * Esta section possui acesso liberado globalmente
             */
            else {
                return true;
            }
        } else {
            return true;
        }

        return true;
    }


}

?>