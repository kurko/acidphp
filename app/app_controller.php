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

    /**
     * beforeFilter() é chamado automaticamente sempre antes de qualquer action
     */
    function beforeFilter(){


    }
    
}
?>