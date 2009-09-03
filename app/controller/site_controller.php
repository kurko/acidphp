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
        $this->pageTitle = "Página principal";

        $this->Usuario->update( array("email"=>"123@123.com"), "1" );

        //pr( $this->params );

    }

    function save(){
        pr( $this->Usuario->save( $this->data ) );
        pr( $this->data );
        pr( $this->Usuario->countRows() );
    }

    function listar(){
        
        $usuarios = $this->Usuario->paginate(array(
            "fields" => array("Usuario.nome"),
            "limit" => 2,
            //"page" => $this->params["args"]["page"]
        ));
        $this->set("usuarios", $usuarios);
    }

    function deletar($id){

        if( $this->Usuario->deleteAll( $id ) )
            echo 'deu';
        else
            echo 'não deu';

        $this->autoRender = false;
        
    }

    /**
     * Para edição de usuário num formulário, basta criar um campo no formHelper
     * chamado id. Simples assim. Não precisa dar find() nem nada. O Helper
     * faz tudo sozinho.
     */
    function editar($id){
        $this->set("id", $id);
    }


    function edit(){
        $this->autoRender = false;

        /**
         * Se o id já existe, atualiza automaticamente.
         */
        if($this->data){
            pr($this->data);
            $this->Usuario->save($this->data);
        }

    }


}
?>