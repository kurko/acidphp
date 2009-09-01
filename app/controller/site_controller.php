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
    var $siteTitle = "AcidPHP";

    function index(){
        //pr($_SESSION);
        //pr($this->data);
        $this->pageTitle = "Página principal";

        //pr( $this->params );

    }

    function save(){
        pr( $this->Usuario->saveAll( $this->data ) );
        pr($this->data);
    }

    function listar(){
        $usuarios = $this->Usuario->paginate(array(
            "fields" => array("Usuario.nome"),
            "limit" => 20,
            //"page" => $this->params["args"]["page"]
        ));

        //pr($this->params);

        $this->set("usuarios", $usuarios);
    }

    function deletar($id){

        //$this->Usuario->id = $id;
        if( $this->Usuario->deleteAll( $id ) )
            echo 'deu';
        else
            echo 'não deu';

        $this->autoRender = false;
        
    }

    function editar($id){

        //pr( $_SESSION);
        //pr( $this->params);
        //$this->Usuario->id = $id;
        //$usuario = $this->Usuario->find($id);
        //pr($usuario);
        $this->set("id", $id);
    }

    function edit(){
        $this->autoRender = false;

        if($this->data){
            //pr($this->data);
            $this->Usuario->saveAll($this->data);
        }

    }


}
?>