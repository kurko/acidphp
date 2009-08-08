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

    var $uses = array("Usuario","Tarefa");

    function index(){

        /*
         * Código de exemplo
         */
        $temp = $this->Usuario->find(array(
                                        'order' => array('Tarefa.id ASC'),
                                        'limit' => '3',
                                    ),
                                    "all"
        );

        $this->set('temp', $temp);
    }

    function listar($id="nao", $id2="naotb"){
        
    }

    function save(){
        pr( $this->data );
        pr( $this->Usuario->saveAll( $this->data ) );
    }

    function savetarefa(){
        pr( $this->Tarefa->saveAll( $this->data ) );

        $this->render("save");
    }

    function login(){
        
    }

    function outro(){

    }

    function outro2(){
        
    }

}
?>