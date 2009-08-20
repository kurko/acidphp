<?php
/**
 * Controller padrão da aplicação
 *
 * @package Controller
 * @name Main
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @since v0.1 18/07/2009
 */

class SiteController extends AppController
{

    var $uses = array("Usuario", "Tarefa", "Idade");

    function index(){
        
    }

    function save(){
        pr( $this->Usuario->saveAll( $this->data ) );
    }

    function listar(){
        $usuarios = $this->Usuario->find(array(
            "limit" => 10
        ));

        $this->set("usuarios", $usuarios);
    }


}
?>