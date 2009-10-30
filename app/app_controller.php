<?php
/**
 * AppController
 *
 * Insira abaixo todos os métodos que você deseja que todos os controllers
 * desta aplicação tenham.
 *
 * @name AppController
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @since v0.1
 */

class AppController extends Controller
{
    /*
     * Helpers são auxiliares nas Views
     */
    var $helpers = array("Html", "Form");

    var $components = array("Auth");

    /**
     * beforeFilter() é chamado automaticamente sempre antes de qualquer action
     */
    function beforeFilter(){

        /**
         * Auth::allow(array) indica quais os controllers e actions usuários não
         * logados podem acessar.
         *
         * Há três formatos possíveis de indicar estas permissões:
         *      - Libera um Controller inteiro -> Controller é um valor na array:
         *      ex.: array(
         *               "controllerA", "controllerB"
         *           );
         *
         *      - Libera Actions específicos -> Controller é um índice com
         *        subarray de Actions.
         *      ex.: array(
         *               "controllerA" => array(
         *                   "actionA", "actionB"
         *               ),
         *               "controllerB"
         *           );
         *
         *      - Libera todos os Controllers -> Um valor de array com asterísco (*)
         *      ex.: array("*");
         *
         * Auth::deny() tem o efeito contrário a allow(), indicando quais os
         * controllers e actions são proibidos. Dica: use allow() sempre.
         *
         * Obs.: Auth::allow() sobrescreve Auth::deny().
         */
            $this->auth->allow(array(
                "site" => array(
                    //"index"
                ),
            ));

        // Use a linha abaixo somente se você deseja deslogar o usuário após
        // x minutos de inatividade. Não use este comando para permitir inatividade
        // $this->auth->expireTime("10"); // tempo em minutos

        // Após login com sucesso, para onde o usuário deve ser redirecionado
        $this->auth->redirectTo( array("controller" => "site", "action" => "index") );

        // Redirecionamento automático para última página acessada (opcional)
        $this->auth->autoRedirect(true);

        // Qual é a página de login
        $this->auth->loginPage( array("controller" => "site", "action" => "login") );

        // Mensagem de erro: dados incorretos
        $this->auth->errorMessage("Seus dados estão incorretos!");

        // Mensagem de erro: quando usuário tenta acessar action proibida
        $this->auth->deniedMessage("Você não tem permissão de acesso!");

        // Qual é o model que contém username e password dos usuários para login
        $this->auth->model("Usuario");
    }
    
}
?>