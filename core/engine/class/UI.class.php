<?php
/**
 * Classe responsável por lidar com a interface do site.
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
         * Se o usuário foi permitido acessar a página atual, retorna a
         * página de acordo com o section que foi pedido.
         *
         * Esta permissão é adquirida através de UI::verificaPermissoes();
         */
        if($param['permitted']){
            return INC_DIR . $_GET['section'] . '.inc.php';
        }
        /**
         * O endereço será de uma página com uma mensagem de acesso negado.
         */
        else {
            return MSG_DENIED_ACCESS;
        }
        
    }

    public function verificaPermissoes(){

        global $navPermissoes;
        global $administrador;
        /**
         * Se uma seção foi especificada
         */
        if(!empty($_GET['section'])){

            /**
             * @todo - planejar carregamento em formato MVC
             */
            /**
             * Verifica se há permissões quanto à seção atual
             */
            if( !empty($navPermissoes[$_GET['section']]) AND is_array($navPermissoes[$_GET['section']])){

                /**
                 * Verifica se há permissões quanto ao action atual
                 */
                if( !empty($navPermissoes[$_GET['section']][$_GET['action']])
                    AND is_array($navPermissoes[$_GET['section']][$_GET['action']])
                ){

                    /**
                     * Verifica se o tipo de usuário conectado tem permissão quanto ao action atual
                     */
                    if(in_array(strtolower($administrador->LeRegistro('tipo')), arraytolower($navPermissoes[$_GET['section']][$_GET['action']]))){
                        /**
                         * Se está tudo ok, se há permissões para este usuário
                         */

                        return true;
                    } else {
                        /**
                         * Se o usuário não tem permissão para acessar esta página
                         */
                        return false;
                    }
                }
                /**
                 * Não há permissões definidas quanto ao action atual
                 */
                /**
                 * Verifica se o action atual não é bloqueado para todos os usuários
                 */
                elseif( !empty($navPermissoes[$_GET['section']]['au-permissao'])
                        AND is_array($navPermissoes[$_GET['section']]['au-permissao'])
                ){
                    /**
                     * O action é bloqueado a todos os usuários.
                     *
                     * Verifica o ranking do usuário atual e ve se este tem
                     * alguma permissão.
                     */
                    if(in_array(strtolower($administrador->LeRegistro('tipo')), arraytolower($navPermissoes[$_GET['section']]['au-permissao']))){
                        return true;
                    } else {
                        return false;
                    }
                }
                /**
                 * Este action é livre para acesso global
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